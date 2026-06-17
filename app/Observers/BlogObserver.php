<?php

namespace App\Observers;

use App\Models\Blog;
use App\Models\Traits\HasSlug;

class BlogObserver
{
    use HasSlug;

    public function creating(Blog $blog): void
    {
        if (empty($blog->slug_id)) {
            $blog->slug_id = $this->createUniqueSlug($blog->judul_id, excludeId: $blog->id, modelClass: Blog::class, slugColumn: 'slug_id');
        }
        if (empty($blog->slug_en)) {
            $blog->slug_en = $this->createUniqueSlug($blog->judul_en, excludeId: $blog->id, modelClass: Blog::class, slugColumn: 'slug_en');
        }
    }

    public function updating(Blog $blog): void
    {
        if ($blog->isDirty('judul_id') && ! $blog->isDirty('slug_id')) {
            $blog->slug_id = $this->createUniqueSlug($blog->judul_id, excludeId: $blog->id, modelClass: Blog::class, slugColumn: 'slug_id');
        }
        if ($blog->isDirty('judul_en') && ! $blog->isDirty('slug_en')) {
            $blog->slug_en = $this->createUniqueSlug($blog->judul_en, excludeId: $blog->id, modelClass: Blog::class, slugColumn: 'slug_en');
        }
    }
}
