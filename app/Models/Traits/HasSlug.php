<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public function createUniqueSlug($name): string
    {
        $slug = Str::slug($name);
        $counter = 1;
        while (self::query()->where('slug', '=', $slug)->exists()) {
            $slug = Str::slug($name) . '-' . $counter++;
        }

        return $slug;
    }
}
