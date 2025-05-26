<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CartItem */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => [
                'numeric' => $this->price,
                'formatted' => 'Rp ' . number_format($this->price, 0, ',', '.'),
            ],
            'total' => [
                'numeric' => $this->price * $this->quantity,
                'formatted' => 'Rp ' . number_format($this->price * $this->quantity, 0, ',', '.'),
            ],

            'discount_amount' => [
                'numeric' => $this->discount_amount,
                'formatted' => 'Rp ' . number_format($this->discount_amount, 0, ',', '.'),
            ],

            'has_discount' => $this->discount_amount > 0,
            'is_selected' => $this->is_selected,

            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,

            'cart' => new CartResource($this->whenLoaded('cart')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
