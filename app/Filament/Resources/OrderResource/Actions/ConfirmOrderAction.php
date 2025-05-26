<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderStatusEnum;
use App\Events\Order\OrderConfirmedEvent;
use App\Models\Order;
use Filament\Actions\Action;

class ConfirmOrderAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'confirm-order-action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Konfirmasi Pesanan');
        $this->icon('heroicon-o-check');

        $this->visible(function (Order $record) {
            return $record->order_status == OrderStatusEnum::WaitingConfirmation;
        });
        $this->requiresConfirmation();

        $this->successNotificationTitle('Order confirmed successfully!');

        $this->action(function (Order $record) {
            $record->order_status = OrderStatusEnum::Processing;
            $record->save();

            event(new OrderConfirmedEvent($record));

            $this->success();
        });
    }
}
