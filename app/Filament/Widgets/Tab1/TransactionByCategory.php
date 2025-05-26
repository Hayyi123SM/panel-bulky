<?php

namespace App\Filament\Widgets\Tab1;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TransactionByCategory extends ChartWidget
{
    protected static ?string $heading = 'Transaksi Berdasarkan Kategori';

    protected static string $color = 'info';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'month';

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $categories = ProductCategory::whereHas('products', function ($query){
            $query->whereHas('orderItems', function ($query){
                $query->whereHas('order', function ($query){
                    $query->whereShippingMethod(ShippingMethodEnum::COURIER_PICKUP);
                });
            });
        })->get();

        [$start, $end, $grouping] = $this->getFilterDates($activeFilter);

        $data = [];
        $lastItem = collect();
        foreach ($categories as $category) {
            $item = $this->buildQuery($category->id);
            $item = $item->between(start: $start, end: $end)->{$grouping}()->count();
            $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
            $data[] = [
                'label' => $category->name,
                'data' => $item->map(fn(TrendValue $value) => $value->aggregate),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'pointBackgroundColor' => $color,
            ];

            $lastItem = $item;
        }

        return [
            'datasets' => $data,
            'labels' => $lastItem->map(fn(TrendValue $value) => $this->formatLabel($value->date)),
            'options' => [
                'borderJoinStyle' => 'round',
            ]
        ];
    }

    private function buildQuery(string $category): Trend
    {
        return Trend::query(
            Order::whereIn('order_status', [OrderStatusEnum::Delivered, OrderStatusEnum::Shipped])
                ->whereShippingMethod(ShippingMethodEnum::COURIER_PICKUP)
                ->whereHas('items', function ($query) use ($category){
                    $query->whereHas('product', function ($query) use ($category){
                        $query->where('product_category_id', $category);
                    });
                })
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
