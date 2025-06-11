<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function creating(Product $product): void
    {
        $product->name = $product->name_trans;
        $product->slug = $product->createUniqueSlug($product->name);
    }
}
