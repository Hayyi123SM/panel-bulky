<?php

namespace App\Observers;

use App\Models\ProductStatus;

class ProductStatusObserver
{
    public function creating(ProductStatus $productStatus): void
    {
        $productStatus->status = $productStatus->status_trans;
    }
}
