<?php

use App\Jobs\FetchNewsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduler FlamingNews ──────────────────────────────────────────────────
// RSS è gratuito → fetch ogni 5 minuti
// FetchNewsJob include già reclusterRecent(48h)
Schedule::job(new FetchNewsJob)->everyFiveMinutes();
