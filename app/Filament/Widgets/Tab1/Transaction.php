<?php

namespace App\Filament\Widgets\Tab1;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class Transaction extends ChartWidget
{
    public ?string $filter = 'month';
    protected static ?string $heading = 'Transaksi';

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $success = $this->buildQuery([OrderStatusEnum::Delivered, OrderStatusEnum::Shipped]);
        $canceled = $this->buildQuery(OrderStatusEnum::Canceled);

        [$start, $end, $grouping] = $this->getFilterDates($activeFilter);

        $success = $success->between(start: $start, end: $end)->{$grouping}()->count();
        $canceled = $canceled->between(start: $start, end: $end)->{$grouping}()->count();

        return [
            'datasets' => [
                [
                    'label' => 'Success',
                    'data' => $success->map(fn(TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#0000FF',
                    'borderColor' => '#4682B4',
                    'pointBackgroundColor' => '#0000FF',
                ],
                [
                    'label' => 'Cancel',
                    'data' => $canceled->map(fn(TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#FF0000',
                    'borderColor' => '#FF6347',
                    'pointBackgroundColor' => '#FF0000',
                ],
            ],
            'labels' => $success->map(fn(TrendValue $value) => $this->formatLabel($value->date)),
        ];
    }

    private function buildQuery(array|OrderStatusEnum $status): Trend
    {
        return Trend::query(
            Order::whereIn('order_status', (array)$status)
                ->whereShippingMethod(ShippingMethodEnum::COURIER_PICKUP)
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
        return 'line';
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
