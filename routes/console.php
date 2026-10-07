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

// Renovação preventiva dos tokens OAuth do Mercado Livre (a cada hora)
Schedule::command('ml:refresh-tokens')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Notificações de consórcios (sorteios disponíveis e resgates pendentes)
Schedule::command('consortium:check-notifications')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

// Limpeza semanal de notificações antigas de consórcios
Schedule::command('consortium:check-notifications --clean')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping();

// Promoções: liga as agendadas e encerra as vencidas ou sem estoque
Schedule::command('promotions:refresh')
    ->hourly()
    ->withoutOverlapping();
