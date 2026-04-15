<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_main')->default(false)->after('topic_id')->index();
        });

        // Articoli senza topic: sono tutti principali
        DB::table('articles')->whereNull('topic_id')->update(['is_main' => true]);

        // Per topic esistenti: marca come main il primo articolo salvato (id minore per topic)
        DB::statement('
            UPDATE articles a
            INNER JOIN (
                SELECT MIN(id) AS min_id FROM articles
                WHERE topic_id IS NOT NULL
                GROUP BY topic_id
            ) m ON a.id = m.min_id
            SET a.is_main = true
        ');
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('is_main');
        });
    }
};
