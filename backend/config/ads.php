<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google AdSense
    |--------------------------------------------------------------------------
    | Publisher ID formato: ca-pub-XXXXXXXXXXXXXXXX
    | Ad unit slot: il numero a 10 cifre dell'unità pubblicitaria
    |
    | Lascia vuoto ADSENSE_PUBLISHER_ID per disabilitare gli annunci.
    */

    'adsense_publisher_id' => env('ADSENSE_PUBLISHER_ID', ''),

    // Slot per il banner inline nel feed (tra gli articoli)
    'feed_ad_slot'         => env('ADSENSE_FEED_SLOT', ''),

    // Inserisce un ad ogni N articoli nel feed
    'feed_ad_frequency'    => (int) env('ADSENSE_FEED_FREQUENCY', 6),
];
