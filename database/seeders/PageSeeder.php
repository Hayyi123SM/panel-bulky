<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::truncate();

        $pages = [
            [
                'title' => 'Kebijakan Privasi',
                'slug' => 'kebijakan-privasi',
                'content' => '<p>Kebijakan Privasi</p>',
            ],
            [
                'title' => 'Syarat & Ketentuan',
                'slug' => 'syarat-ketentuan',
                'content' => '<p>Syarat & Ketentuan</p>',
            ],
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'content' => '<p>Frequently Asked Questions</p>',
            ],
            [
                'title' => 'Cra Pembayaran',
                'slug' => 'cara-pembayaran',
                'content' => '<p>Cara Pembayaran</p>',
            ],
            [
                'title' => 'How To Shop',
                'slug' => 'cara-belanja',
                'content' => '<p>Cara Belanja</p>',
            ]
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
