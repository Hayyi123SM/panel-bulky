<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Events\Order\AlreadyPickedUpEvent;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Support\Colors\Color;

class AlreadyPickedUpAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'already_picked_action';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->label('Sudah Diambil');
        $this->color(Color::Green);
        $this->icon('heroicon-o-check-circle');

        $this->visible(function (Order $record) {
            $processing = $record->order_status == OrderStatusEnum::ReadyToPickup;
            $selfPickup = $record->shipping_method == ShippingMethodEnum::SELF_PICKUP;
            return $processing && $selfPickup;
        });

        $this->form([
            Components\TextInput::make('proof_name')
                ->nullable()
                ->string(),
            Components\Textarea::make('proof_description')
                ->nullable()
                ->string(),
            Components\FileUpload::make('proof_image')
                ->nullable()
                ->image()
        ]);

        $this->action(function (Order $record, array $data) {
            $record->proof_name = $data['proof_name'];
            $record->proof_description = $data['proof_description'];
            $record->proof_image = $data['proof_image'];
            $record->order_status = OrderStatusEnum::Delivered;
            $record->save();

            event(new AlreadyPickedUpEvent($record));
        });
    }
}
