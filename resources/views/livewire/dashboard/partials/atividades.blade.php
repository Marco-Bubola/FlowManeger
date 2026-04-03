@php
    $moduleSummary = collect($atividades ?? [])->groupBy('module')->map->count()->sortDesc()->take(4);
@endphp

<div class="rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75 overflow-hidden">
    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800 sm:px-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 text-white shadow-lg dark:from-slate-500 dark:to-slate-700">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Histórico operacional</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Últimas {{ count($atividades ?? []) }} ações relevantes registradas no app</p>
                </div>
            </div>

            @if ($moduleSummary->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($moduleSummary as $module => $count)
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            {{ $module }} · {{ $count }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="p-5 sm:p-6">
        @if (count($atividades ?? []) > 0)
            <div class="relative">
                <div class="absolute left-5 top-0 bottom-0 w-px bg-gradient-to-b from-indigo-500 via-fuchsia-500 to-cyan-500"></div>

                <div class="max-h-[860px] space-y-4 overflow-auto pr-1 sm:pr-2">
                    @foreach ($atividades as $atividade)
                        <a href="{{ $atividade['link'] }}"
                            class="group relative flex gap-4 rounded-[24px] border border-slate-200/80 bg-white/70 p-4 transition duration-300 hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900/60 dark:hover:border-indigo-500/40">
                            <div class="relative z-10 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-fuchsia-600 text-white shadow-lg sm:h-12 sm:w-12">
                                <i class="{{ $atividade['icon'] }} text-sm sm:text-base"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-sm font-bold text-slate-900 transition group-hover:text-indigo-700 dark:text-white dark:group-hover:text-indigo-300 sm:text-base">
                                                {{ $atividade['title'] }}
                                            </h4>
                                            @if (!empty($atividade['module']))
                                                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200">
                                                    {{ $atividade['module'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                            {{ $atividade['description'] }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300 sm:text-xs">
                                            {{ $atividade['time'] }}
                                        </span>
                                        <i class="fas fa-arrow-right text-xs text-slate-400 transition group-hover:translate-x-1 group-hover:text-indigo-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="rounded-[24px] border border-dashed border-slate-300/80 bg-slate-50/70 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-900/50">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white shadow-sm dark:bg-slate-800">
                    <i class="fas fa-inbox text-3xl text-slate-400 dark:text-slate-500"></i>
                </div>
                <p class="mt-5 text-base font-bold text-slate-700 dark:text-slate-200">Nenhuma atividade recente</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Assim que houver movimentações em vendas, caixa, clientes ou produtos, elas aparecerão aqui.</p>
            </div>
        @endif
    </div>
</div>