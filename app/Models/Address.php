<?php

namespace App\Models;

use App\Observers\AddressObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(AddressObserver::class)]
class Address extends Model
{
    use SoftDeletes, HasUuids, LogsActivity;

    protected $fillable = [
        'user_id',
        'label',
        'name',
        'phone_number',
        'address',
        'sub_district_id',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected $appends = [
        'province_id',
        'city_id',
        'district_id',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class);
    }

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

    public function formattedArea(): Attribute
    {
        return Attribute::make(
            get: function () {
                $province = $this->subDistrict?->district?->city?->province?->name;
                $city = $this->subDistrict?->district?->city?->name;
                $district = $this->subDistrict?->district?->name;
                $subDistrict = $this->subDistrict?->name;
                $postalCode = $this->subDistrict?->postal_code;

                return $province . ', ' . $city . ', ' . $district . ', ' . $subDistrict . ', ' . $postalCode;
            }
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Alamat')
            ->logOnly([
                'user_id',
                'label',
                'name',
                'phone_number',
                'address',
                'sub_district_id',
                'latitude',
                'longitude',
                'is_primary',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Alamat has been {$eventName}");
    }
}
