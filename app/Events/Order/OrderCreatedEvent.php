<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedEvent
{
    use Dispatchable, SerializesModels;

    public Order $order;

    public function __construct($order)
    {
        $this->order = $order;
    }
}
