<?php

namespace App\Models;

use App\Enums\ShippingMethodEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\OrderPaymentTypeEnum;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Traits\HasOrderNumber;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use SoftDeletes, HasUuids, HasOrderNumber;

    protected $fillable = [
        'user_id',
        'order_number',
        'order_date',
        'total_price',
        'discount_amount',
        'shipping_address',
        'name',
        'phone_number',
        'latitude',
        'longitude',
        'billing_address',
        'shipping_method',
        'payment_method',
        'payment_status',
        'order_status',
        'delivery_type',
        'delivery_cost',
        'cancel_reason',
        'tracking_number',
        'notes',
        'has_reviewed',
        'payment_expired_at',
        'paid_off_at',
        'is_tax_active',
        'tax_rate',
        'tax_amount',
        'proof_name',
        'proof_description',
        'proof_image',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(OrderShipping::class);
    }

    public function coupon(): HasOne
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function paidAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->invoices->where('status', InvoiceStatusEnum::PAID)->sum('amount'),
        );
    }

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'has_reviewed' => 'boolean',
            'payment_expired_at' => 'datetime',
            'paid_off_at' => 'datetime',
            'order_status' => OrderStatusEnum::class,
            'payment_method' => OrderPaymentTypeEnum::class,
            'payment_status' => OrderPaymentStatusEnum::class,
            'shipping_method' => ShippingMethodEnum::class,
            'is_tax_active' => 'boolean',
        ];
    }
}
