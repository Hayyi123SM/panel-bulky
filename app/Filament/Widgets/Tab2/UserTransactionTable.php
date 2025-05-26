<?php

namespace App\Filament\Widgets\Tab2;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\UserResource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UserTransactionTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\User::whereHas('orders', function ($query) {
                    $query->where('created_at', '>=', now()->subDays(30))
                        ->whereIn('order_status', [OrderStatusEnum::Delivered, OrderStatusEnum::Shipped]);
                    })
                    ->with(['orders' => function ($query) {
                        $query->latest('created_at');
                    }])
                    ->withCount('orders')
                    ->orderBy('orders_count', 'desc')
                    ->take(10)
            )
            ->columns([
                TextColumn::make('name')->label('Nama'),
                TextColumn::make('orders_count')
                    ->label('Total Belanja')
                    ->counts('orders')
                    ->suffix(' kali'),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn ($record) => UserResource::getUrl('view', ['record' => $record]))
            ])
            ->paginated(false);
    }
}
