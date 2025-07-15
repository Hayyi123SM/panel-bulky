<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ApiMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:maintenance {mode : on|off}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aktifkan atau nonaktifkan maintenance mode global untuk seluruh API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mode = $this->argument('mode');

        if ($mode === 'on') {
            Cache::forever('global_api_maintenance', true);
            $this->info('✅ API masuk ke mode maintenance global.');
        } elseif ($mode === 'off') {
            Cache::forget('global_api_maintenance');
            $this->info('✅ API kembali normal (global off).');
        } else {
            $this->error("Gunakan hanya 'on' atau 'off'");
            return 1;
        }

        return 0;
    }
}
