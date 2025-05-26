<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OrderStatusSummary extends ApexChartWidget
{
    protected static ?string $chartId = 'orderStatusSummary';
    protected static ?string $heading = 'Order Status Summary';

    protected static ?int $contentHeight = 300;

    protected static ?int $sort = 4;

    protected function getOptions(): array
    {

        $statusData = Order::query()
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->order_status->name => $item->total])
            ->toArray();

        $labels = array_keys($statusData);
        $data = array_values($statusData);

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $data,
            'labels' => $labels,
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
