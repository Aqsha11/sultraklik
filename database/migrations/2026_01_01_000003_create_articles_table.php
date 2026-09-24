<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('featured_image')->nullable();
            $table->string('image_caption')->nullable();
            $table->string('image_credit')->nullable();
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_headline')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('articles');
    }
};