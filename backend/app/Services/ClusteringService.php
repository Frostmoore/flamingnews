<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Topic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClusteringService
{
    private string $serviceUrl;

    // Soglia Jaccard: 0.30 = almeno il 30% di parole significative in comune
    private const JACCARD_THRESHOLD = 0.30;

    // Stopwords italiane (+ inglesi per articoli esteri)
    private const STOPWORDS = [
        'il','lo','la','i','gli','le','un','uno','una','del','della','dello',
        'dei','degli','delle','di','da','a','in','con','su','per','tra','fra',
        'e','o','ma','però','che','è','sono','ha','non','si','al','dal','nel',
        'alla','alle','nella','nelle','sul','sulle','ad','ed','sua','suo','suoi',
        'sue','anche','così','come','più','già','solo','dopo','prima','quando',
        'dove','chi','cosa','questo','questa','questi','queste','quello','quella',
        'quelli','quelle','molto','poco','tutto','tutta','tutti','tutte','ogni',
        'essere','avere','fare','dire','vedere','volere','potere','dovere',
        'the','and','for','with','from','that','this','are','was','were',
        'has','have','been','will','its','their','about','after',
    ];

    public function __construct()
    {
        $this->serviceUrl = config('services.clustering.url', 'http://localhost:8765');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RE-AGGREGAZIONE PHP-NATIVA (usata dopo ogni fetch)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Raggruppa gli articoli recenti per similarità titolo (Jaccard + Union-Find).
     * Fonde i topic duplicati, crea topic per i nuovi cluster.
     */
    public function reclusterRecent(int $hours = 48): void
    {
        $articles = Article::where('published_at', '>=', now()->subHours($hours))
            ->orderBy('published_at', 'desc')
            ->get(['id', 'title', 'topic_id'])
            ->toArray();

        if (count($articles) < 2) {
            Log::info('ClusteringService::reclusterRecent: meno di 2 articoli, skip.');
            return;
        }

        // Pre-tokenizza tutti i titoli
        $ids    = array_column($articles, 'id');
        $byId   = array_column($articles, null, 'id');
        $tokens = [];
        foreach ($articles as $a) {
            $tokens[$a['id']] = $this->tokenize($a['title'] ?? '');
        }

        // ── Union-Find ───────────────────────────────────────────────────────
        $parent = array_combine($ids, $ids);

        $find = function (int $x) use (&$parent, &$find): int {
            if ($parent[$x] !== $x) {
                $parent[$x] = $find($parent[$x]); // path compression
            }
            return $parent[$x];
        };

        $union = function (int $x, int $y) use (&$parent, &$find): void {
            $px = $find($x);
            $py = $find($y);
            if ($px !== $py) {
                $parent[$py] = $px;
            }
        };

        $n = count($ids);
        for ($i = 0; $i < $n; $i++) {
            if (empty($tokens[$ids[$i]])) continue;
            for ($j = $i + 1; $j < $n; $j++) {
                if (empty($tokens[$ids[$j]])) continue;
                if ($this->jaccard($tokens[$ids[$i]], $tokens[$ids[$j]]) >= self::JACCARD_THRESHOLD) {
                    $union($ids[$i], $ids[$j]);
                }
            }
        }

        // ── Costruisci gruppi ─────────────────────────────────────────────────
        $groups = [];
        foreach ($ids as $id) {
            $root = $find($id);
            $groups[$root][] = $byId[$id];
        }

        $created = 0;
        $merged  = 0;

        foreach ($groups as $group) {
            if (count($group) < 2) continue;

            $articleIds = array_column($group, 'id');
            $topicIds   = array_values(array_filter(array_unique(array_column($group, 'topic_id'))));

            if (empty($topicIds)) {
                // Nessun topic: crea uno nuovo
                $main  = $group[0];
                $topic = Topic::create([
                    'title'         => $main['title'],
                    'keywords'      => [],
                    'article_count' => count($articleIds),
                ]);
                Article::whereIn('id', $articleIds)->update(['topic_id' => $topic->id]);
                $created++;

            } elseif (count($topicIds) === 1) {
                // Tutti già nello stesso topic: aggiorna solo il conteggio
                // e assegna gli articoli senza topic
                $noTopic = array_column(
                    array_filter($group, fn ($a) => $a['topic_id'] === null),
                    'id'
                );
                if (!empty($noTopic)) {
                    Article::whereIn('id', $noTopic)->update(['topic_id' => $topicIds[0]]);
                }
                Topic::where('id', $topicIds[0])
                    ->update(['article_count' => Article::where('topic_id', $topicIds[0])->count()]);

            } else {
                // Topic multipli: fonde nel più grande
                $counts = Article::whereIn('topic_id', $topicIds)
                    ->selectRaw('topic_id, count(*) as cnt')
                    ->groupBy('topic_id')
                    ->pluck('cnt', 'topic_id');

                $primaryId = $counts->sortDesc()->keys()->first();

                // Sposta tutti gli articoli del gruppo nel topic primario
                Article::whereIn('id', $articleIds)->update(['topic_id' => $primaryId]);

                // Aggiorna conteggio
                Topic::where('id', $primaryId)
                    ->update(['article_count' => Article::where('topic_id', $primaryId)->count()]);

                // Elimina i topic orfani
                $orphans = array_diff($topicIds, [$primaryId]);
                Topic::whereIn('id', $orphans)->doesntHave('articles')->delete();

                $merged++;
            }
        }

        Log::info(sprintf(
            'ClusteringService::reclusterRecent: %d articoli → %d gruppi (%d creati, %d fusi).',
            count($articles), count($groups), $created, $merged
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MICROSERVIZIO PYTHON (fallback opzionale)
    // ─────────────────────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->serviceUrl}/health");
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function clusterArticles(array $articles): array
    {
        try {
            $payload = collect($articles)->map(fn ($a) => [
                'id'    => $a['id'],
                'title' => $a['title'] ?? '',
                'text'  => implode(' ', array_filter([
                    $a['description'] ?? '',
                    isset($a['content']) ? mb_substr($a['content'], 0, 500) : '',
                ])),
            ])->values()->all();

            $response = Http::timeout(30)->post("{$this->serviceUrl}/cluster", [
                'articles' => $payload,
            ]);

            if ($response->failed()) {
                Log::channel('clustering')->error('Clustering service error', [
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
            if (count($articleIds) < 2) continue;

            $topic = Topic::create([
                'title'         => $cluster['title'] ?? 'Evento senza titolo',
                'keywords'      => $cluster['keywords'] ?? [],
                'article_count' => count($articleIds),
            ]);

            Article::whereIn('id', $articleIds)->update(['topic_id' => $topic->id]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UTILITY
    // ─────────────────────────────────────────────────────────────────────────

    private function tokenize(string $title): array
    {
        $title = mb_strtolower($title, 'UTF-8');
        // Rimuovi punteggiatura (mantieni lettere unicode e cifre)
        $title = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
        $words = preg_split('/\s+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_filter(
            $words,
            fn ($w) => mb_strlen($w, 'UTF-8') > 3 && !in_array($w, self::STOPWORDS, true)
        )));
    }

    private function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) return 0.0;
        $intersection = count(array_intersect($a, $b));
        $union        = count(array_unique(array_merge($a, $b)));
        return $union > 0 ? $intersection / $union : 0.0;
    }
}
