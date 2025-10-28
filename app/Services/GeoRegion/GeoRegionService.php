<?php

namespace App\Services\GeoRegion;

use Illuminate\Support\Facades\Http;

class GeoRegionService
{
    protected array $jabodetabekCities = [
        // Depok (Jawa Barat)
        'depok',
        'kota depok',
        'depok city',

        // Bogor (Kota dan Kabupaten, Jawa Barat)
        'bogor',
        'kota bogor',
        'bogor city',
        'kabupaten bogor',
        'bogor regency',

        // Bekasi (Kota dan Kabupaten, Jawa Barat)
        'bekasi',
        'kota bekasi',
        'bekasi city',
        'kabupaten bekasi',
        'bekasi regency',

        // Tangerang (Kota, Kabupaten, dan Tangerang Selatan, Banten)
        'tangerang',
        'kota tangerang',
        'tangerang city',
        'kabupaten tangerang',
        'tangerang regency',
        'tangerang selatan',
        'kota tangerang selatan',
        'south tangerang',
        'south tangerang city',

        //tambahan bandung walau bukan jabodetabek
        'bandung',
        'kota bandung',
        'bandung city',
        'kabupaten bandung',
        'bandung regency',
        'bandung barat', // Kabupaten Bandung Barat
        'kabupaten bandung barat',
        'west bandung regency',
    ];

    protected array $jabodetabekProvinces = [
        'dki jakarta',
        'daerah khusus ibukota jakarta',
        'special capital region of jakarta',
        'jawa barat',
        'west java',
        'banten',
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
            'full_address' => null,
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

        // Ambil alamat lengkap (formatted_address)
        $result['full_address'] = $data['results'][0]['formatted_address'] ?? null;

        $ForwarderFTL = [
            // Bali
            'bali',

            // Bangka Belitung
            'kepulauan bangka belitung',
            'bangkabelitung islands',

            // Bengkulu
            'bengkulu',

            // DI Yogyakarta
            'daerah istimewa yogyakarta',
            'special region of yogyakarta',
            'di yogyakarta',
            'yogyakarta',

            // Gorontalo
            'gorontalo',

            // Jambi
            'jambi',

            // Jawa Tengah
            'jawa tengah',
            'central java',

            // Jawa Timur
            'jawa timur',
            'east java',

            // Kepulauan Riau
            'kepulauan riau',
            'riau islands',

            // Lampung
            'lampung',

            // Maluku Utara
            'maluku utara',
            'north maluku',

            // Nanggroe Aceh Darussalam (NAD)
            'aceh',

            // Nusa Tenggara Barat (NTB)
            'nusa tenggara barat',
            'west nusa tenggara',

            // Riau
            'riau',

            // Sumatera Barat
            'sumatera barat',
            'west sumatra',

            // Sumatera Selatan
            'sumatera selatan',
            'south sumatra',

            // Sumatera Utara
            'sumatera utara',
            'north sumatra',
        ];

        $province = strtolower($result['province'] ?? '');

        if (in_array($province, $ForwarderFTL)) {
            $result['transport_type'] = 3;
            $result['load_type'] = 4;
        } else {
            $result['transport_type'] = 1;
            $result['load_type'] = 1;
        }


        return $result ?: null;
    }

    public function isInJabodetabek(string $city, ?string $province): bool
    {
        $city = strtolower($city);
        $province = strtolower($province ?? '');

        if (!in_array($province, $this->jabodetabekProvinces)) {
            return false; // Bukan di provinsi Jabodetabek
        }

        // Jika provinsi adalah DKI Jakarta, maka otomatis Jabodetabek
        if (in_array($province, ['dki jakarta', 'daerah khusus ibukota jakarta', 'special capital region of jakarta'])) {
            return true;
        }

        return in_array($city, $this->jabodetabekCities);
    }

    public function determineShippingMethod(float $lat, float $lng)
    {
        $location = $this->getLocationFromGoogleMaps($lat, $lng);

        if ($location && $this->isInJabodetabek($location['city'], $location['province'])) {
            // return 'kurir_darat';
            return true;
        }

        // return 'laut_atau_udara';
        return false;
    }
}
