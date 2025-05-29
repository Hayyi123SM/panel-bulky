<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SubDistrict extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'district_id',
        'name',
        'code',
        'postal_code',
    ];

    protected $appends = [
        'province_id',
        'city_id'
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function provinceId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->district->city->province_id,
        );
    }

    public function cityId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->district->city_id,
        );
    }

    public function formattedLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->name . ' - (' . $this->postal_code . ')',
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Sub District')
            ->logOnly(['district_id', 'name', 'code', 'postal_code'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Sub District has been {$eventName}");
    }
}
