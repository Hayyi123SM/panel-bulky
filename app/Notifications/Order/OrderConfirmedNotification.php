<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification
{
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
            ->subject("Pesanan {$this->order->order_number} Dikonfirmasi & Sedang Diproses")
            ->markdown('mail.order.order-confirmed', ['user' => $notifiable, 'order' => $this->order]);
    }
}
