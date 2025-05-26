<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Warehouse extends Model
{
    use HasUuids, SoftDeletes, Notifiable;

    protected $fillable = [
        'name',
        'address',
        'email',
        'sub_district_id',
        'latitude',
        'longitude',
        'contact_info',
    ];

    public function subDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class);
    }

    protected $appends = ['province_id', 'city_id', 'district_id'];

    public function provinceId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->subDistrict->district->city->province_id,
        );
    }

    public function cityId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->subDistrict->district->city_id,
        );
    }

    public function districtId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->subDistrict->district_id,
        );
    }
}
