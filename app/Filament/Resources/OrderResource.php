<?php

namespace App\Filament\Resources;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderPaymentTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'orders';

    protected static ?string $label = 'Pesanan';
    protected static ?string $navigationGroup = 'Manajemen Pesanan';
    protected static ?int $navigationSort = 7;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable(),

                TextColumn::make('order_date')
                    ->label('Tanggal Pesanan')
                    ->date(),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->prefix('Rp ')
                    ->numeric(0, ',', '.'),

                TextColumn::make('delivery_type')
                    ->label('Pengiriman')
                    ->placeholder('-'),

                TextColumn::make('payment_method')
                    ->label('Tipe Pembayaran'),

                TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge(),

                TextColumn::make('order_status')
                    ->label('Status Pesanan')
                    ->badge(),
            ])
            ->defaultSort('created_at', 'DESC')
            ->filters([
                SelectFilter::make('payment_method')
                    ->label('Tipe Pembayaran')
                    ->options(OrderPaymentTypeEnum::class),
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options(OrderPaymentStatusEnum::class),
                SelectFilter::make('order_status')
                    ->label('Status Pesanan')
                    ->options(OrderStatusEnum::class),
                TrashedFilter::make(),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                ViewAction::make()
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrders::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::make(),
            RelationManagers\InvoicesRelationManager::make()
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->user) {
            $details['User'] = $record->user->name;
        }

        return $details;
    }
}
