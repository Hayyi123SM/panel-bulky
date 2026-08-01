<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Services\WMS\ApiRequest;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        if (! empty($data['pdf_url']) && str_starts_with($data['pdf_url'], 'http')) {
            try {
                $pdfUrl = $data['pdf_url'];
                $pdfName = 'product-'.uniqid().'.pdf';
                $relativePath = 'products/pdf/'.$pdfName;
                $pdfContents = Http::timeout(10)->get($pdfUrl)->body();
                // Validate PDF header
                if (substr($pdfContents, 0, 4) !== '%PDF') {
                    throw new \Exception('File bukan PDF valid');
                }
                Storage::disk('public')->put($relativePath, $pdfContents);
                $data['pdf_file'] = $relativePath;
                $data['pdf_url'] = null;
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Gagal mengunduh PDF dari URL')
                    ->body('File PDF tidak dapat diunduh atau tidak valid. Silakan cek URL atau upload manual.')
                    ->danger()
                    ->send();
                // Kosongkan pdf_file dan pdf_url agar validasi tetap jalan
                $data['pdf_file'] = null;
                $data['pdf_url'] = null;
            }
        } elseif (! empty($data['pdf_file'])) {
            // Rename/move upload agar nama file diawali product-
            $file = $data['pdf_file'];
            $ext = pathinfo($file, PATHINFO_EXTENSION) ?: 'pdf';
            $newName = 'product-'.uniqid().'.'.$ext;
            $newPath = 'products/pdf/'.$newName;
            Storage::disk('public')->move($file, $newPath);
            $data['pdf_file'] = $newPath;
        }
        // Pastikan images tetap array
        if (isset($data['images']) && ! is_array($data['images'])) {
            $data['images'] = (array) $data['images'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $templateId = $record->id_pallet ?? null;

        if (! $templateId) {
            Log::info('[SyncB2B] id_pallet kosong, endpoint tidak di-hit');

            return;
        }

        $this->notifyWmsReady($templateId);
        $this->syncToB2B($templateId);
    }

    protected function notifyWmsReady($templateId): void
    {
        try {
            $service = app(\App\Services\WMS\WMSProductDropdownService::class);
            $service->notifyReady($templateId);

            Log::info("[SyncB2B] Notify WMS ready berhasil untuk id {$templateId}");
        } catch (\Throwable $e) {
            Log::warning("[SyncB2B] Notify WMS ready gagal untuk id {$templateId}: {$e->getMessage()}");
        }
    }

    protected function syncToB2B($templateId): void
    {
        $result = ApiRequest::sendPostRequest("/api/sync-b2b/{$templateId}");

        if (isset($result['error'])) {
            Log::warning("[SyncB2B] Hit endpoint gagal untuk id {$templateId}: {$result['error']}");

            return;
        }

        Log::info("[SyncB2B] Hit endpoint berhasil untuk id {$templateId}");
    }
}
