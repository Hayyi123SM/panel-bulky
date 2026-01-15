<?php

namespace App\Filament\Widgets\Tab2;

use App\Enums\OrderPaymentTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Exports\DashboardOrderCustomExport;
use App\Filament\Exports\DashboardOrderExporter;
use App\Jobs\ExportDashboardTransactionJob;
use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class Transaction extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::latest()->whereIn('order_status', [OrderStatusEnum::Shipped, OrderStatusEnum::Delivered])
            )
            ->columns([
                TextColumn::make('user.name')->label('Nama Pembeli'),
                TextColumn::make('items.product.name_trans')
                    ->bulleted(fn($record) => $record->items->count() > 1)
                    ->label('Palet'),
                TextColumn::make('items.product.productCategory.name_trans')
                    ->bulleted(fn($record) => $record->items->count() > 1)
                    ->label('Kategori'),
                TextColumn::make('items.price')
                    ->bulleted(fn($record) => $record->items->count() > 1)
                    ->label('Harga')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->alignRight(),
                TextColumn::make('shipping.shipping_cost')
                    ->default('0')
                    ->label('Ongkos Kirim')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->alignRight(),
                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->alignRight(),
                TextColumn::make('order_date')
                    ->label('Tanggal Pesanan')
                    ->date(),
                TextColumn::make('invoices.paymentMethod.name')
                    ->bulleted(fn($record) => $record->payment_method == OrderPaymentTypeEnum::SplitPayment)
                    ->label('Jenis Pembayaran')
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Ekspor Transaksi')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->modalHeading('Ekspor Transaksi Berdasarkan Tanggal')
                    ->modalSubmitActionLabel('Ekspor')
                    ->form([
                        DateRangePicker::make('date_range')
                            ->label('Rentang Tanggal')
                            ->required()
                    ])
                    ->action(function ($data) {
                        // raw example: "09/01/2026 - 15/01/2026"
                        $range = $data['date_range'];

                        // split by dash
                        [$start, $end] = array_map('trim', explode('-', $range));

                        // normalize using Carbon
                        $dateFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->format('Y-m-d');
                        $dateTo   = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->format('Y-m-d');

                        $filters = [
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                        ];

                        $user = auth()->user();
                        ExportDashboardTransactionJob::dispatch($user, $filters);

                        Notification::make()
                            ->title('Export Sedang Diproses')
                            ->success()
                            ->body('File akan tersedia setelah proses selesai.')
                            ->send();
                    })
            ]);
    }
}
