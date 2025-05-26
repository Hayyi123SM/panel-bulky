<?php

namespace App\Http\Resources;

use App\Models\PaymentMethodGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PaymentMethodGroup */
class PaymentMethodGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group' => $this->name,
            'methods' => PaymentMethodResource::collection($this->paymentMethods()->where('is_active', true)->get()),
        ];
    }
}
