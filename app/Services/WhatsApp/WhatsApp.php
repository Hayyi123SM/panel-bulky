<?php

namespace App\Services\WhatsApp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class WhatsApp
{
    private static Client $client;

    private static function init(): void
    {
        $apiKey = config('services.whatsapp.api_key');
        $baseUri = 'https://wa5408.cloudwa.my.id';

        self::$client = new Client([
            'base_uri' => $baseUri,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
            ]
        ]);
    }

    public static function sendMessage($phoneNumber, $message)
    {
        self::init();

        if(is_array($phoneNumber)){
            $message = [];
            foreach ($phoneNumber as $phone){
                $message[] = [
                    'recipient_type' => 'individual',
                    'to' => str($phone)->remove('+')->toString(),
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
                ];
            }
        } else {
            $message = [
                'recipient_type' => 'individual',
                'to' => str($phoneNumber)->remove('+')->toString(),
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ];
        }

        try {
            $response = self::$client->request('POST', '/api/v1/messages', [
                'json' => $message
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                return json_decode($response->getBody(), true);
            } else {
                return ['error' => 'Request failed'];
            }
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
