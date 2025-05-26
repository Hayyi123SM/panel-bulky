<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->json('name_trans')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->dropColumn('name_trans');
        });
    }
};
