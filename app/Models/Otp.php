<?php

namespace App\Models;

use App\Observers\OtpObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([OtpObserver::class])]
class Otp extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'user_id',
        'otp_code',
        'expired_at',
        'is_used',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'expired_at' => 'timestamp',
            'is_used' => 'boolean',
        ];
    }
}
