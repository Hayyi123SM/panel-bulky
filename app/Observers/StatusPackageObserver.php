<?php

namespace App\Observers;

use App\Models\StatusPackage;

class StatusPackageObserver
{
    public function creating(StatusPackage $statusPackage): void
    {
        $statusPackage->status = $statusPackage->status_trans;
    }
}
