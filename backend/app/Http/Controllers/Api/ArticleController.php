<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleLike;
use App\Models\UserRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = optional($request->user())->id;

        $query = Article::with([
            'source:domain,political_lean,name',
            'topic.articles:id,source_name,source_domain,url,topic_id',
            'topic.articles.source:domain,political_lean',
        ])->withCount('likes')->latest('published_at');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('description', 'LIKE', "%{$q}%");
            });
        }

        $articles = $query->paginate($request->integer('per_page', 20));

        // IDs già messi in like dall'utente autenticato (solo per questa pagina)
        $likedIds = $userId
            ? ArticleLike::where('user_id', $userId)
                ->whereIn('article_id', collect($articles->items())->pluck('id'))
                ->pluck('article_id')
                ->flip()
            : collect();

        return response()->json([
            'data' => collect($articles->items())->map(fn ($a) => $this->formatArticle($a, likedIds: $likedIds)),
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
            'topic.articles:id,source_name,source_domain,url,topic_id',
            'topic.articles.source:domain,political_lean',
        ])->findOrFail($id);

        if ($request->user()) {
            UserRead::updateOrCreate(
                ['user_id' => $request->user()->id, 'article_id' => $article->id],
                ['read_at' => now()]
            );
        }

        $liked = $request->user()
            ? ArticleLike::where('user_id', $request->user()->id)->where('article_id', $article->id)->exists()
            : false;

        return response()->json($this->formatArticle($article, full: true, liked: $liked));
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

    private function formatArticle(Article $article, bool $full = false, bool $liked = false, $likedIds = null): array
    {
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
                ])
                ->values()
                ->all();
        }

        $isLiked = $likedIds !== null
            ? $likedIds->has($article->id)
            : $liked;

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
            'likes_count'   => $article->likes_count ?? 0,
        ];

        if ($full) {
            $data['content'] = $article->content;
            $data['author']  = $article->author;
        }

        return $data;
    }
}
