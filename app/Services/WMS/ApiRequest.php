<?php

namespace App\Services\WMS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class ApiRequest
{
    private static function initClient(): Client
    {
        return new Client([
            'base_uri' => config('wms.base_url'),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => config('wms.api_token'),
            ]
        ]);
    }

    public static function sendGetRequest(string $url, array $queryParams = [])
    {
        $client = self::initClient();
        try {
            $response = $client->request('GET', $url, [
                'query' => $queryParams
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            if ($response['data']['status']){
                return $response['data']['resource'];
            } else {
                throw new \Exception($response['data']['message']);
            }
        } catch (RequestException|\Exception $e) {
            return ['error' => $e->getMessage()];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function sendPostRequest(string $url, array $formParams = [], array $headers = [])
    {
        $client = self::initClient();
        try {
            $response = $client->request('POST', $url, [
                'form_params' => $formParams,
                'headers' => $headers
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            if ($response['data']['status']){
                return $response['data']['resource'];
            } else {
                throw new \Exception($response['data']['message']);
            }

        } catch (RequestException|\Exception $e) {
            return ['error' => $e->getMessage()];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function sendPutRequest(string $url, array $formParams = [], array $headers = [])
    {
        $client = self::initClient();
        try {
            $response = $client->request('PUT', $url, [
                'form_params' => $formParams,
                'headers' => $headers
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            if ($response['data']['status']){
                return $response['data']['resource'];
            } else {
                throw new \Exception($response['data']['message']);
            }
        } catch (RequestException|\Exception $e) {
            return ['error' => $e->getMessage()];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function sendDeleteRequest(string $url, array $headers = [])
    {
        $client = self::initClient();
        try {
            $response = $client->request('DELETE', $url, [
                'headers' => $headers
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            if ($response['data']['status']){
                return $response['data']['resource'];
            } else {
                throw new \Exception($response['data']['message']);
            }
        } catch (RequestException|\Exception $e) {
            return ['error' => $e->getMessage()];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
