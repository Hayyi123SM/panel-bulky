<?php

namespace App\Notifications\Order;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SplitPaymentOrderFullPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $invoice = $this->invoice;

        return (new MailMessage)
            ->subject("Pesanan {$invoice->order->order_number} Telah Lunas!")
            ->markdown('mail.order.split-payment-order-full-paid', ['user' => $notifiable, 'invoice' => $invoice]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
