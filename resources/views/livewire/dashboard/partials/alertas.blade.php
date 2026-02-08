{{-- Seção de Notificações (sidebar) --}}
<div class="mb-0">
    @if(count($alertas) > 0)
        <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur rounded-2xl shadow-xl border border-slate-200/70 dark:border-slate-800 overflow-hidden">
            {{-- Header --}}
            <div class="px-5 py-4 bg-gradient-to-r from-indigo-50 to-slate-50 dark:from-indigo-900/20 dark:to-slate-900/20 border-b border-slate-200/70 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-3">
                        <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-bell text-white"></i>
                        </div>
                        <span>Notificações</span>
                        <span class="px-2.5 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full">
                            {{ count($alertas) }}
                        </span>
                    </h3>
                </div>
            </div>

            {{-- Lista de Notificações (compacta) --}}
            <div class="p-4">
                <div class="space-y-3">
                    @foreach($alertas as $alerta)
                        <a href="{{ $alerta['link'] }}"
                           class="group flex items-start gap-3 p-3 rounded-xl border transition-all duration-200 hover:shadow-md
                              @if($alerta['type'] === 'danger')
                                  bg-red-50/70 dark:bg-red-900/15 border-red-200/70 dark:border-red-800/40
                              @elseif($alerta['type'] === 'warning')
                                  bg-yellow-50/70 dark:bg-yellow-900/15 border-yellow-200/70 dark:border-yellow-800/40
                              @else
                                  bg-blue-50/70 dark:bg-blue-900/15 border-blue-200/70 dark:border-blue-800/40
                              @endif">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                                @if($alerta['type'] === 'danger')
                                    bg-gradient-to-br from-red-500 to-red-600
                                @elseif($alerta['type'] === 'warning')
                                    bg-gradient-to-br from-yellow-500 to-orange-600
                                @else
                                    bg-gradient-to-br from-blue-500 to-indigo-600
                                @endif">
                                <i class="{{ $alerta['icon'] }} text-white text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 leading-snug">
                                    {{ $alerta['message'] }}
                                </p>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                    Toque para abrir
                                </p>
                            </div>
                            <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-arrow-right text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
