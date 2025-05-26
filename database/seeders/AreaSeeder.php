<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::unprepared(file_get_contents(database_path('sql/provinces.sql')));
        DB::unprepared(file_get_contents(database_path('sql/cities.sql')));
        DB::unprepared(file_get_contents(database_path('sql/districts.sql')));
        DB::unprepared(file_get_contents(database_path('sql/sub_districts.sql')));
    }
}
