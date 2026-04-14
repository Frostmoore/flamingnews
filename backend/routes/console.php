<?php

use App\Jobs\ClusterArticlesJob;
use App\Jobs\FetchNewsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler FlamingNews
Schedule::job(new FetchNewsJob)->everyFifteenMinutes();
Schedule::job(new ClusterArticlesJob)->everyThirtyMinutes();
