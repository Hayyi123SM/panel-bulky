<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use App\Models\Warehouse;
use App\Services\Deliveree\Deliveree;
use Filament\Actions\Action;

class SendPackageAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'send_package_action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Kirim Paket');
        $this->icon('heroicon-o-truck');
        $this->color('info');
        $this->visible(function (Order $record) {
            $processing = $record->order_status == OrderStatusEnum::Processing;
            $byCourier = $record->shipping_method == ShippingMethodEnum::COURIER_PICKUP;
            return $processing && $byCourier;
        });

        $this->requiresConfirmation();

        $this->action(function (Order $record) {
            $warehouseIds = $record->items->pluck('product.warehouse_id')->unique();
            $warehouse = Warehouse::find($warehouseIds->first());

            $booking = Deliveree::createDelivery([
                'vehicle_type_id' => $record->shipping->vehicle_type,
                'booking_payment_type' => 'credit',
                'time_type' => 'now',
                'job_order_number' => $record->order_number,
                'allow_parking_fees' => true,
                'allow_tolls_fees' => true,
                'locations' => [
                    [
                        'address' => $warehouse->address,
                        'latitude' => $warehouse->latitude,
                        'longitude' => $warehouse->longitude,
                        'recipient_name' => 'Bulky | Octagon',
                        'recipient_phone' => '+62811833164',
                        'note' => 'Pickup Location'
                    ],
                    [
                        'address' => $record->shipping_address,
                        'latitude' => $record->latitude,
                        'longitude' => $record->longitude,
                        'recipient_name' => $record->name,
                        'recipient_phone' => $record->phone_number,
                    ]
                ]
            ]);

            if(!collect($booking)->has('error')){
                $booking_id = $booking['booking_id'];
                $record->shipping->update([
                    'booking_id' => $booking_id,
                ]);
                $record->order_status = OrderStatusEnum::Shipped;
                $record->save();
            } else {
                $this->failureNotificationTitle($booking['error']);
                $this->failure();
            }

        });
    }
}
