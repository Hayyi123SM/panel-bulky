<?php

namespace App\Observers;

use App\Models\StatusPackage;

class StatusPackageObserver
{
    public function creating(StatusPackage $statusPackage): void
    {
        $statusPackage->status = $statusPackage->status_trans;
    }

    public function updating(StatusPackage $statusPackage): void
    {
        if ($statusPackage->isDirty('status_trans')) {
            $statusPackage->status = $statusPackage->status_trans;
        }
    }
}
