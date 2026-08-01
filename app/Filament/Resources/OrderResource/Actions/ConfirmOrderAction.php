<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderStatusEnum;
use App\Events\Order\OrderConfirmedEvent;
use App\Models\Order;
use App\Services\WMS\ApiRequest;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;

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

            $this->syncSoldToB2B($record);

            $this->success();
        });
    }

    protected function syncSoldToB2B(Order $order): void
    {
        foreach ($order->items()->with('product')->get() as $item) {
            $product = $item->product;

            if (! $product) {
                Log::warning("[SaleB2B] Produk pada order item tidak ditemukan (item id: {$item->id}), di-skip");

                continue;
            }

            $idCargo = $product->id_pallet ?? null;

            if (! $idCargo) {
                Log::info("[SaleB2B] Produk tanpa id_pallet di-skip: {$product->name}");

                continue;
            }

            $result = ApiRequest::sendPostRequest("/api/sale-b2b/{$idCargo}/sold", [
                'price_sale_sold' => (string) $item->price,
            ]);

            if (isset($result['error'])) {
                Log::warning("[SaleB2B] Gagal sync sold - {$product->name} (idCargo: {$idCargo}): {$result['error']}");

                continue;
            }

            Log::info("[SaleB2B] Berhasil sync sold - {$product->name} (idCargo: {$idCargo})");
        }
    }
}
