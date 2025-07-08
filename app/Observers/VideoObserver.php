<?php

namespace App\Observers;

use App\Models\Video;
use Illuminate\Support\Facades\Storage;

class VideoObserver
{
    public function forceDeleted(Video $video): void
    {
        Storage::disk('public')->delete($video->thumbnail);
        Storage::disk('public')->delete($video->path);
    }
}
