<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsJob;
use App\Jobs\FetchPrimePagineJob;
use App\Models\Article;
use App\Services\ClusteringService;
use App\Services\RssFetcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshNewsCommand extends Command
{
    protected $signature = 'news:refresh
                            {--skip-prime-pagine : Non aggiornare le prime pagine}
                            {--skip-seed : Non ri-eseguire il seeder delle fonti}';

    protected $description = 'Svuota il DB e riesegue seed + fetch notizie + fetch prime pagine';

    public function handle(): int
    {
        // ── 1. Svuota le tabelle ──────────────────────────────────────────────
        $this->info('Svuoto il database...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('article_likes')->truncate();
        DB::table('user_reads')->truncate();
        DB::table('articles')->truncate();
        DB::table('topics')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->line('  ✓ article_likes, user_reads, articles, topics svuotati.');

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

        // ── 4. Fetch prime pagine ─────────────────────────────────────────────
        if (!$this->option('skip-prime-pagine')) {
            $this->info('Avvio FetchPrimePagineJob...');
            (new FetchPrimePagineJob())->handle();
            $this->line('  ✓ Prime pagine aggiornate.');
        }

        $this->newLine();
        $this->info('Refresh completato.');

        return self::SUCCESS;
    }
}
