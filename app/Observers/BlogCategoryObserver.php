<?php

namespace App\Observers;

use App\Models\BlogCategory;
use App\Models\Traits\HasSlug;

class BlogCategoryObserver
{
    use HasSlug;

    public function creating(BlogCategory $blogCategory): void
    {
        if (empty($blogCategory->slug_id)) {
            $blogCategory->slug_id = $this->createUniqueSlug($blogCategory->nama_id, excludeId: $blogCategory->id, modelClass: BlogCategory::class, slugColumn: 'slug_id');
        }
        if (empty($blogCategory->slug_en)) {
            $blogCategory->slug_en = $this->createUniqueSlug($blogCategory->nama_en, excludeId: $blogCategory->id, modelClass: BlogCategory::class, slugColumn: 'slug_en');
        }
    }

    public function updating(BlogCategory $blogCategory): void
    {
        if ($blogCategory->isDirty('nama_id') && ! $blogCategory->isDirty('slug_id')) {
            $blogCategory->slug_id = $this->createUniqueSlug($blogCategory->nama_id, excludeId: $blogCategory->id, modelClass: BlogCategory::class, slugColumn: 'slug_id');
        }
        if ($blogCategory->isDirty('nama_en') && ! $blogCategory->isDirty('slug_en')) {
            $blogCategory->slug_en = $this->createUniqueSlug($blogCategory->nama_en, excludeId: $blogCategory->id, modelClass: BlogCategory::class, slugColumn: 'slug_en');
        }
    }
}
