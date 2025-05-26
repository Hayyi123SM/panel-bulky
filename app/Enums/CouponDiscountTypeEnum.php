<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponDiscountTypeEnum: string implements HasLabel
{
    case Percent = 'percent';
    case Amount = 'amount';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Percent => __('coupon.discount_type.percent'),
            self::Amount => __('coupon.discount_type.amount'),
        };
    }
}
