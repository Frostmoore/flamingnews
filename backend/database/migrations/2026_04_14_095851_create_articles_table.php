<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('url', 2048)->unique();
            $table->string('url_to_image', 2048)->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_domain')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('category')->default('generale');
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->timestamps();

            $table->index('category');
            $table->index('published_at');
            $table->index('topic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
