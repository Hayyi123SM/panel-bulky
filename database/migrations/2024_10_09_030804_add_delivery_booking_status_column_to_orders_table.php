<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_shippings', function (Blueprint $table) {
            $table->string('booking_status')->nullable()->after('booking_id');
            $table->string('tracking_url')->nullable()->after('booking_status');
        });
    }

    public function down(): void
    {
        Schema::table('order_shippings', function (Blueprint $table) {
            $table->dropColumn('booking_status');
            $table->dropColumn('tracking_url');
        });
    }
};
