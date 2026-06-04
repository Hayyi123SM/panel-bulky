<?php

namespace App\Models;

use App\Observers\HomeHeroObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(HomeHeroObserver::class)]
class HomeHero extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
