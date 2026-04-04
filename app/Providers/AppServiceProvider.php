<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registrar observadores de modelos
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);

        // ── Gates de Permissão ────────────────────────────────────

        // Acesso admin (somente user ID 2 por enquanto)
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // Gerenciar planos (CRUD admin)
        Gate::define('manage-plans', fn (User $user) => $user->isAdmin());

        // Gerenciar usuários
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());

        // Gerenciar assinaturas de qualquer usuário
        Gate::define('manage-subscriptions', fn (User $user) => $user->isAdmin());

        // Ver painel admin
        Gate::define('view-admin-panel', fn (User $user) => $user->isAdmin());

        // Features por plano — admin sempre tem acesso a tudo
        Gate::define('use-ml-integration', fn (User $user)
            => $user->isAdmin() || $user->hasPlanFeature('has_ml_integration'));

        Gate::define('use-shopee-integration', fn (User $user)
            => $user->isAdmin() || $user->hasPlanFeature('has_shopee_integration'));

        Gate::define('use-ai-features', fn (User $user)
            => $user->isAdmin() || $user->hasPlanFeature('has_ai_features'));

        Gate::define('use-advanced-reports', fn (User $user)
            => $user->isAdmin() || $user->hasPlanFeature('has_advanced_reports'));

        Gate::define('use-financial-control', fn (User $user)
            => $user->isAdmin() || $user->hasPlanFeature('has_financial_control'));

        Gate::define('use-api-access', fn (User $user)
            => $user->isAdmin() || $user->hasPlanFeature('has_api_access'));

        Gate::define('use-priority-support', fn (User $user)
            => $user->isAdmin() || $user->hasPlanFeature('has_priority_support'));

        Gate::define('use-export-pdf-excel', fn (User $user)
            => $user->isAdmin() || $user->hasPlanFeature('has_export_pdf_excel'));

        Gate::define('manage-team', fn (User $user)
            => $user->canCreateOrManageTeam());

        Gate::define('manage-team-members', fn (User $user)
            => $user->teamCan('invite_members') || $user->teamCan('remove_members'));

        Gate::define('transfer-team-records', fn (User $user)
            => $user->teamCan('transfer_records'));

        Gate::define('access-team-products', fn (User $user)
            => $user->canAccessTeamModule('products'));

        Gate::define('access-team-clients', fn (User $user)
            => $user->canAccessTeamModule('clients'));

        Gate::define('access-team-sales', fn (User $user)
            => $user->canAccessTeamModule('sales'));

        Gate::define('access-team-finances', fn (User $user)
            => $user->canAccessTeamModule('finances'));

        // ── HTTPS forçado ─────────────────────────────────────────

        if ($this->app->environment('local', 'development')) {
            $request = request();
            if ($request->header('X-Forwarded-Proto') === 'https' ||
                $request->header('X-Forwarded-Ssl') === 'on' ||
                $request->server('HTTP_X_FORWARDED_PROTO') === 'https' ||
                $request->server('HTTPS') === 'on' ||
                $request->isSecure()) {
                URL::forceScheme('https');
            }
        }

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}

