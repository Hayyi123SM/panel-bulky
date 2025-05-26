<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatusEnum;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderPaymentTypeEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Console\Command;

class AutoCancelOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-cancel-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = \App\Models\Order::where('order_status', OrderStatusEnum::Pending)
            ->where('payment_status', OrderPaymentStatusEnum::PENDING)
            ->where('payment_method', OrderPaymentTypeEnum::SinglePayment)
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
                    $check = $item->product->orderItems()->whereHas('order', function ($query) {
                        $query->where('payment_status', OrderPaymentStatusEnum::PAID);
                    })->doesntExist();

                    if($check){
                        $item->product()->update(['sold_out' => false]);
                    }

                }
            }
        } catch (\Exception $exception){
            \Log::error($exception->getMessage());
        }
    }
}
