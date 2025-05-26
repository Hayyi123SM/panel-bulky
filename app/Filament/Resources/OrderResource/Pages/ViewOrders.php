<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewOrders extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                OrderResource\Actions\ConfirmOrderAction::make(),
                OrderResource\Actions\RejectOrderAction::make(),
            ])->label('Proses')->button(),
            OrderResource\Actions\CancelOrderAction::make(),
            OrderResource\Actions\SendPackageAction::make(),
            OrderResource\Actions\ReadyToPickUpAction::make(),
            OrderResource\Actions\AlreadyPickedUpAction::make(),
            OrderResource\Actions\ManualDelivered::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                Grid::make()->schema([
                    Section::make('Detail')
                        ->columnSpan(1)
                        ->schema([
                            TextEntry::make('user.name')->label('Pengguna'),
                            TextEntry::make('name')
                                ->label('Pembeli')
                                ->helperText(fn (Order $record) => $record->phone_number),
                            TextEntry::make('order_number')->label('No. Pesanan'),
                            TextEntry::make('order_date')->label('Tanggal Pesanan')->date('d F Y'),
                            TextEntry::make('order_status')->badge()->label('Status Pesanan'),
                            TextEntry::make('notes')->label('Catatan')->placeholder('-'),
                        ]),
                    Section::make('Payment & Delivery')
                        ->headerActions([
                            Action::make('tracking')
                                ->label('Tracking')
                                ->url(fn (Order $record) => $record->shipping?->tracking_url)
                                ->visible(fn (Order $record) => $record->shipping?->tracking_url !== null)
                                ->openUrlInNewTab()
                                ->icon('heroicon-o-truck'),
                        ])
                        ->columnSpan(1)
                        ->schema([
                            TextEntry::make('shipping_method')->label('Metode Pengiriman'),
                            TextEntry::make('shipping.shipping_cost')
                                ->default(0)
                                ->label('Biaya Pengiriman')
                                ->prefix('Rp ')
                                ->numeric(0, ',', '.'),
                            TextEntry::make('tax_amount')
                                ->label('PPN')
                                ->numeric(0, ',', '.')
                                ->prefix('Rp '),
                            TextEntry::make('total_price')
                                ->label('Total')
                                ->numeric(0, ',', '.')
                                ->prefix('Rp '),
                            TextEntry::make('payment_method')
                                ->label('Jenis Pembayaran'),
                            TextEntry::make('payment_status')->badge()->label('Status Pembayaran'),
                            TextEntry::make('shipping_address')
                                ->label('Alamat Pengiriman')
                                ->url(fn (Order $record) => "https://www.google.com/maps?q=$record->latitude,$record->longitude")
                                ->openUrlInNewTab()
                                ->hintIcon('heroicon-o-link'),
                        ])
                ])
            ])
            ->inlineLabel()->columns(2);
    }
}
