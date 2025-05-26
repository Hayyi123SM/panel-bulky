<?php

namespace App\Observers;

use App\Models\ProductBrand;
use App\Services\WMS\WMSBrand;

class ProductBrandObserver
{
    public function creating(ProductBrand $productBrand): void
    {
        $productBrand->slug = $productBrand->createUniqueSlug($productBrand->name);
    }

    public function created(ProductBrand $productBrand): void
    {
        if(is_null($productBrand->wms_id)) {
            $response = WMSBrand::createBrand([
                'brand_name' => $productBrand->name
            ]);

            if(!collect($response)->has('error')) {
                $productBrand->updateQuietly([
                    'wms_id' => $response['id']
                ]);
            }
        }
    }
}
