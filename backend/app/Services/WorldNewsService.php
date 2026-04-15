<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorldNewsService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.worldnewsapi.com';

    /**
     * Categorie thin — poco rappresentate in /top-news,
     * fetchate separatamente via /search-news.
     */
    public const THIN_CATEGORIES = [
        'cibo', 'viaggi', 'istruzione', 'ambiente', 'scienza', 'salute',
    ];

    /**
     * Mapping categoria interna → categoria World News API.
     */
    private array $categoryMap = [
        'politica'   => 'politics',
        'economia'   => 'business',
        'esteri'     => null,
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

    /**
     * Mapping inverso: categoria World News → categoria interna.
     */
    private array $reverseCategoryMap;

    public function __construct()
    {
        $this->apiKey = config('services.worldnews.key', '');
        $this->reverseCategoryMap = array_flip(array_filter($this->categoryMap));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOP NEWS — cluster già pronti dall'API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Chiama /top-news e restituisce la struttura cluster intatta.
     * Ogni elemento: ['news' => [article, ...]]
     * dove article ha: id, title, text, summary, url, image, publish_date,
     *                   authors, language, category, source_country
     */
    public function fetchTopNewsRaw(): array
    {
        $response = Http::timeout(20)->get("{$this->baseUrl}/top-news", [
            'api-key'        => $this->apiKey,
            'source-country' => 'it',
            'language'       => 'it',
        ]);

        $this->logQuota($response, 'top-news');

        if ($response->failed()) {
            Log::warning('WorldNews /top-news failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        return $response->json('top_news', []);
    }

    /**
     * Mappa la categoria WN di un articolo alla categoria interna FlamingNews.
     * Fallback: 'generale'.
     */
    public function mapCategory(?string $wnCategory): string
    {
        if (!$wnCategory) return 'generale';
        return $this->reverseCategoryMap[strtolower($wnCategory)] ?? 'generale';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // THIN CATEGORIES — /search-news per categorie poco rappresentate in top-news
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetcha articoli per una categoria thin via /search-news.
     * number=30: costo invariato rispetto a number=10 (~1.1 punti/call).
     */
    public function fetchThinCategory(string $category, int $number = 30): array
    {
        $wnCategory = $this->categoryMap[$category] ?? null;

        $params = [
            'api-key'          => $this->apiKey,
            'language'         => 'it',
            'source-countries' => 'it',
            'number'           => $number,
            'sort'             => 'publish-time',
            'sort-direction'   => 'DESC',
        ];

        if ($wnCategory !== null) {
            $params['categories'] = $wnCategory;
        }

        $response = Http::timeout(20)->get("{$this->baseUrl}/search-news", $params);

        $this->logQuota($response, "search-news/{$category}");

        if ($response->failed()) {
            Log::warning("WorldNews /search-news failed for '{$category}'", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        return $response->json('news', []);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    public function extractDomain(string $url): string
    {
        $host  = parse_url($url, PHP_URL_HOST) ?? $url;
        $host  = ltrim((string) $host, 'www.');
        $parts = explode('.', $host);
        return count($parts) > 2
            ? implode('.', array_slice($parts, -2))
            : $host;
    }

    public function fixImageUrl(?string $url): ?string
    {
        if (empty($url)) return null;
        if (str_starts_with($url, '/remote/')) {
            return 'https://' . substr($url, strlen('/remote/'));
        }
        return $url;
    }

    public function decodeHtml(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function guessSourceName(string $domain): string
    {
        return ucfirst(explode('.', $domain)[0]);
    }

    public function parsePublishedAt(?string $raw): ?string
    {
        if (empty($raw)) return null;
        return date('Y-m-d H:i:s', strtotime($raw));
    }

    public function parseAuthors(?array $authors): ?string
    {
        if (empty($authors) || !is_array($authors)) return null;
        return implode(', ', array_slice($authors, 0, 2));
    }

    public function parseImage(array $raw): ?string
    {
        $image = $raw['image'] ?? null;
        if (empty($image) && !empty($raw['images']) && is_array($raw['images'])) {
            $image = $raw['images'][0]['url'] ?? null;
        }
        return $this->fixImageUrl($image ?: null);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FRONT PAGES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Chiama /retrieve-front-page per un singolo giornale.
     * Restituisce ['name', 'date', 'language', 'country', 'image'] oppure null.
     */
    public function fetchFrontPage(?string $sourceName = null, ?string $date = null): ?array
    {
        $params = ['api-key' => $this->apiKey, 'source-country' => 'it'];
        if ($sourceName) $params['source-name'] = $sourceName;
        if ($date)       $params['date']        = $date;

        $response = Http::timeout(15)->get("{$this->baseUrl}/retrieve-front-page", $params);

        $this->logQuota($response, 'retrieve-front-page');

        if ($response->failed()) {
            Log::warning("WorldNews /retrieve-front-page failed for [{$sourceName}]", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        return $response->json('front_page');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // QUOTA LOGGING
    // ─────────────────────────────────────────────────────────────────────────

    private function logQuota($response, string $endpoint): void
    {
        Log::info("WorldNews quota [{$endpoint}]", [
            'request' => $response->header('X-Api-Quota-Request'),
            'used'    => $response->header('X-Api-Quota-Used'),
            'left'    => $response->header('X-Api-Quota-Left'),
        ]);
    }
}
