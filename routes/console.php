<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronização automática das publicações do Mercado Livre
// Executa a cada hora para manter os dados atualizados
Schedule::command('ml:sync-publications --limit=100')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Sincronização automática de publicações ML executada com sucesso');
    })
    ->onFailure(function () {
        \Log::error('Falha na sincronização automática de publicações ML');
    });
