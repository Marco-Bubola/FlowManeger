<x-layouts.app title="Assinatura ativada">
<section class="settings-plans-page w-full mobile-393-base">
    <x-settings.layout :heading="''">
        <div class="settings-card">
            <div class="flex flex-col items-center text-center" style="padding:1.75rem 1.25rem 1.5rem">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-patch-check-fill"></i></div>
                <h1 class="mt-3 text-xl font-extrabold text-slate-900 dark:text-white">Bem-vindo ao plano {{ $activePlan->name ?? 'FlowManager' }}</h1>
                @if($activatedPlan)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">O plano <strong>{{ $activatedPlan }}</strong> foi ativado e os recursos já estão liberados.</p>
                @else
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sua assinatura está ativa e pronta para usar.</p>
                @endif

                @if($activeSub)
                    <div class="mt-5 grid w-full gap-2" style="max-width:720px;grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-left dark:border-slate-700 dark:bg-slate-800/40">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</p>
                            <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $activeSub->statusLabel() }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-left dark:border-slate-700 dark:bg-slate-800/40">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cobrança</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $activeSub->billingCycleLabel() }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-left dark:border-slate-700 dark:bg-slate-800/40">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Válido até</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $activeSub->current_period_end?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-left dark:border-slate-700 dark:bg-slate-800/40">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Valor pago</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">R$ {{ number_format($activeSub->price_paid, 2, ',', '.') }}</p>
                        </div>
                    </div>
                @endif

                <div class="mt-5 flex w-full flex-wrap justify-center gap-2">
                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25"><i class="bi bi-house"></i>Ir para o início</a>
                    <a href="{{ route('settings.plan') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi bi-patch-check"></i>Ver minha assinatura</a>
                    <a href="{{ route('access.center') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi bi-unlock"></i>O que o plano libera</a>
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
</x-layouts.app>
