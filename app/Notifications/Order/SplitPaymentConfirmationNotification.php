<?php

namespace App\Notifications\Order;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SplitPaymentConfirmationNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Konfirmasi Pembayaran Patungan untuk Pesanan ' . $this->invoice->order->order_number)
            ->markdown('mail.order.split-payment-confirmation', ['invoice' => $this->invoice, 'user' => $notifiable]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
