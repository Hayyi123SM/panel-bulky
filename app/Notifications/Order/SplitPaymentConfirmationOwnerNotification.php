<?php

namespace App\Notifications\Order;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SplitPaymentConfirmationOwnerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $paidAmount = $this->invoice->order->invoices->whereStatus(InvoiceStatusEnum::PAID)->sum('amount');
        $remainingAmount = $this->invoice->order->total_price - $paidAmount;

        return (new MailMessage)
            ->subject('Konfirmasi Pembayaran Patungan untuk Pesanan ' . $this->invoice->order->order_number)
            ->markdown('mail.order.split-payment-confirmation-owner', [
                'invoice' => $this->invoice,
                'user' => $notifiable,
                'remainingAmount' => $remainingAmount
            ]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
