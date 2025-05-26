<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sub_districts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('district_id');
            $table->string('name');
            $table->string('code');
            $table->string('postal_code');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_districts');
    }
};
