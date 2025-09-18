<?php

namespace App\Services\Forwarder;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ApiRequest
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('forwarder.base_url');
    }

    /**
     * Get Bearer Token (Auto based on Scope)
     */
    public function getAccessToken(string $scope): string
    {
        return Cache::remember("forwarder_token_{$scope}", now()->addMinutes(4), function () use ($scope) {
            $response = Http::withHeaders([
                'client_name' => config('forwarder.client_name'),
                'username' => config('forwarder.username'),
                'password' => config('forwarder.password'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/accesstoken', [
                'scope' => $scope
            ]);

            if ($response->successful() && isset($response['access_token'])) {
                return $response['access_token'];
            }

            throw new \Exception('Failed to retrieve access token: ' . $response->body());
        });
    }

    /**
     * Send authenticated request with dynamic scope
     */
    public function request(string $method, string $endpoint, string $scope, array $payload = [])
    {
        $token = $this->getAccessToken($scope);

        $response = Http::withToken($token)
            ->withHeaders([
                'Client_name' => config('forwarder.client_name'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->send($method, $this->baseUrl . $endpoint, [
                'json' => $payload,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Forwarder API Error: ' . $response->body());
    }

    // Optional helper methods
    public function post(string $endpoint, string $scope, array $payload = [])
    {
        return $this->request('POST', $endpoint, $scope, $payload);
    }

    public function get(string $endpoint, string $scope, array $query = [])
    {
        return $this->request('GET', $endpoint, $scope, ['query' => $query]);
    }
}
