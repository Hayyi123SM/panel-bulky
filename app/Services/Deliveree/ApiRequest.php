<?php

namespace App\Services\Deliveree;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class ApiRequest
{
    private static function initClient(): Client
    {
        return new Client([
            'base_uri' => config('deliveree.base_url'),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => config('deliveree.api_key'),
                'Accept-Language' => 'id'
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
            return json_decode($response->getBody()->getContents(), true);
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
                'json' => $formParams,
                'headers' => $headers,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
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
            return json_decode($response->getBody()->getContents(), true);
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
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException|\Exception $e) {
            return ['error' => $e->getMessage()];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
