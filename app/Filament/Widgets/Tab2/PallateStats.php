<?php

namespace App\Filament\Widgets\Tab2;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PallateStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pallate Available', $this->getPallateAvailable()),
            Stat::make('Pallate Sold', $this->getPallateSold()),
            Stat::make('Revenue', $this->getRevenue()),
        ];
    }

    private function getPallateAvailable(): int
    {
        return Product::where('sold_out', false)
            ->where('is_active', true)
            ->count();
    }

    private function getPallateSold(): int
    {
        return Product::where('sold_out', true)
            ->where('is_active', true)
            ->count();
    }

    private function getRevenue(): string
    {
        $sum = Product::where('sold_out', true)
            ->sum('price');

        return $sum >= 1_000_000_000_000
            ? 'Rp ' . number_format($sum / 1_000_000_000_000, 1, ',', '.') . 'T'
            : ($sum >= 1_000_000_000
                ? 'Rp ' . number_format($sum / 1_000_000_000, 1, ',', '.') . 'M'
                : ($sum >= 1_000_000
                    ? 'Rp ' . number_format($sum / 1_000_000, 1, ',', '.') . 'JT'
                    : $sum));
    }
}
