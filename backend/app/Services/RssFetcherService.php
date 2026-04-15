<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssFetcherService
{
    /**
     * Domini che producono esclusivamente notizie sportive.
     * Usato come fallback di categoria quando i tag RSS non sono sufficienti.
     */
    private const SPORT_DOMAINS = [
        'gazzetta.it', 'corrieredellosport.it', 'tuttosport.com',
        'sportmediaset.it', 'fantacalcio.it', 'goal.com',
    ];

    /**
     * Keyword → categoria interna.
     * Ordine significativo: le categorie più specifiche vanno prima.
     */
    private const CATEGORY_KEYWORDS = [
        'sport'      => ['sport', 'calcio', 'basket', 'tennis', 'football', 'motori', 'formula 1', 'ciclismo', 'rugby', 'nuoto', 'atletica'],
        'tecnologia' => ['tecnolog', 'digital', 'software', 'hardware', 'intelligenza artificiale', 'ai ', ' ai\b', 'tech', 'cyber', 'smartphone', 'internet', 'innovazion'],
        'scienza'    => ['scienz', 'science', 'ricerca', 'spazio', 'space', 'astro', 'fisica', 'chimica', 'biologia'],
        'salute'     => ['salute', 'health', 'medic', 'sanit', 'vaccin', 'malatt', 'farmac', 'ospedal'],
        'ambiente'   => ['ambient', 'clima', 'green', 'environment', 'energy', 'energia', 'sostenib', 'riscaldamento globale'],
        'cibo'       => ['cibo', 'food', 'cucina', 'ricett', 'gastrono', 'vino', 'ristorante', 'chef'],
        'viaggi'     => ['viaggi', 'travel', 'turismo', 'vacanz', 'destinazion'],
        'istruzione' => ['istruzion', 'scuola', 'universit', 'education', 'student', 'didattic'],
        'economia'   => ['econom', 'finanz', 'borsa', 'mercato', 'business', 'lavoro', 'aziend', 'impresa', 'pil', 'inflazion', 'commercio'],
        'politica'   => ['politic', 'governo', 'elezioni', 'parlamento', 'premier', 'ministero', 'partito', 'democraz', 'senato', 'camera'],
        'esteri'     => ['esteri', 'mondo', 'world', 'international', 'internazional', 'europa', 'usa', 'russia', 'cina', 'medio oriente'],
        'cultura'    => ['cultur', 'arte', 'musica', 'cinema', 'teatro', 'libri', 'spettacol', 'entertainment', 'film', 'festival'],
        'generale'   => [],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // FETCH
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Scarica e parsa un feed RSS/Atom.
     * Restituisce un array di articoli normalizzati:
     *   ['title', 'description', 'url', 'image', 'published_at', 'categories']
     */
    public function fetchFeed(string $feedUrl): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; FlamingNewsBot/1.0)'])
                ->get($feedUrl);

            if ($response->failed()) {
                Log::warning("RssFetcher: feed failed [{$feedUrl}] status={$response->status()}");
                return [];
            }

            return $this->parseXml($response->body(), $feedUrl);

        } catch (\Throwable $e) {
            Log::warning("RssFetcher: exception [{$feedUrl}]: {$e->getMessage()}");
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARSING
    // ─────────────────────────────────────────────────────────────────────────

    private function parseXml(string $xml, string $feedUrl): array
    {
        // Detect HTML responses before attempting XML parse (avoids massive libxml error spam)
        $trimmed = ltrim($xml);
        if (
            stripos($trimmed, '<!doctype') === 0 ||
            stripos($trimmed, '<html') === 0 ||
            stripos($trimmed, '<!-') === 0
        ) {
            Log::warning("RssFetcher: feed returned HTML instead of XML [{$feedUrl}]");
            return [];
        }

        $xml = $this->fixEncoding($xml);

        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($feed === false) {
            libxml_clear_errors();
            Log::warning("RssFetcher: XML parse failed [{$feedUrl}]");
            return [];
        }

        $rootName = strtolower($feed->getName());

        return match (true) {
            $rootName === 'feed' => $this->parseAtom($feed),
            default              => $this->parseRss($feed),
        };
    }

    /**
     * Parsa un feed RSS 2.0.
     */
    private function parseRss(\SimpleXMLElement $feed): array
    {
        $items   = [];
        $channel = $feed->channel ?? $feed;

        foreach ($channel->item as $item) {
            $ns      = $item->getNamespaces(true);
            $media   = isset($ns['media'])   ? $item->children($ns['media'])   : null;
            $content = isset($ns['content']) ? $item->children($ns['content']) : null;

            $url = trim((string)($item->link ?? $item->guid ?? ''));

            // Alcune feed usano <guid isPermaLink="false">; url può non essere HTTP
            if (empty($url) || !str_starts_with($url, 'http')) continue;

            $title = $this->decodeText((string)($item->title ?? ''));
            if (empty($title)) continue;

            $rawDesc    = (string)($item->description ?? '');
            $description = $rawDesc ? $this->decodeText(strip_tags($rawDesc)) : null;

            $items[] = [
                'title'        => $title,
                'description'  => $description ? mb_substr($description, 0, 500) : null,
                'url'          => $url,
                'image'        => $this->extractRssImage($item, $media, $content, $rawDesc),
                'published_at' => $this->parsePubDate((string)($item->pubDate ?? '')),
                'categories'   => $this->extractRssCategories($item, $ns),
            ];
        }

        return $items;
    }

    /**
     * Parsa un feed Atom.
     */
    private function parseAtom(\SimpleXMLElement $feed): array
    {
        $items = [];
        $ns    = $feed->getNamespaces(true);

        foreach ($feed->entry as $entry) {
            // URL: <link href="..."> oppure <link>...</link>
            $url = '';
            foreach ($entry->link as $link) {
                $rel  = (string)($link['rel']  ?? 'alternate');
                $href = (string)($link['href'] ?? '');
                if ($rel === 'alternate' && !empty($href)) {
                    $url = $href;
                    break;
                }
                if (empty($url) && !empty($href)) $url = $href;
            }
            if (empty($url) || !str_starts_with($url, 'http')) continue;

            $title = $this->decodeText((string)($entry->title ?? ''));
            if (empty($title)) continue;

            $rawDesc    = (string)($entry->summary ?? $entry->content ?? '');
            $description = $rawDesc ? $this->decodeText(strip_tags($rawDesc)) : null;

            $mediaNs = isset($ns['media']) ? $entry->children($ns['media']) : null;
            $image   = $this->extractAtomImage($entry, $mediaNs, $rawDesc);

            $pubDate = (string)($entry->published ?? $entry->updated ?? '');

            $categories = [];
            foreach ($entry->category as $cat) {
                $term  = (string)($cat['term']  ?? '');
                $label = (string)($cat['label'] ?? '');
                if ($term)  $categories[] = $term;
                if ($label) $categories[] = $label;
            }

            $items[] = [
                'title'        => $title,
                'description'  => $description ? mb_substr($description, 0, 500) : null,
                'url'          => trim($url),
                'image'        => $image,
                'published_at' => $this->parsePubDate($pubDate),
                'categories'   => array_unique($categories),
            ];
        }

        return $items;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMAGE EXTRACTION
    // ─────────────────────────────────────────────────────────────────────────

    private function extractRssImage(
        \SimpleXMLElement $item,
        ?\SimpleXMLElement $media,
        ?\SimpleXMLElement $content,
        string $rawDescription
    ): ?string {
        // 1. media:content url=
        if ($media) {
            foreach ($media as $tag) {
                if (strtolower($tag->getName()) === 'content') {
                    $url = (string)($tag['url'] ?? '');
                    if ($url && $this->looksLikeImage($url)) return $url;
                }
            }
            // media:thumbnail
            foreach ($media as $tag) {
                if (strtolower($tag->getName()) === 'thumbnail') {
                    $url = (string)($tag['url'] ?? '');
                    if ($url && $this->looksLikeImage($url)) return $url;
                }
            }
        }

        // 2. content:encoded — cerca <img src=...>
        if ($content) {
            foreach ($content as $tag) {
                if (strtolower($tag->getName()) === 'encoded') {
                    $img = $this->extractImgFromHtml((string)$tag);
                    if ($img) return $img;
                }
            }
        }

        // 3. enclosure type="image/..."
        if (isset($item->enclosure)) {
            $type = strtolower((string)($item->enclosure['type'] ?? ''));
            $url  = (string)($item->enclosure['url'] ?? '');
            if (str_starts_with($type, 'image/') && $url) return $url;
        }

        // 4. <img> nella description HTML
        if ($rawDescription) {
            $img = $this->extractImgFromHtml($rawDescription);
            if ($img) return $img;
        }

        return null;
    }

    private function extractAtomImage(
        \SimpleXMLElement $entry,
        ?\SimpleXMLElement $media,
        string $rawContent
    ): ?string {
        if ($media) {
            foreach ($media as $tag) {
                $name = strtolower($tag->getName());
                if ($name === 'content' || $name === 'thumbnail') {
                    $url = (string)($tag['url'] ?? '');
                    if ($url && $this->looksLikeImage($url)) return $url;
                }
            }
        }

        if ($rawContent) {
            $img = $this->extractImgFromHtml($rawContent);
            if ($img) return $img;
        }

        return null;
    }

    private function extractImgFromHtml(string $html): ?string
    {
        if (!preg_match_all('/<img[^>]+(?:data-src|src)\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return null;
        }

        foreach ($matches[1] as $src) {
            if (empty($src)) continue;
            if (preg_match('/logo|icon|avatar|sprite|placeholder|pixel|tracking|\/\d{1,3}x\d{1,3}\//i', $src)) continue;
            if (str_starts_with($src, 'http') || str_starts_with($src, '//')) {
                return str_starts_with($src, '//') ? 'https:' . $src : $src;
            }
        }

        return null;
    }

    private function looksLikeImage(string $url): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|webp|gif|avif)(\?|$)/i', $url)
            || str_contains($url, '/image')
            || str_contains($url, '/img');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CATEGORY MAPPING
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mappa i tag RSS + dominio sorgente alla categoria interna FlamingNews.
     */
    public function mapCategory(array $categories, string $sourceDomain): string
    {
        // Fonti sport-only: sempre 'sport'
        if (in_array($sourceDomain, self::SPORT_DOMAINS, true)) {
            return 'sport';
        }

        if (empty($categories)) return 'generale';

        $haystack = strtolower(implode(' ', $categories));

        foreach (self::CATEGORY_KEYWORDS as $category => $keywords) {
            if ($category === 'generale') continue;
            foreach ($keywords as $kw) {
                if (str_contains($haystack, $kw)) return $category;
            }
        }

        return 'generale';
    }

    private function extractRssCategories(\SimpleXMLElement $item, array $ns): array
    {
        $categories = [];

        foreach ($item->category as $cat) {
            $v = trim((string)$cat);
            if ($v !== '') $categories[] = $v;
        }

        // Alcuni feed usano dc:subject
        if (isset($ns['dc'])) {
            $dc = $item->children($ns['dc']);
            foreach ($dc as $tag) {
                if (strtolower($tag->getName()) === 'subject') {
                    $v = trim((string)$tag);
                    if ($v !== '') $categories[] = $v;
                }
            }
        }

        return array_unique($categories);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    public function extractDomain(string $url): string
    {
        $host  = parse_url($url, PHP_URL_HOST) ?? $url;
        $host  = ltrim((string) $host, 'www.');
        $parts = explode('.', $host);
        return count($parts) > 2
            ? implode('.', array_slice($parts, -2))
            : $host;
    }

    public function parsePubDate(string $date): ?string
    {
        if (empty(trim($date))) return null;
        $ts = strtotime($date);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    private function decodeText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function fixEncoding(string $xml): string
    {
        // Rimuove il BOM UTF-8 se presente
        if (str_starts_with($xml, "\xEF\xBB\xBF")) {
            $xml = substr($xml, 3);
        }

        // Se la dichiarazione XML dice encoding diverso da UTF-8, forza la conversione
        if (preg_match('/<?xml[^>]+encoding=["\']([^"\']+)["\'][^>]*>/i', $xml, $m)) {
            $encoding = strtoupper($m[1]);
            if (!in_array($encoding, ['UTF-8', 'UTF8'], true)) {
                $converted = mb_convert_encoding($xml, 'UTF-8', $encoding);
                if ($converted !== false) {
                    $xml = preg_replace('/encoding=["\'][^"\']+["\']/', 'encoding="UTF-8"', $converted);
                }
            }
        }

        return $xml;
    }
}
