<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
            $table->string('name')->nullable()->change();
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });

        Schema::table('product_conditions', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
        });

        Schema::table('product_statuses', function (Blueprint $table) {
            $table->string('status')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
