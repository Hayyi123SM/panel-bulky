<?php

namespace App\Observers;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Storage;

class PaymentMethodObserver
{
    public function forceDeleted(PaymentMethod $paymentMethod): void
    {
        if ($paymentMethod->logo) {
            Storage::disk('public')->delete($paymentMethod->logo);
        }
    }
}
