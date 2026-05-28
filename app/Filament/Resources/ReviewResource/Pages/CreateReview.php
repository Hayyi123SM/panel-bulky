<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReview extends CreateRecord
{
    protected static string $resource = ReviewResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract images dari form sebelum create review
        $images = $data['review_images'] ?? [];
        unset($data['review_images']);

        // Simpan di session/temp untuk digunakan di afterCreate
        session()->put('_review_images_temp', $images);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync images ke ReviewImage setelah review dibuat
        $record = $this->getRecord();
        $images = session()->pull('_review_images_temp', []);

        foreach ($images as $imagePath) {
            $record->images()->create(['path' => $imagePath]);
        }
    }
}
