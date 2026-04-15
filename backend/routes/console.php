<?php

use App\Jobs\ClusterArticlesJob;
use App\Jobs\FetchNewsJob;
use App\Jobs\FetchPrimePagineJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduler FlamingNews ──────────────────────────────────────────────────
// FetchNewsJob: ogni ora (RSS è gratuito, nessuna quota API)
//   include già reclusterRecent(48h) → ClusterArticlesJob non è più necessario
Schedule::job(new FetchNewsJob)->hourly();
Schedule::job(new FetchPrimePagineJob)->dailyAt('06:30');
