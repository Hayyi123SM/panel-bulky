<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentMethod extends Model
{
    use SoftDeletes, HasUuids, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'is_active',
        'payment_method_group_id'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
        ];
    }

    public function paymentMethodGroup(): BelongsTo
    {
        return $this->belongsTo(PaymentMethodGroup::class, 'payment_method_group_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Metode Pembayaran')
            ->logOnly(['name', 'code', 'logo', 'is_active', 'payment_method_group_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Metode Pembayaran has been {$eventName}");
    }
}
