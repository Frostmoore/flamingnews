<?php

namespace App\Jobs;

use App\Services\ClusteringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ClusterArticlesJob implements ShouldQueue
{
    use Queueable;

    public function handle(ClusteringService $clustering): void
    {
        // Ri-aggrega sempre con il metodo PHP-nativo (finestra 72h per intercettare
        // articoli non coperti dal FetchNewsJob più recente)
        $clustering->reclusterRecent(hours: 72);

        Log::info('ClusterArticlesJob completato.');
    }
}
