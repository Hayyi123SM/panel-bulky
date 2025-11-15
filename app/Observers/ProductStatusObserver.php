<?php

namespace App\Observers;

use App\Models\ProductStatus;

class ProductStatusObserver
{
    public function creating(ProductStatus $productStatus): void
    {
        $productStatus->status = $productStatus->status_trans;
    }

    public function updating(ProductStatus $productStatus): void
    {
        if ($productStatus->isDirty('status_trans')) {
            $productStatus->status = $productStatus->status_trans;
        }
    }
}
