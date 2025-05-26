<?php

namespace App\Observers;

use App\Models\Video;

class VideoObserver
{
    public function creating(Video $video): void
    {

    }

    public function created(Video $video): void
    {
    }

    public function updating(Video $video): void
    {
    }

    public function updated(Video $video): void
    {
    }

    public function saving(Video $video): void
    {
    }

    public function saved(Video $video): void
    {
    }

    public function deleting(Video $video): void
    {
    }

    public function deleted(Video $video): void
    {
    }

    public function restoring(Video $video): void
    {
    }

    public function restored(Video $video): void
    {
    }

    public function retrieved(Video $video): void
    {
    }

    public function forceDeleting(Video $video): void
    {

    }

    public function forceDeleted(Video $video): void
    {
        \Storage::disk('public')->delete($video->thumbnail);
        \Storage::disk('public')->delete($video->path);
    }
}
