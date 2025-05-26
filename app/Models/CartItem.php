<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
        'discount_amount',
        'is_selected',
    ];

    protected function casts(): array
    {
        return [
            'is_selected' => 'boolean',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
