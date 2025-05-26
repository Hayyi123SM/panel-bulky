<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductStatus extends Model
{
    use SoftDeletes, HasUuids, HasTranslations;

    public array $translatable = ['status_trans'];

    protected $fillable = [
        'wms_id',
        'status',
        'status_trans'
    ];
}
