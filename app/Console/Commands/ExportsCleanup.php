<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportsCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:cleanup {--days= : Number of days to retain (overrides EXPORT_RETENTION_DAYS)} {--dry-run : Show files that would be deleted without deleting} {--path=exports : Storage path to scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup exported files older than configured retention days';

    public function handle()
    {
        $envDays = (int) env('EXPORT_RETENTION_DAYS', 14);
        $days = (int) $this->option('days') ?: $envDays;
        $dryRun = (bool) $this->option('dry-run');
        $path = $this->option('path') ?: 'exports';

        $this->info("Starting exports cleanup. Path={$path}, retention={$days} days, dry-run=" . ($dryRun ? 'yes' : 'no'));

        $threshold = now()->subDays($days)->getTimestamp();

        $files = Storage::disk('local')->allFiles($path);

        $toDelete = [];
        $totalSize = 0;

        foreach ($files as $file) {
            try {
                $lastModified = Storage::disk('local')->lastModified($file);
            } catch (\Exception $e) {
                // Skip files we cannot inspect
                continue;
            }

            if ($lastModified <= $threshold) {
                $size = 0;
                try {
                    $size = Storage::disk('local')->size($file);
                } catch (\Exception $e) {
                    // ignore size failures
                }
                $toDelete[] = ['path' => $file, 'size' => $size, 'lastModified' => $lastModified];
                $totalSize += $size;
            }
        }

        if (empty($toDelete)) {
            $this->info('No exported files older than ' . $days . ' days found.');
            return 0;
        }

        $this->line('Found ' . count($toDelete) . ' file(s) to delete. Total size: ' . $this->humanFilesize($totalSize));

        foreach ($toDelete as $item) {
            $this->line(' - ' . $item['path'] . ' (' . $this->humanFilesize($item['size']) . ')');
        }

        if ($dryRun) {
            $this->info('Dry-run mode enabled, no files were deleted.');
            return 0;
        }

        $deletedCount = 0;
        foreach ($toDelete as $item) {
            try {
                if (Storage::disk('local')->exists($item['path'])) {
                    Storage::disk('local')->delete($item['path']);
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                $this->error('Failed to delete ' . $item['path'] . ': ' . $e->getMessage());
            }
        }

        // Cleanup empty directories under the path
        try {
            $dirs = Storage::disk('local')->allDirectories($path);
            foreach ($dirs as $dir) {
                $filesInDir = Storage::disk('local')->files($dir);
                $subdirs = Storage::disk('local')->directories($dir);
                if (empty($filesInDir) && empty($subdirs)) {
                    Storage::disk('local')->deleteDirectory($dir);
                }
            }
        } catch (\Exception $e) {
            // ignore cleanup errors
        }

        $this->info("Deleted {$deletedCount} file(s). Freed: " . $this->humanFilesize($totalSize));

        return 0;
    }

    protected function humanFilesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor == 0) return $bytes . ' B';
        return sprintf("%.{$decimals}f %sB", $bytes / pow(1024, $factor), @$sz[$factor]);
    }
}
