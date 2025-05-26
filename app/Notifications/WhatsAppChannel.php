<?php

namespace App\Notifications;

use App\Services\WhatsApp\WhatsApp;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toWhatsApp($notifiable);
        WhatsApp::sendMessage($notifiable->phone_number, $message);
    }
}
