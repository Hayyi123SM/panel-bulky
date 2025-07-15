<?php

namespace App\Services\WhatsApp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class WhatsApp
{
    private static Client $accountClient;
    private static Client $deviceClient;
    private static string $deviceId;

    private static function init(): void
    {
        $baseUri = 'https://api.starsender.online';
        $accountApiKey = config('services.whatsapp.api_key_account');

        self::$accountClient = new Client([
            'base_uri' => $baseUri,
            'headers' => [
                'Authorization' => $accountApiKey,
            ],
        ]);

        $deviceApiKey = self::getAvailableApiKey();

        self::$deviceClient = new Client([
            'base_uri' => $baseUri,
            'headers' => [
                'Authorization' => $deviceApiKey,
            ],
        ]);
    }

    private static function getAvailableApiKey(): string
    {
        $devices = config('services.whatsapp.devices');
        $statusList = [];

        foreach ($devices as $device) {
            try {
                $response = self::$accountClient->request('GET', '/api/devices/' . $device['id']);
                $status = json_decode($response->getBody(), true)['data']['device']['status'] ?? 'unknown';
                $statusList[] = [
                    'id' => $device['id'],
                    'status' => $status,
                ];

                if ($status === 'connected') {
                    self::$deviceId = $device['id'];
                    return $device['api_key'];
                }
            } catch (\Exception $e) {
                $statusList[] = [
                    'id' => $device['id'],
                    'status' => 'error',
                ];
            }
        }

        // Semua device gagal
        $statusMsg = collect($statusList)
            ->map(fn($s) => "{$s['id']}: {$s['status']}")
            ->join(', ');

        logger()->warning('Semua device WhatsApp untuk notifikasi tidak terhubung.', [
            'status_devices' => $statusMsg
        ]);
        throw new \RuntimeException("Semua device WhatsApp untuk notifikasi tidak terhubung. Status devices: {$statusMsg}");
    }

    public static function sendMessage(string|array $phoneNumber, string $message): array
    {
        self::init();

        $results = [];

        $numbers = is_array($phoneNumber) ? $phoneNumber : [$phoneNumber];

        foreach ($numbers as $phone) {
            $payload = [
                'messageType' => 'text',
                'to' => str($phone)->remove('+')->toString(),
                'body' => $message,
            ];

            try {
                $response = self::$deviceClient->request('POST', '/api/send', [
                    'json' => $payload,
                ]);
                $results[] = json_decode($response->getBody(), true);
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $results[] = json_decode($e->getResponse()->getBody(), true);
                } else {
                    $results[] = ['error' => 'Request failed'];
                }
            } catch (GuzzleException $e) {
                $results[] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }
}
