<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CancelOrderByAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public bool $hasPaid = false)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pesanan {$this->order->order_number} Dibatalkan")
            ->markdown('mail.order.cancel-order-by-admin', [
                'hasPaid' => $this->hasPaid,
                'order' => $this->order,
                'user' => $notifiable
            ]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
