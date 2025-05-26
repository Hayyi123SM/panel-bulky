<?php

namespace App\Filament\Widgets\Tab2;

use App\Enums\OrderPaymentTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Exports\DashboardOrderExporter;
use App\Models\Order;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

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
                ExportAction::make()
                    ->exporter(DashboardOrderExporter::class)
                    ->fileDisk('export')
            ]);
    }
}
