<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\ClusteringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ClusterArticlesJob implements ShouldQueue
{
    use Queueable;

    public function handle(ClusteringService $clustering): void
    {
        if (!$clustering->isAvailable()) {
            Log::channel('clustering')->warning('ClusterArticlesJob: microservizio non disponibile, skip.');
            return;
        }

        // Articoli senza topic degli ultimi 2 giorni
        $articles = Article::whereNull('topic_id')
            ->where('created_at', '>=', now()->subDays(2))
            ->get(['id', 'title', 'description', 'content'])
            ->toArray();

        if (empty($articles)) {
            Log::info('ClusterArticlesJob: nessun articolo da clusterizzare.');
            return;
        }

        Log::info('ClusterArticlesJob: invio ' . count($articles) . ' articoli al microservizio.');

        $clusters = $clustering->clusterArticles($articles);

        if (empty($clusters)) {
            Log::channel('clustering')->warning('ClusterArticlesJob: nessun cluster restituito.');
            return;
        }

        $clustering->applyClusterResults($clusters);

        Log::info('ClusterArticlesJob: ' . count($clusters) . ' cluster applicati.');
    }
}
