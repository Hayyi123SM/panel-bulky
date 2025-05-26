<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('xendit_id');
            $table->string('midtrans_snap_token')->nullable()->after('status');
            $table->string('midtrans_redirect_url')->nullable()->after('midtrans_snap_token');

            $table->foreignUuid('payment_method_id')
                ->nullable()
                ->after('order_id')
                ->constrained('payment_methods');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('xendit_id')->nullable()->after('status');
            $table->dropColumn('midtrans_snap_token');
            $table->dropColumn('midtrans_redirect_url');
            $table->dropConstrainedForeignId('payment_method_id');
        });
    }
};
