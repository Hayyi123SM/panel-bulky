<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderCreatedEvent
{
    use SerializesModels;

    /**
     * @var Order
     */
    public Order $order;

    public function __construct($order)
    {
        $this->order = $order;
    }
}
