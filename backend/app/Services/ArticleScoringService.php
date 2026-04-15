<?php

namespace App\Services;

/**
 * Genera l'espressione SQL ORDER BY per il feed FlamingNews.
 *
 * La logica è: ordine cronologico (UNIX_TIMESTAMP) + boost editoriale
 * calcolato dai pesi in config/scoring.php.
 *
 * Il boost è espresso in secondi, quindi un articolo popolare di stamattina
 * può scalare di qualche ora nella lista, ma non supererà mai un articolo
 * molto più recente.
 */
class ArticleScoringService
{
    public function orderByExpression(): string
    {
        /** @var array<string,int> $w */
        $w = config('scoring.weights');

        // Cast esplicito a int per sicurezza (nessun input utente nel config,
        // ma meglio prevenire injection se il file venisse modificato male)
        $wSources  = (int) $w['sources'];
        $wVariety  = (int) $w['variety'];
        $wShares   = (int) $w['shares'];
        $wLikes    = (int) $w['likes'];
        $wNeutral  = (int) $w['neutral'];

        $neutralLeans = implode("','", array_map(
            fn (string $l) => addslashes($l),
            config('scoring.neutral_leans')
        ));

        // ── Componenti del punteggio ──────────────────────────────────────

        // 1. Numero di testate DIVERSE che coprono il topic
        $cSources = "COALESCE((
            SELECT COUNT(DISTINCT a2.source_domain)
            FROM articles a2
            WHERE a2.topic_id = articles.topic_id
        ), 0)";

        // 2. Numero di orientamenti politici DISTINTI nel topic
        $cVariety = "COALESCE((
            SELECT COUNT(DISTINCT s2.political_lean)
            FROM articles a2
            INNER JOIN sources s2 ON s2.domain = a2.source_domain
            WHERE a2.topic_id = articles.topic_id
              AND s2.political_lean IS NOT NULL
        ), 0)";

        // 3. Condivisioni dell'articolo principale
        $cShares = "COALESCE((
            SELECT COUNT(*)
            FROM article_shares
            WHERE article_shares.article_id = articles.id
        ), 0)";

        // 4. Like dell'articolo principale
        $cLikes = "COALESCE((
            SELECT COUNT(*)
            FROM article_likes
            WHERE article_likes.article_id = articles.id
        ), 0)";

        // 5. Numero di testate NEUTRALI/CENTRISTE nel topic
        $cNeutral = "COALESCE((
            SELECT COUNT(DISTINCT a2.source_domain)
            FROM articles a2
            INNER JOIN sources s2 ON s2.domain = a2.source_domain
            WHERE a2.topic_id = articles.topic_id
              AND s2.political_lean IN ('{$neutralLeans}')
        ), 0)";

        // ── Punteggio totale ──────────────────────────────────────────────
        $score = "
            {$cSources}  * {$wSources}
          + {$cVariety}  * {$wVariety}
          + {$cShares}   * {$wShares}
          + {$cLikes}    * {$wLikes}
          + {$cNeutral}  * {$wNeutral}
        ";

        // Applica il boost solo agli articoli di oggi (se configurato)
        $boost = config('scoring.same_day_only')
            ? "IF(DATE(articles.published_at) = CURDATE(), {$score}, 0)"
            : $score;

        // Ordine finale: base cronologica + boost editoriale
        return "UNIX_TIMESTAMP(articles.published_at) + ({$boost}) DESC";
    }
}
