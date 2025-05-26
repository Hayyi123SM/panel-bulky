<?php

namespace Database\Seeders;

use App\Models\PaymentMethodGroup;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethodGroups = [
            'Kartu Kredit' => [
                [
                    'name' => 'Kartu Kredit',
                    'code' => 'credit_card',
                    'is_active' => true,
                ]
            ],
            'Bank Transfer / VA' => [
                [
                    'name' => 'BCA',
                    'code' => 'bca_va',
                    'is_active' => true,
                ],
                [
                    'name' => 'Mandiri',
                    'code' => 'mandiri_va',
                    'is_active' => true,
                ],
                [
                    'name' => 'BNI',
                    'code' => 'bni_va',
                    'is_active' => true,
                ],
                [
                    'name' => 'BRI',
                    'code' => 'bri_va',
                    'is_active' => true,
                ],
                [
                    'name' => 'BRI',
                    'code' => 'permata_va',
                    'is_active' => true,
                ],
                [
                    'name' => 'CIMB',
                    'code' => 'cimb_va',
                    'is_active' => true,
                ],
                [
                    'name' => 'Other Bank',
                    'code' => 'other_va',
                    'is_active' => true,
                ]
            ],
        ];

        foreach ($paymentMethodGroups as $group => $paymentMethods) {
            $group = PaymentMethodGroup::create([
                'name' => $group,
            ]);

            foreach ($paymentMethods as $paymentMethod) {
                $group->paymentMethods()->create([
                    'name' => $paymentMethod['name'],
                    'code' => $paymentMethod['code'],
                    'is_active' => $paymentMethod['is_active'],
                ]);
            }
        }
    }
}
