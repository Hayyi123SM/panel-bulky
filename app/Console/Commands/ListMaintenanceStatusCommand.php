<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class ListMaintenanceStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menampilkan daftar route yang sedang dalam mode maintenance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $global = Cache::get('global_api_maintenance', false);

        $this->info('=== STATUS MAINTENANCE API ===');
        $this->line('Global Maintenance: ' . ($global ? '🟥 AKTIF' : '✅ OFF'));
        $this->newLine();

        $routes = collect(Route::getRoutes())
            ->filter(fn($route) => $route->getName())
            ->map(fn($route) => $route->getName())
            ->sort()
            ->unique();

        $downRoutes = [];

        foreach ($routes as $name) {
            if (Cache::get("route_maintenance:$name")) {
                $downRoutes[] = $name;
            }
        }

        if (count($downRoutes) > 0) {
            $this->info('Route dalam mode maintenance:');
            foreach ($downRoutes as $route) {
                $this->line("- 🔒 $route");
            }
        } else {
            $this->info('Tidak ada route dalam mode maintenance.');
        }

        return 0;
    }
}
