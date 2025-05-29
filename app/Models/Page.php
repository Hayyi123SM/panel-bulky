<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use SoftDeletes, HasUuids, HasTranslations, LogsActivity;

    public array $translatable = ['title_trans', 'content_trans'];

    protected $fillable = [
        'title',
        'title_trans',
        'slug',
        'content',
        'content_trans',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Page')
            ->logOnly(['title', 'title_trans', 'slug', 'content', 'content_trans'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Page has been {$eventName}");
    }
}
