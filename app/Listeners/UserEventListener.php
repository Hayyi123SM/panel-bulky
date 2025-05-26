<?php

namespace App\Listeners;

use App\Notifications\RegisteredNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Events\Dispatcher;

class UserEventListener
{
    public function handleUserRegister(Registered $event): void
    {
        $user = $event->user;
        \Notification::send($user, new RegisteredNotification());
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            Registered::class,
            [self::class, 'handleUserRegister']
        );
    }
}
