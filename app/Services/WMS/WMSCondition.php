<?php

namespace App\Services\WMS;

class WMSCondition
{
    public static function getConditions(array $query = [])
    {
        return ApiRequest::sendGetRequest('/api/product-conditions', $query);
    }
}
