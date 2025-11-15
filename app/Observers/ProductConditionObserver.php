<?php

namespace App\Observers;

use App\Models\ProductCondition;

class ProductConditionObserver
{
    public function creating(ProductCondition $productCondition): void
    {
        $productCondition->title = $productCondition->title_trans;
        $productCondition->slug = $productCondition->createUniqueSlug($productCondition->title);
    }

    public function updating(ProductCondition $productCondition): void
    {
        if ($productCondition->isDirty('title_trans')) {
            $productCondition->title = $productCondition->title_trans;
            $productCondition->slug = $productCondition->createUniqueSlug($productCondition->title, $productCondition->id ?? null);
        }
    }
}
