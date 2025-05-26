<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRejectedNotification extends Notification
{
    public function __construct(public Order $order, public string $reason)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pesanan {$this->order->order_number} Ditolak")
            ->markdown('mail.order.order-rejected', ['order' => $this->order, 'reason' => $this->reason, 'user' => $notifiable]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
