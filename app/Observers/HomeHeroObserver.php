<?php

namespace App\Observers;

use App\Models\HomeHero;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HomeHeroObserver
{
    public function forceDeleted(HomeHero $homeHero): void
    {
        if ($homeHero->image_path) {
            Storage::disk('public')->delete($homeHero->image_path);
        }
    }

    public function saved(HomeHero $homeHero): void
    {
        if (! $homeHero->is_active) {
            return;
        }

        try {
            Cache::lock('home_hero_active_lock', 10)->block(5, function () use ($homeHero) {
                HomeHero::where('is_active', true)
                    ->whereKeyNot($homeHero->getKey())
                    ->whereNull('deleted_at')
                    ->update(['is_active' => false]);
            });
        } catch (\Throwable $e) {
            Log::warning('HomeHeroObserver: failed to enforce single active hero - ' . $e->getMessage());
        }
    }
}
