<?php

namespace App\Http\Resources;

use App\Models\OrderShipping;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderShipping */
class OrderShippingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipping_cost' => 'Rp ' . number_format($this->shipping_cost, 0, ',', '.'),
            'vehicle_type' => $this->vehicle_type,
//            'booking_id' => $this->booking_id,
//            'extra_helper_id' => $this->extra_helper_id,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
            'order_id' => $this->order_id,
            'tracking_url' => $this->tracking_url,
            'show_tracking_url' => !is_null($this->tracking_url)
        ];
    }
}
