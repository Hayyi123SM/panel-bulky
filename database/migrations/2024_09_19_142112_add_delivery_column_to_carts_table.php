<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignUuid('address_id')->nullable()->after('user_id')
                ->constrained('addresses')->nullOnDelete();
            $table->integer('shipping_cost')->default(0)->after('shipping_method');
            $table->integer('extra_helper_id')->nullable()->after('shipping_cost');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign('carts_address_id_foreign');
            $table->dropColumn('address_id');
            $table->dropColumn('shipping_cost');
            $table->dropColumn('extra_helper_id');
        });
    }
};
