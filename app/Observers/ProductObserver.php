<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function creating(Product $product): void
    {
        $product->slug = $product->createUniqueSlug($product->name);
    }
}
