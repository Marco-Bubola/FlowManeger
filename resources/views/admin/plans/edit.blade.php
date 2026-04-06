<x-layouts.app title="Admin - Editar plano">
    <style>
        :root {
            --plan-muted: #64748b;
            --plan-border: rgba(148,163,184,.18);
            --plan-border-dark: rgba(71,85,105,.48);
            --muted: #64748b;
            --border: rgba(148,163,184,.18);
            --text: #0f172a;
        }

        .plan-form-page {
            width: 100%;
            padding: 1.25rem var(--app-fluid-padding, clamp(0.65rem, 1.2vw, 1rem)) max(7.5rem, env(safe-area-inset-bottom));
            color: #0f172a;
        }

        .dark .plan-form-page { color: #f8fafc; }
        .plan-form-page a { color: inherit; text-decoration: none; }
        .page-title { font-size:1.5rem;font-weight:900;letter-spacing:-.03em;margin-bottom:.2rem; }
        .page-sub { font-size:.84rem;color:var(--plan-muted); }
        .dark .page-sub { color:#94a3b8; }
        .btn { display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.2rem;border-radius:9px;font-size:.82rem;font-weight:700;cursor:pointer;border:none;transition:all .18s; }
        .btn-primary { background:linear-gradient(135deg, #3b82f6, #7c3aed);color:#fff;box-shadow:0 12px 24px -18px rgba(79,70,229,.85); }
        .btn-primary:hover { opacity:.9;transform:translateY(-1px); }
        .btn-ghost { background:rgba(255,255,255,.78);color:#475569;border:1px solid var(--plan-border); }
        .btn-ghost:hover { background:rgba(255,255,255,.96);color:#0f172a; }
        .dark .btn-ghost { background:rgba(15,23,42,.62);color:#cbd5e1;border-color:var(--plan-border-dark); }
        .btn-danger { background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.2); }
        .form-card { background: white; border: 1.5px solid #f1f5f9; border-radius: 1.1rem; padding: 1.25rem 1.5rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.03); }
        .dark .form-card { background: #1e293b; border-color: #334155; }
        .section-divider { font-size:.7rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--plan-muted);padding:.5rem 0;border-bottom:1px solid var(--plan-border);margin-bottom:1rem; }
        .dark .section-divider { color:#94a3b8;border-bottom-color:var(--plan-border-dark); }
        .field-label { display:block;font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--plan-muted);margin-bottom:.35rem; }
        .field-input { width:100%;background:rgba(255,255,255,.8);border:1px solid var(--plan-border);border-radius:8px;padding:.6rem .9rem;color:#0f172a;font-size:.85rem;outline:none;transition:border-color .15s; }
        .dark .field-input { background:rgba(15,23,42,.62);border-color:var(--plan-border-dark);color:#f8fafc; }
        .field-input:focus { border-color:#a855f7; }
        .field-input::placeholder { color:rgba(100,116,139,.55); }
        textarea.field-input { resize:vertical;font-family:inherit; }
        .field-error { display:block;font-size:.72rem;color:#f87171;margin-top:.25rem; }
        .toggle-row { display:flex;align-items:center;gap:.75rem;cursor:pointer;padding:.4rem 0; }
        .toggle-track { width:36px;height:20px;border-radius:10px;background:rgba(255,255,255,.1);border:1px solid var(--border);position:relative;flex-shrink:0;transition:background .15s; }
        .toggle-thumb { position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#fff;transition:transform .15s;opacity:.5; }
        input[type=checkbox]:checked + .toggle-track { background:rgba(168,85,247,.4);border-color:#a855f7; }
        input[type=checkbox]:checked + .toggle-track .toggle-thumb { transform:translateX(16px);opacity:1; }
        input[type=checkbox] { display:none; }
        .toggle-label { font-size:.83rem;font-weight:500; }
        .form-footer { display:flex;gap:.75rem;justify-content:space-between;align-items:center;padding-top:1.25rem;border-top:1px solid var(--border);margin-top:1.5rem; }
        .alert { padding:.85rem 1.2rem;border-radius:10px;font-size:.84rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.6rem; }
        .alert-error { background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171; }
        .alert-warning { background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#fbbf24; }
        .form-footer { border-top-color: var(--plan-border); }
        .dark .form-footer { border-top-color: var(--plan-border-dark); }
        @media (max-width: 768px) {
            .plan-form-page { padding: .85rem .65rem max(7.25rem, env(safe-area-inset-bottom)); }
            .form-card { padding: .85rem; }
            .form-footer { flex-direction: column-reverse; align-items: stretch; }
            .form-footer .btn { width: 100%; justify-content: center; }
        }
    </style>

    <div class="plan-form-page w-full app-viewport-fit mobile-393-base">
        @include('partials.plan-center-nav', [
            'scope' => 'admin',
            'title' => 'Editar plano',
            'subtitle' => 'Ajuste catálogo e recursos no mesmo layout padrão do FlowManager.',
        ])

        <div style="margin-bottom:1rem">
            <a href="{{ route('admin.plans.index') }}" style="font-size:.8rem;color:var(--plan-muted)">← Voltar aos Planos</a>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem">
            <div>
                <div class="page-title">Editar: {{ $plan->name }}</div>
                <div class="page-sub">{{ $subscribersCount }} assinante(s) ativo(s) neste plano.</div>
            </div>
            @if($subscribersCount == 0)
                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('Excluir o plano {{ $plan->name }}? Esta ação não pode ser desfeita.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Excluir Plano</button>
                </form>
            @else
                <div class="alert alert-warning" style="margin:0;padding:.5rem .9rem">
                    ⚠ Não é possível excluir — há {{ $subscribersCount }} assinante(s) ativo(s).
                </div>
            @endif
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <div>
                    <strong>Corrija os erros:</strong>
                    <ul style="margin-top:.4rem;padding-left:1rem">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
            @csrf @method('PUT')
            <div class="form-card">
                @include('admin.plans._form')
            </div>
            <div class="form-footer">
                <a href="{{ route('admin.plans.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">✓ Salvar Alterações</button>
            </div>
        </form>
    </div>
</x-layouts.app>
