<?php

use App\Jobs\ClusterArticlesJob;
use App\Jobs\FetchNewsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduler FlamingNews ──────────────────────────────────────────────────
// FetchNewsJob: ogni 3h (~8 cicli/giorno, ~5 punti/ciclo = ~40 punti/giorno)
// ClusterArticlesJob: ogni 3h sfasato di 30min, copre le thin categories
Schedule::job(new FetchNewsJob)->everyThreeHours();
Schedule::job(new ClusterArticlesJob)->everyThreeHours()->at('00:30');
