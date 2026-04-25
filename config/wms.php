<?php

return [
    'api_token' => env('WMS_TOKEN'),
    'base_url' => env('WMS_SERVER_URL'),
    'mode' => env('WMS_API_MODE', 'dummy'), // dummy|real
];
