<?php

namespace App\Services\WMS;

use App\Services\WMS\Contracts\ProductDropdownServiceInterface;

class DummyProductDropdownService implements ProductDropdownServiceInterface
{
    public function getDropdownOptions(): array
    {
        return [
            1 => 'Produk Dummy A',
            2 => 'Produk Dummy B',
        ];
    }

    public function getDropdownDetail($id): ?array
    {
        $data = [
            1 => [
                'id' => 1,
                'name_document' => 'Produk Dummy A',
                'old_price' => 100000,
                'new_price' => 90000,
                'total_quantity' => 10,
                'packaging_type' => 'palet',
                'dimension' => [
                    'length' => 100,
                    'width' => 50,
                    'height' => 30,
                    'weight' => 20,
                ],
                'pdf_url' => 'https://example.com/dummy-a.pdf',
            ],
            2 => [
                'id' => 2,
                'name_document' => 'Produk Dummy B',
                'old_price' => 200000,
                'new_price' => 180000,
                'total_quantity' => 5,
                'packaging_type' => 'container',
                'dimension' => [
                    'length' => 200,
                    'width' => 100,
                    'height' => 60,
                    'weight' => 40,
                ],
                'pdf_url' => 'https://example.com/dummy-b.pdf',
            ],
        ];
        return $data[$id] ?? null;
    }
}
