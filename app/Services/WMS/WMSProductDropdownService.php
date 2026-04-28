<?php

namespace App\Services\WMS;

use App\Services\WMS\Contracts\ProductDropdownServiceInterface;
use Illuminate\Support\Facades\Http;

class WMSProductDropdownService implements ProductDropdownServiceInterface
{
    protected array $waitingCache = [];

    public function getDropdownOptions(): array
    {
        $data = $this->fetchWaiting();
        // id => name_document
        return collect($data)
            ->mapWithKeys(fn($item) => [$item['id'] => $item['name_document']])
            ->toArray();
    }

    public function getDropdownDetail($id): ?array
    {
        $data = $this->fetchWaiting();
        foreach ($data as $item) {
            if ((string)$item['id'] === (string)$id) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Call /api/cargo-online/waiting and cache result per request.
     */
    protected function fetchWaiting(): array
    {
        if (!empty($this->waitingCache)) {
            return $this->waitingCache;
        }
        $baseUrl = config('wms.base_url');
        $token = config('wms.api_token');
        $url = rtrim($baseUrl, '/') . '/api/cargo-online/waiting';
        $response = Http::withToken($token)->get($url);
        if ($response->failed()) {
            return [];
        }
        $data = $response->json();
        // If wrapped in 'data', unwrap
        if (is_array($data) && isset($data['data'])) {
            $data = $data['data'];
        }
        $this->waitingCache = is_array($data) ? $data : [];
        return $this->waitingCache;
    }

    /**
     * Notify WMS that a product has been processed (webhook/feedback).
     *
     * @param int|string $id
     * @return bool Success
     */
    public function notifyReady($id): bool
    {
        $baseUrl = config('wms.base_url');
        $token = config('wms.api_token');
        $url = rtrim($baseUrl, '/') . "/api/cargo-online/{$id}/ready";
        $response = Http::withToken($token)->put($url);
        return $response->successful();
    }
}
