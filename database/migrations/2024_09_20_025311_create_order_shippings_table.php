<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_shippings', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->integer('shipping_cost')->default(0);
            $table->string('vehicle_type')->nullable();
            $table->integer('booking_id')->nullable();
            $table->integer('extra_helper_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shippings');
    }
};
