<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\VideoCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(VideoCategoryObserver::class)]
class VideoCategory extends Model
{
    use SoftDeletes, HasUuids, HasSlug;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function videos(): HasMany
    {
        return $this->hasMany(VideoCategory::class);
    }
}
