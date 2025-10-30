<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\DisclaimerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(DisclaimerObserver::class)]
class Disclaimer extends Model
{
    use SoftDeletes, HasSlug, HasUuids, HasTranslations, LogsActivity;

    public array $translatable = ['title_trans', 'content_trans'];

    protected $fillable = [
        'title',
        'title_trans',
        'slug',
        'content',
        'content_trans',
        'is_active',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Disclaimer')
            ->logOnly(['title', 'title_trans', 'slug', 'content', 'content_trans'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Disclaimer has been {$eventName}");
    }
}
