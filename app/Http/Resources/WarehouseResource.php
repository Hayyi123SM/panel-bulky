<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Warehouse */
class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'contact_info' => $this->contact_info,
            'location' => [
                'province' => $this->subDistrict->district->city->province->name,
                'city' => $this->subDistrict->district->city->name,
                'district' => $this->subDistrict->district->name,
                'sub_district' => $this->subDistrict->name,
            ],
        ];
    }
}
