<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PickupInfoSetting extends Settings
{
    public string $address = '';
    public string $operational_hours = '';
    public string $whatsapp_number = '';
    public array $open_hour = [];

    public static function group(): string
    {
        return 'pickup_info';
    }
}
