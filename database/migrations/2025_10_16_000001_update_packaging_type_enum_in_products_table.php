<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            DB::statement("ALTER TABLE products MODIFY packaging_type ENUM('palet', 'truck_load', 'container') NOT NULL");
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            DB::statement("ALTER TABLE products MODIFY packaging_type ENUM('palet', 'container') NOT NULL");
        });
    }
};
