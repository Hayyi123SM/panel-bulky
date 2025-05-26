<?php

namespace App\Filament\Widgets\Tab1;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TotalSales extends ChartWidget
{
    public ?string $filter = 'month';
    protected static ?string $heading = 'Total Penjualan';

    protected static string $color = 'success';

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $transactions = $this->buildQuery();

        [$start, $end, $grouping] = $this->getFilterDates($activeFilter);

        $transactions = $transactions->between(start: $start, end: $end)->{$grouping}()->sum('total_price');

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan: Rp.',
                    'data' => $transactions->map(fn(TrendValue $value) => $value->aggregate),
                    'pointStyle' => 'circle'
                ],
            ],
            'labels' => $transactions->map(fn(TrendValue $value) => $this->formatLabel($value->date)),
        ];
    }

    private function buildQuery(): Trend
    {
        $status = [OrderStatusEnum::Delivered, OrderStatusEnum::Shipped];
        return Trend::query(
            Order::whereIn('order_status', $status)
        );
    }

    private function getFilterDates(string $filter): array
    {
        return match ($filter) {
            'today' => [now()->startOfDay(), now()->endOfDay(), 'perHour'],
            'week' => [now()->startOf('week'), now()->endOfWeek(), 'perDay'],
            'month' => [now()->startOfMonth(), now()->endOfMonth(), 'perDay'],
            default => [now()->startOfYear(), now()->endOfYear(), 'perMonth']
        };
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
        ];
    }

    private function formatLabel($value): string
    {
        return match ($this->filter) {
            'today' => Carbon::parse($value)->locale('id')->isoFormat('HH:mm'),
            'week' => Carbon::parse($value)->locale('id')->isoFormat('ddd - DD'),
            'month' => Carbon::parse($value)->locale('id')->isoFormat('MMM - DD'),
            'year' => Carbon::parse($value)->locale('id')->isoFormat('MMM'),
            default => $value
        };
    }
}
