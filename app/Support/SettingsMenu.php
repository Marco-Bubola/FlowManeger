<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Mapa único das Configurações: grupos, páginas, ícones e descrições.
 * Usado pelo menu lateral, pela janela "Todas as configurações" do celular e pela página inicial.
 */
class SettingsMenu
{
    public static function groups($user = null): array
    {
        $user = $user ?? auth()->user();

        $groups = [
            'conta' => ['Conta', 'bi-person-circle', [
                ['settings.profile', 'Perfil', 'bi-person', 'Foto, nome, contato e redes', 'from-indigo-500 to-purple-600'],
                ['settings.password', 'Senha e login', 'bi-key', 'Trocar a senha e formas de entrar', 'from-amber-500 to-orange-500'],
                ['settings.security', 'Segurança', 'bi-shield-check', 'Nota de segurança e dicas', 'from-emerald-500 to-teal-500'],
                ['settings.devices', 'Dispositivos', 'bi-phone', 'Onde sua conta está aberta', 'from-sky-500 to-blue-600'],
            ]],
            'plano' => ['Plano e equipe', 'bi-patch-check', [
                ['settings.plan', 'Meu plano', 'bi-patch-check', 'Uso, cobrança e renovação', 'from-violet-500 to-purple-600'],
                ['subscription.plans', 'Trocar plano', 'bi-stars', 'Compare os planos e mude', 'from-pink-500 to-rose-500'],
                ['access.center', 'Acesso e permissões', 'bi-person-lock', 'O que o seu plano libera', 'from-teal-500 to-emerald-600'],
                ['settings.team', 'Equipe', 'bi-people', 'Pessoas, convites e permissões', 'from-blue-500 to-indigo-600'],
            ]],
            'preferencias' => ['Preferências', 'bi-sliders', [
                ['settings.appearance', 'Aparência', 'bi-palette', 'Tema, cores, fonte e modo escuro', 'from-fuchsia-500 to-pink-500'],
                ['settings.notifications', 'Notificações', 'bi-bell', 'O que aparece no sino', 'from-orange-500 to-red-500'],
            ]],
            'loja' => ['Loja e vendas', 'bi-shop', [
                ['settings.products', 'Produtos', 'bi-box-seam', 'Custo, variações e importação', 'from-lime-500 to-green-600'],
                ['settings.payments', 'Pagamentos online', 'bi-qr-code', 'Pix, cartão, boleto e frete', 'from-sky-500 to-cyan-600'],
            ]],
            'integracoes' => ['Integrações', 'bi-plug', [
                ['settings.connections', 'Conexões', 'bi-link-45deg', 'Mercado Livre, Shopee e outros', 'from-yellow-500 to-amber-600'],
                ['settings.whatsapp', 'WhatsApp', 'bi-whatsapp', 'Lançar compras mandando o print', 'from-green-500 to-emerald-600'],
                ['settings.bank-connections', 'Bancos conectados', 'bi-bank', 'Extratos automáticos dos bancos', 'from-slate-500 to-slate-700'],
            ]],
        ];

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $groups['admin'] = ['Administração', 'bi-gear', [
                ['admin.plans.index', 'Planos', 'bi-gear', 'Preços e recursos dos planos', 'from-slate-600 to-slate-800'],
                ['admin.subscriptions.index', 'Assinaturas', 'bi-receipt', 'Assinaturas de todos os usuários', 'from-slate-600 to-slate-800'],
                ['admin.plans.users', 'Usuários', 'bi-person-gear', 'Contas e permissões', 'from-slate-600 to-slate-800'],
            ]];
        }

        foreach ($groups as $key => [$name, $icon, $items]) {
            $groups[$key][2] = array_values(array_filter($items, fn ($i) => Route::has($i[0])));
            if (! $groups[$key][2]) {
                unset($groups[$key]);
            }
        }

        return $groups;
    }

    /** Nome da rota da página aberta (em ações do Livewire a requisição é /livewire/update, então usa a URL da página). */
    public static function currentRouteName(): ?string
    {
        $name = request()->route()?->getName();
        if (class_exists(Livewire::class) && Livewire::isLivewireRequest()) {
            try {
                $name = app('router')->getRoutes()->match(\Illuminate\Http\Request::create(Livewire::originalUrl()))->getName();
            } catch (\Throwable $e) {
            }
        }

        return $name;
    }

    public static function isActive(string $route, ?string $current = null): bool
    {
        $current = $current ?? static::currentRouteName();

        return $current && Str::is($route . '*', $current);
    }

    /** [grupoKey, nomeDoGrupo, item] da página aberta, ou null na página inicial. */
    public static function current($user = null): ?array
    {
        $cur = static::currentRouteName();
        foreach (static::groups($user) as $key => [$name, $icon, $items]) {
            foreach ($items as $item) {
                if (static::isActive($item[0], $cur)) {
                    return [$key, $name, $item];
                }
            }
        }

        return null;
    }
}
