<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum OrderStatusEnum: string implements HasLabel, HasDescription, HasColor
{
    case Pending = 'pending';
    case Processing = 'processing';
    case WaitingConfirmation = 'waiting_confirmation';
    case Shipped = 'shipped';
    case ReadyToPickup = 'ready_to_pickup';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Canceled = 'canceled';

    case Refunding = 'refunding';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => Color::Gray,
            self::Processing, self::WaitingConfirmation => Color::Cyan,
            self::Shipped, self::Delivered, self::ReadyToPickup, self::Completed => Color::Green,
            self::Rejected => Color::Red,
            self::Canceled, self::Refunding => Color::Rose,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::Pending => __('order.description.pending'),
            self::Processing => __('order.description.processing'),
            self::Shipped => __('order.description.shipped'),
            self::ReadyToPickup => __('order.description.ready_to_pickup'),
            self::Delivered => __('order.description.delivered'),
            self::Completed => __('order.description.completed'),
            self::Rejected => __('order.description.rejected'),
            self::Canceled => __('order.description.canceled'),
            self::Refunding => __('order.description.refunding'),
            self::WaitingConfirmation => __('order.description.waiting_confirmation'),
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => __('order.label.pending'),
            self::Processing => __('order.label.processing'),
            self::Shipped => __('order.label.shipped'),
            self::ReadyToPickup => __('order.label.ready_to_pickup'),
            self::Delivered => __('order.label.delivered'),
            self::Completed => __('order.label.delivered'),
            self::Rejected => __('order.label.rejected'),
            self::Canceled => __('order.label.canceled'),
            self::Refunding => __('order.label.refunding'),
            self::WaitingConfirmation => __('order.label.waiting_confirmation'),
        };
    }
}
