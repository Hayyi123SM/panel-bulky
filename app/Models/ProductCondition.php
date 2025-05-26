<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\ProductConditionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(ProductConditionObserver::class)]
class ProductCondition extends Model
{
    use SoftDeletes, HasUuids, HasSlug, HasTranslations;

    public array $translatable = ['title_trans'];

    protected $fillable = [
        'wms_id',
        'title',
        'title_trans',
        'slug',
    ];
}
