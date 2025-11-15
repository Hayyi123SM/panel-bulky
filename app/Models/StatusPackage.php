<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use App\Observers\StatusPackageObserver;

#[ObservedBy(StatusPackageObserver::class)]
class StatusPackage extends Model
{
    use SoftDeletes, HasUuids, HasTranslations, LogsActivity;

    public array $translatable = ['status_trans'];

    protected $fillable = [
        'status',
        'status_trans',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Status Paket')
            ->logOnly(['status', 'status_trans'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Status Paket has been {$eventName}");
    }
}
