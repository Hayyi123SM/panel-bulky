<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    use SoftDeletes, HasUuids, HasSlug, Hastranslations;

    public array $translatable = ['name_trans', 'description_trans'];

    protected $fillable = [
        'wms_id',
        'images',
        'name',
        'name_trans',
        'slug',
        'id_pallet',
        'price',
        'price_before_discount',
        'total_quantity',
        'pdf_file',
        'description',
        'description_trans',
        'is_active',
        'warehouse_id',
        'product_category_id',
        'product_condition_id',
        'product_status_id',
        'sold_out',
        'vehicle_type_id'
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productCondition(): BelongsTo
    {
        return $this->belongsTo(ProductCondition::class);
    }

    public function productStatus(): BelongsTo
    {
        return $this->belongsTo(ProductStatus::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(ProductBrand::class, 'product_brand_pivot', 'product_id', 'product_brand_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_product');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'images' => 'array',
            'sold_out' => 'boolean',
            'name_trans' => 'json',
            'description_trans' => 'json',
        ];
    }
}
