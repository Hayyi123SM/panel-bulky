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
}
