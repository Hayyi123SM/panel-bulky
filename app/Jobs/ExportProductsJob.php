<?php

namespace App\Jobs;

use App\Exports\ProductCustomExport;
use App\Notifications\ProductExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Jobs\DeleteExportFileJob;

class ExportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $filters;

    /**
     * Create a new job instance.
     */
    public function __construct($user, array $filters = [])
    {
        $this->user = $user;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user;
        $filters = $this->filters ?? [];

        $filename = $user->id . '_produk_' . now()->format('Ymd_His') . '.xlsx';
        $relativePath = "exports/{$user->id}/{$filename}";

        Storage::disk('local')->makeDirectory("exports/{$user->id}");

        Excel::store(new ProductCustomExport($filters), $relativePath, 'local');

        $signedUrl = URL::temporarySignedRoute(
            'exports.download',
            now()->addDays(7),
            [
                'user_id' => $user->id,
                'filename' => $filename,
            ]
        );

        $user->notify(new ProductExportReady($filename, $signedUrl));

        // Schedule automatic deletion of the exported file after retention period
        $retentionDays = (int) env('EXPORT_RETENTION_DAYS', 14);
        DeleteExportFileJob::dispatch($relativePath)
            ->delay(now()->addDays($retentionDays));
    }
}
