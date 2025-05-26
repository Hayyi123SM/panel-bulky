<?php

namespace App\Services\Deliveree;

class Deliveree
{
    /**
     * Sends a POST request to the deliveries API to get a delivery quote.
     *
     * @param array $data An array of data required to get the delivery quote.
     *
     * @return mixed The response from the API request.
     */
    public static function getDeliveryQuote(array $data): mixed
    {
        return ApiRequest::sendPostRequest('/public_api/v10/deliveries/get_quote', $data);
    }

    /**
     * Retrieve the list of vehicle types.
     *
     * @return mixed The response from the API request.
     */
    public static function getVehicleTypes(): mixed
    {
        return ApiRequest::sendGetRequest('/public_api/v10/vehicle_types');
    }

    /**
     * Create a new delivery entry using the provided data.
     *
     * @param array $data An associative array containing the data required to create a delivery.
     * @return mixed The response from the API request.
     */
    public static function createDelivery(array $data): mixed
    {
        return ApiRequest::sendPostRequest('/public_api/v10/deliveries', $data);
    }

    public static function getUserProfile(): mixed
    {
        return ApiRequest::sendPostRequest('/public_api/v10/customers/user_profile');
    }

    public static function getExtraService(int $vehicleId): mixed
    {
        return ApiRequest::sendGetRequest("/public_api/v10/vehicle_types/$vehicleId/extra_services");
    }

    public static function getDeliveryDetail(int $bookingId): mixed
    {
        return ApiRequest::sendGetRequest("/public_api/v10/deliveries/$bookingId");
    }

    public static function getDeliveryList(): mixed
    {
        return ApiRequest::sendGetRequest("/public_api/v10/deliveries");
    }
}
