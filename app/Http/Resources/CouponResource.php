<?php

namespace App\Http\Resources;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Coupon */
class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'discount_type' => [
                'value' => $this->discount_type->value,
                'label' => $this->discount_type->getLabel(),
            ],
            'discount_value' => (float)$this->discount_value,
            'expiry_date' => $this->expiry_date->toDateString(),
            'minimum_purchase' => $this->minimum_purchase,
            'usage_limit' => $this->usage_limit,
            'usages_count' => $this->usages_count,
        ];
    }
}
