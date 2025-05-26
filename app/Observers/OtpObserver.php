<?php

namespace App\Observers;

use App\Models\Otp;

class OtpObserver
{
    public function creating(Otp $otp): void
    {
        $otp->expired_at = now()->addMinutes(10);
    }
}
