<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('comments_count');
        });

        Schema::create('article_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['article_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_likes');

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });
    }
};
