<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class OrderDeliveredEvent
{
    use Dispatchable;

    public Order $order;

    public function __construct($order)
    {
        $this->order = $order;
    }
}
