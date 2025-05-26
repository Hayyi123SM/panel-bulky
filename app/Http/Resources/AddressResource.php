<?php

namespace App\Http\Resources;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Address */
class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'address' => $this->address,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

//            'province_id' => $this->province_id,
//            'city_id' => $this->city_id,
//            'district_id' => $this->district_id,
//            'sub_district_id' => $this->sub_district_id,

//            'formatted_area' => $this->formatted_area,

            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_primary' => $this->is_primary,

//            'subDistrict' => new SubDistrictResource($this->subDistrict),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
