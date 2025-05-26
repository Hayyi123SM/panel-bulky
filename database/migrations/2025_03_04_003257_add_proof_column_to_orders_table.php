<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('proof_name')->nullable()->after('has_reviewed');
            $table->string('proof_description')->nullable()->after('proof_name');
            $table->string('proof_image')->nullable()->after('proof_description');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('proof_name');
            $table->dropColumn('proof_description');
            $table->dropColumn('proof_image');
        });
    }
};
