<div class="cofrinhos-index-page w-full mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cofrinhos-index-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cofrinhos-index-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cofrinhos-index-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cofrinhos-index-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cofrinhos-index-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/cofrinhos-index-ultrawide.css') }}">
    @php
        $cofList = collect($cofrinhos);
        $cofGuardado = $cofList->sum('valor_acumulado');
        $cofMetas = $cofList->where('status', 'ativo')->sum('meta_valor');
        $cofAtivos = $cofList->where('status', 'ativo')->count();
        $cofConcluidos = $cofList->filter(fn ($c) => $c['meta_valor'] > 0 && $c['valor_acumulado'] >= $c['meta_valor'])->count();
    @endphp

    <div class="w-full">
        <x-cashbook-page-header title="Cofrinhos" subtitle="Separe dinheiro para cada objetivo e acompanhe quanto falta" icon="bi-piggy-bank" active="cofrinhos">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="bi bi-piggy-bank"></i>{{ $cofList->count() }} {{ $cofList->count() === 1 ? 'cofrinho' : 'cofrinhos' }}</span>
            </x-slot:meta>
            <x-slot:actions>
                <a href="{{ route('cofrinhos.create') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition">
                    <i class="bi bi-plus-lg"></i>Novo cofrinho
                </a>
            </x-slot:actions>
        </x-cashbook-page-header>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <x-gestao-stat label="Guardado" :value="'R$ ' . number_format($cofGuardado, 2, ',', '.')" icon="bi-piggy-bank" tone="emerald" />
            <x-gestao-stat label="Metas ativas" :value="'R$ ' . number_format($cofMetas, 2, ',', '.')" icon="bi-flag" tone="indigo" />
            <x-gestao-stat label="Ativos" :value="$cofAtivos" icon="bi-lightning-charge" tone="sky" />
            <x-gestao-stat label="Metas alcançadas" :value="$cofConcluidos" icon="bi-trophy" tone="amber" />
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="w-full py-4">

        <!-- Botão Criar movido para o header -->

        <!-- Ranking dos Cofrinhos -->
        @if(collect($ranking)->sum('crescimento') > 0)
        <div class="mb-6">
            <div class="bg-white dark:bg-slate-900/80 rounded-2xl shadow-sm p-5 border border-slate-200/80 dark:border-slate-700/70">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-trophy-fill text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Ranking do Mês</h2>
                        <p class="text-gray-600 dark:text-gray-400">Cofrinhos que mais cresceram este mês</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($ranking as $index => $item)
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-slate-800 dark:to-indigo-900/20 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-4
                                        @if($index == 0) bg-gradient-to-r from-yellow-400 to-orange-500 text-white
                                        @elseif($index == 1) bg-gradient-to-r from-gray-400 to-gray-600 text-white
                                        @else bg-gradient-to-r from-orange-600 to-red-600 text-white @endif">
                                <span class="font-bold text-sm">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $item['nome'] }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Meta: R$ {{ number_format($item['meta'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-green-600 font-bold text-lg">+R$ {{ number_format($item['crescimento'], 2, ',', '.') }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total: R$ {{ number_format($item['valor_acumulado'], 2, ',', '.') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Lista de Cofrinhos -->
        @if(count($cofrinhos) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
            @foreach($cofrinhos as $cofrinho)
            @php
                $pct = (float) $cofrinho['porcentagem'];
                $done = $cofrinho['meta_valor'] > 0 && $cofrinho['valor_acumulado'] >= $cofrinho['meta_valor'];
                $archived = $cofrinho['status'] !== 'ativo';
                $falta = max(0, $cofrinho['meta_valor'] - $cofrinho['valor_acumulado']);
                $icon = $cofrinho['icone'] ?? 'fa-piggy-bank';
            @endphp
            <div class="group relative rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm hover:shadow-lg transition {{ $archived ? 'opacity-75' : '' }}">
                <div class="h-1.5 rounded-t-2xl {{ $done ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : ($archived ? 'bg-slate-300 dark:bg-slate-600' : 'bg-gradient-to-r from-indigo-500 to-purple-500') }}"></div>
                <div class="p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-white shadow-md bg-gradient-to-br {{ $done ? 'from-emerald-500 to-teal-600' : 'from-indigo-500 to-purple-600' }}">
                            <i class="fas {{ $icon }} text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('cofrinhos.show', $cofrinho['id']) }}" wire:navigate class="block truncate text-base font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-300">{{ $cofrinho['nome'] }}</a>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                                @if($done)
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-trophy-fill mr-0.5"></i>Meta alcançada</span>
                                @elseif($archived)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-archive mr-0.5"></i>Arquivado</span>
                                @else
                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-hourglass-split mr-0.5"></i>Em andamento</span>
                                @endif
                                <span class="text-slate-500 dark:text-slate-400">{{ $cofrinho['cashbooks_count'] }} {{ $cofrinho['cashbooks_count'] == 1 ? 'movimentação' : 'movimentações' }}</span>
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <a href="{{ route('cofrinhos.edit', $cofrinho['id']) }}" wire:navigate title="Editar"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 transition"><i class="bi bi-pencil"></i></a>
                            <button type="button" wire:click="confirmDelete({{ $cofrinho['id'] }})" title="Excluir"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-500/10 transition"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>

                    <div class="mt-4 flex items-end justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Guardado</p>
                            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($cofrinho['valor_acumulado'], 2, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Meta</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">R$ {{ number_format($cofrinho['meta_valor'], 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full {{ $done ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500' }}" style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-xs">
                        <span class="font-semibold text-indigo-600 dark:text-indigo-300">{{ number_format($pct, 1, ',', '.') }}%</span>
                        <span class="text-slate-500 dark:text-slate-400">{{ $done ? 'Meta concluída' : 'Faltam R$ ' . number_format($falta, 2, ',', '.') }}</span>
                    </div>

                    <a href="{{ route('cofrinhos.show', $cofrinho['id']) }}" wire:navigate
                       class="mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <i class="bi bi-eye"></i>Ver movimentações
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Estado Vazio -->
        <div class="text-center py-16">
            <div class="w-24 h-24 bg-gradient-to-r from-purple-100 to-blue-100 dark:from-purple-900/20 dark:to-blue-900/20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="bi bi-piggy-bank text-purple-500 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Nenhum cofrinho criado ainda</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                Crie seu primeiro cofrinho para começar a economizar e acompanhar suas metas financeiras.
            </p>
                <a href="{{ route('cofrinhos.create') }}" wire:navigate
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="bi bi-plus-circle-fill text-xl mr-2"></i>
                Criar Primeiro Cofrinho
            </a>
        </div>
        @endif
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Confirmar Exclusão</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-2">Tem certeza que deseja excluir o cofrinho?</p>
                @if($deletingCofrinho)
                <p class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ $deletingCofrinho->nome }}</p>
                @endif
                <div class="flex gap-3 justify-center">
                    <button wire:click="cancelDelete"
                            class="px-5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button wire:click="deleteCofrinho"
                            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold shadow-sm transition">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <i class="bi bi-check-circle-fill mr-2"></i>{{ session('success') }}
    </div>
    @endif
</div>
