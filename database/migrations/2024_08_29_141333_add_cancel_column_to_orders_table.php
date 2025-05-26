<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cancel_reason')->nullable()->after('order_status');
            $table->integer('refund_amount')->default(0)->after('cancel_reason');
            $table->timestamp('paid_off_at')->nullable()->after('refund_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cancel_reason');
            $table->dropColumn('refund_amount');
            $table->dropColumn('paid_off_at');
        });
    }
};
