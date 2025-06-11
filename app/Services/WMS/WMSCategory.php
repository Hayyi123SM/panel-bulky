<?php

namespace App\Services\WMS;

class WMSCategory
{
    public static function getCategories(array $query = [])
    {
        return ApiRequest::sendGetRequest('/api/list-categories2', $query);
    }
}
