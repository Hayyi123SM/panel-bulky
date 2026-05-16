<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\LocaleSwitcher::make()
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        // Handle PDF lama: hapus jika diganti
        if (!empty($data['pdf_file']) && $data['pdf_file'] !== $record->pdf_file) {
            if ($record->pdf_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($record->pdf_file)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($record->pdf_file);
            }
            // Rename/move upload agar nama file diawali product-
            $file = $data['pdf_file'];
            $ext = pathinfo($file, PATHINFO_EXTENSION) ?: 'pdf';
            $newName = 'product-' . uniqid() . '.' . $ext;
            $newPath = 'products/pdf/' . $newName;
            \Illuminate\Support\Facades\Storage::disk('public')->move($file, $newPath);
            $data['pdf_file'] = $newPath;
        }
        // Handle gambar: hapus file lama yang tidak ada di array baru
        $oldImages = $record->images ?? [];
        $newImages = $data['images'] ?? [];
        $deletedImages = array_diff((array) $oldImages, (array) $newImages);
        foreach ($deletedImages as $img) {
            if ($img && \Illuminate\Support\Facades\Storage::disk('public')->exists($img)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($img);
            }
        }
        // Pastikan images tetap array
        if (isset($data['images']) && !is_array($data['images'])) {
            $data['images'] = (array) $data['images'];
        }
        return $data;
    }
}
