<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul_id');
            $table->string('judul_en');
            $table->string('slug_id')->unique();
            $table->string('slug_en')->unique();
            $table->longText('konten_id');
            $table->longText('konten_en');
            $table->text('highlight_id')->nullable();
            $table->text('highlight_en')->nullable();
            $table->string('featured_image_url')->nullable();
            $table->foreignUuid('kategori_id')
                ->constrained('blog_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('meta_title_id')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description_id')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['kategori_id', 'is_active']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
