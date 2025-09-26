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
        Schema::table('order_shippings', function (Blueprint $table) {
            $table->decimal('insurance_amount', 12, 2)->default(0)->after('is_insurance');
            $table->decimal('insurance_percentage', 5, 3)->default(0)->after('insurance_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_shippings', function (Blueprint $table) {
            $table->dropColumn(['insurance_amount', 'insurance_percentage']);
        });
    }
};
