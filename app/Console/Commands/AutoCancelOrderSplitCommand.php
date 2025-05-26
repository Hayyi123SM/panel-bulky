<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatusEnum;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderPaymentTypeEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Console\Command;

class AutoCancelOrderSplitCommand extends Command
{
    protected $signature = 'app:auto-cancel-order-split';

    protected $description = 'Command description';

    public function handle(): void
    {
        $orders = \App\Models\Order::where('order_status', OrderStatusEnum::Pending)
            ->whereIn('payment_status', [OrderPaymentStatusEnum::PENDING, OrderPaymentStatusEnum::PARTIALLY_PAID])
            ->where('payment_method', OrderPaymentTypeEnum::SplitPayment)
            ->where('payment_expired_at', '<', now())
            ->get();

        try {
            foreach ($orders as $order) {
                $order->update([
                    'order_status' => OrderStatusEnum::Canceled,
                    'payment_status' => OrderPaymentStatusEnum::CANCELED,
                ]);

                if($order->invoices()->exists()){
                    $order->invoices()->update(['status' => InvoiceStatusEnum::CANCELLED]);
                }

                foreach ($order->items as $item) {
                    $item->product()->update(['sold_out' => false]);
                }
            }
        } catch (\Exception $exception){
            \Log::error($exception->getMessage());
        }
    }
}
