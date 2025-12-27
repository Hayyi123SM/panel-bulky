<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Exports\ProductCustomExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use App\Jobs\ExportProductsJob;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Tables;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;

class ListProducts extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportProduk')
                ->label('Export Produk')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Forms\Components\Select::make('is_active')
                        ->label('Status Aktif')
                        ->options([
                            '' => 'Semua',
                            '1' => 'Aktif',
                            '0' => 'Tidak Aktif',
                        ]),
                    Forms\Components\Select::make('sold_out')
                        ->label('Status Penjualan')
                        ->options([
                            '' => 'Semua',
                            '0' => 'Belum Terjual',
                            '1' => 'Terjual',
                        ]),
                ])
                ->action(function ($data) {
                    $filters = [
                        'is_active' => $data['is_active'] ?? '',
                        'sold_out' => $data['sold_out'] ?? '',
                    ];

                    $user = auth()->user();
                    ExportProductsJob::dispatch($user, $filters);

                    Notification::make()
                        ->success()
                        ->title('Export Diproses')
                        ->body('Export produk sedang diproses. Anda akan menerima notifikasi saat file siap diunduh.')
                        ->send();
                })
                ->modalHeading('Export Produk')
                ->modalSubmitActionLabel('Export'),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
