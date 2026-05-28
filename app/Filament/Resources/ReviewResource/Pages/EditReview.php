<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(function (Review $record): bool {
                    $imagePaths = $record->images()->pluck('path')->toArray();

                    foreach ($imagePaths as $imagePath) {
                        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                            Storage::disk('public')->delete($imagePath);
                        }
                    }

                    $record->images()->delete();

                    return (bool) $record->forceDelete();
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing review images
        $record = $this->getRecord();
        $existingImages = $record->images()
            ->pluck('path')
            ->toArray();

        $data['review_images'] = $existingImages;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hapus field non-model agar tidak error saat fill ke DB
        unset($data['review_images']);
        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        // Ambil state dari Livewire component (sudah final setelah file dipindah)
        $newImages = array_values(array_filter((array) ($this->data['review_images'] ?? [])));
        $existingImages = $record->images()->pluck('path')->toArray();

        // Hapus file & record yang dihapus user
        $deletedImages = array_diff($existingImages, $newImages);
        foreach ($deletedImages as $imagePath) {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $record->images()->where('path', $imagePath)->delete();
        }

        // Simpan image baru yang belum ada di DB
        $addedImages = array_diff($newImages, $existingImages);
        foreach ($addedImages as $imagePath) {
            $record->images()->create(['path' => $imagePath]);
        }
    }
}
