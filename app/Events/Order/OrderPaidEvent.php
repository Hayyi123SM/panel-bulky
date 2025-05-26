<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderPaidEvent
{
    use SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
