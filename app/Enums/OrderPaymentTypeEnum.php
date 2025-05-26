<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum OrderPaymentTypeEnum: string implements HasLabel, HasDescription
{
    case SinglePayment = 'single_payment';
    case SplitPayment = 'split_payment';

    public function getDescription(): ?string
    {
        return match ($this) {
            self::SinglePayment => __('order.payment_method_description.single_payment'),
            self::SplitPayment => __('order.payment_method_description.split_payment'),
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SinglePayment => __('order.payment_method_label.single_payment'),
            self::SplitPayment => __('order.payment_method_label.split_payment'),
        };
    }
}
