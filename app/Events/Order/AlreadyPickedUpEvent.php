<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class AlreadyPickedUpEvent
{
    use Dispatchable;

    public function __construct(public Order $order)
    {
    }
}
