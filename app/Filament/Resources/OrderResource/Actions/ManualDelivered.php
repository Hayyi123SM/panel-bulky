<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use Filament\Actions\Action;

class ManualDelivered extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manual-delivered';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Manual Delivered');
        $this->color('success');
        $this->icon('heroicon-o-check-circle');

        $this->visible(function (Order $record) {
            $status = $record->order_status == OrderStatusEnum::Processing || $record->order_status == OrderStatusEnum::Shipped;
            $selfPickup = $record->shipping_method == ShippingMethodEnum::COURIER_PICKUP;

            return $status && $selfPickup;
        });

        $this->requiresConfirmation();

        $this->action(function (Order $record) {
            $record->order_status = OrderStatusEnum::Delivered;
            $record->save();
        });
    }
}
