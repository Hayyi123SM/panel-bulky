<?php

namespace App\Http\Resources;

use App\Enums\ShippingMethodEnum;
use App\Models\Cart;
use App\Settings\PpnSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Cart */
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tax = app(PpnSettings::class);
        $shipping_cost = $this->shipping_method == ShippingMethodEnum::COURIER_PICKUP ? $this->shipping_cost : 0;

        $total = $this->total_price + $shipping_cost - ($this->discount_amount ?? 0);

        $items = $this->items->load('product');

        if(request()->filled('mode') && \request('mode') == 'checkout'){
            $items = $this->items->where('is_selected', true)->load('product');
            if ($tax->enabled) {
                $total += $this->tax_amount;
            }
        }

        return [
            'id' => $this->id,
            'total_price' => [
                'numeric' => $this->total_price,
                'formatted' => 'Rp ' . number_format($this->total_price, 0, ',', '.'),
            ],
            'coupon_code' => $this->coupon_code,
            'tax_enabled' => $tax->enabled,
            'tax_rate' => [
                'numeric' => $tax->rate,
                'formatted' => $tax->rate . '%',
            ],
            'tax_amount' => [
                'numeric' => $this->tax_amount ?? 0,
                'formatted' => 'Rp ' . number_format($this->tax_amount, 0, ',', '.'),
            ],
            'discount_amount' => [
                'numeric' => $this->discount_amount ?? 0,
                'formatted' => 'Rp ' . number_format($this->discount_amount, 0, ',', '.'),
            ],
            'total' => [
                'numeric' => $total,
                'formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
            ],
            'notes' => $this->notes,
            'shipping_method' => $this->shipping_method,
            'shipping_cost' => [
                'numeric' => $shipping_cost,
                'formatted' => 'Rp ' . number_format($shipping_cost, 0, ',', '.'),
            ],
            'payment_method' => $this->payment_method,
            'items_count' => $this->items_count,
            'address' => new AddressResource($this->address),
            'items' => CartItemResource::collection($items),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
