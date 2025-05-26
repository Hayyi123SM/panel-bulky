<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WhatsAppFloatingSettings extends Settings
{
    public string $phone_number = '';
    public string $message = '';

    public static function group(): string
    {
        return 'floating_whatsapp';
    }
}
