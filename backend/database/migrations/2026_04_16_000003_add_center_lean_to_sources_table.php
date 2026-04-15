<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sources MODIFY political_lean ENUM('left','center-left','center','center-right','right','international','altro') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sources MODIFY political_lean ENUM('left','center','right','international','altro') NULL");
    }
};
