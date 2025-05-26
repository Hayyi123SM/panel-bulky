<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\ProductCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(ProductCategoryObserver::class)]
class ProductCategory extends Model
{
    use SoftDeletes, HasUuids, HasSlug, HasTranslations;

    public array $translatable = ['name_trans'];

    protected $fillable = [
        'wms_id',
        'name',
        'name_trans',
        'slug',
        'icon'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_category', 'product_category_id', 'coupon_id');
    }
}
