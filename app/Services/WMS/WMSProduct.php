<?php

namespace App\Services\WMS;

class WMSProduct
{
    public static function getProducts(array $query = [])
    {
        return ApiRequest::sendGetRequest('/api/palets', $query);
    }
}
