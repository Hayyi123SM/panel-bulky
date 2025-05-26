<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ShippingMethodEnum: string implements HasLabel
{
    case SELF_PICKUP = 'self_pickup';
    case COURIER_PICKUP = 'courier_pickup';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SELF_PICKUP => 'Self Pickup',
            self::COURIER_PICKUP => 'By Courier',
        };
    }
}
