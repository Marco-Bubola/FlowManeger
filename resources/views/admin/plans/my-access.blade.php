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

        {{-- O que o seu plano libera --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(16,185,129,.12);color:#10b981"><i class="bi bi-unlock"></i></div>
                    <div>
                        <p class="settings-card-title">O que o seu plano libera</p>
                        <p class="settings-card-desc">Plano <strong>{{ $myPlan?->name ?? 'sem plano' }}</strong> · {{ $onCount }} de {{ $myPerms->count() }} recursos liberados</p>
                    </div>
                </div>
                @if($mySub)
                    <span class="s-badge" style="background:{{ $mySub->statusColor() }}1a;color:{{ $mySub->statusColor() }};border:1px solid {{ $mySub->statusColor() }}50">{{ $mySub->statusLabel() }}</span>
                @endif
            </div>

            <div style="padding:0 1.25rem 1.25rem">
                @if($me->isAdmin())
                    <p class="mb-3 text-sm text-slate-600 dark:text-slate-300"><i class="bi bi-shield-lock text-pink-500 mr-1"></i>Você é administrador: tudo está liberado.</p>
                @endif
                <div class="grid gap-2" style="grid-template-columns:repeat(auto-fill,minmax(min(100%,190px),1fr))">
                    @foreach($myPerms as $perm)
                        <div class="flex items-center gap-2 rounded-xl border px-3 py-2 text-sm {{ $perm['enabled'] ? 'border-emerald-200 bg-emerald-50/60 text-slate-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-slate-100' : 'border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-500' }}">
                            <i class="bi {{ $perm['enabled'] ? 'bi-check-circle-fill text-emerald-500' : 'bi-lock' }}"></i>
                            <span class="min-w-0">{{ $perm['label'] }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('subscription.plans') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25"><i class="bi bi-stars"></i>Trocar plano</a>
                    @if(\Illuminate\Support\Facades\Route::has('settings.plan'))
                        <a href="{{ route('settings.plan') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi bi-patch-check"></i>Minha assinatura</a>
                    @endif
                    @if($me->isAdmin() && \Illuminate\Support\Facades\Route::has('admin.plans.users'))
                        <a href="{{ route('admin.plans.users') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi bi-person-gear"></i>Usuários (admin)</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Comparar planos --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(168,85,247,.12);color:#a855f7"><i class="bi bi-layers"></i></div>
                    <div>
                        <p class="settings-card-title">Comparar planos</p>
                        <p class="settings-card-desc">Ao trocar de plano os recursos mudam na hora</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-3" style="padding:0 1.25rem 1.25rem;grid-template-columns:repeat(auto-fit,minmax(250px,1fr))">
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
    </x-settings.layout>
</section>
</x-layouts.app>
