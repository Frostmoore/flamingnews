<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiService
{
    private string $apiKey;
    private string $baseUrl = 'https://newsapi.org/v2';

    private array $categoryQueries = [
        'politica'   => 'politica italia',
        'economia'   => 'economia finanza italia',
        'esteri'     => 'world international news',
        'tecnologia' => 'tecnologia intelligenza artificiale',
        'sport'      => 'sport calcio italia',
        'cultura'    => 'cultura arte cinema italia',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.newsapi.key');
    }

    public function fetchByCategory(string $category): array
    {
        $query = $this->categoryQueries[$category] ?? $category;

        $response = Http::timeout(15)->get("{$this->baseUrl}/everything", [
            'q'        => $query,
            'language' => 'it',
            'sortBy'   => 'publishedAt',
            'pageSize' => 50,
            'apiKey'   => $this->apiKey,
        ]);

        if ($response->failed()) {
            Log::warning("NewsAPI fetch failed for category {$category}", [
                'status' => $response->status(),
            ]);
            return [];
        }

        return $response->json('articles', []);
    }

    public function saveArticles(array $articles, string $category): int
    {
        $saved = 0;

        foreach ($articles as $raw) {
            $url = $raw['url'] ?? null;
            if (!$url || $url === '[Removed]') {
                continue;
            }

            $domain = $this->extractDomain($raw['source']['url'] ?? $url);

            $exists = Article::where('url', $url)->exists();
            if ($exists) {
                continue;
            }

            Article::create([
                'title'        => $raw['title'] ?? '',
                'description'  => $raw['description'] ?? null,
                'content'      => $raw['content'] ?? null,
                'url'          => $url,
                'url_to_image' => $raw['urlToImage'] ?? null,
                'source_name'  => $raw['source']['name'] ?? null,
                'source_domain'=> $domain,
                'author'       => $raw['author'] ?? null,
                'published_at' => isset($raw['publishedAt']) ? date('Y-m-d H:i:s', strtotime($raw['publishedAt'])) : null,
                'category'     => $category,
            ]);

            $saved++;
        }

        return $saved;
    }

    private function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? $url;
        return ltrim($host, 'www.');
    }
}
