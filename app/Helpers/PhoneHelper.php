<?php

namespace App\Helpers;

class PhoneHelper
{
    public static function formatIndonesianPhoneNumber($phoneNumber)
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
