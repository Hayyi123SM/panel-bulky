<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderPaymentTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Events\Order\OrderCanceledEvent;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Support\Colors\Color;

class CancelOrderAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'cancel-order-action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Batalkan Pesanan');
        $this->icon('heroicon-s-x-circle');
        $this->color(Color::Rose);

        $this->visible(function (Order $record) {
            $statuses = [OrderStatusEnum::Pending, OrderStatusEnum::Processing, OrderStatusEnum::WaitingConfirmation];
            return in_array($record->order_status, $statuses);
        });

        $this->requiresConfirmation();

        $this->form([
            TextArea::make('reason')
                ->label('Alasan Pembatalan')
                ->placeholder('Masukan Alasan Pembatalan')
                ->string()
                ->maxLength(255),
            Toggle::make('keep_product')
                ->default(true)
                ->label('Ubah semua produk pada pesanan ini menjadi Tersedia?')
                ->onColor('success')
        ]);

        $this->action(function (Order $record, array $data){

            $hasPaid = false;
            $statuses = [OrderPaymentStatusEnum::PAID, OrderPaymentStatusEnum::PARTIALLY_PAID];
            if(in_array($record->payment_status, $statuses)){
                $hasPaid = true;
            }

            $record->update([
                'order_status' => OrderStatusEnum::Canceled,
                'payment_status' => OrderPaymentStatusEnum::CANCELED,
                'cancel_reason' => $data['reason'],
            ]);

            if($data['keep_product']){
                foreach ($record->items as $item) {
                    $item?->product?->update([
                        'sold_out' => false
                    ]);
                }
            }

            event(new OrderCanceledEvent($record, 'admin',  $hasPaid));
        });
    }
}
