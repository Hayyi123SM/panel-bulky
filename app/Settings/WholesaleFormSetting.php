<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WholesaleFormSetting extends Settings
{

    public array $emails;
    public array $budgets;

    public static function group(): string
    {
        return 'wholesale_form';
    }
}
