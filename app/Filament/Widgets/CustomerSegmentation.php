<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CustomerSegmentation extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'customerSegmentation';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Customer Segmentation';

    protected static ?int $sort = 5;

    protected static ?int $contentHeight = 300;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $segments = $this->getCustomerSegments();
        $segmentCounts = $segments->groupBy('segment')->map->count()->toArray();

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => array_values($segmentCounts),
            'labels' => array_keys($segmentCounts),
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }

    private function getCustomerSegments(): \Illuminate\Support\Collection
    {
        return User::select(['users.id', 'users.name'])
            ->selectRaw('COUNT(orders.id) as total_orders')
            ->selectRaw('SUM(orders.total_price) as total_spent')
            ->selectRaw('MAX(orders.created_at) as last_order_date')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->groupBy('users.id', 'users.name')
            ->get()
            ->map(function ($user) {
                $now = now();
                $daysSinceLastOrder = $now->diffInDays($user->last_order_date);

                if ($user->total_orders >= 5 && $user->total_spent > 500000) {
                    $user->segment = 'Loyal';
                } elseif ($daysSinceLastOrder > 90) {
                    $user->segment = 'Inactive';
                } elseif ($user->total_orders <= 2) {
                    $user->segment = 'New';
                } else {
                    $user->segment = 'Potential';
                }

                return $user;
            });
    }
}
