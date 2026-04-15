<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsJob;
use App\Models\Article;
use App\Services\ClusteringService;
use App\Services\RssFetcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshNewsCommand extends Command
{
    protected $signature = 'news:refresh
                            {--skip-seed : Non ri-eseguire il seeder delle fonti}';

    protected $description = 'Svuota il DB e riesegue seed + fetch notizie RSS';

    public function handle(): int
    {
        // ── 1. Svuota le tabelle ──────────────────────────────────────────────
        $this->info('Svuoto il database...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('article_likes')->truncate();
        DB::table('article_shares')->truncate();
        DB::table('article_clicks')->truncate();
        DB::table('user_reads')->truncate();
        DB::table('articles')->truncate();
        DB::table('topics')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->line('  ✓ Tabelle svuotate.');

        // ── 2. Seed delle fonti ───────────────────────────────────────────────
        if (!$this->option('skip-seed')) {
            $this->info('Eseguo SourcesSeeder...');
            $this->call('db:seed', ['--class' => 'SourcesSeeder', '--force' => true]);
        }

        // ── 3. Fetch notizie RSS ──────────────────────────────────────────────
        $this->info('Avvio FetchNewsJob (RSS + clustering)...');

        (new FetchNewsJob())->handle(
            app(RssFetcherService::class),
            app(ClusteringService::class),
        );

        $total = Article::count();
        $this->line("  ✓ {$total} articoli salvati.");

        $this->newLine();
        $this->info('Refresh completato.');

        return self::SUCCESS;
    }
}
