<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(VideoCategory::class)]
class Video extends Model
{
    use SoftDeletes, HasUuids, LogsActivity;

    protected $fillable = [
        'user_id',
        'video_category_id',
        'title',
        'thumbnail',
        'description',
        'path',
        'type',
        'view_count',
        'order_column',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function videoCategory(): BelongsTo
    {
        return $this->belongsTo(VideoCategory::class);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count', 1);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Video')
            ->logOnly([
                'user_id',
                'video_category_id',
                'title',
                'thumbnail',
                'description',
                'path',
                'type',
                'view_count'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Video has been {$eventName}");
    }
}
