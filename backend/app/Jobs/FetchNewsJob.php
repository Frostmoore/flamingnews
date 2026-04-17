<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Source;
use App\Services\ClusteringService;
use App\Services\RssFetcherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
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

        $this->fetchUserFeeds($rss);
    }

    private function fetchUserFeeds(RssFetcherService $rss): void
    {
        $feedUrls = DB::table('user_feeds')
            ->select('feed_url')
            ->distinct()
            ->pluck('feed_url');

        if ($feedUrls->isEmpty()) return;

        $now  = now();
        $total = 0;

        foreach ($feedUrls as $feedUrl) {
            $items = $rss->fetchFeed($feedUrl);
            if (empty($items)) continue;

            $rows = [];
            foreach ($items as $item) {
                $url = $item['url'] ?? '';
                if (empty($url)) continue;
                $rows[] = [
                    'feed_url'     => $feedUrl,
                    'title'        => $item['title'],
                    'description'  => $item['description'] ?? null,
                    'url'          => $url,
                    'url_to_image' => $item['image'] ?? null,
                    'published_at' => $item['published_at'] ?? null,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            if (!empty($rows)) {
                $saved = DB::table('feed_articles')->insertOrIgnore($rows);
                $total += $saved;
            }

            DB::table('user_feeds')->where('feed_url', $feedUrl)
                ->update(['last_fetched_at' => $now]);

            usleep(200_000);
        }

        // Elimina articoli utente più vecchi di 7 giorni
        $deleted = DB::table('feed_articles')
            ->where('created_at', '<', now()->subDays(7))
            ->delete();

        Log::info("FetchNewsJob user feeds: {$total} nuovi articoli, {$deleted} scaduti eliminati.");
    }
}
