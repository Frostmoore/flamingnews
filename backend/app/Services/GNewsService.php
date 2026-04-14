<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GNewsService
{
    private string $apiKey;
    private string $baseUrl = 'https://gnews.io/api/v4';

    /**
     * Mapping from internal category slugs to GNews top-headlines categories.
     * We always request Italian-language articles; for "esteri" we use world
     * but drop the country filter so we get international sources in Italian.
     */
    private array $categoryMap = [
        'politica'   => ['category' => 'nation',        'country' => 'it'],
        'economia'   => ['category' => 'business',       'country' => 'it'],
        'esteri'     => ['category' => 'world',          'country' => null],
        'tecnologia' => ['category' => 'technology',     'country' => null],
        'sport'      => ['category' => 'sports',         'country' => 'it'],
        'cultura'    => ['category' => 'entertainment',  'country' => 'it'],
        'generale'   => ['category' => 'general',        'country' => 'it'],
        'scienza'    => ['category' => 'science',        'country' => null],
        'salute'     => ['category' => 'health',         'country' => 'it'],
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gnews.key');
    }

    /**
     * Fetch articles for a given internal category via GNews /top-headlines.
     * Returns the raw articles array from the API response.
     */
    public function fetchByCategory(string $category, int $max = 10): array
    {
        $map = $this->categoryMap[$category] ?? ['category' => 'general', 'country' => null];

        $params = [
            'category' => $map['category'],
            'lang'     => 'it',
            'max'      => $max,
            'apikey'   => $this->apiKey,
        ];

        if ($map['country'] !== null) {
            $params['country'] = $map['country'];
        }

        $response = Http::timeout(15)->get("{$this->baseUrl}/top-headlines", $params);

        if ($response->failed()) {
            Log::warning("GNews fetch failed for category '{$category}'", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        return $response->json('articles', []);
    }

    /**
     * Search articles by keyword across all categories.
     * Useful for supplementing thin categories.
     */
    public function search(string $query, string $lang = 'it', int $max = 10): array
    {
        $response = Http::timeout(15)->get("{$this->baseUrl}/search", [
            'q'       => $query,
            'lang'    => $lang,
            'sortby'  => 'publishedAt',
            'max'     => $max,
            'apikey'  => $this->apiKey,
        ]);

        if ($response->failed()) {
            Log::warning("GNews search failed for query '{$query}'", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        return $response->json('articles', []);
    }

    /**
     * Persist a batch of raw GNews articles for a given category.
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

            // Skip already-stored articles
            if (Article::where('url', $url)->exists()) {
                continue;
            }

            $sourceUrl    = $raw['source']['url'] ?? $url;
            $sourceDomain = $this->extractDomain($sourceUrl);

            Article::create([
                'title'        => $raw['title']       ?? '',
                'description'  => $raw['description'] ?? null,
                'content'      => $raw['content']     ?? null,
                'url'          => $url,
                'url_to_image' => $raw['image']       ?? null,   // GNews uses "image"
                'source_name'  => $raw['source']['name'] ?? null,
                'source_domain'=> $sourceDomain,
                'author'       => null,                           // GNews doesn't expose author
                'published_at' => isset($raw['publishedAt'])
                    ? date('Y-m-d H:i:s', strtotime($raw['publishedAt']))
                    : null,
                'category'     => $category,
            ]);

            $saved++;
        }

        return $saved;
    }

    private function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? $url;
        $host = ltrim((string) $host, 'www.');

        // Strip subdomains: keep only the root domain (last 2 parts)
        // e.g. milano.corriere.it → corriere.it
        $parts = explode('.', $host);
        return count($parts) > 2
            ? implode('.', array_slice($parts, -2))
            : $host;
    }
}
