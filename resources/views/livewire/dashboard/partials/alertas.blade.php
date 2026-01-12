{{-- Seção de Alertas e Notificações --}}
@if(count($alertas) > 0)
<div class="mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border-b border-red-100 dark:border-red-800/30">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-bell text-white"></i>
                    </div>
                    <span>Alertas e Notificações</span>
                    <span class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full">
                        {{ count($alertas) }}
                    </span>
                </h3>
            </div>
        </div>

        {{-- Lista de Alertas --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($alertas as $alerta)
                <a href="{{ $alerta['link'] }}"
                   class="group block p-4 rounded-xl border-2 transition-all duration-300 hover:shadow-lg hover:scale-105
                          @if($alerta['type'] === 'danger')
                              bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 hover:border-red-400
                          @elseif($alerta['type'] === 'warning')
                              bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 hover:border-yellow-400
                          @else
                              bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 hover:border-blue-400
                          @endif">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center
                                    @if($alerta['type'] === 'danger')
                                        bg-gradient-to-br from-red-500 to-red-600
                                    @elseif($alerta['type'] === 'warning')
                                        bg-gradient-to-br from-yellow-500 to-orange-600
                                    @else
                                        bg-gradient-to-br from-blue-500 to-indigo-600
                                    @endif">
                            <i class="{{ $alerta['icon'] }} text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $alerta['message'] }}
                            </p>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                Clique para visualizar →
                            </p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
