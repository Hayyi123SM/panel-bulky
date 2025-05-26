<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TotalRevenue extends ChartWidget
{
    protected static ?string $heading = 'Total Revenue';

    protected static ?string $maxHeight = '300px';

    protected static string $color = 'success';
    protected static ?int $sort = 3;

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
    ];

    protected function getData(): array
    {
        $revenue = Trend::model(Order::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('total_price');

        return [
            'datasets' => [
                [
                    'label' => 'Total Revenue',
                    'data' => $revenue->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $revenue->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
