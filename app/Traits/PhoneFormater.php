<?php

namespace App\Traits;

trait PhoneFormater
{
    public function formatIndonesianPhoneNumber($phoneNumber): array|string|null
    {
        $phoneNumber = ltrim($phoneNumber, '+');
        if (str_starts_with($phoneNumber, '62')) {
            return "+{$phoneNumber}";
        } elseif (str_starts_with($phoneNumber, '8')) {
            return "+62{$phoneNumber}";
        } elseif (str_starts_with($phoneNumber, '08')) {
            return "+" . preg_replace('/^0/', '62', $phoneNumber);
        } else {
            return $phoneNumber;
        }
    }
}
