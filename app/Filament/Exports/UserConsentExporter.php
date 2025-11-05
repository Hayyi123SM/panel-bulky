<?php

namespace App\Filament\Exports;

use App\Models\UserConsent;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserConsentExporter extends Exporter
{
    protected static ?string $model = UserConsent::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),

            ExportColumn::make('user.name')
                ->label('Nama Pengguna'),

            ExportColumn::make('order.order_number')
                ->label('Nomor Order'),

            ExportColumn::make('disclaimer.title')
                ->label('Judul Disclaimer'),

            ExportColumn::make('consent_type')
                ->label('Tipe Persetujuan'),

            ExportColumn::make('ip_address')
                ->label('Alamat IP'),

            ExportColumn::make('user_agent')
                ->label('User Agent'),

            ExportColumn::make('accepted_at')
                ->label('Waktu Diterima')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i:s') : null),

            ExportColumn::make('created_at')
                ->label('Tanggal Dibuat')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i:s') : null),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor persetujuan pengguna telah selesai.';

        $successfulRowsCount = $export->successful_rows;
        $failedRowsCount = $export->failed_rows;

        if (filled($successfulRowsCount)) {
            $body = 'Ekspor persetujuan pengguna telah selesai dan ' . number_format($successfulRowsCount) . ' baris telah diekspor.';
        }

        if (filled($failedRowsCount) && $failedRowsCount > 0) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
