<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaidAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pesanan Baru #{$this->order->order_number} - Pembayaran Lunas")
            ->markdown('mail.order.paid-admin', ['user' => $notifiable, 'order' => $this->order]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
