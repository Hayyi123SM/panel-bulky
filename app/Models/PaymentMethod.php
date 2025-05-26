<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes, HasUuids;

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
}
