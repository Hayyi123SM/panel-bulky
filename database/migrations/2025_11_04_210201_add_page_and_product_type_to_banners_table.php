<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->enum('page', ['home', 'product'])->nullable()->after('id');
            $table->enum('product_type', ['palet', 'truck_load', 'container'])->nullable()->after('page');
            $table->softDeletes()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('page');
            $table->dropColumn('product_type');
            $table->dropSoftDeletes();
        });
    }
};
