<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteExportFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;

    /**
     * Create a new job instance.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (Storage::disk('local')->exists($this->path)) {
            Storage::disk('local')->delete($this->path);
        }

        // Try to remove parent directory if empty (e.g. exports/{user_id})
        $dir = dirname($this->path);
        $files = Storage::disk('local')->files($dir);
        if (empty($files)) {
            Storage::disk('local')->deleteDirectory($dir);
        }
    }
}
