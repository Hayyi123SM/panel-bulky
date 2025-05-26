<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    use HasUuids;

    protected $fillable = [
        'city_id',
        'name',
        'code',
    ];

    protected $appends = ['province_id'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function provinceId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->city->province_id,
        );
    }
}
