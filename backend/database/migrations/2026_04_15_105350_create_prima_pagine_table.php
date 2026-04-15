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
        Schema::create('prima_pagine', function (Blueprint $table) {
            $table->id();
            $table->string('source_name');
            $table->string('source_domain')->index();
            $table->string('political_lean')->nullable();
            $table->string('image_url', 1000)->nullable();
            $table->string('headline', 500)->nullable();
            $table->string('article_url', 1000)->nullable();
            $table->date('edition_date')->index();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['source_domain', 'edition_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prima_pagine');
    }
};
