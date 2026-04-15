<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleClick;
use App\Models\ArticleLike;
use App\Models\ArticleShare;
use App\Models\UserRead;
use App\Services\ArticleScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request, ArticleScoringService $scoring): JsonResponse
    {
        $userId = optional(auth('sanctum')->user())->id;

        $query = Article::with([
            'source:domain,political_lean,name',
            'topic.articles' => fn ($q) => $q
                ->select(['id', 'title', 'source_name', 'source_domain', 'url', 'topic_id'])
                ->withCount(['likes', 'shares']),
            'topic.articles.source:domain,political_lean',
        ])->withCount(['likes', 'shares'])
          ->where('is_main', true)
          ->orderByRaw($scoring->orderByExpression());

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        } elseif ($request->input('tab') === 'tutte') {
            // Tab "Tutte": tutti gli articoli, nessun filtro aggiuntivo
        } else {
            // Tab "Temi": solo articoli coperti da 4+ testate DIVERSE
            $query->whereNotNull('topic_id')
                  ->whereRaw(
                      '(SELECT COUNT(DISTINCT source_domain) FROM articles AS a2 WHERE a2.topic_id = articles.topic_id) >= 4'
                  );

            $user = $request->user();
            if ($user && !empty($user->preferred_categories)) {
                $query->whereIn('category', $user->preferred_categories);
            }
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('description', 'LIKE', "%{$q}%");
            });
        }

        $articles = $query->paginate($request->integer('per_page', 20));

        // ── Batch query liked/shared per main + coverage articles ──────────
        $coverageIds = collect($articles->items())->flatMap(function ($a) {
            if ($a->topic_id && $a->relationLoaded('topic') && $a->topic) {
                return $a->topic->articles->pluck('id');
            }
            return [];
        });
        $allIds = collect($articles->items())->pluck('id')->merge($coverageIds)->unique();

        $likedIds = $userId
            ? ArticleLike::where('user_id', $userId)->whereIn('article_id', $allIds)->pluck('article_id')->flip()
            : collect();

        $sharedIds = $userId
            ? ArticleShare::where('user_id', $userId)->whereIn('article_id', $allIds)->pluck('article_id')->flip()
            : collect();

        return response()->json([
            'data' => collect($articles->items())->map(
                fn ($a) => $this->formatArticle($a, likedIds: $likedIds, sharedIds: $sharedIds)
            ),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page'    => $articles->lastPage(),
                'per_page'     => $articles->perPage(),
                'total'        => $articles->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $article = Article::with([
            'source:domain,political_lean,name',
            'topic.articles' => fn ($q) => $q
                ->select(['id', 'title', 'source_name', 'source_domain', 'url', 'topic_id'])
                ->withCount(['likes', 'shares']),
            'topic.articles.source:domain,political_lean',
        ])->withCount(['likes', 'shares'])
          ->findOrFail($id);

        $user = auth('sanctum')->user();

        if ($user) {
            UserRead::updateOrCreate(
                ['user_id' => $user->id, 'article_id' => $article->id],
                ['read_at' => now()]
            );
        }

        $liked  = $user ? ArticleLike::where('user_id', $user->id)->where('article_id', $article->id)->exists() : false;
        $shared = $user ? ArticleShare::where('user_id', $user->id)->where('article_id', $article->id)->exists() : false;

        // Liked/shared per coverage
        $coverageIds = $article->topic?->articles->pluck('id') ?? collect();
        $likedIds  = $user
            ? ArticleLike::where('user_id', $user->id)->whereIn('article_id', $coverageIds)->pluck('article_id')->flip()
            : collect();
        $sharedIds = $user
            ? ArticleShare::where('user_id', $user->id)->whereIn('article_id', $coverageIds)->pluck('article_id')->flip()
            : collect();

        return response()->json(
            $this->formatArticle($article, full: true, liked: $liked, shared: $shared, likedIds: $likedIds, sharedIds: $sharedIds)
        );
    }

    public function like(Request $request, int $id): JsonResponse
    {
        $request->validate(['action' => 'in:like,unlike']);

        $article = Article::findOrFail($id);
        $user    = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $existing = ArticleLike::where('user_id', $user->id)->where('article_id', $id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            ArticleLike::create([
                'user_id'    => $user->id,
                'article_id' => $id,
                'category'   => $article->category,
            ]);
            $liked = true;
        }

        $count = ArticleLike::where('article_id', $id)->count();

        return response()->json(['liked' => $liked, 'likes_count' => $count]);
    }

    public function click(Request $request, int $id): JsonResponse
    {
        Article::findOrFail($id); // 404 se non esiste

        ArticleClick::create([
            'user_id'    => optional(auth('sanctum')->user())->id, // null se anonimo
            'article_id' => $id,
        ]);

        return response()->json(['ok' => true]);
    }

    public function share(Request $request, int $id): JsonResponse
    {
        Article::findOrFail($id); // 404 se non esiste
        $user = $request->user();

        $shared = false;
        if ($user) {
            $existing = ArticleShare::where('user_id', $user->id)->where('article_id', $id)->first();
            if ($existing) {
                $existing->delete();
                $shared = false;
            } else {
                ArticleShare::create(['user_id' => $user->id, 'article_id' => $id]);
                $shared = true;
            }
        }

        $count = ArticleShare::where('article_id', $id)->count();
        return response()->json(['shared' => $shared, 'shares_count' => $count]);
    }

    private function formatArticle(
        Article $article,
        bool $full = false,
        bool $liked = false,
        bool $shared = false,
        $likedIds = null,
        $sharedIds = null,
    ): array {
        $coverage = [];
        if ($article->topic_id && $article->relationLoaded('topic') && $article->topic) {
            $coverage = $article->topic->articles
                ->where('id', '!=', $article->id)
                ->map(fn ($a) => [
                    'id'           => $a->id,
                    'title'        => $a->title,
                    'source_name'  => $a->source_name,
                    'source_domain'=> $a->source_domain,
                    'url'          => $a->url,
                    'lean'         => optional($a->source)->political_lean,
                    'likes_count'  => $a->likes_count ?? 0,
                    'shares_count' => $a->shares_count ?? 0,
                    'liked'        => $likedIds?->has($a->id) ?? false,
                    'shared'       => $sharedIds?->has($a->id) ?? false,
                ])
                ->values()
                ->all();
        }

        $isLiked  = $likedIds  !== null ? $likedIds->has($article->id)  : $liked;
        $isShared = $sharedIds !== null ? $sharedIds->has($article->id) : $shared;

        $data = [
            'id'            => $article->id,
            'title'         => $article->title,
            'description'   => $article->description,
            'url'           => $article->url,
            'url_to_image'  => $article->url_to_image,
            'source_name'   => $article->source_name,
            'source_domain' => $article->source_domain,
            'published_at'  => $article->published_at?->toIso8601String(),
            'category'      => $article->category,
            'political_lean'=> optional($article->source)->political_lean,
            'topic_id'      => $article->topic_id,
            'coverage'      => $coverage,
            'liked'         => $isLiked,
            'shared'        => $isShared,
            'likes_count'   => $article->likes_count ?? 0,
            'shares_count'  => $article->shares_count ?? 0,
        ];

        if ($full) {
            $data['content'] = $article->content;
            $data['author']  = $article->author;
        }

        return $data;
    }
}
