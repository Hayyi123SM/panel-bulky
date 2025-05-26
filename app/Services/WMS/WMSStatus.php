<?php

namespace App\Services\WMS;

class WMSStatus
{
    public static function getStatus(array $query = [])
    {
        return ApiRequest::sendGetRequest('/api/product-statuses', $query);
    }
}
