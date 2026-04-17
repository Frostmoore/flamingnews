<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_feed_articles');

        Schema::create('feed_articles', function (Blueprint $table) {
            $table->id();
            $table->string('feed_url');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('url_to_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['feed_url', 'url'], 'feed_articles_unique_url');
            $table->index('feed_url');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_articles');
    }
};
