<?php

namespace App\Observers;

use App\Models\ProductCategory;

class ProductCategoryObserver
{
    public function creating(ProductCategory $productCategory): void
    {
        $productCategory->slug = $productCategory->createUniqueSlug($productCategory->name);
    }

    public function deleting(ProductCategory $productCategory): void
    {
        $productCategory->slug = 'deleted-' . $productCategory->slug;
        $productCategory->save();
    }

    public function restored(ProductCategory $productCategory): void
    {
        $productCategory->slug = str_replace('deleted-', '', $productCategory->slug);
        $productCategory->save();
    }
}
