<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
    public function creating(Product $product): void
    {
        $product->name = $product->name_trans;
        $product->slug = $product->createUniqueSlug($product->name);
    }

    public function forceDeleted(Product $product): void
    {
        Storage::disk('public')->delete($product->images);
    }
}
