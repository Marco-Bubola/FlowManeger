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

// Notificações de consórcios (sorteios disponíveis, resgates pendentes e parcelas atrasadas)
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

// Lançamentos recorrentes do livro-caixa (aluguel, salário, assinaturas...)
Schedule::command('recorrentes:gerar')
    ->dailyAt('06:00')
    ->withoutOverlapping();

// Estoque baixo: notifica produtos no mínimo/esgotados com sugestão de reposição
Schedule::command('stock:check-low')
    ->dailyAt('08:30')
    ->withoutOverlapping();

// Cobranças: resumo diário de parcelas vencendo hoje e vencidas sem lembrete
Schedule::command('reminders:due')
    ->dailyAt('09:00')
    ->withoutOverlapping();

// Shopee: renova os tokens que vencem em até 1 hora (a loja não desconecta
// mesmo sem uso; o refresh_token também é estendido a cada renovação)
Schedule::call(function () {
    $auth = app(\App\Services\Shopee\AuthService::class);
    \App\Models\ShopeeToken::where('is_active', true)
        ->where('expires_at', '<=', now()->addHour())
        ->get()
        ->each(function ($token) use ($auth) {
            try {
                $auth->refreshToken($token);
            } catch (\Throwable $e) {
                \Log::warning('Renovação agendada do token Shopee falhou', ['token_id' => $token->id, 'error' => $e->getMessage()]);
            }
        });
})->name('shopee:refresh-tokens')->everyThirtyMinutes()->withoutOverlapping();
