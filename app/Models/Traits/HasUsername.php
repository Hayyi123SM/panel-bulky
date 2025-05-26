<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasUsername
{
    public function generateUniqueUsername(): string
    {
        do {
            $username = Str::random(10);
        } while (self::where('username', $username)->exists());

        return $username;
    }
}
