<x-layouts.app title="Acesso e permissões">
<section class="settings-access-page w-full mobile-393-base">
    <x-settings.layout :heading="''">
        @php
            // Visão da própria conta: o que o plano libera e a comparação com os outros planos.
            $me = auth()->user();
            $myPlan = $me->activeSubscription?->plan ?? $defaultPlan;
            $mySub = $me->activeSubscription;
            $myPerms = collect($me->effectivePermissions())->reject(fn ($p) => $p['source'] === 'admin-only');
            $onCount = $myPerms->where('enabled', true)->count();
        @endphp

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">{{ session('error') }}</div>
        @endif

        @php
            $acIcons = [
                'use-ml-integration' => ['bi-bag-check', 'from-amber-400 to-yellow-500', 'Anúncios, pedidos e estoque do Mercado Livre'],
                'use-shopee-integration' => ['bi-shop', 'from-orange-500 to-red-500', 'Pedidos e estoque da Shopee'],
                'use-ai-features' => ['bi-stars', 'from-violet-500 to-fuchsia-500', 'Leitura de prints, sugestões e textos prontos'],
                'use-advanced-reports' => ['bi-graph-up-arrow', 'from-sky-500 to-blue-600', 'Lucro real, painéis e comparativos'],
                'use-financial-control' => ['bi-wallet2', 'from-emerald-500 to-teal-500', 'Contas, cartões, faturas e livro caixa'],
                'use-api-access' => ['bi-plug', 'from-slate-500 to-slate-700', 'Ligue o app a outros sistemas'],
                'use-priority-support' => ['bi-headset', 'from-pink-500 to-rose-500', 'Atendimento na frente da fila'],
                'use-export-pdf-excel' => ['bi-file-earmark-spreadsheet', 'from-green-500 to-emerald-600', 'Baixe relatórios em PDF e planilha'],
                'manage-team' => ['bi-people', 'from-indigo-500 to-purple-600', 'Mais pessoas usando a mesma conta'],
            ];
            $acPct = $myPerms->count() ? (int) round($onCount / $myPerms->count() * 100) : 0;
        @endphp

        {{-- Topo: plano atual e quanto dele está liberado --}}
        <div class="ac-hero bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
            <span class="ac-glow"></span>
            <div class="ac-ring" style="--pct: {{ $acPct }}"><b>{{ $onCount }}<small>/{{ $myPerms->count() }}</small></b></div>
            <div class="ac-hero-txt">
                <span class="ac-kicker"><i class="bi bi-person-lock"></i> Acesso e permissões</span>
                <h2>Plano {{ $myPlan?->name ?? 'sem plano' }}</h2>
                <p>
                    @if($me->isAdmin())
                        Você é administrador: tudo está liberado.
                    @else
                        {{ $onCount }} de {{ $myPerms->count() }} recursos incluídos no seu plano. Os com cadeado fazem parte de outros planos.
                    @endif
                </p>
                @if($mySub)
                    <span class="ac-status"><i class="bi bi-circle-fill"></i> {{ $mySub->statusLabel() }}</span>
                @endif
            </div>
            <div class="ac-hero-actions">
                <a href="{{ route('subscription.plans') }}" class="ac-btn-light"><i class="bi bi-stars"></i> Trocar plano</a>
                @if(\Illuminate\Support\Facades\Route::has('settings.plan'))
                    <a href="{{ route('settings.plan') }}" wire:navigate class="ac-btn-glass"><i class="bi bi-patch-check"></i> Minha assinatura</a>
                @endif
                @if($me->isAdmin() && \Illuminate\Support\Facades\Route::has('admin.plans.users'))
                    <a href="{{ route('admin.plans.users') }}" class="ac-btn-glass"><i class="bi bi-person-gear"></i> Todos os usuários</a>
                @endif
            </div>
        </div>

        {{-- O que o seu plano libera --}}
        <div class="ap-card ac-block">
            <div class="ap-card-head">
                <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-unlock"></i></span>
                <div class="min-w-0">
                    <h2>O que o seu plano libera</h2>
                    <p>Verde está no seu plano. Com cadeado, está em outro plano.</p>
                </div>
            </div>
            <div class="ac-feats">
                @foreach($myPerms as $perm)
                    @php [$fi, $ft, $fd] = $acIcons[$perm['ability']] ?? ['bi-shield-check', 'from-indigo-500 to-purple-600', 'Recurso de administração']; @endphp
                    <div class="ac-feat {{ $perm['enabled'] ? 'is-on' : '' }}">
                        <span class="ac-feat-ico {{ $perm['enabled'] ? 'bg-gradient-to-br ' . $ft : '' }}"><i class="bi {{ $fi }}"></i></span>
                        <div class="min-w-0">
                            <b>{{ $perm['label'] }}</b>
                            <small>{{ $fd }}</small>
                        </div>
                        <i class="bi {{ $perm['enabled'] ? 'bi-check-circle-fill' : 'bi-lock-fill' }} ac-feat-state"></i>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Comparar planos --}}
        <div class="ap-card ac-block">
            <div class="ap-card-head">
                <span class="ap-ico bg-gradient-to-br from-violet-500 to-fuchsia-500"><i class="bi bi-layers"></i></span>
                <div class="min-w-0">
                    <h2>Comparar planos</h2>
                    <p>O que cada plano inclui, lado a lado.</p>
                </div>
            </div>

            <div class="grid gap-3" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,250px),1fr))">
                @foreach($plans as $plan)
                    @php $isMine = $myPlan && $myPlan->id === $plan->id; @endphp
                    <article class="flex flex-col rounded-2xl border p-4 {{ $isMine ? 'border-indigo-300 bg-indigo-50/50 dark:border-indigo-500/50 dark:bg-indigo-500/10' : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900/40' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $plan->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $plan->max_users }} usuário{{ $plan->max_users > 1 ? 's' : '' }} · {{ $plan->max_products === -1 ? 'produtos ilimitados' : $plan->max_products . ' produtos' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-purple-600 dark:text-purple-300 whitespace-nowrap">{{ $plan->formattedPriceMonthly() }}</p>
                                @if(! $plan->isFree())<p class="text-[11px] text-slate-400">por mês</p>@endif
                            </div>
                        </div>
                        @if($isMine)
                            <span style="width:max-content" class="mt-2 inline-flex items-center gap-1 rounded-full bg-indigo-600 px-2 py-0.5 text-[11px] font-semibold text-white"><i class="bi bi-check2"></i>Seu plano</span>
                        @elseif($plan->badge_label)
                            <span style="width:max-content" class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">{{ $plan->badge_label }}</span>
                        @endif
                        <ul class="mt-3 space-y-1 text-sm">
                            @foreach($plan->permissionMatrix() as $perm)
                                <li class="flex items-center gap-2 {{ $perm['enabled'] ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500' }}">
                                    <i class="bi {{ $perm['enabled'] ? 'bi-check-lg text-emerald-500' : 'bi-x-lg' }}"></i>{{ $perm['label'] }}
                                </li>
                            @endforeach
                        </ul>
                        @unless($isMine)
                            <a href="{{ route('subscription.plans') }}" class="mt-4 inline-flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold border border-indigo-200 dark:border-indigo-500/40 text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100">Escolher {{ $plan->name }}</a>
                        @endunless
                    </article>
                @endforeach
            </div>
        </div>
    <style>
        .ac-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .ac-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .ac-ring { position: relative; flex: none; display: grid; place-items: center; width: 5.2rem; height: 5.2rem; border-radius: 999px; background: conic-gradient(#fff calc(var(--pct) * 1%), rgba(255, 255, 255, .22) 0); }
        .ac-ring::before { content: ''; position: absolute; inset: .45rem; border-radius: 999px; background: rgba(76, 29, 149, .55); backdrop-filter: blur(6px); }
        .ac-ring b { position: relative; font-size: 1.5rem; font-weight: 900; line-height: 1; }
        .ac-ring small { font-size: .8rem; font-weight: 700; opacity: .8; }
        .ac-hero-txt { position: relative; flex: 1; min-width: 0; }
        .ac-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .9; }
        .ac-hero-txt h2 { font-size: 1.5rem; font-weight: 900; line-height: 1.15; margin-top: .2rem; }
        .ac-hero-txt p { font-size: .82rem; opacity: .92; max-width: 34rem; }
        .ac-status { display: inline-flex; align-items: center; gap: .35rem; margin-top: .55rem; font-size: .7rem; font-weight: 800; padding: .25rem .65rem; border-radius: 999px; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .25); }
        .ac-status i { font-size: .45rem; color: #4ade80; }
        .ac-hero-actions { position: relative; flex: none; display: flex; flex-direction: column; gap: .45rem; }
        .ac-btn-light, .ac-btn-glass { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; font-size: .8rem; font-weight: 800; padding: .6rem 1rem; border-radius: .9rem; white-space: nowrap; }
        .ac-btn-light { background: #fff; color: var(--color-indigo-700); box-shadow: 0 10px 20px -12px rgba(0, 0, 0, .5); }
        .ac-btn-glass { background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .3); color: #fff; }
        .ac-feats { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 250px), 1fr)); gap: .5rem; }
        .ac-feat { display: flex; align-items: center; gap: .7rem; padding: .7rem .8rem; border-radius: 1rem; border: 1px solid #eef2f7; background: #f8fafc; }
        .ac-feat.is-on { background: #fff; border-color: #a7f3d0; box-shadow: 0 8px 20px -16px rgba(16, 185, 129, .7); }
        .dark .ac-feat { background: #111827; border-color: #1f2937; }
        .dark .ac-feat.is-on { background: rgba(16, 185, 129, .07); border-color: rgba(16, 185, 129, .35); box-shadow: none; }
        .ac-feat-ico { flex: none; display: grid; place-items: center; width: 2.4rem; height: 2.4rem; border-radius: .8rem; background-color: #e2e8f0; color: #94a3b8; font-size: 1.05rem; }
        .ac-feat.is-on .ac-feat-ico { color: #fff; }
        .dark .ac-feat-ico { background-color: #1f2937; color: #64748b; }
        .ac-feat > div { flex: 1; }
        .ac-feat b { display: block; font-size: .84rem; color: #0f172a; }
        .ac-feat small { display: block; font-size: .7rem; color: #64748b; line-height: 1.3; }
        .ac-feat:not(.is-on) b { color: #94a3b8; }
        .dark .ac-feat b { color: #f1f5f9; }
        .dark .ac-feat:not(.is-on) b { color: #64748b; }
        .dark .ac-feat small { color: #94a3b8; }
        .ac-feat-state { flex: none; color: #cbd5e1; }
        .ac-feat.is-on .ac-feat-state { color: #10b981; }
        .dark .ac-feat-state { color: #475569; }
        @media (max-width: 1023px) {
            .ac-hero { flex-wrap: wrap; }
            .ac-hero-txt { flex-basis: calc(100% - 6.5rem); }
            .ac-hero-actions { width: 100%; flex-direction: row; flex-wrap: wrap; }
        }
        @media (max-width: 767px) {
            .ac-hero { padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .ac-ring { width: 4.2rem; height: 4.2rem; }
            .ac-ring b { font-size: 1.2rem; }
            .ac-hero-txt { flex-basis: calc(100% - 5.2rem); }
            .ac-hero-txt h2 { font-size: 1.2rem; }
            .ac-hero-actions a { flex: 1; }
        }
    </style>
    </x-settings.layout>
</section>
</x-layouts.app>
