<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public function createUniqueSlug($name, $excludeId = null, $modelClass = null, string $slugColumn = 'slug'): string
    {
        $modelClass = $modelClass ?? self::class;
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while ($modelClass::query()
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where($slugColumn, '=', $slug)
            ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
