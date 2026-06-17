<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_id');
            $table->string('nama_en');
            $table->string('slug_id')->unique();
            $table->string('slug_en')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('order_column')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_categories');
    }
};
