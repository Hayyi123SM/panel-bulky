<?php

namespace App\Models\Traits;

trait HasOrderNumber
{
    /**
     * Function to generate a 10 character order number with digits and uppercase letters.
     *
     * @return string
     */
    public function generateOrderNumber(): string
    {
        do {
            $order_number = strtoupper(substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 14));
        } while (self::where('order_number', $order_number)->exists());

        return $order_number;
    }
}
