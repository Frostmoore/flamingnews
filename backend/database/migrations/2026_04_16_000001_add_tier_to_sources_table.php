<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->tinyInteger('tier')->unsigned()->default(2)->after('country')
                  ->comment('1 = testata nazionale principale, 2 = regionale/niche/aggregatore');
        });
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropColumn('tier');
        });
    }
};
