<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum OrderPaymentStatusEnum: string implements HasLabel, HasDescription, HasColor
{

    case PENDING = 'PENDING';
    case PARTIALLY_PAID = 'PARTIALLY_PAID';
    case PAID = 'PAID';
    case CANCELED = 'CANCELED';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => Color::Orange,
            self::PARTIALLY_PAID => Color::Blue,
            self::PAID => Color::Green,
            self::CANCELED => Color::Rose,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::PENDING => __('order.payments_status_description.pending'),
            self::PARTIALLY_PAID => __('order.payments_status_description.partially_paid'),
            self::PAID => __('order.payments_status_description.paid'),
            self::CANCELED => __('order.payments_status_description.canceled'),
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => __('order.payments_status_label.pending'),
            self::PARTIALLY_PAID => __('order.payments_status_label.partially_paid'),
            self::PAID => __('order.payments_status_label.paid'),
            self::CANCELED => __('order.payments_status_label.canceled'),
        };
    }
}
