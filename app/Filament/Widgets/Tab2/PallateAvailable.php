<?php

namespace App\Filament\Widgets\Tab2;

use App\Models\ProductCategory;
use Filament\Support\Colors\Color;
use Filament\Widgets\ChartWidget;

class PallateAvailable extends ChartWidget
{
    protected static ?string $heading = 'Pallate Available';
    protected static string $color = 'success';

    protected function getData(): array
    {
        $categories = ProductCategory::whereHas('products')->with('products')->orderBy('name_trans')->get();

        $data = $categories->map(function ($category) {
            return $category->products()->where('sold_out', false)->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pallate Tersedia per Kategori',
                    'data' => $data->toArray(),
                    'pointStyle' => 'circle',
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
