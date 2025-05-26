<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::latest()->limit(10)
            )
            ->columns([
                TextColumn::make('order_number')->label('Nomor Pesanan'),
                TextColumn::make('name')->label('Nama')->placeholder('-'),
            ]);
    }
}
