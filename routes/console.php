<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule sitemap generation
Schedule::command('sitemap:generate')->daily();

// Schedule notification pruning (keep 30 days). Registered once — this block was duplicated, so the
// scheduler queued the same command twice every night.
Schedule::command('notifications:prune --days=30')->daily();
