<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethodGroup extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
    ];

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'payment_method_group_id');
    }
}
