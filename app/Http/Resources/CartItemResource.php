<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CartItem */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Check product availability
        $product = $this->product;
        $isAvailable = $product && $product->is_active && !$product->sold_out;

        $availabilityStatus = 'available';
        $statusMessage = null;

        if ($product) {
            if (!$product->is_active) {
                $availabilityStatus = 'inactive';
                $statusMessage = 'Produk tidak tersedia';
            } elseif ($product->sold_out) {
                $availabilityStatus = 'sold_out';
                $statusMessage = 'Produk sudah terjual';
            }
        }

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

            // Product availability status for FE
            'is_available' => $isAvailable,
            'can_checkout' => $isAvailable,
            'availability_status' => $availabilityStatus,
            'status_message' => $statusMessage,

            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,

            'cart' => new CartResource($this->whenLoaded('cart')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
