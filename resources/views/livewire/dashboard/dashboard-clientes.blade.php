<div class="min-h-screen w-full bg-slate-50 dark:bg-slate-950">

    <div class="sticky top-0 z-40">
        @include('livewire.dashboard.partials.header-new')
    </div>

    <div class="px-4 sm:px-6 lg:px-8 py-6">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Dashboard de Clientes</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">Visão rápida do seu CRM (clientes, novos, pendências).</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center shadow">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Total de clientes</div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $totalClientes }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow">
                        <i class="fas fa-user-plus text-white"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Novos no mês</div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $clientesNovosMes }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow">
                        <i class="fas fa-user-clock text-white"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Clientes com pendências</div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $clientesInadimplentes }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center shadow">
                        <i class="fas fa-birthday-cake text-white"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Aniversariantes do mês</div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $clientesAniversariantes }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Se ficar 0, é porque não existe campo de nascimento cadastrado.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Ações rápidas</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Atalhos para operações comuns</div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                        <i class="fas fa-list"></i>
                        Lista
                    </a>
                    <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                        <i class="fas fa-plus"></i>
                        Novo
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
