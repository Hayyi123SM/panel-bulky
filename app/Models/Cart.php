<?php

namespace App\Models;

use App\Enums\ShippingMethodEnum;
use App\Enums\OrderPaymentTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'user_id',
        'total_price',
        'coupon_code',
        'discount_amount',
        'tax_amount',
        'notes',
        'shipping_method',
        'shipping_cost',
        'extra_helper_id',
        'payment_method',
        'payment_type',
        'address_id',
        'vehicle_type_id'
    ];

    protected function casts(): array
    {
        return [
            'payment_type' => OrderPaymentTypeEnum::class,
            'shipping_method' => ShippingMethodEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
