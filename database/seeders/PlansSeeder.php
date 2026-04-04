<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        // ── Criar planos ──────────────────────────────────────────

        $free = Plan::updateOrCreate(['slug' => 'free'], [
            'name'                  => 'Grátis',
            'description'           => 'Para começar a organizar suas vendas sem gastar nada.',
            'price_monthly'         => 0,
            'price_annual'          => 0,
            'trial_days'            => 0,
            'is_active'             => true,
            'is_default'            => true,
            'sort_order'            => 1,
            'max_products'          => 50,
            'max_orders_per_month'  => 100,
            'max_clients'           => 200,
            'max_users'             => 1,
            'has_ml_integration'    => false,
            'has_shopee_integration'=> false,
            'has_ai_features'       => false,
            'has_advanced_reports'  => false,
            'has_financial_control' => true,
            'has_api_access'        => false,
            'has_priority_support'  => false,
            'has_export_pdf_excel'  => false,
            'color'                 => '#64748b',
            'badge_label'           => null,
        ]);

        $pro = Plan::updateOrCreate(['slug' => 'pro'], [
            'name'                  => 'Pro',
            'description'           => 'Para vendedores sérios que querem crescer com dados.',
            'price_monthly'         => 49.00,
            'price_annual'          => 39.00,
            'trial_days'            => 7,
            'is_active'             => true,
            'is_default'            => false,
            'sort_order'            => 2,
            'max_products'          => -1,
            'max_orders_per_month'  => -1,
            'max_clients'           => -1,
            'max_users'             => 1,
            'has_ml_integration'    => true,
            'has_shopee_integration'=> false,
            'has_ai_features'       => true,
            'has_advanced_reports'  => true,
            'has_financial_control' => true,
            'has_api_access'        => false,
            'has_priority_support'  => false,
            'has_export_pdf_excel'  => true,
            'color'                 => '#a855f7',
            'badge_label'           => 'Mais popular',
        ]);

        $business = Plan::updateOrCreate(['slug' => 'business'], [
            'name'                  => 'Business',
            'description'           => 'Para operações maiores com múltiplos canais e equipes.',
            'price_monthly'         => 149.00,
            'price_annual'          => 119.00,
            'trial_days'            => 7,
            'is_active'             => true,
            'is_default'            => false,
            'sort_order'            => 3,
            'max_products'          => -1,
            'max_orders_per_month'  => -1,
            'max_clients'           => -1,
            'max_users'             => 10,
            'has_ml_integration'    => true,
            'has_shopee_integration'=> true,
            'has_ai_features'       => true,
            'has_advanced_reports'  => true,
            'has_financial_control' => true,
            'has_api_access'        => true,
            'has_priority_support'  => true,
            'has_export_pdf_excel'  => true,
            'color'                 => '#06b6d4',
            'badge_label'           => null,
        ]);

        // ── Atribuir plano gratuito a usuários sem assinatura ─────

        $usersWithoutSub = User::whereDoesntHave('subscriptions')->get();

        foreach ($usersWithoutSub as $user) {
            $user->subscriptions()->create([
                'plan_id'               => $free->id,
                'status'                => Subscription::STATUS_ACTIVE,
                'billing_cycle'         => 'monthly',
                'price_paid'            => 0,
                'gateway'               => Subscription::GATEWAY_MANUAL,
                'current_period_start'  => now(),
                'current_period_end'    => '2037-12-31 23:59:59', // Plano free "sem vencimento"
            ]);
        }

        // ── Admin (user ID 2) recebe plano Business ───────────────

        $admin = User::find(2);
        if ($admin) {
            // Cancela assinaturas atuais do admin
            $admin->subscriptions()
                ->whereIn('status', ['active', 'trialing'])
                ->update(['status' => 'canceled', 'canceled_at' => now()]);

            $admin->subscriptions()->create([
                'plan_id'               => $business->id,
                'status'                => Subscription::STATUS_ACTIVE,
                'billing_cycle'         => 'annual',
                'price_paid'            => 0,
                'gateway'               => Subscription::GATEWAY_MANUAL,
                'current_period_start'  => now(),
                'current_period_end'    => '2037-12-31 23:59:59',
                'notes'                 => 'Admin — acesso permanente.',
            ]);
        }

        $this->command->info('✓ Planos criados: Grátis, Pro, Business');
        $this->command->info("✓ {$usersWithoutSub->count()} usuário(s) receberam o plano Grátis.");
        $this->command->info('✓ Admin (ID 2) recebeu plano Business permanente.');
    }
}
