<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PpnSettings extends Settings
{
    public float $rate = 0;
    public bool $enabled = false;

    public static function group(): string
    {
        return 'tax';
    }
}
