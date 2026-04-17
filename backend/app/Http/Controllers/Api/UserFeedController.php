<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedArticle;
use App\Models\UserFeed;
use App\Services\RssFetcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserFeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $feeds = $request->user()
            ->userFeeds()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($f) => array_merge($f->toArray(), [
                'articles_count' => FeedArticle::where('feed_url', $f->feed_url)->count(),
            ]));

        return response()->json(['data' => $feeds]);
    }

    public function store(Request $request, RssFetcherService $rss): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'feed_url' => 'required|url|max:500',
        ]);

        $user = $request->user();

        if ($user->userFeeds()->where('feed_url', $data['feed_url'])->exists()) {
            return response()->json(['message' => 'Feed già aggiunto.'], 422);
        }

        $items = $rss->fetchFeed($data['feed_url']);
        if (empty($items)) {
            return response()->json(['message' => 'URL non valido o feed RSS non raggiungibile.'], 422);
        }

        $feed = UserFeed::create([
            'user_id'         => $user->id,
            'name'            => $data['name'],
            'feed_url'        => $data['feed_url'],
            'last_fetched_at' => now(),
        ]);

        $this->saveItems($data['feed_url'], $items);

        return response()->json([
            'data' => array_merge($feed->toArray(), [
                'articles_count' => FeedArticle::where('feed_url', $feed->feed_url)->count(),
            ]),
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $feed = UserFeed::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $feedUrl = $feed->feed_url;
        $feed->delete();

        // Se nessun altro utente ha questo feed, cancella gli articoli
        if (!UserFeed::where('feed_url', $feedUrl)->exists()) {
            FeedArticle::where('feed_url', $feedUrl)->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function articles(Request $request): JsonResponse
    {
        $user     = $request->user();
        $feedUrls = $user->userFeeds()->pluck('feed_url');

        if ($feedUrls->isEmpty()) {
            return response()->json([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 0],
            ]);
        }

        // Filtro opzionale per singolo user_feed
        if ($request->filled('user_feed_id')) {
            $specificUrl = $user->userFeeds()
                ->where('id', $request->user_feed_id)
                ->value('feed_url');
            $feedUrls = $specificUrl ? collect([$specificUrl]) : collect();
        }

        // Mappa feed_url → nome per il campo source_name
        $feedNames = $user->userFeeds()
            ->whereIn('feed_url', $feedUrls)
            ->pluck('name', 'feed_url');

        $articles = FeedArticle::whereIn('feed_url', $feedUrls)
            ->orderByDesc('published_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => collect($articles->items())->map(fn ($a) => [
                'id'             => $a->id,
                'title'          => $a->title,
                'description'    => $a->description,
                'url'            => $a->url,
                'url_to_image'   => $a->url_to_image,
                'source_name'    => $feedNames[$a->feed_url] ?? $a->feed_url,
                'source_domain'  => '',
                'published_at'   => $a->published_at?->toIso8601String(),
                'category'       => null,
                'political_lean' => null,
                'topic_id'       => null,
                'coverage'       => [],
                'liked'          => false,
                'shared'         => false,
                'likes_count'    => 0,
                'shares_count'   => 0,
            ]),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page'    => $articles->lastPage(),
                'per_page'     => $articles->perPage(),
                'total'        => $articles->total(),
            ],
        ]);
    }

    public function refresh(Request $request, int $id, RssFetcherService $rss): JsonResponse
    {
        $feed = UserFeed::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $items = $rss->fetchFeed($feed->feed_url);
        $saved = $this->saveItems($feed->feed_url, $items);
        $feed->update(['last_fetched_at' => now()]);

        return response()->json([
            'new_articles'   => $saved,
            'articles_count' => FeedArticle::where('feed_url', $feed->feed_url)->count(),
            'data'           => array_merge($feed->fresh()->toArray(), [
                'articles_count' => FeedArticle::where('feed_url', $feed->feed_url)->count(),
            ]),
        ]);
    }

    private function saveItems(string $feedUrl, array $items): int
    {
        if (empty($items)) return 0;

        $rows = [];
        $now  = now();

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

        return DB::table('feed_articles')->insertOrIgnore($rows);
    }
}
