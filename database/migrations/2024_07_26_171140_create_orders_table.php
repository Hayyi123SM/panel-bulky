<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->timestamp('order_date');
            $table->integer('total_price');
            $table->string('shipping_address')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('order_status');
            $table->string('tracking_number')->nullable();
            $table->string('notes')->nullable();
            $table->boolean('has_reviewed')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
