<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class ClearAllMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus semua status maintenance (global dan per-route)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Hapus global
        Cache::forget('global_api_maintenance');
        $this->info('✅ Global maintenance dimatikan.');

        // Ambil semua nama route
        $routes = collect(Route::getRoutes())
            ->filter(fn($route) => $route->getName())
            ->map(fn($route) => $route->getName())
            ->unique();

        $cleared = 0;

        foreach ($routes as $name) {
            if (Cache::has("route_maintenance:$name")) {
                Cache::forget("route_maintenance:$name");
                $cleared++;
            }
        }

        $this->info("✅ $cleared route maintenance status dihapus.");

        return 0;
    }
}
