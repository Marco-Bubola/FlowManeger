<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Bloqueio de recursos pelo plano. Cada recurso do plano (has_*) libera um grupo de telas;
 * com a chave ligada (Admin › Planos), quem não tem o recurso vê a tela "faz parte do plano"
 * no lugar da página e o item aparece com cadeado no menu. Admin sempre passa.
 * Começa desligado: o dono dá os planos às contas e liga quando quiser.
 */
class PlanGate
{
    /** recurso => [nome, ícone, rotas (aceita *), rotas que nunca bloqueiam] */
    public const FEATURES = [
        'has_ml_integration' => ['Mercado Livre', 'bi-shop', ['mercadolivre.*'], ['mercadolivre.webhooks.*']],
        'has_shopee_integration' => ['Shopee', 'bi-bag-heart', ['shopee.*'], ['shopee.webhook.*']],
        'has_ai_features' => ['Lançar por print e WhatsApp', 'bi-stars', ['finance.print-capture', 'finance.print-capture.*', 'settings.whatsapp'], []],
        'has_advanced_reports' => ['Relatórios avançados', 'bi-graph-up-arrow', ['gestao.profit', 'gestao.channel-profit', 'promotions.site-report'], []],
        'has_financial_control' => ['Controle financeiro', 'bi-wallet2', ['banks.*', 'cashbook.*', 'invoices.*', 'cofrinhos.*', 'consortiums.*', 'accounts.*', 'dashboard.cashbook', 'dashboard.banks', 'finance.*'], []],
        'has_export_pdf_excel' => ['Exportar PDF e Excel', 'bi-file-earmark-arrow-down', ['reports.*.export'], []],
    ];

    private static ?bool $on = null;

    public static function enabled(): bool
    {
        if (static::$on !== null) {
            return static::$on;
        }
        try {
            static::$on = Schema::hasTable('app_settings')
                && DB::table('app_settings')->where('key', 'plan_gate')->value('value') === '1';
        } catch (\Throwable $e) {
            static::$on = false;
        }

        return static::$on;
    }

    public static function setEnabled(bool $on): void
    {
        DB::table('app_settings')->updateOrInsert(['key' => 'plan_gate'], ['value' => $on ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]);
        static::$on = $on;
    }

    /** Recurso que a rota precisa, ou null. */
    public static function featureFor(?string $route): ?string
    {
        if (! $route) {
            return null;
        }
        foreach (static::FEATURES as $feature => [$name, $icon, $routes, $free]) {
            if (Str::is($free, $route)) {
                continue;
            }
            if (Str::is($routes, $route)) {
                return $feature;
            }
        }

        return null;
    }

    /** Recurso que falta para abrir a rota (null = pode abrir). */
    public static function missing(?User $user, ?string $route): ?string
    {
        if (! $user || $user->isAdmin() || ! static::enabled()) {
            return null;
        }
        $feature = static::featureFor($route);

        return $feature && ! $user->hasPlanFeature($feature) ? $feature : null;
    }

    /** Recursos que a pessoa não tem (vazio com a chave desligada). */
    public static function lockedFeatures(?User $user): array
    {
        if (! $user || $user->isAdmin() || ! static::enabled()) {
            return [];
        }

        return array_values(array_filter(array_keys(static::FEATURES), fn ($f) => ! $user->hasPlanFeature($f)));
    }

    /** CSS que põe cadeado nos links do menu das telas bloqueadas. */
    public static function menuCss(?User $user = null): string
    {
        $user = $user ?? auth()->user();
        $locked = static::lockedFeatures($user);
        if (! $locked) {
            return '';
        }
        $base = rtrim(url('/'), '/');
        $paths = [];
        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $r) {
            $name = $r->getName();
            if (! $name || ! in_array(static::featureFor($name), $locked, true) || str_contains($r->uri(), '{') || ! in_array('GET', $r->methods(), true)) {
                continue;
            }
            $paths[] = $base . '/' . ltrim($r->uri(), '/');
        }
        $sel = [];
        foreach (['#sidebarScroll', '#mobileMoreSheet', '#mobileFabSheet'] as $scope) {
            foreach (array_unique($paths) as $u) {
                $u = addslashes($u);
                $sel[] = "{$scope} a[href=\"{$u}\"]";
            }
        }
        if (! $sel) {
            return '';
        }
        $list = implode(',', $sel);
        $after = implode(',', array_map(fn ($s) => $s . '::after', $sel));

        return "{$list}{opacity:.6;position:relative}{$after}{content:\"\\F47A\";font-family:bootstrap-icons!important;font-size:.7rem;margin-left:auto;padding-left:.35rem;color:#94a3b8}";
    }

    /** Primeiro plano ativo (mais barato) que inclui o recurso. */
    public static function planWith(string $feature): ?\App\Models\Plan
    {
        try {
            return \App\Models\Plan::query()->where('is_active', true)->where($feature, true)->orderBy('sort_order')->first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
