<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodaySnapshot extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $order = Order::whereDate('created_at', today())->count();
        $revenue = Order::whereDate('created_at', today())->sum('total_price');
        $user = User::whereDate('created_at', today())->count();

        $orderChart = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('DAY(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();

        $revenueChart = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('DAY(created_at) as day, SUM(total_price) as sum')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('sum')
            ->toArray();

        $userChart = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('DAY(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();

        return [
            Stat::make('Total Orders Today', $order)
                ->chart($orderChart)
                ->color('success'),
            Stat::make('Total Revenue Today', 'Rp ' . number_format($revenue, 0, ',', '.'))
                ->chart($revenueChart)
                ->color('danger'),
            Stat::make('New Customers Today', $user)
                ->chart($userChart)
                ->color('info'),
        ];
    }

    protected function getHeading(): ?string
    {
        return 'Today’s Snapshot';
    }

    protected function getDescription(): ?string
    {
        return 'Daily performance overview';
    }
}
