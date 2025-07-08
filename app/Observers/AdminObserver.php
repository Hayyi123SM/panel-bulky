<?php

namespace App\Observers;

use App\Models\Admin;
use Illuminate\Support\Facades\App;

class AdminObserver
{
    /**
     * Handle the Admin "creating" event.
     */
    public function creating(Admin $admin): void
    {
        if (App::runningInConsole()) {
            return;
        }

        if (auth()->user()->is_dev === 1) {
            $admin->api_key = $admin->createApiKey();
            $admin->is_dev = 1;
        }
    }
}
