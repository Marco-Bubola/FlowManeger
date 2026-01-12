{{-- Timeline de Atividades Recentes --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    {{-- Header --}}
    <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-gray-50 dark:from-slate-700/50 dark:to-gray-700/50 border-b border-slate-200 dark:border-slate-600">
        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-slate-600 to-gray-700 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-history text-white"></i>
            </div>
            <span>Atividades Recentes</span>
            <span class="text-sm font-normal text-slate-600 dark:text-slate-400">
                (Últimas {{ count($atividades) }} ações)
            </span>
        </h3>
    </div>

    {{-- Timeline --}}
    <div class="p-6">
        @if(count($atividades) > 0)
        <div class="relative">
            {{-- Linha vertical da timeline --}}
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500"></div>

            <div class="space-y-4">
                @foreach($atividades as $atividade)
                <a href="{{ $atividade['link'] }}"
                   class="group relative flex items-start gap-4 p-4 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all duration-300 hover:shadow-md">
                    {{-- Ícone --}}
                    <div class="relative z-10 flex-shrink-0 w-12 h-12 bg-white dark:bg-slate-800 rounded-full border-4 border-white dark:border-slate-800 shadow-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                            <i class="{{ $atividade['icon'] }} {{ $atividade['color'] }} text-white text-sm"></i>
                        </div>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="flex-1 min-w-0 pt-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ $atividade['title'] }}
                                </h4>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 line-clamp-2">
                                    {{ $atividade['description'] }}
                                </p>
                            </div>
                            <span class="flex-shrink-0 text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-full">
                                {{ $atividade['time'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Seta indicadora --}}
                    <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <i class="fas fa-arrow-right text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-24 h-24 mx-auto bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-inbox text-4xl text-slate-400 dark:text-slate-500"></i>
            </div>
            <p class="text-slate-600 dark:text-slate-400 font-medium">
                Nenhuma atividade recente
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-500 mt-2">
                Comece criando sua primeira venda, cliente ou lançamento
            </p>
        </div>
        @endif
    </div>
</div>
