<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\BlogObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(BlogObserver::class)]
class Blog extends Model
{
    use HasUuids, SoftDeletes, HasSlug;

    protected $fillable = [
        'judul_id',
        'judul_en',
        'slug_id',
        'slug_en',
        'konten_id',
        'konten_en',
        'highlight_id',
        'highlight_en',
        'featured_image_url',
        'kategori_id',
        'meta_title_id',
        'meta_title_en',
        'meta_description_id',
        'meta_description_en',
        'meta_keywords',
        'is_active',
        'view_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'view_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'kategori_id');
    }
}
