<?php

namespace App\Filament\Exports;

use App\Enums\OrderStatusEnum;
use App\Models\DashboardOrder;
use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class DashboardOrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->whereIn('order_status', [OrderStatusEnum::Shipped, OrderStatusEnum::Delivered])
            ->with('user');
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.name')
                ->label('Nama Pembeli'),
            ExportColumn::make('items.product.name_trans')
                ->label('Palet'),
            ExportColumn::make('items.product.productCategory.name_trans')
                ->label('Kategori')
                ->distinctList(true),
            ExportColumn::make('items.price')
                ->label('Harga')
                ->prefix('Rp'),
            ExportColumn::make('shipping.shipping_cost')
                ->default('0')
                ->label('Ongkos Kirim')
                ->prefix('Rp'),
            ExportColumn::make('discount_amount')
                ->default('0')
                ->label('Diskon')
                ->prefix('Rp'),
            ExportColumn::make('created_at')
                ->label('Tanggal Pesanan'),
            ExportColumn::make('invoices.paymentMethod.name')
                ->label('Jenis Pembayaran'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your dashboard order export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
