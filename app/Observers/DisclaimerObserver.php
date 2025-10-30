<?php

namespace App\Observers;

use App\Models\Disclaimer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DisclaimerObserver
{
    public function creating(Disclaimer $disclaimer)
    {
        $disclaimer->title_trans = [
            'id' => $disclaimer->title,
            'en' => $disclaimer->title,
        ];
        $disclaimer->content_trans = [
            'id' => $disclaimer->content,
            'en' => $disclaimer->content,
        ];
        // Generate slug on create only if not already provided
        if (empty($disclaimer->slug)) {
            $disclaimer->slug = $disclaimer->createUniqueSlug($disclaimer->title);
        }
    }

    public function updating(Disclaimer $disclaimer)
    {
        $disclaimer->title_trans = [
            'id' => $disclaimer->title
        ];
        $disclaimer->content_trans = [
            'id' => $disclaimer->content
        ];
        // Only regenerate slug when the title actually changed to avoid uniqueness conflicts
        if ($disclaimer->isDirty('title')) {
            $disclaimer->slug = $disclaimer->createUniqueSlug($disclaimer->title);
        }
    }

    /**
     * After saving (create or update), ensure only one Disclaimer has is_active = true.
     * Policy: ignore trashed records; if current record is activated, deactivate others.
     */
    public function saved(Disclaimer $disclaimer)
    {
        // Only act when the saved model is active
        if (! $disclaimer->is_active) {
            return;
        }

        // Use a cache lock to avoid race conditions when multiple admins try to activate
        try {
            Cache::lock('disclaimer_active_lock', 10)->block(5, function () use ($disclaimer) {
                // Deactivate all other (non-deleted) disclaimers
                Disclaimer::where('is_active', true)
                    ->whereKeyNot($disclaimer->getKey())
                    ->whereNull('deleted_at')
                    ->update(['is_active' => false]);
            });
        } catch (\Throwable $e) {
            // Log warning but don't break the save flow
            Log::warning('DisclaimerObserver: failed to enforce single active disclaimer - ' . $e->getMessage());
        }
    }
}
