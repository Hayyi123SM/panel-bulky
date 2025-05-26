<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_tax_active')->default(false)->after('payment_expired_at');
            $table->integer('tax_rate')->default(0)->after('is_tax_active');
            $table->integer('tax_amount')->default(0)->after('tax_rate');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_tax_active');
            $table->dropColumn('tax_rate');
            $table->dropColumn('tax_amount');
        });
    }
};
