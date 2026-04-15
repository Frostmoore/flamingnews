<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsJob;
use App\Jobs\ClusterArticlesJob;
use App\Models\Article;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    protected $signature = 'news:fetch {--cluster : Esegui anche il clustering dopo il fetch}';
    protected $description = 'Fetcha le notizie dalle API configurate e le salva nel DB';

    public function handle(): int
    {
        $before = Article::count();
        $this->info("Articoli prima del fetch: {$before}");
        $this->newLine();

        $this->info('Avvio FetchNewsJob...');
        (new FetchNewsJob())->handle(
            app(\App\Services\RssFetcherService::class),
            app(\App\Services\ClusteringService::class),
        );

        $after = Article::count();
        $this->info("Articoli dopo il fetch: {$after} (+" . ($after - $before) . " nuovi)");

        if ($this->option('cluster') && $after > 0) {
            $this->newLine();
            $this->info('Avvio ClusterArticlesJob...');
            (new ClusterArticlesJob())->handle();
            $this->info('Clustering completato.');
        }

        return self::SUCCESS;
    }
}
