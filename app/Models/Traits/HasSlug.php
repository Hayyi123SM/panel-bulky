<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public function createUniqueSlug($name, $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (self::query()
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where('slug', '=', $slug)
            ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
