<div x-data="{ showFilters: false, showDeleteModal: @entangle('showDeleteModal').live }" class="consortiums-index-page w-full mobile-393-base relative">
    <style>[x-cloak] { display: none !important; }</style>

    {{-- Cabeçalho no mesmo padrão de Clientes --}}
    <div class="relative overflow-hidden mb-6 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(168,85,247,0.12),transparent_32%)]"></div>
        <div class="pointer-events-none absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>

        <div class="relative px-4 sm:px-6 py-4 sm:py-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg shadow-purple-500/25">
                        <i class="bi bi-piggy-bank text-white text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi bi-house mr-1"></i>Dashboard</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="text-indigo-600 dark:text-indigo-300"><i class="bi bi-piggy-bank mr-1"></i>Consórcios</span>
                        </nav>
                        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">Consórcios</h1>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs font-semibold">
                            <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-500/10 border border-indigo-200 dark:border-indigo-700 px-2 py-1 text-indigo-700 dark:text-indigo-300"><i class="bi bi-play-circle"></i>{{ $totalActive }} ativo{{ $totalActive === 1 ? '' : 's' }}</span>
                            @if($totalParticipants > 0)
                                <span class="inline-flex items-center gap-1 rounded-lg bg-purple-500/10 border border-purple-200 dark:border-purple-700 px-2 py-1 text-purple-700 dark:text-purple-300"><i class="bi bi-people"></i>{{ $totalParticipants }} participantes</span>
                            @endif
                            @if($totalCompleted > 0)
                                <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-500/10 border border-indigo-200 dark:border-indigo-700 px-2 py-1 text-indigo-700 dark:text-indigo-300"><i class="bi bi-check-circle"></i>{{ $totalCompleted }} concluído{{ $totalCompleted === 1 ? '' : 's' }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2 xl:max-w-[60%] w-full xl:w-auto">
                    <div class="relative flex-1 sm:min-w-[260px]">
                        <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar consórcio pelo nome"
                            class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-slate-200/80 dark:border-slate-600 bg-white/90 dark:bg-slate-800/90 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 shadow-sm focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400">
                        <button type="button" wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0" x-cloak
                            class="absolute right-2 top-1/2 -translate-y-1/2 h-6 w-6 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-700" title="Limpar busca"><i class="bi bi-x-lg text-xs"></i></button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-600 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm whitespace-nowrap">
                            <i class="bi bi-piggy-bank text-indigo-500"></i>{{ $consortiums->total() }}
                        </span>
                        <button type="button" @click="showFilters = !showFilters" title="Filtros"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/80 dark:border-slate-600 bg-white/90 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 shadow-sm hover:text-indigo-600"
                            :class="{ '!bg-indigo-50 !border-indigo-300 !text-indigo-600 dark:!bg-indigo-500/10': showFilters }"><i class="bi bi-funnel"></i></button>
                        <button type="button" wire:click="$dispatch('openExportModal')" title="Exportar"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/80 dark:border-slate-600 bg-white/90 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 shadow-sm hover:text-indigo-600"><i class="bi bi-download"></i></button>
                        <button type="button" wire:click="toggleTips" title="Como funciona"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/80 dark:border-slate-600 bg-white/90 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 shadow-sm hover:text-amber-500"><i class="bi bi-lightbulb"></i></button>
                        <a href="{{ route('consortiums.create') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-semibold shadow-md shadow-indigo-500/25 whitespace-nowrap">
                            <i class="bi bi-plus-lg"></i>Consórcio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros: mesma janela de Produtos, Vendas e Clientes -->
    @php
        $csActive = $statusFilter !== '' || $dateStart !== '' || $dateEnd !== '';
        $opt = fn ($on) => 'fm-fs-opt' . ($on ? ' is-on' : '');
        $sortIcon = fn ($col) => $sortBy === $col ? ($sortDirection === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : '';
    @endphp
    <x-filter-sheet title="Filtros de consórcios" subtitle="Status, período e ordem" :active="$csActive"
        target="statusFilter,setQuickFilter,sortByField,dateStart,dateEnd,quickFilter,clearFilters">
        <div class="fm-fs-group">
            <h4><i class="bi bi-flag text-indigo-500"></i>Status do consórcio</h4>
            <div class="fm-fs-options">
                <button type="button" wire:click="$set('statusFilter', '')" class="{{ $opt($statusFilter === '') }}"><i class="bi bi-list-ul"></i>Todos</button>
                <button type="button" wire:click="setQuickFilter('active')" class="{{ $opt($statusFilter === 'active') }}"><i class="bi bi-check-circle"></i>Ativo</button>
                <button type="button" wire:click="setQuickFilter('completed')" class="{{ $opt($statusFilter === 'completed') }}"><i class="bi bi-trophy"></i>Concluído</button>
                <button type="button" wire:click="setQuickFilter('cancelled')" class="{{ $opt($statusFilter === 'cancelled') }}"><i class="bi bi-x-circle"></i>Cancelado</button>
            </div>
        </div>
        <div class="fm-fs-group">
            <h4><i class="bi bi-calendar-range text-purple-500"></i>Período</h4>
            <div class="fm-fs-options">
                <button type="button" @click="$wire.dateStart = ''; $wire.dateEnd = ''; $wire.quickFilter = ''; $wire.$commit()" class="{{ $opt($dateStart === '' && $dateEnd === '') }}">Qualquer data</button>
                <button type="button" wire:click="setQuickFilter('today')" class="{{ $opt($quickFilter === 'today') }}">Hoje</button>
                <button type="button" wire:click="setQuickFilter('week')" class="{{ $opt($quickFilter === 'week') }}">Esta semana</button>
                <button type="button" wire:click="setQuickFilter('month')" class="{{ $opt($quickFilter === 'month') }}">Este mês</button>
            </div>
        </div>
        <div class="fm-fs-group fm-fs-wide">
            <h4><i class="bi bi-arrow-down-up text-amber-500"></i>Ordenar por <span class="font-normal text-slate-400">(toque de novo para inverter)</span></h4>
            <div class="fm-fs-options">
                @foreach(['created_at' => 'Data de cadastro', 'name' => 'Nome', 'monthly_value' => 'Valor mensal', 'start_date' => 'Início'] as $col => $lbl)
                    <button type="button" wire:click="sortByField('{{ $col }}')" class="{{ $opt($sortBy === $col) }}">{{ $lbl }}@if($sortIcon($col))<i class="bi {{ $sortIcon($col) }}"></i>@endif</button>
                @endforeach
            </div>
        </div>
    </x-filter-sheet>

    <!-- Grid de Cards (Responsivo com Ultra Wide) -->
    <div class="consortiums-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5 mb-8">
            @forelse($consortiums as $consortium)
                <x-consortium-card :consortium="$consortium" />
            @empty
                <div class="col-span-full">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl p-12 text-center border border-gray-200 dark:border-zinc-700">
                        <div class="w-20 h-20 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="bi bi-piggy-bank text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            Nenhum consórcio encontrado
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            @if ($search ?? false)
                                Não encontramos consórcios com o termo "{{ $search }}".
                            @else
                                Comece criando seu primeiro consórcio.
                            @endif
                        </p>
                        <a href="{{ route('consortiums.create') }}"
                            class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="bi bi-plus-circle mr-2"></i>
                            Criar Novo Consórcio
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

    <x-pagination-bar :paginator="$consortiums" :per-page-options="$perPageOptions" label="consórcios" fit-grid=".consortiums-grid" />

    <!-- Modal de Dicas (Wizard Moderno) -->
    @if($showTipsModal)
        <div x-data="{
            currentStep: 1,
            totalSteps: 4,
            nextStep() {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                }
            },
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            }
        }" x-show="$wire.showTipsModal" x-cloak
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
            style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);">

            <!-- Modal Content -->
            <div @click.away="if(currentStep === totalSteps) $wire.toggleTips()"
                class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-slate-200/50 dark:border-slate-700/50"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <!-- Header with Progress Bar -->
                <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-700 px-8 py-6 text-white">
                    <button @click="$wire.toggleTips()" class="absolute top-4 right-4 p-2 hover:bg-white/20 rounded-lg transition-all duration-200">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>

                    <div class="pr-12">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="bi bi-lightbulb-fill text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-3xl font-bold">Dicas de Consórcios</h2>
                                <p class="text-indigo-100 text-sm mt-1">Aprenda a gerenciar seus consórcios com eficiência</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="flex gap-2 mt-6">
                            <template x-for="step in totalSteps" :key="step">
                                <div class="flex-1 h-2 rounded-full overflow-hidden bg-white/20">
                                    <div class="h-full bg-white rounded-full transition-all duration-500"
                                         :style="currentStep >= step ? 'width: 100%' : 'width: 0%'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="relative overflow-y-auto max-h-[calc(90vh-280px)] p-8">
                    <!-- Step 1: Visão Geral -->
                    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-piggy-bank text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Visão Geral de Consórcios</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Conheça as principais funcionalidades do módulo de consórcios</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-6 bg-gradient-to-br from-indigo-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-indigo-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-indigo-500 rounded-xl">
                                        <i class="bi bi-check-circle text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Gerenciamento</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Acompanhe participantes e contemplações</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-blue-500 rounded-xl">
                                        <i class="bi bi-calendar-check text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Sorteios</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Realize sorteios conforme frequência definida</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-purple-500 rounded-xl">
                                        <i class="bi bi-wallet2 text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Pagamentos</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Controle financeiro preciso e detalhado</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-amber-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-amber-500 rounded-xl">
                                        <i class="bi bi-file-pdf text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Exportação</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Gere relatórios em PDF dos consórcios</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Status -->
                    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 to-cyan-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-flag text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Status de Consórcios</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Entenda os diferentes status e suas cores</p>
                        </div>

                        <div class="space-y-4">
                            <div class="p-6 bg-gradient-to-r from-green-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-green-300 dark:border-green-600">
                                <div class="flex items-center gap-4 mb-3">
                                    <span class="flex items-center justify-center w-12 h-12 bg-green-500 rounded-xl">
                                        <i class="bi bi-check-circle-fill text-2xl text-white"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white">Ativo</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Consórcio em andamento</p>
                                    </div>
                                    <span class="ml-auto px-4 py-2 bg-green-500 text-white rounded-lg font-bold">Ativo</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 text-sm">O consórcio está ativo com participantes e sorteios acontecendo regularmente.</p>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-blue-300 dark:border-blue-600">
                                <div class="flex items-center gap-4 mb-3">
                                    <span class="flex items-center justify-center w-12 h-12 bg-blue-500 rounded-xl">
                                        <i class="bi bi-trophy-fill text-2xl text-white"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white">Concluído</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Todos contemplados</p>
                                    </div>
                                    <span class="ml-auto px-4 py-2 bg-blue-500 text-white rounded-lg font-bold">Concluído</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 text-sm">O consórcio foi finalizado com todos os participantes contemplados.</p>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-red-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-red-300 dark:border-red-600">
                                <div class="flex items-center gap-4 mb-3">
                                    <span class="flex items-center justify-center w-12 h-12 bg-red-500 rounded-xl">
                                        <i class="bi bi-x-circle-fill text-2xl text-white"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white">Cancelado</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Consórcio encerrado</p>
                                    </div>
                                    <span class="ml-auto px-4 py-2 bg-red-500 text-white rounded-lg font-bold">Cancelado</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 text-sm">O consórcio foi cancelado antes de concluir.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Participantes -->
                    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-people text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Participantes e Sorteios</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Gerencie participantes e realize sorteios</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                    <i class="bi bi-person-plus text-purple-500"></i>
                                    Adicionar Participantes
                                </h4>
                                <p class="text-slate-700 dark:text-slate-300 text-sm mb-3">Cadastre novos participantes com as informações:</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle text-green-500"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Nome completo</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle text-green-500"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">CPF</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle text-green-500"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Contato</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle text-green-500"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Valor da cota</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                    <i class="bi bi-shuffle text-blue-500"></i>
                                    Realizar Sorteios
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold">1</div>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Defina a frequência dos sorteios</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold">2</div>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Sistema sorteia participante automaticamente</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold">3</div>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Notifique contemplado e registre</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Ações -->
                    <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-lightning-charge text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Ações Disponíveis</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">O que você pode fazer com os consórcios</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="p-6 bg-gradient-to-br from-green-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-green-500 rounded-xl">
                                        <i class="bi bi-plus-circle text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Novo Consórcio</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Crie um novo consórcio e adicione participantes</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-blue-500 rounded-xl">
                                        <i class="bi bi-eye text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Visualizar Detalhes</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Veja participantes e histórico completo</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-purple-500 rounded-xl">
                                        <i class="bi bi-pencil text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Editar Consórcio</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Altere informações e gerencie participantes</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-orange-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-orange-500 rounded-xl">
                                        <i class="bi bi-file-pdf text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Exportar PDF</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Gere relatórios detalhados</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-indigo-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="bi bi-lightbulb text-2xl text-indigo-500"></i>
                                <h4 class="text-lg font-bold text-slate-800 dark:text-white">Dica Final</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 text-sm">
                                Mantenha seus consórcios sempre atualizados! Registre pagamentos, realize sorteios regularmente e
                                comunique-se com os participantes para manter tudo organizado e transparente.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div class="bg-slate-50 dark:bg-slate-900/50 px-8 py-6 border-t border-slate-200/50 dark:border-slate-700/50 flex items-center justify-between">
                    <button @click="prevStep()" x-show="currentStep > 1"
                        class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold hover:bg-slate-100 dark:hover:bg-slate-600 transition-all duration-200 shadow-md hover:shadow-lg border border-slate-200 dark:border-slate-600">
                        <i class="bi bi-arrow-left"></i>
                        <span>Anterior</span>
                    </button>

                    <div class="flex gap-2">
                        <template x-for="step in totalSteps" :key="step">
                            <button @click="currentStep = step"
                                class="w-3 h-3 rounded-full transition-all duration-300"
                                :class="currentStep === step ? 'bg-indigo-600 w-8' : 'bg-slate-300 dark:bg-slate-600 hover:bg-indigo-400'">
                            </button>
                        </template>
                    </div>

                    <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()"
                        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg"
                        x-text="currentStep < totalSteps ? 'Próximo' : 'Concluir'">
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Exclusão -->
    <x-delete-dialog show="showDeleteModal" confirm="deleteConsortium" cancel="cancelDelete" title="Excluir consórcio?" confirm-label="Excluir consórcio" icon="bi-people">
        @if($deletingConsortium) O consórcio <strong>{{ $deletingConsortium->name }}</strong> e as parcelas dele serão apagados. @else O consórcio e as parcelas dele serão apagados. @endif Não dá para desfazer.
    </x-delete-dialog>

    <!-- Componente de Exportar (Livewire) -->
    @livewire('consortiums.export-consortium')
</div>
