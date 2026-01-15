<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class DashboardTransactionExportReady extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $fileName;
    public string $downloadUrl;

    public function __construct(string $fileName, string $downloadUrl)
    {
        $this->fileName = $fileName;
        $this->downloadUrl = $downloadUrl;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Export Transaksi Selesai',
            'body'  => 'Export transaksi Anda telah selesai. Klik tombol di bawah untuk mengunduh file.',
            'file_name' => $this->fileName,

            // Filament-specific fields
            'view' => 'filament-notifications::notification',
            'viewData' => [],
            'format' => 'filament',
            'duration' => 'persistent',
            'icon' => 'heroicon-o-check-circle',
            'iconColor' => 'success',
            'status' => 'success',
            'shouldMarkAsRead' => true,
            'shouldOpenUrlInNewTab' => true,

            'actions' => [
                [
                    'name' => 'download_xlsx',
                    'label' => 'Unduh .xlsx',
                    'url' => $this->downloadUrl,
                    'view' => 'filament-actions::link-action',
                    'shouldOpenUrlInNewTab' => true,
                    'shouldMarkAsRead' => true,
                ],
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
