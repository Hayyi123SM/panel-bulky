<?php

namespace App\Http\Resources;

use App\Enums\InvoiceStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $paidAmount = $this->resource->invoices()->whereStatus(InvoiceStatusEnum::PAID)->sum('amount');
        $cancelable = now()->diffInMinutes($this->created_at) < 60 && $this->user_id === $request->user()->id;
        $total = $this->total_price;
        $totalPrice = $this->total_price - $this->shipping?->shipping_cost;
        $remaining = $total - $paidAmount;

        $statusLabel = $this->order_status->getLabel();
        $statusDescription = $this->order_status->getDescription();

        if($this->order_status == OrderStatusEnum::Shipped && $this->shipping_method == ShippingMethodEnum::SELF_PICKUP){
            $statusLabel = 'Siap di ambil.';
            $statusDescription = 'Paket sudah siap di ambil.';
        }

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date->format('d F Y'),
            'tax_enabled' => $this->is_tax_active,
            'tax_rate' => [
                'numeric' => (int)$this->tax_rate,
                'formatted' => (int)$this->tax_rate . '%',
            ],
            'tax_amount' => [
                'numeric' => (int)$this->tax_amount,
                'formatted' => 'Rp ' . number_format($this->tax_amount, 0, ',', '.'),
            ],
            'total_price' => [
                'numeric' => $totalPrice,
                'formatted' => 'Rp ' . number_format($totalPrice, 0, ',', '.'),
            ],
            'paid_amount' => [
                'numeric' => $paidAmount,
                'formatted' => 'Rp ' . number_format($paidAmount, 0, ',', '.'),
            ],
            'remaining_amount' => [
                'numeric' => $remaining,
                'formatted' => 'Rp ' . number_format($remaining, 0, ',', '.'),
            ],
            'total' => [
                'numeric' => $this->total_price,
                'formatted' => 'Rp ' . number_format($this->total_price, 0, ',', '.'),
            ],
            'payment_status' => [
                'value' => $this->payment_status->value,
                'label' => $statusLabel,
                'description' => $statusDescription,
                'color' => $this->payment_status->getColor(),
            ],
            'order_status' => [
                'value' => $this->order_status->value,
                'label' => $this->order_status->getLabel(),
                'description' => $this->order_status->getDescription(),
                'color' => $this->order_status->getColor(),
            ],
            'notes' => $this->notes ?? '-',
            'has_reviewed' => $this->has_reviewed,
            'cancelable' => $cancelable,
            'invoices_count' => $this->invoices_count,
            'items_count' => $this->items_count,
            'shipping_address' => $this->shipping_address,
            'expired_at' => [
                'date' => !is_null($this->payment_expired_at) ? $this->payment_expired_at->toDateTimeString() : null,
                'human' => !is_null($this->payment_expired_at)? $this->payment_expired_at->diffForHumans() : null,
            ],
            'user' => new UserResource($this->user),
            'items' => OrderItemResource::collection($this->items),
            'invoices' => InvoiceResource::collection($this->invoices),
            'shipping_method' => [
                'value' => $this->shipping_method?->value,
                'label' => $this->shipping_method?->getLabel()
            ],
            'shipping' => new OrderShippingResource($this->shipping)
        ];
    }
}
