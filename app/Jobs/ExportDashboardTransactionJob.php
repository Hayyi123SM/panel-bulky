<?php

namespace App\Jobs;

use App\Exports\DashboardOrderCustomExport;
use App\Notifications\DashboardTransactionExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Jobs\DeleteExportFileJob;

class ExportDashboardTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $filters;

    public function __construct($user, array $filters = [])
    {
        $this->user = $user;
        $this->filters = $filters;
    }

    public function handle(): void
    {
        $user = $this->user;
        $filters = $this->filters ?? [];

        $filename = $user->id . '_export_transactions_' . now()->format('Ymd_His') . '.xlsx';
        $relativePath = "exports/{$user->id}/{$filename}";

        Storage::disk('local')->makeDirectory("exports/{$user->id}");

        // Export data ke storage
        Excel::store(new DashboardOrderCustomExport($filters), $relativePath, 'local');

        // Signed URL untuk download (berlaku 7 hari)
        $signedUrl = URL::temporarySignedRoute(
            'exports.download',
            now()->addDays(7),
            [
                'user_id' => $user->id,
                'filename' => $filename,
            ]
        );

        $user->notify(new DashboardTransactionExportReady($filename, $signedUrl));

        // Hapus file otomatis setelah retention period
        $retentionDays = (int) env('EXPORT_RETENTION_DAYS', 14);
        DeleteExportFileJob::dispatch($relativePath)
            ->delay(now()->addDays($retentionDays));
    }
}
