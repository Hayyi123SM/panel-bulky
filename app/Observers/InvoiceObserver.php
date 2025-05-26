<?php

namespace App\Observers;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use Xendit\Invoice\InvoiceApi;
use Xendit\XenditSdkException;
use function PHPUnit\Framework\isNull;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {

    }

    public function created(Invoice $invoice): void
    {
    }

    public function updating(Invoice $invoice): void
    {
    }

    /**
     * @throws XenditSdkException
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->isDirty('status') && $invoice->status === InvoiceStatusEnum::CANCELLED && !empty($invoice->xendit_id)) {
            (new InvoiceApi())->expireInvoice($invoice->xendit_id);
        }
    }

    public function saving(Invoice $invoice): void
    {
    }

    public function saved(Invoice $invoice): void
    {
    }

    public function deleting(Invoice $invoice): void
    {
    }

    public function deleted(Invoice $invoice): void
    {
    }

    public function restoring(Invoice $invoice): void
    {
    }

    public function restored(Invoice $invoice): void
    {
    }

    public function retrieved(Invoice $invoice): void
    {
    }

    public function forceDeleting(Invoice $invoice): void
    {
    }

    public function forceDeleted(Invoice $invoice): void
    {
    }

    public function replicating(Invoice $invoice): void
    {
    }
}
