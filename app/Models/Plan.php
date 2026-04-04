<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description',
        'price_monthly', 'price_annual',
        'trial_days', 'is_active', 'is_default', 'sort_order',
        'max_products', 'max_orders_per_month', 'max_clients', 'max_users',
        'has_ml_integration', 'has_shopee_integration', 'has_ai_features',
        'has_advanced_reports', 'has_financial_control', 'has_api_access',
        'has_priority_support', 'has_export_pdf_excel',
        'features', 'color', 'badge_label',
    ];

    protected $casts = [
        'price_monthly'        => 'decimal:2',
        'price_annual'         => 'decimal:2',
        'is_active'            => 'boolean',
        'is_default'           => 'boolean',
        'has_ml_integration'   => 'boolean',
        'has_shopee_integration' => 'boolean',
        'has_ai_features'      => 'boolean',
        'has_advanced_reports' => 'boolean',
        'has_financial_control'=> 'boolean',
        'has_api_access'       => 'boolean',
        'has_priority_support' => 'boolean',
        'has_export_pdf_excel' => 'boolean',
        'features'             => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('status', 'active');
    }

    // ── Helpers ───────────────────────────────────────────────────

    /** Retorna true se o limite é ilimitado (-1). */
    public function isUnlimited(string $field): bool
    {
        return isset($this->{$field}) && (int) $this->{$field} === -1;
    }

    public function isFree(): bool
    {
        return $this->slug === 'free';
    }

    public function formattedPriceMonthly(): string
    {
        if ($this->price_monthly == 0) {
            return 'Grátis';
        }
        return 'R$ ' . number_format($this->price_monthly, 2, ',', '.');
    }

    public function formattedPriceAnnual(): string
    {
        if ($this->price_annual == 0) {
            return 'Grátis';
        }
        return 'R$ ' . number_format($this->price_annual, 2, ',', '.');
    }

    public function annualSavingsPercent(): int
    {
        if ($this->price_monthly == 0 || $this->price_annual == 0) {
            return 0;
        }
        return (int) round((1 - ($this->price_annual / $this->price_monthly)) * 100);
    }

    /** Lista de features para exibir nas cards de pricing */
    public function featuresList(): array
    {
        $base = $this->features ?? [];

        $auto = [];

        if ($this->max_products === -1) {
            $auto[] = ['label' => 'Produtos ilimitados', 'active' => true];
        } else {
            $auto[] = ['label' => "Até {$this->max_products} produtos", 'active' => true];
        }

        if ($this->max_orders_per_month === -1) {
            $auto[] = ['label' => 'Pedidos ilimitados/mês', 'active' => true];
        } else {
            $auto[] = ['label' => "Até {$this->max_orders_per_month} pedidos/mês", 'active' => true];
        }

        if ($this->max_clients === -1) {
            $auto[] = ['label' => 'Clientes ilimitados', 'active' => true];
        } else {
            $auto[] = ['label' => "Até {$this->max_clients} clientes", 'active' => true];
        }

        $auto[] = ['label' => 'Dashboard completo', 'active' => true];
        $auto[] = ['label' => 'Integração Mercado Livre', 'active' => $this->has_ml_integration];
        $auto[] = ['label' => 'Integração Shopee', 'active' => $this->has_shopee_integration];
        $auto[] = ['label' => 'IA + Gemini', 'active' => $this->has_ai_features];
        $auto[] = ['label' => 'Relatórios avançados + PDF/Excel', 'active' => $this->has_advanced_reports];
        $auto[] = ['label' => 'Controle financeiro', 'active' => $this->has_financial_control];
        $auto[] = ['label' => 'API de integração', 'active' => $this->has_api_access];
        $auto[] = ['label' => "Até {$this->max_users} usuário(s) na equipe", 'active' => $this->max_users > 1];
        $auto[] = ['label' => 'Suporte prioritário 24/7', 'active' => $this->has_priority_support];

        return array_merge($auto, $base);
    }

    public static function permissionCatalog(): array
    {
        return [
            'use-ml-integration' => ['label' => 'Mercado Livre', 'field' => 'has_ml_integration'],
            'use-shopee-integration' => ['label' => 'Shopee', 'field' => 'has_shopee_integration'],
            'use-ai-features' => ['label' => 'IA e automacoes', 'field' => 'has_ai_features'],
            'use-advanced-reports' => ['label' => 'Relatorios avancados', 'field' => 'has_advanced_reports'],
            'use-financial-control' => ['label' => 'Controle financeiro', 'field' => 'has_financial_control'],
            'use-api-access' => ['label' => 'API de integracao', 'field' => 'has_api_access'],
            'use-priority-support' => ['label' => 'Suporte prioritario', 'field' => 'has_priority_support'],
            'use-export-pdf-excel' => ['label' => 'Exportacao PDF/Excel', 'field' => 'has_export_pdf_excel'],
            'manage-team' => ['label' => 'Equipe multiusuario'],
        ];
    }

    public function permissionMatrix(): array
    {
        return collect(static::permissionCatalog())
            ->map(function (array $meta, string $ability) {
                $enabled = $ability === 'manage-team'
                    ? (int) $this->max_users > 1
                    : (bool) ($this->{$meta['field']} ?? false);

                return [
                    'ability' => $ability,
                    'label' => $meta['label'],
                    'enabled' => $enabled,
                ];
            })
            ->values()
            ->all();
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price_monthly');
    }

    // ── Static helpers ────────────────────────────────────────────

    public static function getDefault(): self
    {
        return static::where('is_default', true)->first()
            ?? static::where('slug', 'free')->first()
            ?? static::first();
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
