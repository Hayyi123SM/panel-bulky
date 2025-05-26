<?php

namespace App\Events\Order;

use App\Models\Invoice;
use Illuminate\Queue\SerializesModels;

class InvoicePaidEvent
{
    use SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
