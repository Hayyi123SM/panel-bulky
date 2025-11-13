<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds `note_discrepancy` to `products` as unsigned tiny integer with default 1.
     * Stores values in range 1..100.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // place after product_status_id as requested
            $table->unsignedTinyInteger('note_discrepancy')->default(0)->after('product_status_id');
        });

        // Optional: ensure existing rows have a non-null value (default already applied by column definition)
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('note_discrepancy');
        });
    }
};
