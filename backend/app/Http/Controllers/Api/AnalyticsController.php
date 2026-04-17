<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController
{
    private ?string $category = null;
    private ?string $period   = null;

    public function index(): JsonResponse
    {
        $this->category = request('category') ?: null;
        $this->period   = request('period')   ?: null;

        return response()->json([
            'generated_at'   => now()->toIso8601String(),
            'category'       => $this->category,
            'period'         => $this->period,
            'totals'         => $this->totals(),
            'articles'       => $this->articleStats(),
            'sources'        => $this->sourceStats(),
            'political_lean' => $this->leanStats(),
            'categories'     => $this->categoryStats(),
        ]);
    }

    private function dateFrom(): ?string
    {
        return match($this->period) {
            'day'     => now()->startOfDay()->toDateTimeString(),
            'week'    => now()->startOfWeek()->toDateTimeString(),
            'month'   => now()->startOfMonth()->toDateTimeString(),
            'quarter' => now()->startOfQuarter()->toDateTimeString(),
            'year'    => now()->startOfYear()->toDateTimeString(),
            default   => null,
        };
    }

    // ── Totali globali ────────────────────────────────────────────────────────

    private function totals(): array
    {
        $cat      = $this->category;
        $dateFrom = $this->dateFrom();

        $base = DB::table('articles')
            ->when($cat,      fn($q) => $q->where('category',     $cat))
            ->when($dateFrom, fn($q) => $q->where('published_at', '>=', $dateFrom));

        return [
            'articles' => (clone $base)->count(),
            'topics'   => (clone $base)->whereNotNull('topic_id')->distinct()->count('topic_id'),
            'sources'  => DB::table('sources')->where('active', true)->count(),
            'clicks'   => DB::table('article_clicks')
                ->join('articles', 'articles.id', '=', 'article_clicks.article_id')
                ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
                ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
                ->count(),
            'likes'    => DB::table('article_likes')
                ->join('articles', 'articles.id', '=', 'article_likes.article_id')
                ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
                ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
                ->count(),
            'shares'   => DB::table('article_shares')
                ->join('articles', 'articles.id', '=', 'article_shares.article_id')
                ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
                ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
                ->count(),
        ];
    }

    // ── Classifiche articoli ──────────────────────────────────────────────────

    private function articleStats(): array
    {
        $cat      = $this->category;
        $dateFrom = $this->dateFrom();

        $top_clicked = DB::table('articles')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
            ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
            ->select('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url', DB::raw('COUNT(*) as clicks_count'))
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('clicks_count')->limit(50)->get();

        $top_liked = DB::table('articles')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
            ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
            ->select('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url', DB::raw('COUNT(*) as likes_count'))
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('likes_count')->limit(50)->get();

        $top_shared = DB::table('articles')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
            ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
            ->select('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url', DB::raw('COUNT(*) as shares_count'))
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('shares_count')->limit(50)->get();

        $top_covered = DB::table('articles as main')
            ->join('articles as cov', 'main.topic_id', '=', 'cov.topic_id')
            ->where('main.is_main', true)
            ->whereNotNull('main.topic_id')
            ->when($cat, fn($q) => $q->where('main.category', $cat))
            ->when($dateFrom, fn($q) => $q->where('main.published_at', '>=', $dateFrom))
            ->select('main.id', 'main.title', 'main.source_name', 'main.source_domain', 'main.url', DB::raw('COUNT(DISTINCT cov.source_domain) as coverage_count'))
            ->groupBy('main.id', 'main.title', 'main.source_name', 'main.source_domain', 'main.url')
            ->orderByDesc('coverage_count')->limit(50)->get();

        return compact('top_clicked', 'top_liked', 'top_shared', 'top_covered');
    }

    // ── Classifiche testate ───────────────────────────────────────────────────

    private function sourceStats(): array
    {
        $cat      = $this->category;
        $dateFrom = $this->dateFrom();

        $by_articles = DB::table('articles')
            ->when($cat, fn($q) => $q->where('category', $cat))
            ->when($dateFrom, fn($q) => $q->where('published_at', '>=', $dateFrom))
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as articles_count'))
            ->groupBy('source_name', 'source_domain')->orderByDesc('articles_count')->limit(50)->get();

        $by_likes = DB::table('articles')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
            ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as likes_count'))
            ->groupBy('source_name', 'source_domain')->orderByDesc('likes_count')->limit(50)->get();

        $by_shares = DB::table('articles')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
            ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as shares_count'))
            ->groupBy('source_name', 'source_domain')->orderByDesc('shares_count')->limit(50)->get();

        $by_clicks = DB::table('articles')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->when($cat,      fn($q) => $q->where('articles.category',     $cat))
            ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as clicks_count'))
            ->groupBy('source_name', 'source_domain')->orderByDesc('clicks_count')->limit(50)->get();

        return compact('by_articles', 'by_likes', 'by_shares', 'by_clicks');
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
        $cat      = $this->category;
        $dateFrom = $this->dateFrom();

        $by_articles = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->when($cat,      fn($q) => $q->where('articles.category',      $cat))
            ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
            ->select('sources.political_lean', DB::raw('COUNT(*) as articles_count'))
            ->groupBy('sources.political_lean')->orderByDesc('articles_count')->get();

        $like_rate = DB::table('articles')
            ->join('sources',        'articles.source_domain', '=', 'sources.domain')
            ->leftJoin('article_likes', 'articles.id',         '=', 'article_likes.article_id')
            ->when($cat,      fn($q) => $q->where('articles.category',      $cat))
            ->when($dateFrom, fn($q) => $q->where('articles.published_at', '>=', $dateFrom))
            ->select(
                'sources.political_lean',
                DB::raw('COUNT(DISTINCT articles.id) as articles_count'),
                DB::raw('COUNT(article_likes.id) as likes_count'),
                DB::raw('ROUND(COUNT(article_likes.id) / COUNT(DISTINCT articles.id), 3) as like_rate')
            )
            ->groupBy('sources.political_lean')->orderByDesc('like_rate')->get();

        return compact('by_articles', 'like_rate');
    }

    // ── Analisi per categoria ─────────────────────────────────────────────────

    private function categoryStats(): array
    {
        $dateFrom = $this->dateFrom();

        $by_articles = DB::table('articles')
            ->when($dateFrom, fn($q) => $q->where('published_at', '>=', $dateFrom))
            ->select('category', DB::raw('COUNT(*) as articles_count'))
            ->groupBy('category')
            ->orderByDesc('articles_count')
            ->get();

        return compact('by_articles');
    }
}
