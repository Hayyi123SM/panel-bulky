<?php

namespace App\Notifications\Order;

use App\Enums\OrderPaymentTypeEnum;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
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
        $url = $this->buildOrderUrl();

        return (new MailMessage)
            ->subject('Konfirmasi Pesanan Anda - Menunggu Pembayaran')
            ->markdown('mail.order.order-created', [
                'order' => $this->order,
                'user' => $notifiable,
                'url' => $url
            ]);
    }

    private function buildOrderUrl(): string
    {
        return $this->order->payment_method == OrderPaymentTypeEnum::SplitPayment
            ? 'https://bulky.id/redirect?type=order-split&order_id=' . $this->order->id
            : 'https://bulky.id/redirect?type=order&order_id=' . $this->order->id;
    }
}
