<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PrimaPagina;
use Illuminate\Http\JsonResponse;

class PrimePagineController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $pagine = PrimaPagina::whereDate('edition_date', $today)
            ->whereNotNull('image_url')
            ->orderBy('source_name')
            ->get()
            ->map(fn ($p) => [
                'id'            => $p->id,
                'source_name'   => $p->source_name,
                'source_domain' => $p->source_domain,
                'political_lean'=> $p->political_lean,
                'image_url'     => $p->image_url,
                'headline'      => $p->headline,
                'article_url'   => $p->article_url,
                'edition_date'  => $p->edition_date->toDateString(),
                'fetched_at'    => $p->fetched_at?->toIso8601String(),
            ]);

        // Se oggi non ci sono ancora, restituisce quelle più recenti
        if ($pagine->isEmpty()) {
            $pagine = PrimaPagina::whereNotNull('image_url')
                ->orderByDesc('edition_date')
                ->orderBy('source_name')
                ->limit(30)
                ->get()
                ->map(fn ($p) => [
                    'id'            => $p->id,
                    'source_name'   => $p->source_name,
                    'source_domain' => $p->source_domain,
                    'political_lean'=> $p->political_lean,
                    'image_url'     => $p->image_url,
                    'headline'      => $p->headline,
                    'article_url'   => $p->article_url,
                    'edition_date'  => $p->edition_date->toDateString(),
                    'fetched_at'    => $p->fetched_at?->toIso8601String(),
                ]);
        }

        return response()->json(['data' => $pagine]);
    }
}
