<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Testimony extends Model
{
    use SoftDeletes, HasUuids, LogsActivity;

    protected $fillable = [
        'name',
        'label',
        'content',
        'image',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Testimoni')
            ->logOnly(['name', 'label', 'content', 'image'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Testimoni has been {$eventName}");
    }
}
