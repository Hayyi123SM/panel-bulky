<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrderVolumeOverTime extends ChartWidget
{
    protected static ?string $heading = 'Order Volume Over Time';
    protected static ?string $maxHeight = '200px';

    public ?string $filter = 'year';

    protected int | string | array $columnSpan = 'full';

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
    ];

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        if ($activeFilter === 'today') {
            $data = Trend::model(Order::class)
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->count();
        } elseif ($activeFilter === 'week') {
            $data = Trend::model(Order::class)
                ->between(
                    start: now()->startOfWeek(),
                    end: now()->endOfWeek(),
                )
                ->perDay()
                ->count();
        } elseif ($activeFilter === 'month') {
            $data = Trend::model(Order::class)
                ->between(
                    start: now()->startOfMonth(),
                    end: now()->endOfMonth(),
                )
                ->perDay()
                ->count();
        } else {
            $data = Trend::model(Order::class)
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth()
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Order Volume',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    public function getDescription(): ?string
    {
        return 'Jumlah pesanan yang masuk pada periode tertentu.';
    }
}
