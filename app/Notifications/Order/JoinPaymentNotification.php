<?php

namespace App\Notifications\Order;

use App\Enums\OrderPaymentTypeEnum;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JoinPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = 'https://bulky.id/redirect?type=order-split&order_id=' . $this->order->id;

        return (new MailMessage)
            ->subject('Undangan Pembayaran Patungan untuk Pesanan ' . $this->order->order_number)
            ->markdown('mail.order.join-payment', ['order' => $this->order, 'user' => $notifiable, 'url' => $url]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
