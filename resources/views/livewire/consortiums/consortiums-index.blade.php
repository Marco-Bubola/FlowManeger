<div x-data="{ showFilters: false, showDeleteModal: @entangle('showDeleteModal').live }" class="consortiums-index-page w-full mobile-393-base relative">
    <style>[x-cloak] { display: none !important; }</style>

    {{-- Cabeçalho e busca no padrão das outras listas (Clientes, Vendas, Categorias) --}}
    <x-page-header title="Consórcios" icon="bi-piggy-bank" subtitle="Grupos, participantes, parcelas e sorteios">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-piggy-bank"></i>{{ $consortiums->total() }} {{ $consortiums->total() === 1 ? 'consórcio' : 'consórcios' }}</span>
            @if($totalActive > 0)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-play-circle"></i>{{ $totalActive }} ativo{{ $totalActive === 1 ? '' : 's' }}</span>
            @endif
            @if($totalParticipants > 0)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300"><i class="bi bi-people"></i>{{ $totalParticipants }} participante{{ $totalParticipants === 1 ? '' : 's' }}</span>
            @endif
            @if($totalCompleted > 0)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300"><i class="bi bi-check-circle"></i>{{ $totalCompleted }} concluído{{ $totalCompleted === 1 ? '' : 's' }}</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            <button type="button" @click="showFilters = !showFilters" title="Filtros" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-sliders text-indigo-500"></i><span class="hidden sm:inline">Filtros</span></button>
            <button type="button" wire:click="$dispatch('openExportModal')" title="Exportar" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-download text-emerald-500"></i><span class="hidden sm:inline">Exportar</span></button>
            <button type="button" wire:click="toggleTips" title="Como funciona" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/70 dark:border-slate-700/70 hover:text-amber-500"><i class="bi bi-lightbulb"></i></button>
            <a href="{{ route('consortiums.create') }}" title="Novo consórcio"
               class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition whitespace-nowrap"><i class="bi bi-plus-lg"></i>Novo consórcio</a>
        </x-slot:actions>
    </x-page-header>

    <x-list-toolbar placeholder="Buscar consórcio pelo nome...">
        <x-toolbar.group>
            <x-toolbar.chip wire:click="$set('statusFilter', '')" :active="$statusFilter === ''" icon="bi-grid">Todos</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('statusFilter', 'active')" :active="$statusFilter === 'active'" icon="bi-play-circle">Ativos</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('statusFilter', 'completed')" :active="$statusFilter === 'completed'" icon="bi-trophy">Concluídos</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('statusFilter', 'cancelled')" :active="$statusFilter === 'cancelled'" icon="bi-x-circle">Cancelados</x-toolbar.chip>
        </x-toolbar.group>
        <x-toolbar.group icon="bi-arrow-down-up">
            <x-toolbar.chip wire:click="sortByField('created_at')" :active="$sortBy === 'created_at'">Recentes</x-toolbar.chip>
            <x-toolbar.chip wire:click="sortByField('name')" :active="$sortBy === 'name'">A-Z</x-toolbar.chip>
            <x-toolbar.chip wire:click="sortByField('monthly_value')" :active="$sortBy === 'monthly_value'">Valor</x-toolbar.chip>
        </x-toolbar.group>
    </x-list-toolbar>

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
                @php $csFiltered = ($search ?? '') !== '' || $statusFilter !== ''; @endphp
                <div class="col-span-full">
                    <x-empty-state icon="bi-piggy-bank"
                        :title="$csFiltered ? 'Nenhum consórcio com esses filtros' : 'Nenhum consórcio ainda'"
                        :text="($search ?? '') !== '' ? 'Não encontramos consórcios com o termo “' . $search . '”.' : ($csFiltered ? 'Troque o status ou limpe os filtros para ver todos.' : 'Crie o primeiro grupo e depois adicione os participantes.')">
                        @if($csFiltered)
                            <button type="button" wire:click="clearFilters" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi bi-x-circle"></i>Limpar filtros</button>
                        @endif
                        <a href="{{ route('consortiums.create') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25"><i class="bi bi-plus-lg"></i>Novo consórcio</a>
                    </x-empty-state>
                </div>
            @endforelse
        </div>

    <x-infinite-feed :paginator="$consortiums" label="consórcios" />

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
