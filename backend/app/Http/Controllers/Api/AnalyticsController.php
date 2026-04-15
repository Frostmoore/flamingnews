<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController
{
    private ?string $category = null;

    public function index(): JsonResponse
    {
        $this->category = request('category') ?: null;

        return response()->json([
            'generated_at'   => now()->toIso8601String(),
            'category'       => $this->category,
            'totals'         => $this->totals(),
            'articles'       => $this->articleStats(),
            'sources'        => $this->sourceStats(),
            'political_lean' => $this->leanStats(),
            'categories'     => $this->categoryStats(),
        ]);
    }

    // ── Totali globali ────────────────────────────────────────────────────────

    private function totals(): array
    {
        $base = DB::table('articles')->when($this->category, fn($q) => $q->where('category', $this->category));

        return [
            'articles' => (clone $base)->count(),
            'topics'   => (clone $base)->whereNotNull('topic_id')->distinct()->count('topic_id'),
            'sources'  => DB::table('sources')->where('active', true)->count(),
            'clicks'   => DB::table('article_clicks')->when($this->category, fn($q) => $q->join('articles', 'articles.id', '=', 'article_clicks.article_id')->where('articles.category', $this->category))->count(),
            'likes'    => DB::table('article_likes')->when($this->category, fn($q) => $q->join('articles', 'articles.id', '=', 'article_likes.article_id')->where('articles.category', $this->category))->count(),
            'shares'   => DB::table('article_shares')->when($this->category, fn($q) => $q->join('articles', 'articles.id', '=', 'article_shares.article_id')->where('articles.category', $this->category))->count(),
        ];
    }

    // ── Classifiche articoli ──────────────────────────────────────────────────

    private function articleStats(): array
    {
        $cat = $this->category;

        $most_recent = DB::table('articles')
            ->when($cat, fn($q) => $q->where('category', $cat))
            ->select('id', 'title', 'source_name', 'source_domain', 'url', 'published_at', 'category')
            ->orderByDesc('published_at')
            ->limit(50)->get();

        $top_clicked = DB::table('articles')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url', DB::raw('COUNT(*) as clicks_count'))
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('clicks_count')->limit(50)->get();

        $top_liked = DB::table('articles')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url', DB::raw('COUNT(*) as likes_count'))
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('likes_count')->limit(50)->get();

        $top_shared = DB::table('articles')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url', DB::raw('COUNT(*) as shares_count'))
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('shares_count')->limit(50)->get();

        $top_covered = DB::table('articles as main')
            ->join('articles as cov', 'main.topic_id', '=', 'cov.topic_id')
            ->where('main.is_main', true)
            ->whereNotNull('main.topic_id')
            ->when($cat, fn($q) => $q->where('main.category', $cat))
            ->select('main.id', 'main.title', 'main.source_name', 'main.source_domain', 'main.url', DB::raw('COUNT(DISTINCT cov.source_domain) as coverage_count'))
            ->groupBy('main.id', 'main.title', 'main.source_name', 'main.source_domain', 'main.url')
            ->orderByDesc('coverage_count')->limit(50)->get();

        $top_engaged = DB::table('articles')
            ->leftJoin('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->leftJoin('article_likes',  'articles.id', '=', 'article_likes.article_id')
            ->leftJoin('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select(
                'articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url',
                DB::raw('COUNT(DISTINCT article_clicks.id) as clicks_count'),
                DB::raw('COUNT(DISTINCT article_likes.id)  as likes_count'),
                DB::raw('COUNT(DISTINCT article_shares.id) as shares_count'),
                DB::raw('COUNT(DISTINCT article_clicks.id) + COUNT(DISTINCT article_likes.id) + COUNT(DISTINCT article_shares.id) as engagement_total')
            )
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('engagement_total')->having('engagement_total', '>', 0)->limit(50)->get();

        return compact('most_recent', 'top_clicked', 'top_liked', 'top_shared', 'top_covered', 'top_engaged');
    }

    // ── Classifiche testate ───────────────────────────────────────────────────

    private function sourceStats(): array
    {
        $cat = $this->category;

        $by_articles = DB::table('articles')
            ->when($cat, fn($q) => $q->where('category', $cat))
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as articles_count'))
            ->groupBy('source_name', 'source_domain')->orderByDesc('articles_count')->limit(50)->get();

        $by_likes = DB::table('articles')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as likes_count'))
            ->groupBy('source_name', 'source_domain')->orderByDesc('likes_count')->limit(50)->get();

        $by_shares = DB::table('articles')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as shares_count'))
            ->groupBy('source_name', 'source_domain')->orderByDesc('shares_count')->limit(50)->get();

        $by_clicks = DB::table('articles')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as clicks_count'))
            ->groupBy('source_name', 'source_domain')->orderByDesc('clicks_count')->limit(50)->get();

        $by_engagement_rate = DB::table('articles')
            ->leftJoin('article_likes',  'articles.id', '=', 'article_likes.article_id')
            ->leftJoin('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select(
                'source_name', 'source_domain',
                DB::raw('COUNT(DISTINCT articles.id) as articles_count'),
                DB::raw('COUNT(DISTINCT article_likes.id) as likes_count'),
                DB::raw('COUNT(DISTINCT article_shares.id) as shares_count'),
                DB::raw('ROUND((COUNT(DISTINCT article_likes.id) + COUNT(DISTINCT article_shares.id)) / COUNT(DISTINCT articles.id), 2) as engagement_rate')
            )
            ->groupBy('source_name', 'source_domain')
            ->havingRaw('COUNT(DISTINCT articles.id) >= 5')
            ->orderByDesc('engagement_rate')->limit(50)->get();

        return compact('by_articles', 'by_likes', 'by_shares', 'by_clicks', 'by_engagement_rate');
    }

    // ── Stats per singola testata ─────────────────────────────────────────────

    public function source(): JsonResponse
    {
        $domain = request('domain');
        if (!$domain) {
            return response()->json(['error' => 'domain required'], 422);
        }

        $source = DB::table('sources')->where('domain', $domain)->first();

        $totals = [
            'articles' => DB::table('articles')->where('source_domain', $domain)->count(),
            'clicks'   => DB::table('article_clicks')
                ->join('articles', 'articles.id', '=', 'article_clicks.article_id')
                ->where('articles.source_domain', $domain)->count(),
            'likes'    => DB::table('article_likes')
                ->join('articles', 'articles.id', '=', 'article_likes.article_id')
                ->where('articles.source_domain', $domain)->count(),
            'shares'   => DB::table('article_shares')
                ->join('articles', 'articles.id', '=', 'article_shares.article_id')
                ->where('articles.source_domain', $domain)->count(),
        ];

        $top_articles = DB::table('articles')
            ->leftJoin('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->leftJoin('article_likes',  'articles.id', '=', 'article_likes.article_id')
            ->where('articles.source_domain', $domain)
            ->select(
                'articles.id', 'articles.title', 'articles.url',
                'articles.published_at', 'articles.category',
                DB::raw('COUNT(DISTINCT article_clicks.id) as clicks'),
                DB::raw('COUNT(DISTINCT article_likes.id)  as likes')
            )
            ->groupBy('articles.id', 'articles.title', 'articles.url', 'articles.published_at', 'articles.category')
            ->orderByDesc('clicks')
            ->limit(10)->get();

        $by_category = DB::table('articles')
            ->where('source_domain', $domain)
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        return response()->json(compact('source', 'totals', 'top_articles', 'by_category'));
    }

    // ── Analisi orientamento politico ─────────────────────────────────────────

    private function leanStats(): array
    {
        $cat = $this->category;

        $by_articles = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('sources.political_lean', DB::raw('COUNT(*) as articles_count'))
            ->groupBy('sources.political_lean')->orderByDesc('articles_count')->get();

        $by_likes = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('sources.political_lean', DB::raw('COUNT(*) as likes_count'))
            ->groupBy('sources.political_lean')->orderByDesc('likes_count')->get();

        $by_shares = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('sources.political_lean', DB::raw('COUNT(*) as shares_count'))
            ->groupBy('sources.political_lean')->orderByDesc('shares_count')->get();

        $by_clicks = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select('sources.political_lean', DB::raw('COUNT(*) as clicks_count'))
            ->groupBy('sources.political_lean')->orderByDesc('clicks_count')->get();

        $like_rate = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->leftJoin('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->when($cat, fn($q) => $q->where('articles.category', $cat))
            ->select(
                'sources.political_lean',
                DB::raw('COUNT(DISTINCT articles.id) as articles_count'),
                DB::raw('COUNT(article_likes.id) as likes_count'),
                DB::raw('ROUND(COUNT(article_likes.id) / COUNT(DISTINCT articles.id), 3) as like_rate')
            )
            ->groupBy('sources.political_lean')->orderByDesc('like_rate')->get();

        return compact('by_articles', 'by_likes', 'by_shares', 'by_clicks', 'like_rate');
    }

    // ── Analisi per categoria ─────────────────────────────────────────────────

    private function categoryStats(): array
    {
        $by_articles = DB::table('articles')
            ->select('category', DB::raw('COUNT(*) as articles_count'))
            ->groupBy('category')
            ->orderByDesc('articles_count')
            ->get();

        $by_likes = DB::table('articles')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->select('articles.category', DB::raw('COUNT(*) as likes_count'))
            ->groupBy('articles.category')
            ->orderByDesc('likes_count')
            ->get();

        $by_shares = DB::table('articles')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->select('articles.category', DB::raw('COUNT(*) as shares_count'))
            ->groupBy('articles.category')
            ->orderByDesc('shares_count')
            ->get();

        $by_clicks = DB::table('articles')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->select('articles.category', DB::raw('COUNT(*) as clicks_count'))
            ->groupBy('articles.category')
            ->orderByDesc('clicks_count')
            ->get();

        return compact('by_articles', 'by_likes', 'by_shares', 'by_clicks');
    }
}
