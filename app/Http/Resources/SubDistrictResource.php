<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\SubDistrict */
class SubDistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'postal_code' => $this->postal_code,
            'formatted_label' => $this->formatted_label,
            'district' => new DistrictResource($this->district),
        ];
    }
}
