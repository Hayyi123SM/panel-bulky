<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('pickup_info.open_hour', [
            [
                'day' => 1,
                'name' => 'Senin',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_open' => true
            ],
            [
                'day' => 2,
                'name' => 'Selasa',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_open' => true
            ],
            [
                'day' => 3,
                'name' => 'Rabu',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_open' => true
            ],
            [
                'day' => 4,
                'name' => 'Kamis',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_open' => true
            ],
            [
                'day' => 5,
                'name' => 'Jumat',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_open' => true
            ],
            [
                'day' => 6,
                'name' => 'Sabtu',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_open' => true
            ],
            [
                'day' => 7,
                'name' => 'Minggu',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'is_open' => true
            ],
        ]);
    }
};
