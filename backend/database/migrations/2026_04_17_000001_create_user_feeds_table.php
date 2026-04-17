<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('feed_url');
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_feed_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_feed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('url_to_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['user_feed_id', 'url'], 'user_feed_articles_unique_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feed_articles');
        Schema::dropIfExists('user_feeds');
    }
};
