<?php

namespace App\Filament\Widgets\Tab2;

use App\Enums\OrderStatusEnum;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class UserTransaction extends ChartWidget
{
    protected static ?string $heading = 'Penjualan Per User';

    protected static string $color = 'danger';

    protected function getData(): array
    {

        $topUsers = User::where('name', 'not like', '%test%')
            ->where('name', 'not like', '%tes%')
            ->withSum(['orders' => function ($query) {
                $query->whereNotIn('order_status', [
                    OrderStatusEnum::Canceled,
                    OrderStatusEnum::Pending,
                    OrderStatusEnum::Rejected,
                    OrderStatusEnum::Refunding,
                ]);
            }], 'total_price')
            ->orderByDesc('orders_sum_total_price')
            ->take(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Pembelian (Rp)',
                    'data' => $topUsers->pluck('orders_sum_total_price')->toArray(),
                ],
            ],
            'labels' => $topUsers->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
