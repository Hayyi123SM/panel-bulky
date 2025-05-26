<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReadyToPickUpEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
    }
}
