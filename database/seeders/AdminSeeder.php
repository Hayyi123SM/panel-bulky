<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        activity()->withoutLogs(function () {
            Admin::create([
                'name' => 'Developer',
                'email' => 'dev@dev.com',
                'password' => Hash::make('password'),
                'api_key' => bin2hex(random_bytes(24)),
                'is_dev' => 1,
            ]);
        });
    }
}
