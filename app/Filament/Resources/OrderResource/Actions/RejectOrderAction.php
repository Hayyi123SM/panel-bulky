<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Events\Order\OrderConfirmedEvent;
use App\Events\Order\OrderRejectedEvent;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class RejectOrderAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reject-order-action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Tolak Pesanan');
        $this->icon('heroicon-o-x-mark');

        $this->visible(function (Order $record) {
            return $record->order_status == OrderStatusEnum::WaitingConfirmation;
        });

        $this->requiresConfirmation();

        $this->form([
            TextArea::make('reason')
                ->label('Alasan Penolakan')
                ->placeholder('Masukan Alasan Penolakan')
                ->string()
                ->maxLength(255),
            Toggle::make('keep_product')
                ->default(true)
                ->label('Ubah semua produk pada pesanan ini menjadi Tersedia?')
                ->onColor('success')
        ]);

        $this->successNotificationTitle('Order rejected successfully!');

        $this->action(function (Order $record, array $data) {
            $record->order_status = OrderStatusEnum::Rejected;
            $record->cancel_reason = $data['reason'];
            $record->save();

            if($data['keep_product']){
                foreach ($record->items as $item) {
                    $item->product->update([
                        'sold_out' => false
                    ]);
                }
            }

            event(new OrderRejectedEvent($record, $data['reason']));

            $this->success();
        });
    }
}
