<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('images');
            $table->string('name');
            $table->string('slug');
            $table->string('id_pallet')->nullable();
            $table->integer('price');
            $table->integer('total_quantity')->default(0);
            $table->string('pdf_file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->longText('description');
            $table->foreignUuid('warehouse_id');
            $table->foreignUuid('product_category_id');
            $table->foreignUuid('product_condition_id');
            $table->foreignUuid('product_status_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
