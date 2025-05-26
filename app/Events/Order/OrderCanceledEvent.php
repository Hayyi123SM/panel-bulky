<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderCanceledEvent
{
    use SerializesModels;

    public Order $order;
    public string $type;
    public bool $hasPaid = false;

    public function __construct(Order $order, string $type, bool $hasPaid = false)
    {
        $this->order = $order;
        $this->type = $type;
        $this->hasPaid = $hasPaid;
    }
}
