<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Topic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClusteringService
{
    private string $serviceUrl;

    public function __construct()
    {
        $this->serviceUrl = config('services.clustering.url', 'http://localhost:8765');
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->serviceUrl}/health");
            return $response->successful();
        } catch (\Throwable $e) {
            Log::channel('clustering')->warning('Clustering service non raggiungibile: ' . $e->getMessage());
            return false;
        }
    }

    public function clusterArticles(array $articles): array
    {
        try {
            $payload = collect($articles)->map(fn ($a) => [
                'id'    => $a['id'],
                'title' => $a['title'] ?? '',
                // Combina description + inizio content per più contesto al TF-IDF
                'text'  => implode(' ', array_filter([
                    $a['description'] ?? '',
                    isset($a['content']) ? mb_substr($a['content'], 0, 500) : '',
                ])),
            ])->values()->all();

            $response = Http::timeout(30)->post("{$this->serviceUrl}/cluster", [
                'articles' => $payload,
            ]);

            if ($response->failed()) {
                Log::channel('clustering')->error('Clustering service returned error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            return $response->json('clusters', []);
        } catch (\Throwable $e) {
            Log::channel('clustering')->error('Clustering service exception: ' . $e->getMessage());
            return [];
        }
    }

    public function applyClusterResults(array $clusters): void
    {
        foreach ($clusters as $cluster) {
            $articleIds = $cluster['article_ids'] ?? [];
            if (count($articleIds) < 2) {
                continue;
            }

            $topic = Topic::create([
                'title'         => $cluster['title'] ?? 'Evento senza titolo',
                'keywords'      => $cluster['keywords'] ?? [],
                'article_count' => count($articleIds),
            ]);

            Article::whereIn('id', $articleIds)->update(['topic_id' => $topic->id]);
        }
    }
}
