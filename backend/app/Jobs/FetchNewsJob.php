<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Source;
use App\Services\ClusteringService;
use App\Services\RssFetcherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchNewsJob implements ShouldQueue
{
    use Queueable;

    public function handle(RssFetcherService $rss, ClusteringService $clustering): void
    {
        $sources = Source::whereNotNull('feed_url')
            ->where('active', true)
            ->get();

        if ($sources->isEmpty()) {
            Log::warning('FetchNewsJob: nessuna fonte con feed_url configurato.');
            return;
        }

        $total = 0;

        foreach ($sources as $source) {
            $items = $rss->fetchFeed($source->feed_url);
            $saved = 0;

            foreach ($items as $item) {
                $url = $item['url'] ?? '';
                if (empty($url)) continue;
                if (Article::where('url', $url)->exists()) continue;

                Article::create([
                    'title'         => $item['title'],
                    'description'   => $item['description'] ?? null,
                    'content'       => null,
                    'url'           => $url,
                    'url_to_image'  => $item['image'] ?? null,
                    'source_name'   => $source->name,
                    'source_domain' => $source->domain,
                    'author'        => null,
                    'published_at'  => $item['published_at'] ?? now(),
                    'category'      => $rss->mapCategory($item['categories'] ?? [], $source->domain),
                    'topic_id'      => null,
                    'is_main'       => true, // provvisorio: recluster correggerà
                ]);

                $saved++;
            }

            $total += $saved;

            if (count($items) > 0) {
                Log::info("FetchNewsJob [{$source->domain}]: {$saved} nuovi su " . count($items) . " items.");
            }

            // Piccola pausa tra le fonti per non martellare i server
            usleep(200_000);
        }

        Log::info("FetchNewsJob: {$total} articoli totali salvati. Avvio re-aggregazione…");

        $clustering->reclusterRecent(hours: 48);

        Log::info("FetchNewsJob completato.");
    }
}
