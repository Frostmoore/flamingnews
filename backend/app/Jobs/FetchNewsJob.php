<?php

namespace App\Jobs;

use App\Services\GNewsService;
use App\Services\WorldNewsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchNewsJob implements ShouldQueue
{
    use Queueable;

    private array $categories = [
        'politica', 'economia', 'esteri', 'tecnologia', 'sport', 'cultura',
        'generale', 'scienza', 'salute', 'ambiente', 'istruzione', 'cibo', 'viaggi',
    ];

    public function handle(GNewsService $gnews, WorldNewsService $worldnews): void
    {
        $provider = config('services.news_provider', 'gnews'); // 'gnews' | 'worldnews' | 'both'
        $total    = 0;

        foreach ($this->categories as $category) {
            try {
                if ($provider === 'gnews' || $provider === 'both') {
                    $articles = $gnews->fetchByCategory($category, max: 10);
                    $saved    = $gnews->saveArticles($articles, $category);
                    $total   += $saved;
                    Log::info("FetchNewsJob [gnews]: {$saved} articoli per '{$category}'");
                }

                if ($provider === 'worldnews' || $provider === 'both') {
                    $articles = $worldnews->fetchByCategory($category, max: 10);
                    $saved    = $worldnews->saveArticles($articles, $category);
                    $total   += $saved;
                    Log::info("FetchNewsJob [worldnews]: {$saved} articoli per '{$category}'");
                }
            } catch (\Throwable $e) {
                Log::error("FetchNewsJob [{$provider}]: errore per '{$category}': " . $e->getMessage());
            }
        }

        Log::info("FetchNewsJob completato ({$provider}): {$total} articoli totali salvati.");
    }
}
