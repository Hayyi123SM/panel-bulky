<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\BlogCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(BlogCategoryObserver::class)]
class BlogCategory extends Model
{
    use HasUuids, SoftDeletes, HasSlug;

    protected $fillable = [
        'nama_id',
        'nama_en',
        'slug_id',
        'slug_en',
        'is_active',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'kategori_id');
    }
}
