<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_price')->default(0);
            $table->string('coupon_code')->nullable();
            $table->integer('discount_amount')->nullable();
            $table->integer('tax_amount')->nullable();
            $table->string('notes')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('payment_method')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
