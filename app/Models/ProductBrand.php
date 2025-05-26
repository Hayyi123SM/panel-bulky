<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\ProductBrandObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(ProductBrandObserver::class)]
class ProductBrand extends Model
{
    use SoftDeletes, HasUuids, HasSlug;

    protected $fillable = [
        'wms_id',
        'name',
        'slug',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_brand_pivot', 'product_brand_id', 'product_id');
    }
}
