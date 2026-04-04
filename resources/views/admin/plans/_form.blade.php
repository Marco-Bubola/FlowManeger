{{-- resources/views/admin/plans/_form.blade.php --}}
<div class="plan-form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">

    <style>
        @media (max-width: 768px) {
            .plan-form-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
        }
    </style>

    {{-- Nome --}}
    <div>
        <label class="field-label">NOME DO PLANO</label>
        <input type="text" name="name" class="field-input" value="{{ old('name', $plan->name ?? '') }}" required placeholder="Ex: Pro">
        @error('name')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    {{-- Slug --}}
    <div>
        <label class="field-label">SLUG (identificador único)</label>
        <input type="text" name="slug" class="field-input" value="{{ old('slug', $plan->slug ?? '') }}" required placeholder="ex: pro">
        @error('slug')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    {{-- Descrição --}}
    <div style="grid-column:1/-1">
        <label class="field-label">DESCRIÇÃO</label>
        <textarea name="description" class="field-input" rows="2" placeholder="Breve descrição para a landing page...">{{ old('description', $plan->description ?? '') }}</textarea>
        @error('description')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    {{-- Preços --}}
    <div>
        <label class="field-label">PREÇO MENSAL (R$)</label>
        <input type="number" name="price_monthly" class="field-input" step="0.01" min="0"
               value="{{ old('price_monthly', $plan->price_monthly ?? '0') }}" required>
        @error('price_monthly')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div>
        <label class="field-label">PREÇO ANUAL (R$/mês cobrando anualmente)</label>
        <input type="number" name="price_annual" class="field-input" step="0.01" min="0"
               value="{{ old('price_annual', $plan->price_annual ?? '0') }}" required>
        @error('price_annual')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    {{-- Limites --}}
    <div>
        <label class="field-label">MÁX. PRODUTOS <span style="color:var(--muted);font-weight:400">(-1 = ilimitado)</span></label>
        <input type="number" name="max_products" class="field-input" min="-1"
               value="{{ old('max_products', $plan->max_products ?? 50) }}">
    </div>
    <div>
        <label class="field-label">MÁX. PEDIDOS/MÊS <span style="color:var(--muted);font-weight:400">(-1 = ilimitado)</span></label>
        <input type="number" name="max_orders_per_month" class="field-input" min="-1"
               value="{{ old('max_orders_per_month', $plan->max_orders_per_month ?? 100) }}">
    </div>
    <div>
        <label class="field-label">MÁX. CLIENTES <span style="color:var(--muted);font-weight:400">(-1 = ilimitado)</span></label>
        <input type="number" name="max_clients" class="field-input" min="-1"
               value="{{ old('max_clients', $plan->max_clients ?? 200) }}">
    </div>
    <div>
        <label class="field-label">MÁX. USUÁRIOS NA EQUIPE</label>
        <input type="number" name="max_users" class="field-input" min="1"
               value="{{ old('max_users', $plan->max_users ?? 1) }}">
    </div>

    {{-- Período de trial --}}
    <div>
        <label class="field-label">DIAS DE TRIAL (0 = sem trial)</label>
        <input type="number" name="trial_days" class="field-input" min="0" max="90"
               value="{{ old('trial_days', $plan->trial_days ?? 0) }}">
    </div>

    {{-- Cor e Badge --}}
    <div>
        <label class="field-label">COR DO PLANO (hex)</label>
        <input type="text" name="color" class="field-input" placeholder="#a855f7"
               value="{{ old('color', $plan->color ?? '') }}">
    </div>
    <div>
        <label class="field-label">RÓTULO DESTAQUE (opcional)</label>
        <input type="text" name="badge_label" class="field-input" placeholder="Mais popular"
               value="{{ old('badge_label', $plan->badge_label ?? '') }}">
    </div>

    {{-- Sort + Flags --}}
    <div>
        <label class="field-label">ORDEM DE EXIBIÇÃO</label>
        <input type="number" name="sort_order" class="field-input" min="0"
               value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
    </div>

    {{-- Booleans --}}
    <div style="grid-column:1/-1">
        <div class="section-divider">Recursos do Plano</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;margin-top:1rem">
            @foreach([
                'has_ml_integration'     => 'Integração Mercado Livre',
                'has_shopee_integration' => 'Integração Shopee',
                'has_ai_features'        => 'Recursos de IA',
                'has_advanced_reports'   => 'Relatórios Avançados',
                'has_financial_control'  => 'Controle Financeiro',
                'has_api_access'         => 'Acesso à API',
                'has_priority_support'   => 'Suporte Prioritário 24/7',
                'has_export_pdf_excel'   => 'Exportar PDF/Excel',
            ] as $field => $label)
                <label class="toggle-row">
                    <input type="checkbox" name="{{ $field }}" value="1"
                           {{ old($field, isset($plan) ? ($plan->$field ? 'checked' : '') : '') === '1' || (old($field) === null && isset($plan) && $plan->$field) ? 'checked' : '' }}>
                    <span class="toggle-track"><span class="toggle-thumb"></span></span>
                    <span class="toggle-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Status flags --}}
    <div>
        <label class="toggle-row">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', isset($plan) ? ($plan->is_active ? '1' : '') : '1') === '1' ? 'checked' : '' }}>
            <span class="toggle-track"><span class="toggle-thumb"></span></span>
            <span class="toggle-label">Plano visível/ativo</span>
        </label>
    </div>
    <div>
        <label class="toggle-row">
            <input type="checkbox" name="is_default" value="1"
                   {{ old('is_default', isset($plan) ? ($plan->is_default ? '1' : '') : '') === '1' ? 'checked' : '' }}>
            <span class="toggle-track"><span class="toggle-thumb"></span></span>
            <span class="toggle-label">Plano padrão (atribuído ao cadastrar)</span>
        </label>
    </div>
</div>
