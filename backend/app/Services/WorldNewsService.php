<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorldNewsService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.worldnewsapi.com';

    /**
     * Mapping from internal category slugs → World News API categories.
     * null = nessun filtro categoria (usa solo lingua/paese).
     */
    private array $categoryMap = [
        'politica'   => 'politics',
        'economia'   => 'business',
        'esteri'     => null,          // broad language filter senza categoria
        'tecnologia' => 'technology',
        'sport'      => 'sports',
        'cultura'    => 'entertainment',
        'generale'   => 'lifestyle',
        'scienza'    => 'science',
        'salute'     => 'health',
        'ambiente'   => 'environment',
        'istruzione' => 'education',
        'cibo'       => 'food',
        'viaggi'     => 'travel',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.worldnews.key', '');
    }

    /**
     * Fetch top news for a given internal category.
     * Uses /search-news with language=it and optional category filter.
     */
    public function fetchByCategory(string $category, int $max = 10): array
    {
        $wnCategory = $this->categoryMap[$category] ?? null;

        $params = [
            'api-key'          => $this->apiKey,
            'language'         => 'it',
            'source-countries' => 'it',
            'number'           => $max,
            'sort'             => 'publish-time',
            'sort-direction'   => 'DESC',
        ];

        if ($wnCategory !== null) {
            $params['categories'] = $wnCategory;
        }

        // Per "esteri" apriamo a tutte le lingue ma filtriamo per italiano
        if ($category === 'esteri') {
            unset($params['source-countries']);
        }

        $response = Http::timeout(20)->get("{$this->baseUrl}/search-news", $params);

        if ($response->failed()) {
            Log::warning("WorldNews fetch failed for category '{$category}'", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        return $response->json('news', []);
    }

    /**
     * Fetch the most significant stories from Italy via /top-news.
     * Returns a flat array of articles (appiattisce la struttura top_news[].news[]).
     */
    public function fetchTopNews(int $max = 30): array
    {
        $response = Http::timeout(20)->get("{$this->baseUrl}/top-news", [
            'api-key'        => $this->apiKey,
            'source-country' => 'it',
            'language'       => 'it',
        ]);

        if ($response->failed()) {
            Log::warning("WorldNews top-news failed", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        // La risposta è: { "top_news": [ { "news": [...] }, ... ] }
        $groups = $response->json('top_news', []);
        $flat   = [];
        foreach ($groups as $group) {
            foreach ($group['news'] ?? [] as $article) {
                $flat[] = $article;
                if (count($flat) >= $max) break 2;
            }
        }

        return $flat;
    }

    /**
     * Persist a batch of raw World News articles for a given category.
     * Skips duplicates (by URL). Returns the count of newly saved articles.
     */
    public function saveArticles(array $articles, string $category): int
    {
        $saved = 0;

        foreach ($articles as $raw) {
            $url = $raw['url'] ?? null;
            if (empty($url)) {
                continue;
            }

            if (Article::where('url', $url)->exists()) {
                continue;
            }

            $sourceDomain = $this->extractDomain($url);

            // World News: autori è un array
            $author = null;
            if (!empty($raw['authors']) && is_array($raw['authors'])) {
                $author = implode(', ', array_slice($raw['authors'], 0, 2));
            }

            // publish_date formato "2026-04-14 10:00:00" oppure ISO
            $publishedAt = null;
            if (!empty($raw['publish_date'])) {
                $publishedAt = date('Y-m-d H:i:s', strtotime($raw['publish_date']));
            }

            // Immagine: prova prima "image" (stringa), poi "images[0].url" (array)
            $image = $raw['image'] ?? null;
            if (empty($image) && !empty($raw['images']) && is_array($raw['images'])) {
                $image = $raw['images'][0]['url'] ?? null;
            }
            $image = $this->fixImageUrl($image ?: null);

            Article::create([
                'title'        => $this->decodeHtml($raw['title']   ?? ''),
                'description'  => $this->decodeHtml($raw['summary'] ?? ''),
                'content'      => $this->decodeHtml($raw['text']    ?? ''),
                'url'          => $url,
                'url_to_image' => $image,
                'source_name'  => $this->guessSourceName($sourceDomain),
                'source_domain'=> $sourceDomain,
                'author'       => $author ? $this->decodeHtml($author) : null,
                'published_at' => $publishedAt,
                'category'     => $category,
            ]);

            $saved++;
        }

        return $saved;
    }

    private function extractDomain(string $url): string
    {
        $host  = parse_url($url, PHP_URL_HOST) ?? $url;
        $host  = ltrim((string) $host, 'www.');
        $parts = explode('.', $host);
        return count($parts) > 2
            ? implode('.', array_slice($parts, -2))
            : $host;
    }

    /**
     * World News non restituisce il nome della testata — lo ricaviamo dal dominio.
     * Esempio: corriere.it → Corriere, gazzetta.it → Gazzetta
     */
    /**
     * World News API restituisce le immagini come URL relativi proxati:
     *   /remote/static.milanofinanza.it/path/img.jpg
     * Le convertiamo in URL diretti:
     *   https://static.milanofinanza.it/path/img.jpg
     */
    private function fixImageUrl(?string $url): ?string
    {
        if (empty($url)) return null;

        if (str_starts_with($url, '/remote/')) {
            return 'https://' . substr($url, strlen('/remote/'));
        }

        return $url;
    }

    private function decodeHtml(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function guessSourceName(string $domain): string
    {
        $name = explode('.', $domain)[0];
        return ucfirst($name);
    }
}
