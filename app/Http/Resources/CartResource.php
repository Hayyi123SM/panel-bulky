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

        if ($this->shipping_provider === "Forwarder") {
            $insurance_amount =  $this->requirement_provider['transport_name'] === 'LAND TRANSPORT' ? (0.125 / 100) * $this->total_price : (0.2 / 100) * $this->total_price;
        }

        $total = $this->total_price + $shipping_cost - ($this->discount_amount ?? 0);

        $isCheckout = request()->filled('mode') && request('mode') == 'checkout';

        // Filter items sesuai mode
        $filteredItems = $isCheckout
            ? $this->items->where('is_selected', true)->load('product')
            : $this->items->load('product');

        if ($isCheckout && $tax->enabled) {
            $total += $this->tax_amount;
        }

        // Group items by packaging_type untuk response
        $grouped = $filteredItems->groupBy(fn($item) => $item->product?->packaging_type ?? 'unknown');
        $packaging_types = [
            'palet' => [],
            'container' => [],
        ];
        foreach ($grouped as $type => $items) {
            $packaging_types[$type] = CartItemResource::collection($items->values());
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
                'with_insurance' => isset($insurance_amount) ? $total + $insurance_amount : $total,
            ],
            'notes' => $this->notes,
            'shipping_method' => $this->shipping_method,
            'shipping_cost' => [
                'numeric' => $shipping_cost,
                'formatted' => 'Rp ' . number_format($shipping_cost, 0, ',', '.'),
                'insurance_amount' => $insurance_amount ?? 0,
            ],
            'payment_method' => $this->payment_method,
            'items_count' => $this->items_count,
            'address' => new AddressResource($this->address),
            'items' => $isCheckout ? CartItemResource::collection($filteredItems) : $packaging_types,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
