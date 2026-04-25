<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CreateProduct extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If autofill used and pdf_url is external, download and store locally
        if (!empty($data['product_template_id']) && !empty($data['pdf_url']) && str_starts_with($data['pdf_url'], 'http')) {
            try {
                $pdfUrl = $data['pdf_url'];
                $pdfName = 'product-' . uniqid() . '.pdf';
                $pdfContents = Http::timeout(10)->get($pdfUrl)->body();
                // Validate PDF header
                if (substr($pdfContents, 0, 4) !== '%PDF') {
                    throw new \Exception('File bukan PDF valid');
                }
                $relativePath = 'products/pdfs/' . $pdfName;
                Storage::disk('public')->put($relativePath, $pdfContents);
                $data['pdf_file'] = $relativePath;
                $data['pdf_url'] = null;
            } catch (\Throwable $e) {
                \Filament\Notifications\Notification::make()
                    ->title('Gagal mengunduh PDF dari URL')
                    ->body('File PDF tidak dapat diunduh atau tidak valid. Silakan cek URL atau upload manual.')
                    ->danger()
                    ->send();
                // Kosongkan pdf_file dan pdf_url agar validasi tetap jalan
                $data['pdf_file'] = null;
                $data['pdf_url'] = null;
            }
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $templateId = $record->id_pallet ?? null;
        if ($templateId) {
            try {
                $service = app(\App\Services\WMS\WMSProductDropdownService::class);
                $service->notifyReady($templateId);
            } catch (\Throwable $e) {
                \Filament\Notifications\Notification::make()
                    ->title('Gagal notifikasi ke WMS')
                    ->body('Tidak dapat mengirim feedback ke WMS. Silakan cek koneksi atau hubungi admin.')
                    ->danger()
                    ->send();
            }
        }
    }
}
