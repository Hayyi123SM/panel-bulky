<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class DeliveryInProgressEvent
{
    use Dispatchable;

    public function __construct(public Order $order, public string $tracking_url)
    {
    }
}
