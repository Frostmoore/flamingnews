<?php

/*
|--------------------------------------------------------------------------
| Algoritmo di scoring FlamingNews
|--------------------------------------------------------------------------
|
| I pesi sono espressi in SECONDI di "boost" sull'orario di pubblicazione
| dell'articolo. Un peso di 600 significa che quel fattore vale +10 minuti.
|
| Esempio: un articolo con 3 testate riceve un boost di 3 × 900 = 2700 s
| (45 min), quindi appare come se fosse stato pubblicato 45 minuti dopo.
|
| Il boost si applica solo agli articoli pubblicati OGGI (same_day_only).
| Per gli articoli più vecchi l'ordine rimane cronologico puro.
|
| Ordine d'importanza attuale (modificare i pesi per aggiustare):
|
|   1. più testate coprono il tema        → w_sources
|   2. maggiore varietà di orientamenti  → w_variety
|   3. più condivisioni                  → w_shares
|   4. più like                          → w_likes
|   5. più media neutrali / centristi    → w_neutral
|
*/

return [

    // ── Attiva il boost solo per i giornata corrente
    'same_day_only' => true,

    'weights' => [
        //                              valore   significato
        'sources'  =>  900,   //  1 testata aggiuntiva      → +15 min
        'variety'  => 1800,   //  1 orientamento in più     → +30 min
        'shares'   =>  180,   //  1 condivisione            →  +3 min
        'likes'    =>   60,   //  1 like                    →  +1 min
        'neutral'  =>  600,   //  1 fonte neutrale/centro   → +10 min
    ],

    // Orientamenti considerati "neutrali" per il peso w_neutral
    'neutral_leans' => ['altro', 'center'],

];
