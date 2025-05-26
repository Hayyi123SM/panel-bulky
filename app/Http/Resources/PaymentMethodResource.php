<?php

namespace App\Http\Resources;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

/** @mixin PaymentMethod */
class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'logo' => $this->logo ? Storage::disk('public')->url($this->logo) : null,
            'is_active' => $this->is_active,
        ];
    }
}
