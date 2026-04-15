<?php

namespace App\Jobs;

use App\Models\PrimaPagina;
use App\Models\Source;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchPrimePagineJob implements ShouldQueue
{
    use Queueable;

    private const SOURCES = [
        ['domain' => 'corriere.it',         'name' => 'Corriere della Sera',     'url' => 'https://www.corriere.it/'],
        ['domain' => 'repubblica.it',        'name' => 'la Repubblica',           'url' => 'https://www.repubblica.it/'],
        ['domain' => 'lastampa.it',          'name' => 'La Stampa',               'url' => 'https://www.lastampa.it/'],
        ['domain' => 'ilsole24ore.com',      'name' => 'Il Sole 24 Ore',          'url' => 'https://www.ilsole24ore.com/'],
        ['domain' => 'ilmessaggero.it',      'name' => 'Il Messaggero',           'url' => 'https://www.ilmessaggero.it/'],
        ['domain' => 'ilfattoquotidiano.it', 'name' => 'Il Fatto Quotidiano',     'url' => 'https://www.ilfattoquotidiano.it/'],
        ['domain' => 'ilgiornale.it',        'name' => 'Il Giornale',             'url' => 'https://www.ilgiornale.it/'],
        ['domain' => 'ilpost.it',            'name' => 'Il Post',                 'url' => 'https://www.ilpost.it/'],
        ['domain' => 'avvenire.it',          'name' => 'Avvenire',                'url' => 'https://www.avvenire.it/'],
        ['domain' => 'ansa.it',              'name' => 'ANSA',                    'url' => 'https://www.ansa.it/'],
        ['domain' => 'rainews.it',           'name' => 'RaiNews',                 'url' => 'https://www.rainews.it/'],
        ['domain' => 'skytg24.it',           'name' => 'Sky TG24',                'url' => 'https://tg24.sky.it/'],
        ['domain' => 'ilfoglio.it',          'name' => 'Il Foglio',               'url' => 'https://www.ilfoglio.it/'],
        ['domain' => 'liberoquotidiano.it',  'name' => 'Libero Quotidiano',       'url' => 'https://www.liberoquotidiano.it/'],
        ['domain' => 'gazzetta.it',          'name' => 'La Gazzetta dello Sport', 'url' => 'https://www.gazzetta.it/'],
    ];

    public function handle(): void
    {
        $today = now()->toDateString();
        $leans = Source::whereIn('domain', array_column(self::SOURCES, 'domain'))
            ->pluck('political_lean', 'domain');

        $saved = 0;

        foreach (self::SOURCES as $src) {
            try {
                $og = $this->fetchOgTags($src['url']);

                if (empty($og['image'])) {
                    Log::warning("FetchPrimePagineJob: nessuna immagine per {$src['domain']}");
                    continue;
                }

                PrimaPagina::updateOrCreate(
                    ['source_domain' => $src['domain'], 'edition_date' => $today],
                    [
                        'source_name'   => $src['name'],
                        'political_lean'=> $leans[$src['domain']] ?? null,
                        'image_url'     => $og['image'],
                        'headline'      => $og['title'] ? mb_substr($og['title'], 0, 500) : null,
                        'article_url'   => $og['url'] ?? $src['url'],
                        'fetched_at'    => now(),
                    ]
                );

                $saved++;
                usleep(300_000);

            } catch (\Throwable $e) {
                Log::warning("FetchPrimePagineJob: errore per {$src['domain']}: " . $e->getMessage());
            }
        }

        Log::info("FetchPrimePagineJob: {$saved}/" . count(self::SOURCES) . " prime pagine salvate.");
    }

    private function fetchOgTags(string $url): array
    {
        $response = Http::timeout(10)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; FlamingNewsBot/1.0)'])
            ->get($url);

        if ($response->failed()) return [];

        $html = $response->body();

        $image = $this->extractOg($html, 'og:image')
              ?? $this->extractOg($html, 'twitter:image')
              ?? $this->extractFirstArticleImage($html, $url);

        $title = $this->extractOg($html, 'og:title')
              ?? $this->extractOg($html, 'twitter:title');

        if ($title && preg_match('/^(.+?)\s*[\|\-–—]\s*.{3,}$/', $title, $m)) {
            $title = trim($m[1]);
        }

        return [
            'image' => $image,
            'title' => $title,
            'url'   => $this->extractOg($html, 'og:url') ?? $url,
        ];
    }

    private function extractOg(string $html, string $property): ?string
    {
        $prop = preg_quote($property, '/');
        $patterns = [
            '/<meta\s[^>]*(?:property|name)\s*=\s*["\']' . $prop . '["\'][^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*>/i',
            '/<meta\s[^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*(?:property|name)\s*=\s*["\']' . $prop . '["\'][^>]*>/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $v = trim($m[1]);
                return $v !== '' ? $v : null;
            }
        }
        return null;
    }

    private function extractFirstArticleImage(string $html, string $baseUrl): ?string
    {
        $patterns = [
            '/<img[^>]+(?:data-src|src)\s*=\s*["\']([^"\']+\.(?:jpg|jpeg|png|webp)[^"\']*)["\'][^>]*>/i',
            '/<source[^>]+srcset\s*=\s*["\']([^"\'"\s,]+)["\'][^>]*>/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] as $src) {
                    if (preg_match('/logo|icon|avatar|sprite|placeholder|pixel|tracking|_\d{1,3}_\d{1,3}_|thumb_\d|\/\d{1,3}x\d{1,3}\//i', $src)) continue;
                    if (str_starts_with($src, '//')) return 'https:' . $src;
                    if (str_starts_with($src, '/')) {
                        $parsed = parse_url($baseUrl);
                        return $parsed['scheme'] . '://' . $parsed['host'] . $src;
                    }
                    if (str_starts_with($src, 'http')) return $src;
                }
            }
        }
        return null;
    }
}
