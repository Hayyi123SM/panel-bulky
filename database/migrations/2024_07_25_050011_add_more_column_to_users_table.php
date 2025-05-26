<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('sub_district_id')->after('id')->nullable()->constrained('sub_districts')->cascadeOnUpdate()->nullOnDelete();
            $table->string('phone_number')->nullable()->after('sub_district_id');
            $table->string('address')->nullable()->after('sub_district_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sub_district_id');
            $table->dropColumn('phone_number');
            $table->dropColumn('address');
        });
    }
};
