<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'generated_at'   => now()->toIso8601String(),
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
        return [
            'articles' => DB::table('articles')->count(),
            'topics'   => DB::table('topics')->count(),
            'sources'  => DB::table('sources')->where('active', true)->count(),
            'clicks'   => DB::table('article_clicks')->count(),
            'likes'    => DB::table('article_likes')->count(),
            'shares'   => DB::table('article_shares')->count(),
        ];
    }

    // ── Classifiche articoli ──────────────────────────────────────────────────

    private function articleStats(): array
    {
        $top_clicked = DB::table('articles')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->select(
                'articles.id', 'articles.title',
                'articles.source_name', 'articles.source_domain', 'articles.url',
                DB::raw('COUNT(*) as clicks_count')
            )
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('clicks_count')
            ->limit(50)
            ->get();

        $top_liked = DB::table('articles')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->select(
                'articles.id', 'articles.title',
                'articles.source_name', 'articles.source_domain', 'articles.url',
                DB::raw('COUNT(*) as likes_count')
            )
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('likes_count')
            ->limit(50)
            ->get();

        $top_shared = DB::table('articles')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->select(
                'articles.id', 'articles.title',
                'articles.source_name', 'articles.source_domain', 'articles.url',
                DB::raw('COUNT(*) as shares_count')
            )
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('shares_count')
            ->limit(50)
            ->get();

        // Articoli principale con più testate che coprono lo stesso topic
        $top_covered = DB::table('articles as main')
            ->join('articles as cov', 'main.topic_id', '=', 'cov.topic_id')
            ->where('main.is_main', true)
            ->whereNotNull('main.topic_id')
            ->select(
                'main.id', 'main.title',
                'main.source_name', 'main.source_domain', 'main.url',
                DB::raw('COUNT(DISTINCT cov.source_domain) as coverage_count')
            )
            ->groupBy('main.id', 'main.title', 'main.source_name', 'main.source_domain', 'main.url')
            ->orderByDesc('coverage_count')
            ->limit(50)
            ->get();

        // Articoli con più engagement totale (clicks + likes + shares)
        $top_engaged = DB::table('articles')
            ->leftJoin('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->leftJoin('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->leftJoin('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->select(
                'articles.id', 'articles.title',
                'articles.source_name', 'articles.source_domain', 'articles.url',
                DB::raw('COUNT(DISTINCT article_clicks.id) as clicks_count'),
                DB::raw('COUNT(DISTINCT article_likes.id)  as likes_count'),
                DB::raw('COUNT(DISTINCT article_shares.id) as shares_count'),
                DB::raw('COUNT(DISTINCT article_clicks.id) + COUNT(DISTINCT article_likes.id) + COUNT(DISTINCT article_shares.id) as engagement_total')
            )
            ->groupBy('articles.id', 'articles.title', 'articles.source_name', 'articles.source_domain', 'articles.url')
            ->orderByDesc('engagement_total')
            ->having('engagement_total', '>', 0)
            ->limit(50)
            ->get();

        return compact('top_clicked', 'top_liked', 'top_shared', 'top_covered', 'top_engaged');
    }

    // ── Classifiche testate ───────────────────────────────────────────────────

    private function sourceStats(): array
    {
        $by_articles = DB::table('articles')
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as articles_count'))
            ->groupBy('source_name', 'source_domain')
            ->orderByDesc('articles_count')
            ->limit(50)
            ->get();

        $by_likes = DB::table('articles')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as likes_count'))
            ->groupBy('source_name', 'source_domain')
            ->orderByDesc('likes_count')
            ->limit(50)
            ->get();

        $by_shares = DB::table('articles')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as shares_count'))
            ->groupBy('source_name', 'source_domain')
            ->orderByDesc('shares_count')
            ->limit(50)
            ->get();

        $by_clicks = DB::table('articles')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->select('source_name', 'source_domain', DB::raw('COUNT(*) as clicks_count'))
            ->groupBy('source_name', 'source_domain')
            ->orderByDesc('clicks_count')
            ->limit(50)
            ->get();

        // Engagement rate: (likes + shares) / articles — min 5 articoli
        $by_engagement_rate = DB::table('articles')
            ->leftJoin('article_likes',  'articles.id', '=', 'article_likes.article_id')
            ->leftJoin('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->select(
                'source_name', 'source_domain',
                DB::raw('COUNT(DISTINCT articles.id)        as articles_count'),
                DB::raw('COUNT(DISTINCT article_likes.id)   as likes_count'),
                DB::raw('COUNT(DISTINCT article_shares.id)  as shares_count'),
                DB::raw('ROUND((COUNT(DISTINCT article_likes.id) + COUNT(DISTINCT article_shares.id)) / COUNT(DISTINCT articles.id), 2) as engagement_rate')
            )
            ->groupBy('source_name', 'source_domain')
            ->havingRaw('COUNT(DISTINCT articles.id) >= 5')
            ->orderByDesc('engagement_rate')
            ->limit(50)
            ->get();

        return compact('by_articles', 'by_likes', 'by_shares', 'by_clicks', 'by_engagement_rate');
    }

    // ── Analisi orientamento politico ─────────────────────────────────────────

    private function leanStats(): array
    {
        $by_articles = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->select('sources.political_lean', DB::raw('COUNT(*) as articles_count'))
            ->groupBy('sources.political_lean')
            ->orderByDesc('articles_count')
            ->get();

        $by_likes = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->join('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->select('sources.political_lean', DB::raw('COUNT(*) as likes_count'))
            ->groupBy('sources.political_lean')
            ->orderByDesc('likes_count')
            ->get();

        $by_shares = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->join('article_shares', 'articles.id', '=', 'article_shares.article_id')
            ->select('sources.political_lean', DB::raw('COUNT(*) as shares_count'))
            ->groupBy('sources.political_lean')
            ->orderByDesc('shares_count')
            ->get();

        $by_clicks = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->join('article_clicks', 'articles.id', '=', 'article_clicks.article_id')
            ->select('sources.political_lean', DB::raw('COUNT(*) as clicks_count'))
            ->groupBy('sources.political_lean')
            ->orderByDesc('clicks_count')
            ->get();

        // Likes per articolo per orientamento (like rate)
        $like_rate = DB::table('articles')
            ->join('sources', 'articles.source_domain', '=', 'sources.domain')
            ->leftJoin('article_likes', 'articles.id', '=', 'article_likes.article_id')
            ->select(
                'sources.political_lean',
                DB::raw('COUNT(DISTINCT articles.id) as articles_count'),
                DB::raw('COUNT(article_likes.id) as likes_count'),
                DB::raw('ROUND(COUNT(article_likes.id) / COUNT(DISTINCT articles.id), 3) as like_rate')
            )
            ->groupBy('sources.political_lean')
            ->orderByDesc('like_rate')
            ->get();

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
