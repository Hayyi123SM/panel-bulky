<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderItem */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => [
                'numeric' => $this->price,
                'formatted' => number_format($this->price, 0,',', '.'),
            ],
            'discount_amount' => [
                'numeric' => $this->discount_amount,
                'formatted' => number_format($this->discount_amount, 0,',', '.'),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'order_id' => $this->order_id,

            'product' => new ProductResource($this->product),
        ];
    }
}
