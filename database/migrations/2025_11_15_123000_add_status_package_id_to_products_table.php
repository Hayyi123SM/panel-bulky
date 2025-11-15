<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Add nullable UUID column for status_package relationship
            $table->uuid('status_package_id')->nullable()->after('product_status_id')->index();

            // Add foreign key to status_packages (simple and standard)
            $table->foreign('status_package_id')
                ->references('id')
                ->on('status_packages')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key and column (straightforward rollback)
            $table->dropForeign(['status_package_id']);
            $table->dropColumn('status_package_id');
        });
    }
};
