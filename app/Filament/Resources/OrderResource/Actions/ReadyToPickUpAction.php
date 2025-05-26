<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Events\Order\ReadyToPickUpEvent;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;

class ReadyToPickUpAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'ready-to-pick-up-action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->color(Color::Emerald);
        $this->icon('heroicon-o-document-check');
        $this->label('Siap Diambil');

        $this->visible(function (Order $record) {
            $processing = $record->order_status == OrderStatusEnum::Processing;
            $selfPickUp = $record->shipping_method == ShippingMethodEnum::SELF_PICKUP;
            return $processing && $selfPickUp;
        });

        $this->requiresConfirmation();

        $this->action(function (Order $record) {
            $record->order_status = OrderStatusEnum::ReadyToPickup;
            $record->save();

            event(new ReadyToPickUpEvent($record));
        });
    }
}
