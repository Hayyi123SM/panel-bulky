<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RouteMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:maintenance {route} {mode : on|off}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aktifkan atau nonaktifkan maintenance untuk route tertentu (berdasarkan route name)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $route = $this->argument('route');
        $mode = $this->argument('mode');

        if ($mode === 'on') {
            Cache::forever("route_maintenance:$route", true);
            $this->info("✅ Route '$route' masuk maintenance mode.");
        } elseif ($mode === 'off') {
            Cache::forget("route_maintenance:$route");
            $this->info("✅ Route '$route' kembali normal.");
        } else {
            $this->error("Gunakan mode 'on' atau 'off'");
        }
    }
}
