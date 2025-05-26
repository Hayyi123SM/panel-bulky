<?php

namespace App\Observers;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $order->order_number = $order->generateOrderNumber();
        $order->order_status = OrderStatusEnum::Pending;
        $order->payment_status = OrderPaymentStatusEnum::PENDING;
        $order->order_date = now();
    }
}
