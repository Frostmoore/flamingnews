<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Services\AnthropicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $topics = Topic::withCount('articles')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $topics->items(),
            'meta' => [
                'current_page' => $topics->currentPage(),
                'last_page'    => $topics->lastPage(),
                'per_page'     => $topics->perPage(),
                'total'        => $topics->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $topic = Topic::with(['articles.source'])->findOrFail($id);

        $user      = $request->user();
        $isPremium = $user?->is_premium ?? false;

        // Free: massimo 3 fonti per cluster
        $articles = $topic->articles;
        if (!$isPremium) {
            $articles = $articles->take(3);
        }

        $byLean = $articles->groupBy(fn ($a) => optional($a->source)->political_lean ?? 'international');

        return response()->json([
            'id'              => $topic->id,
            'title'           => $topic->title,
            'keywords'        => $topic->keywords,
            'article_count'   => $topic->article_count,
            'ai_analysis'     => $isPremium ? $topic->ai_analysis : null,
            'ai_generated_at' => $topic->ai_generated_at?->toIso8601String(),
            'sources'         => $byLean->map(fn ($group) => $group->map(fn ($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'description'  => $a->description,
                'url'          => $a->url,
                'url_to_image' => $a->url_to_image,
                'source_name'  => $a->source_name,
                'source_domain'=> $a->source_domain,
                'published_at' => $a->published_at?->toIso8601String(),
                'political_lean'=> optional($a->source)->political_lean,
            ])),
        ]);
    }

    public function analyze(Request $request, int $id, AnthropicService $anthropic): JsonResponse
    {
        $user = $request->user();

        if (!$user?->is_premium) {
            return response()->json(['message' => 'Funzionalità disponibile solo per utenti Premium.'], 403);
        }

        $topic    = Topic::with(['articles.source'])->findOrFail($id);
        $analysis = $anthropic->generateTopicAnalysis($topic);

        if (!$analysis) {
            return response()->json(['message' => 'Errore nella generazione dell\'analisi AI.'], 500);
        }

        return response()->json([
            'ai_analysis'     => $analysis,
            'ai_generated_at' => $topic->fresh()->ai_generated_at?->toIso8601String(),
        ]);
    }
}
