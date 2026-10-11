<div x-data="{ showFilters: false, showDeleteModal: @entangle('showDeleteModal').live }" class="w-full clients-index-page mobile-393-base relative">
    {{-- Escala proporcional das telas de cliente --}}
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/clients-compact.css') }}?v=20260806">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-index-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-index-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-index-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-index-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-index-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-index-ultrawide.css') }}">
    <style>
        [x-cloak] {
            display: none !important;
        }
        /* Card novo: os ajustes antigos de .rounded-full/img (clients-compact.css) não valem aqui */
        .clients-index-page .grid > .client-card-v2 .rounded-full,
        .clients-index-page .grid > .client-card-v2 img {
            max-width: none;
            max-height: none;
        }
        /* No celular o card novo ocupa a largura toda (as regras antigas forçavam 2 colunas) */
        @media (max-width: 640px) {
            .clients-index-page .clients-grid-wrap .clients-grid.clients-grid:has(> .client-card-v2) {
                grid-template-columns: minmax(0, 1fr) !important;
                gap: 0.85rem !important;
            }
        }
    </style>

    <x-loading-overlay message="Carregando clientes..." />

    <div class="">

        <x-page-header title="Clientes" icon="bi-people" section="clientes" tabs-section="clientes" tabs-active="lista">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-people"></i>{{ $clients->total() }} {{ $clients->total() === 1 ? 'cliente' : 'clientes' }}</span>
                @if($this->activeClients > 0)
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-person-check"></i>{{ $this->activeClients }} ativos</span>
                @endif
                @if($this->premiumClients > 0)
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-star"></i>{{ $this->premiumClients }} premium</span>
                @endif
            </x-slot:meta>
            <x-slot:actions>
                <button type="button" @click="showFilters = !showFilters" title="Filtros avançados" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-sliders text-indigo-500"></i>Filtros</button>
                <a href="{{ route('clients.create') }}" title="Novo cliente"
                   class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition"><i class="bi bi-person-plus"></i>Novo cliente</a>
            </x-slot:actions>
        </x-page-header>

        <x-list-toolbar placeholder="Buscar clientes por nome, email, telefone ou cidade...">
            <x-toolbar.group>
                <x-toolbar.chip wire:click="$set('statusFilter', '')" :active="$statusFilter === ''" icon="bi-grid">Todos</x-toolbar.chip>
                <x-toolbar.chip wire:click="$set('statusFilter', 'ativo')" :active="$statusFilter === 'ativo'" icon="bi-person-check">Ativos</x-toolbar.chip>
                <x-toolbar.chip wire:click="$set('statusFilter', 'inativo')" :active="$statusFilter === 'inativo'" icon="bi-person-dash">Inativos</x-toolbar.chip>
                <x-toolbar.chip wire:click="$set('statusFilter', 'premium')" :active="$statusFilter === 'premium'" icon="bi-star">Premium</x-toolbar.chip>
            </x-toolbar.group>
            <x-toolbar.group icon="bi-arrow-down-up">
                <x-toolbar.chip wire:click="toggleSort('created_at')" :active="$sortBy === 'created_at'">Recentes</x-toolbar.chip>
                <x-toolbar.chip wire:click="toggleSort('name')" :active="$sortBy === 'name'">A-Z</x-toolbar.chip>
                <x-toolbar.chip wire:click="toggleSort('most_sales')" :active="$sortBy === 'most_sales'">Mais compras</x-toolbar.chip>
            </x-toolbar.group>
            <x-toolbar.group title="Clientes por página">
                @foreach([12, 24, 48, 96] as $pp)
                    <x-toolbar.chip wire:click="$set('perPage', {{ $pp }})" :active="$perPage == $pp">{{ $pp }}</x-toolbar.chip>
                @endforeach
            </x-toolbar.group>
            <x-toolbar.pager :paginator="$clients" />
        </x-list-toolbar>


        <!-- Filtros Avançados (usando showFilters do Alpine.js) -->
        <div x-show="showFilters" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-4"
            class="clients-filters-panel bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50 mb-6">

            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="bi bi-sliders text-purple-600 dark:text-purple-400 mr-2"></i>
                    Filtros Avançados
                </div>
                <button wire:click="clearFilters"
                    class="flex items-center gap-2 px-3 py-1 text-xs font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition-all duration-200">
                    <i class="bi bi-arrow-clockwise"></i>
                    Limpar
                </button>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Coluna Esquerda: Status e Período -->


                <!-- Status do Cliente -->
                <div class="space-y-2">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                        <i class="bi bi-person-check mr-2 text-purple-500"></i>
                        Status do Cliente
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Todos os Status -->
                        <button wire:click="$set('statusFilter', '')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $statusFilter === '' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-list-ul text-purple-500 text-sm"></i>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Todos</span>
                            </div>
                        </button>

                        <!-- Ativo -->
                        <button wire:click="$set('statusFilter', 'ativo')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $statusFilter === 'ativo' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-person-check text-green-500 text-sm"></i>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Ativo</span>
                            </div>
                        </button>

                        <!-- Inativo -->
                        <button wire:click="$set('statusFilter', 'inativo')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500 transition-all duration-200 {{ $statusFilter === 'inativo' ? 'ring-2 ring-red-500 bg-red-50 dark:bg-red-900/30' : '' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-person-x text-red-500 text-sm"></i>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Inativo</span>
                            </div>
                        </button>

                        <!-- Premium -->
                        <button wire:click="$set('statusFilter', 'premium')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-yellow-300 dark:hover:border-yellow-500 transition-all duration-200 {{ $statusFilter === 'premium' ? 'ring-2 ring-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : '' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-star text-yellow-500 text-sm"></i>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Premium</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Período -->
                <div class="space-y-2">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                        <i class="bi bi-calendar-range mr-2 text-indigo-500"></i>
                        Período de Cadastro
                    </h4>
                    <div class="grid grid-cols-3 gap-1">
                        <!-- Qualquer período -->
                        <button wire:click="$set('dateFilter', '')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-200 {{ $dateFilter === '' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-list-ul text-indigo-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Todos</div>
                            </div>
                        </button>

                        <!-- Hoje -->
                        <button wire:click="$set('dateFilter', 'hoje')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $dateFilter === 'hoje' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-calendar-day text-blue-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Hoje</div>
                            </div>
                        </button>

                        <!-- Esta semana -->
                        <button wire:click="$set('dateFilter', 'semana')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $dateFilter === 'semana' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-calendar-week text-green-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Semana</div>
                            </div>
                        </button>

                        <!-- Este mês -->
                        <button wire:click="$set('dateFilter', 'mes')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $dateFilter === 'mes' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-calendar-month text-purple-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Mês</div>
                            </div>
                        </button>

                        <!-- Este ano -->
                        <button wire:click="$set('dateFilter', 'ano')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 {{ $dateFilter === 'ano' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-calendar text-orange-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Ano</div>
                            </div>
                        </button>
                    </div>
                </div>
                <!-- Ordenação -->
                <div class="space-y-2">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                        <i class="bi bi-arrow-up-down mr-2 text-emerald-500"></i>
                        Ordenar por
                    </h4>
                    <div class="grid grid-cols-3 gap-1">
                        <!-- Por Nome -->
                        <button wire:click="toggleSort('name')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-emerald-300 dark:hover:border-emerald-500 transition-all duration-200 {{ $sortBy === 'name' ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-person text-emerald-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Nome</div>
                            </div>
                        </button>

                        <!-- Por Data -->
                        <button wire:click="toggleSort('created_at')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $sortBy === 'created_at' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-calendar text-blue-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Data</div>
                            </div>
                        </button>

                        <!-- Por Email -->
                        <button wire:click="toggleSort('email')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $sortBy === 'email' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-envelope text-purple-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Email</div>
                            </div>
                        </button>

                        <!-- Por Status -->
                        <button wire:click="toggleSort('status')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 {{ $sortBy === 'status' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-flag text-orange-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Status</div>
                            </div>
                        </button>

                        <!-- Por Telefone -->
                        <button wire:click="toggleSort('phone')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-500 transition-all duration-200 {{ $sortBy === 'phone' ? 'ring-2 ring-teal-500 bg-teal-50 dark:bg-teal-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-telephone text-teal-500 text-sm"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Telefone</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Itens por página -->
                <div class="space-y-2">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                        <i class="bi bi-grid mr-2 text-pink-500"></i>
                        Itens por Página
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <!-- 12 por página -->
                        <button wire:click="$set('perPage', '12')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage === '12' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-grid-3x3 text-pink-500 text-sm"></i>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">12</span>
                            </div>
                        </button>

                        <!-- 24 por página -->
                        <button wire:click="$set('perPage', '24')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage === '24' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-grid text-pink-500 text-sm"></i>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">24</span>
                            </div>
                        </button>

                        <!-- 48 por página -->
                        <button wire:click="$set('perPage', '48')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage === '48' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-grid-1x2 text-pink-500 text-sm"></i>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">48</span>
                            </div>
                        </button>

                        <!-- 96 por página -->
                        <button wire:click="$set('perPage', '96')"
                            class="group p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $perPage === '96' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-list-ul text-pink-500 text-sm"></i>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">96</span>
                            </div>
                        </button>
                    </div>
                </div>

            </div>

        </div> <!-- Botões de Ação dos Filtros -->


        <!-- Grid de clientes -->
        <div class="clients-grid-wrap p-3">
            @if($clients->isEmpty())
            @php $temClientes = \App\Models\Client::query()->exists(); @endphp
            <x-empty-state icon="bi-people" :title="$temClientes ? 'Nenhum cliente com esses filtros' : 'Nenhum cliente ainda'"
                :text="$temClientes ? 'Tente outro nome, e-mail ou telefone.' : 'Cadastre o primeiro cliente para acompanhar compras e pagamentos.'">
                <a href="{{ route('clients.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 shadow-md"><i class="bi bi-person-plus"></i> Novo cliente</a>
            </x-empty-state>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5 clients-grid">
                @foreach ($clients as $client)
                @php
                    $salesCount = $client->sales->count();
                    $salesTotal = (float) $client->sales->sum('total_price');
                    $openAmount = (float) $client->sales->sum(fn ($s) => max(0, (float) $s->total_price - (float) $s->amount_paid));
                    $lastSale = $client->sales->sortByDesc('created_at')->first();
                    $consortiumCount = $client->consortiumParticipants ? $client->consortiumParticipants->count() : 0;
                    $tier = $salesCount >= 10 ? 'vip' : ($salesCount >= 5 ? 'premium' : ($salesCount === 0 ? 'novo' : 'padrao'));
                    $tierStyle = [
                        'vip' => ['label' => 'VIP', 'icon' => 'bi-trophy-fill', 'band' => 'from-amber-400 via-orange-500 to-rose-500', 'pill' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300'],
                        'premium' => ['label' => 'Premium', 'icon' => 'bi-star-fill', 'band' => 'from-indigo-500 via-purple-500 to-fuchsia-500', 'pill' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300'],
                        'padrao' => ['label' => 'Cliente', 'icon' => 'bi-person-fill', 'band' => 'from-sky-400 via-indigo-500 to-purple-500', 'pill' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300'],
                        'novo' => ['label' => 'Sem compras', 'icon' => 'bi-person-plus-fill', 'band' => 'from-slate-300 via-slate-400 to-slate-500', 'pill' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300'],
                    ][$tier];
                    $place = collect([$client->city ?? null, $client->state ?? null])->filter()->implode(' / ');
                    $whats = $client->phone ? preg_replace('/[^0-9]/', '', $client->phone) : null;
                @endphp
                <div wire:key="client-card-{{ $client->id }}"
                    class="client-card-v2 group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-0.5 hover:border-indigo-300 dark:hover:border-indigo-500/60 transition-all duration-300 {{ in_array($client->id, $selectedClients ?? []) ? 'ring-2 ring-indigo-500' : '' }}">

                    {{-- Faixa de cor pelo nível do cliente --}}
                    <div class="h-1.5 w-full bg-gradient-to-r {{ $tierStyle['band'] }}"></div>

                    <div class="flex flex-1 flex-col p-4">
                        {{-- Topo: seleção + selo --}}
                        <div class="flex items-center justify-between">
                            <input type="checkbox" wire:model.live="selectedClients" value="{{ $client->id }}" title="Selecionar"
                                class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-800">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $tierStyle['pill'] }}">
                                <i class="bi {{ $tierStyle['icon'] }} text-[10px]"></i>{{ $tierStyle['label'] }}
                            </span>
                        </div>

                        {{-- Identidade --}}
                        <a href="{{ route('clients.resumo', $client->id) }}" class="mt-2 flex items-center gap-3 min-w-0">
                            <x-client-avatar :client="$client" size="w-14 h-14 text-lg" rounded="rounded-full" class="ring-2 ring-white dark:ring-slate-800 group-hover:scale-105 transition-transform" />
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">{{ $client->name }}</h3>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                    @if($place)<i class="bi bi-geo-alt mr-0.5"></i>{{ $place }} · @endif
                                    desde {{ optional($client->created_at)->format('m/Y') }}
                                </p>
                            </div>
                        </a>

                        {{-- Contato --}}
                        <div class="mt-3 space-y-1 text-xs">
                            <div class="flex items-center gap-2 truncate {{ $client->email ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500 italic' }}">
                                <i class="bi bi-envelope text-indigo-500"></i><span class="truncate">{{ $client->email ?: 'Sem e-mail' }}</span>
                            </div>
                            <div class="flex items-center gap-2 {{ $client->phone ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500 italic' }}">
                                <i class="bi bi-telephone text-indigo-500"></i><span>{{ $client->phone ?: 'Sem telefone' }}</span>
                            </div>
                        </div>

                        {{-- Números --}}
                        <div class="mt-3 grid grid-cols-3 divide-x divide-slate-200 dark:divide-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 py-2 text-center">
                            <div class="px-1">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Compras</p>
                                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $salesCount }}</p>
                            </div>
                            <div class="px-1">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                                <p class="text-sm font-bold text-indigo-700 dark:text-indigo-300 leading-6 truncate">R$ {{ number_format($salesTotal, 0, ',', '.') }}</p>
                            </div>
                            <div class="px-1">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Em aberto</p>
                                @if($openAmount > 0.009)
                                    <p class="text-sm font-bold text-rose-600 dark:text-rose-400 leading-6 truncate">R$ {{ number_format($openAmount, 0, ',', '.') }}</p>
                                @else
                                    <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 leading-6"><i class="bi bi-check2-circle"></i> Em dia</p>
                                @endif
                            </div>
                        </div>

                        {{-- Situação --}}
                        <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[11px] font-medium">
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-slate-600 dark:text-slate-300">
                                <i class="bi bi-clock-history"></i>{{ $lastSale ? 'Última compra ' . $lastSale->created_at->locale('pt_BR')->diffForHumans() : 'Nenhuma compra ainda' }}
                            </span>
                            @if($client->portal_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-emerald-700 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Portal ativo</span>
                            @endif
                            @if($consortiumCount > 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 dark:bg-teal-500/10 px-2 py-0.5 text-teal-700 dark:text-teal-300"><i class="bi bi-building"></i>{{ $consortiumCount }} consórcio{{ $consortiumCount > 1 ? 's' : '' }}</span>
                            @endif
                        </div>

                        {{-- Ações principais --}}
                        <div class="mt-auto pt-4 grid grid-cols-2 gap-2">
                            <a href="{{ route('clients.resumo', $client->id) }}"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20 transition">
                                <i class="bi bi-graph-up"></i>Resumo
                            </a>
                            <a href="{{ route('clients.dashboard', $client->id) }}"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-indigo-200 dark:border-indigo-500/40 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 px-3 py-2 text-xs font-semibold text-indigo-700 dark:text-indigo-300 transition">
                                <i class="bi bi-speedometer2"></i>Dashboard
                            </a>
                        </div>

                        {{-- Ações rápidas --}}
                        @php $iconBtn = 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition'; @endphp
                        <div class="mt-2 flex items-center justify-between border-t border-slate-100 dark:border-slate-800 pt-2">
                            <a href="{{ route('clients.edit', $client->id) }}" class="{{ $iconBtn }} hover:text-indigo-600" title="Editar"><i class="bi bi-pencil"></i></a>
                            @if($whats)
                                <a href="https://wa.me/55{{ $whats }}?text={{ urlencode('Olá ' . $client->name . ', tudo bem?') }}" target="_blank" class="{{ $iconBtn }} hover:text-emerald-600" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                            @else
                                <span class="{{ $iconBtn }} opacity-30 cursor-not-allowed" title="Sem telefone"><i class="bi bi-whatsapp"></i></span>
                            @endif
                            @if($client->email)
                                <a href="mailto:{{ $client->email }}" class="{{ $iconBtn }} hover:text-sky-600" title="E-mail"><i class="bi bi-envelope"></i></a>
                            @else
                                <span class="{{ $iconBtn }} opacity-30 cursor-not-allowed" title="Sem e-mail"><i class="bi bi-envelope"></i></span>
                            @endif
                            <a href="{{ route('clients.portal.quotes', $client->id) }}" class="{{ $iconBtn }} hover:text-violet-600" title="Orçamentos"><i class="bi bi-file-earmark-text"></i></a>
                            <a href="{{ route('clients.portal.access', $client->id) }}" class="{{ $iconBtn }} hover:text-amber-600" title="Acesso ao portal"><i class="bi bi-key"></i></a>
                            <button type="button" wire:click="openExportModal({{ $client->id }})" class="{{ $iconBtn }} hover:text-purple-600" title="Exportar"><i class="bi bi-download"></i></button>
                            @if($salesCount === 0)
                                <button type="button" wire:click="confirmDelete({{ $client->id }})" class="{{ $iconBtn }} hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10" title="Excluir"><i class="bi bi-trash"></i></button>
                            @else
                                <span class="{{ $iconBtn }} opacity-30 cursor-not-allowed" title="Cliente com vendas não pode ser excluído"><i class="bi bi-trash"></i></span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Incluir Modais de Export (um para cada cliente) --}}
        @include('livewire.clients._export-modal')

        <!-- Modal de Confirmação de Exclusão -->
        <div x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden flex justify-center items-center w-full h-full bg-black/30 backdrop-blur-md"
            x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" wire:click="cancelDelete">
            <div class="relative p-4 w-full max-w-lg max-h-full transform transition-all duration-300 scale-100"
                x-transition:enter="transition ease-out duration-300 delay-75"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4" wire:click.stop>
                <div
                    class="relative bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/30 dark:border-slate-700/50 overflow-hidden">
                    <!-- Gradiente decorativo no topo -->
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-pink-500 to-purple-600">
                    </div>

                    <!-- Header do modal -->
                    <div class="flex items-center justify-between p-6 border-b border-slate-200/50 dark:border-slate-700/50">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="bi bi-shield-exclamation text-lg"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                Confirmar Exclusão
                            </h3>
                        </div>
                        <button type="button" wire:click="cancelDelete"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 bg-slate-100/50 hover:bg-slate-200/70 dark:bg-slate-700/50 dark:hover:bg-slate-600/70 rounded-xl text-sm w-10 h-10 flex justify-center items-center transition-all duration-200"
                            title="Fechar">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <!-- Conteúdo do modal -->
                    <div class="p-6 text-center">
                        <!-- Ícone central com animação -->
                        <div class="relative mx-auto mb-6">
                            <div
                                class="w-20 h-20 mx-auto bg-gradient-to-br from-red-100 to-pink-100 dark:from-red-900/40 dark:to-pink-900/40 rounded-full flex items-center justify-center shadow-xl ring-4 ring-red-100 dark:ring-red-900/30">
                                <i class="bi bi-person-x text-red-600 dark:text-red-400 text-3xl animate-pulse"></i>
                            </div>
                            <!-- Ícones decorativos orbitando -->
                            <div
                                class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                                <i class="bi bi-exclamation text-white text-sm font-bold"></i>
                            </div>
                            <div
                                class="absolute -bottom-2 -left-2 w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                <i class="bi bi-trash text-white text-sm"></i>
                            </div>
                        </div>

                        <!-- Título e descrição -->
                        <h3 class="mb-2 text-2xl font-bold text-slate-900 dark:text-slate-100">
                            Excluir Cliente?
                        </h3>
                        <p class="mb-6 text-slate-600 dark:text-slate-400 leading-relaxed">
                            Esta ação não pode ser desfeita. Todas as informações e histórico deste cliente serão <span
                                class="font-semibold text-red-600 dark:text-red-400">permanentemente removidos</span>.
                        </p>

                        <!-- Alertas adicionais -->
                        <div
                            class="mb-6 p-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border border-amber-200 dark:border-amber-700/50">
                            <div class="flex items-center justify-center gap-2 text-amber-700 dark:text-amber-400">
                                <i class="bi bi-info-circle text-lg"></i>
                                <span class="text-sm font-medium">Dados que serão perdidos:</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-3 text-xs text-amber-600 dark:text-amber-500">
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-person-circle"></i>
                                    <span>Informações pessoais</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-receipt"></i>
                                    <span>Histórico de vendas</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Dados financeiros</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-graph-up"></i>
                                    <span>Relatórios e métricas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de ação -->
                        <div class="flex gap-3">
                            <button wire:click="cancelDelete" type="button"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-slate-700 dark:text-slate-300 bg-slate-100/70 dark:bg-slate-700/70 hover:bg-slate-200/80 dark:hover:bg-slate-600/80 rounded-xl font-semibold transition-all duration-200 backdrop-blur-sm border border-slate-200/50 dark:border-slate-600/50 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="bi bi-shield-check text-lg"></i>
                                <span>Manter Cliente</span>
                            </button>
                            <button wire:click="deleteClient" type="button"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 ring-2 ring-red-500/20 focus:ring-4 focus:ring-red-500/40">
                                <i class="bi bi-trash-fill text-lg"></i>
                                <span>Confirmar Exclusão</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notificações Toast -->
        @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div
                class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl border border-green-400 backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ session('message') }}</p>
                    </div>
                    <div class="ml-4">
                        <button @click="show = false"
                            class="text-green-200 hover:text-white transition-colors duration-200">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div
                class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4 rounded-xl shadow-2xl border border-red-400 backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                    <div class="ml-4">
                        <button @click="show = false"
                            class="text-red-200 hover:text-white transition-colors duration-200">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Modal de Importação -->
        <div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4"
            style="display: none;">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center">
                        <i class="bi bi-upload text-green-500 mr-2"></i>
                        Importar Clientes
                    </h3>
                    <button onclick="document.getElementById('importModal').style.display='none'"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Selecione o arquivo CSV
                        </label>
                        <input type="file" accept=".csv"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Formato do arquivo:</h4>
                        <p class="text-xs text-blue-700 dark:text-blue-400">
                            O arquivo deve conter as colunas: nome, email, telefone, endereço, cidade
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button onclick="document.getElementById('importModal').style.display='none'"
                        class="flex-1 px-4 py-2 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                        Cancelar
                    </button>
                    <button class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Importar
                    </button>
                </div>
            </div>
        </div>

        <x-pagination-bar :paginator="$clients" :per-page-options="[12, 24, 48, 96]" label="clientes" />
    </div>


</div>