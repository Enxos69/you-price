<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Sincronizzazione catalogo CruiseHost — ogni giorno alle 03:00
Schedule::command('catalog:sync --source=cron')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[CatalogSync] Esecuzione schedulata fallita.');
    });

// Controllo alert prezzi — ogni giorno alle 03:30 (dopo il catalog:sync)
Schedule::command('alerts:check')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[AlertsCheck] Esecuzione schedulata fallita.');
    });
