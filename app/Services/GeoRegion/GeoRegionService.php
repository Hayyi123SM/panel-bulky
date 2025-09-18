<?php

namespace App\Services\GeoRegion;

use Illuminate\Support\Facades\Http;

class GeoRegionService
{
    protected array $jabodetabekCities = [
        'jakarta pusat',
        'jakarta utara',
        'jakarta timur',
        'jakarta barat',
        'jakarta selatan',
        'depok',
        'bogor',
        'bekasi',
        'tangerang',
        'tangerang selatan',
    ];

    public function getLocationFromGoogleMaps(float $lat, float $lng): ?array
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'latlng' => "$lat,$lng",
            'key' => $apiKey,
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        if (empty($data['results'])) {
            return null;
        }

        $components = $data['results'][0]['address_components'];

        // mapping tipe google → key lokal
        $map = [
            'locality' => 'city',
            'administrative_area_level_2' => 'city',
            'administrative_area_level_1' => 'province',
            'postal_code' => 'post_code',
        ];

        // Default if empty
        $result = [
            'city' => null,
            'province' => null,
            'post_code' => null,
            'transport_type' => null,
            'load_type' => null,
        ];

        foreach ($components as $component) {
            foreach ($map as $type => $key) {
                if (in_array($type, $component['types'])) {
                    $result[$key] = strtolower($component['long_name']);
                }
            }
        }

        $jawaBali = [
            'dki jakarta',
            'daerah khusus ibukota jakarta',
            'special capital region of jakarta',
            'banten',
            'jawa barat',
            'west java',
            'jawa tengah',
            'central java',
            'jawa timur',
            'east java',
            'daerah istimewa yogyakarta',
            'special region of yogyakarta',
            'di yogyakarta',
            'yogyakarta',
            'bali',
        ];

        $province = strtolower($result['province'] ?? '');

        if (in_array($province, $jawaBali)) {
            $result['transport_type'] = 3;
            $result['load_type'] = 4;
        } else {
            $result['transport_type'] = 1;
            $result['load_type'] = 1;
        }


        return $result ?: null;
    }

    public function isInJabodetabek(string $city): bool
    {
        return in_array(strtolower($city), $this->jabodetabekCities);
    }

    public function determineShippingMethod(float $lat, float $lng): bool
    {
        $location = $this->getLocationFromGoogleMaps($lat, $lng);

        if ($location && $this->isInJabodetabek($location['city'])) {
            // return 'kurir_darat';
            return true;
        }

        // return 'laut_atau_udara';
        return false;
    }
}
