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
        'sources'  =>  300,   //  1 testata aggiuntiva      →  +5 min
        'variety'  =>  600,   //  1 orientamento in più     → +10 min
        'shares'   =>   60,   //  1 condivisione            →  +1 min
        'likes'    =>   20,   //  1 like                    → +20 sec
        'neutral'  =>  180,   //  1 fonte neutrale/centro   →  +3 min
    ],

    // Orientamenti considerati "neutrali" per il peso w_neutral
    'neutral_leans' => ['altro', 'center'],

];
