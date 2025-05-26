<?php

namespace App\Observers;

use App\Models\VideoCategory;

class VideoCategoryObserver
{
    public function creating(VideoCategory $videoCategory): void
    {
        $videoCategory->slug = $videoCategory->createUniqueSlug($videoCategory->name);
    }
}
