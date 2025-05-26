<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShipping extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'shipping_cost',
        'vehicle_type',
        'booking_id',
        'booking_status',
        'tracking_url',
        'extra_helper_id'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
