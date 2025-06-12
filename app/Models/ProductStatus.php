<?php

namespace App\Models;

use App\Observers\ProductStatusObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(ProductStatusObserver::class)]
class ProductStatus extends Model
{
    use SoftDeletes, HasUuids, HasTranslations, LogsActivity;

    public array $translatable = ['status_trans'];

    protected $fillable = [
        'wms_id',
        'status',
        'status_trans'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Status Produk')
            ->logOnly(['wms_id', 'status', 'status_trans'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Status Produk has been {$eventName}");
    }
}
