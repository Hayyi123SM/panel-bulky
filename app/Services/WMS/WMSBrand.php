<?php

namespace App\Services\WMS;

class WMSBrand
{
    public static function getBrands(array $query = [])
    {
        return ApiRequest::sendGetRequest('/api/product-brands', $query);
    }

    public static function createBrand(array $params = [])
    {
        return ApiRequest::sendPostRequest('/api/product-brands', $params);
    }
}
