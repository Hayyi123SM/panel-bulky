<?php

namespace App\Models;

use App\Observers\HomeHeroObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(HomeHeroObserver::class)]
class HomeHero extends Model
{
    use HasUuids, SoftDeletes, HasTranslations;

    public array $translatable = ['title_trans', 'subtitle_trans'];

    protected $fillable = [
        'title_trans',
        'subtitle_trans',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'title_trans' => 'json',
            'subtitle_trans' => 'json',
        ];
    }
}
