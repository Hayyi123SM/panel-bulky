<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('home_heroes', function (Blueprint $table) {
            $table->json('title_trans')->nullable()->after('id');
            $table->json('subtitle_trans')->nullable()->after('title_trans');
            $table->dropColumn('title');
            $table->dropColumn('subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('home_heroes', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->text('subtitle')->after('title');
            $table->dropColumn('title_trans');
            $table->dropColumn('subtitle_trans');
        });
    }
};
