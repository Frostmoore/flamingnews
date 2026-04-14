<?php

namespace App\Services;

use App\Models\Topic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicService
{
    private string $apiKey;
    private string $model = 'claude-haiku-4-5-20251001';
    private string $baseUrl = 'https://api.anthropic.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key');
    }

    public function generateTopicAnalysis(Topic $topic): ?string
    {
        if ($topic->ai_analysis) {
            return $topic->ai_analysis;
        }

        $articles = $topic->articles()->with('source')->get();

        if ($articles->isEmpty()) {
            return null;
        }

        $articlesText = $articles->map(function ($article) {
            $lean  = optional($article->source)->political_lean ?? 'sconosciuto';
            $name  = $article->source_name ?? $article->source_domain ?? 'Fonte sconosciuta';
            return "**{$name}** ({$lean}):\nTitolo: {$article->title}\n{$article->excerpt}";
        })->implode("\n\n---\n\n");

        $prompt = <<<PROMPT
Sei un analista mediatico italiano. Di seguito trovi lo stesso evento raccontato da diverse testate italiane con diversi orientamenti politici.

Analizza come ogni testata inquadra l'evento: quali aspetti enfatizza, quali omette, che tono usa, come presenta i protagonisti. Sii obiettivo e neutro.

Scrivi un'analisi comparativa in italiano di circa 200 parole.

ARTICOLI:
{$articlesText}
PROMPT;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->post("{$this->baseUrl}/messages", [
                'model'      => $this->model,
                'max_tokens' => 512,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if ($response->failed()) {
                Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $text = $response->json('content.0.text');

            if ($text) {
                $topic->update([
                    'ai_analysis'      => $text,
                    'ai_generated_at'  => now(),
                ]);
            }

            return $text;
        } catch (\Throwable $e) {
            Log::error('Anthropic service exception: ' . $e->getMessage());
            return null;
        }
    }
}
