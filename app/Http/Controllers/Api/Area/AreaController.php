<?php

namespace App\Http\Controllers\Api\Area;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\SubDistrictResource;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;

/**
 * @group Geo
 */
class AreaController extends Controller
{
    /**
     * Provinces
     */
    public function province()
    {
        $provinces = Province::orderBy('name')->get();
        return ProvinceResource::collection($provinces);
    }

    /**
     * Cities
     */
    public function cities(Province $province)
    {
        $cities = City::where('province_id', $province->id)->orderBy('name')->get();
        return CityResource::collection($cities);
    }

    /**
     * Districts
     */
    public function districts(City $city)
    {
        $districts = District::where('city_id', $city->id)->orderBy('name')->get();
        return DistrictResource::collection($districts);
    }

    /**
     * Sub Districts
     */
    public function subDistricts(District $district)
    {
        $subDistricts = SubDistrict::where('district_id', $district->id)->orderBy('name')->get();
        return SubDistrictResource::collection($subDistricts);
    }
}
