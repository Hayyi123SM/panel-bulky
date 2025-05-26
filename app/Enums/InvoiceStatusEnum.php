<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use function Laravel\Prompts\select;

enum InvoiceStatusEnum: string implements HasLabel, HasDescription, HasColor
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case EXPIRED = 'expired';
    case DENY = 'deny';

    public function getLabel(): ?string
    {
        return match ($this){
            self::PENDING => __('invoices.label.pending'),
            self::PAID => __('invoices.label.paid'),
            self::CANCELLED => __('invoices.label.cancelled'),
            self::REFUNDED => __('invoices.label.refunded'),
            self::EXPIRED => __('invoices.label.expired'),
            self::DENY => __('invoices.label.deny'),
        };
    }

    public function getDescription(): ?string
    {
        return match ($this){
            self::PENDING => __('invoices.description.pending'),
            self::PAID => __('invoices.description.paid'),
            self::CANCELLED => __('invoices.description.cancelled'),
            self::REFUNDED => __('invoices.description.refunded'),
            self::EXPIRED => __('invoices.description.expired'),
            self::DENY => __('invoices.description.deny'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this){
            self::PENDING => Color::Orange,
            self::PAID => Color::Green,
            self::CANCELLED, self::EXPIRED => Color::Rose,
            self::REFUNDED, self::DENY => Color::Fuchsia,
        };
    }
}
