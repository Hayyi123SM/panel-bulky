<?php

namespace App\Observers;

use App\Models\Banner;
use Filament\Forms\Components\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BannerObserver
{
    public function creating(Banner $banner) {}

    public function updating(Banner $banner) {}

    public function forceDeleted(Banner $banner): void
    {
        Storage::disk('public')->delete($banner->path);
    }
    /**
     * After a banner is saved, ensure only one banner is active per page (and product_type when page = 'product').
     * - Ignore trashed banners
     * - Use a cache lock to reduce race conditions
     */
    public function saved(Banner $banner)
    {
        // Only act when this banner is active
        if (! $banner->is_active) {
            return;
        }

        // Normalize page and product_type for lock key
        $page = is_string($banner->page) ? trim(strtolower($banner->page)) : (string) $banner->page;
        $productType = $banner->product_type !== null ? (string) $banner->product_type : null;

        $lockName = 'banner_active_lock:' . $page;
        if ($page === 'product' && $productType !== null) {
            $lockName .= ':' . preg_replace('/[^a-z0-9_\-]/', '_', strtolower($productType));
        }

        try {
            Cache::lock($lockName, 10)->block(5, function () use ($banner, $page, $productType) {
                $query = Banner::where('is_active', true)
                    ->whereKeyNot($banner->getKey())
                    ->whereNull('deleted_at')
                    ->where('page', $banner->page);

                if ($page === 'product') {
                    // match product_type exactly (including null)
                    if ($productType === null) {
                        $query->whereNull('product_type');
                    } else {
                        $query->where('product_type', $banner->product_type);
                    }
                }

                $affected = $query->update(['is_active' => false]);

                if ($affected > 0) {
                    Log::info(sprintf(
                        'BannerObserver: deactivated %d banner(s) for page=%s%s (triggered_by=%s)',
                        $affected,
                        $page,
                        $page === 'product' ? (" product_type={$productType}") : '',
                        $banner->getKey()
                    ));
                }
            });
        } catch (\Throwable $e) {
            Log::warning('BannerObserver: failed to enforce single active banner - ' . $e->getMessage());
        }
    }
}
