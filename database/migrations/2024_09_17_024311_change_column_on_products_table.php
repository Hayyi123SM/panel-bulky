<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('images')->nullable()->change();
            $table->integer('price')->nullable()->change();
            $table->longText('description')->nullable()->change();

            $table->dropForeign('products_warehouse_id_foreign');
            $table->dropForeign('products_product_category_id_foreign');
            $table->dropForeign('products_product_condition_id_foreign');
            $table->dropForeign('products_product_status_id_foreign');

            $table->foreignUuid('warehouse_id')->nullable()->change()->constrained()->nullOnDelete();
            $table->foreignUuid('product_category_id')->nullable()->change()->constrained()->nullOnDelete();
            $table->foreignUuid('product_condition_id')->nullable()->change()->constrained()->nullOnDelete();
            $table->foreignUuid('product_status_id')->nullable()->change()->constrained()->nullOnDelete();

            $table->integer('vehicle_type_id')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('images')->change();
            $table->integer('price')->change();
            $table->longText('description')->change();

            $table->dropColumn('vehicle_type_id');
        });
    }
};
