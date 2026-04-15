<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Source;
use App\Models\Topic;
use App\Services\WorldNewsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchNewsJob implements ShouldQueue
{
    use Queueable;

    public function handle(WorldNewsService $wn): void
    {
        $total = 0;

        // ── 1. TOP NEWS: cluster già pronti dall'API ──────────────────────────
        $total += $this->processTopNews($wn);

        // ── 2. THIN CATEGORIES: fetch separato, clustering Python dopo ────────
        foreach (WorldNewsService::THIN_CATEGORIES as $category) {
            $total += $this->processThinCategory($wn, $category);
        }

        Log::info("FetchNewsJob completato: {$total} articoli salvati.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOP NEWS
    // ─────────────────────────────────────────────────────────────────────────

    private function processTopNews(WorldNewsService $wn): int
    {
        $clusters = $wn->fetchTopNewsRaw();

        if (empty($clusters)) {
            Log::warning('FetchNewsJob: top-news ha restituito 0 cluster.');
            return 0;
        }

        // Cache tier-1 domains per evitare N query al DB
        $tier1 = Source::where('tier', 1)->pluck('domain')->flip()->all();

        $saved = 0;

        foreach ($clusters as $cluster) {
            $articles = $cluster['news'] ?? [];
            if (empty($articles)) continue;

            // Scegli l'articolo principale del cluster
            $main     = $this->pickMainArticle($articles, $tier1, $wn);
            $coverage = array_filter($articles, fn($a) => ($a['url'] ?? '') !== ($main['url'] ?? ''));

            $category = $wn->mapCategory($main['category'] ?? null);

            // Salta se l'URL principale è già in DB senza topic (verrà aggiornato)
            $existing = Article::where('url', $main['url'])->first();

            if ($existing) {
                // Recupera o crea il topic
                $topic = $existing->topic ?? $this->createTopic($main, count($articles));
                if (!$existing->topic_id) {
                    $existing->update(['topic_id' => $topic->id]);
                }
            } else {
                $topic = $this->createTopic($main, count($articles));
                $this->saveArticle($main, $category, $topic->id, $wn);
                $saved++;
            }

            // Salva gli articoli di coverage
            foreach ($coverage as $raw) {
                $url = $raw['url'] ?? '';
                if (empty($url) || Article::where('url', $url)->exists()) continue;
                $this->saveArticle($raw, $category, $topic->id, $wn);
                $saved++;
            }
        }

        Log::info("FetchNewsJob [top-news]: {$saved} articoli da " . count($clusters) . ' cluster.');
        return $saved;
    }

    /**
     * Sceglie l'articolo rappresentativo del cluster con priorità:
     *   1. Fonte tier-1 nazionale
     *   2. Ha immagine
     *   3. Titolo più lungo
     */
    private function pickMainArticle(array $articles, array $tier1, WorldNewsService $wn): array
    {
        usort($articles, function (array $a, array $b) use ($tier1, $wn) {
            $domainA = $wn->extractDomain($a['url'] ?? '');
            $domainB = $wn->extractDomain($b['url'] ?? '');

            $tierA = isset($tier1[$domainA]) ? 0 : 1;
            $tierB = isset($tier1[$domainB]) ? 0 : 1;
            if ($tierA !== $tierB) return $tierA <=> $tierB;

            $imgA = !empty($a['image']) || !empty($a['images']) ? 0 : 1;
            $imgB = !empty($b['image']) || !empty($b['images']) ? 0 : 1;
            if ($imgA !== $imgB) return $imgA <=> $imgB;

            return strlen($b['title'] ?? '') <=> strlen($a['title'] ?? '');
        });

        return $articles[0];
    }

    private function createTopic(array $mainArticle, int $articleCount): Topic
    {
        return Topic::create([
            'title'         => $mainArticle['title'] ?? 'Evento senza titolo',
            'keywords'      => [],
            'article_count' => $articleCount,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // THIN CATEGORIES
    // ─────────────────────────────────────────────────────────────────────────

    private function processThinCategory(WorldNewsService $wn, string $category): int
    {
        $articles = $wn->fetchThinCategory($category, 30);
        $saved    = 0;

        foreach ($articles as $raw) {
            $url = $raw['url'] ?? '';
            if (empty($url) || Article::where('url', $url)->exists()) continue;
            $this->saveArticle($raw, $category, null, $wn);
            $saved++;
        }

        Log::info("FetchNewsJob [thin/{$category}]: {$saved} articoli salvati.");
        return $saved;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PERSISTENZA
    // ─────────────────────────────────────────────────────────────────────────

    private function saveArticle(array $raw, string $category, ?int $topicId, WorldNewsService $wn): void
    {
        $url = $raw['url'] ?? '';
        if (empty($url)) return;

        $domain = $wn->extractDomain($url);

        Article::create([
            'title'        => $wn->decodeHtml($raw['title']   ?? ''),
            'description'  => $wn->decodeHtml($raw['summary'] ?? ''),
            'content'      => $wn->decodeHtml($raw['text']    ?? ''),
            'url'          => $url,
            'url_to_image' => $wn->parseImage($raw),
            'source_name'  => $wn->guessSourceName($domain),
            'source_domain'=> $domain,
            'author'       => ($author = $wn->parseAuthors($raw['authors'] ?? null))
                                ? $wn->decodeHtml($author)
                                : null,
            'published_at' => $wn->parsePublishedAt($raw['publish_date'] ?? null),
            'category'     => $category,
            'topic_id'     => $topicId,
        ]);
    }
}
