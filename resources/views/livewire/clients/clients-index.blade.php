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


        <!-- Filtros: mesma janela de Produtos e Vendas -->
        @php
            $cliActive = $statusFilter || $dateFilter || $minValue !== '' || $maxValue !== '' || $minSales !== '' || $maxSales !== '';
            $opt = fn ($on) => 'fm-fs-opt' . ($on ? ' is-on' : '');
            $sortIcon = fn ($col) => $sortBy === $col ? ($sortDirection === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : '';
        @endphp
        <x-filter-sheet title="Filtros de clientes" subtitle="Status, cadastro, valor comprado e ordem" :active="$cliActive"
            target="statusFilter,dateFilter,toggleSort,minValue,maxValue,minSales,maxSales,clearFilters">
            <div class="fm-fs-group">
                <h4><i class="bi bi-person-check text-purple-500"></i>Status</h4>
                <div class="fm-fs-options">
                    @foreach(['' => ['Todos', 'bi-list-ul'], 'ativo' => ['Ativos', 'bi-person-check'], 'inativo' => ['Inativos', 'bi-person-x'], 'premium' => ['Premium', 'bi-star']] as $val => [$lbl, $ico])
                        <button type="button" wire:click="$set('statusFilter', '{{ $val }}')" class="{{ $opt($statusFilter === $val) }}"><i class="bi {{ $ico }}"></i>{{ $lbl }}</button>
                    @endforeach
                </div>
            </div>
            <div class="fm-fs-group">
                <h4><i class="bi bi-calendar-range text-indigo-500"></i>Cadastrado em</h4>
                <div class="fm-fs-options">
                    @foreach(['' => 'Qualquer data', 'hoje' => 'Hoje', 'semana' => 'Esta semana', 'mes' => 'Este mês', 'ano' => 'Este ano'] as $val => $lbl)
                        <button type="button" wire:click="$set('dateFilter', '{{ $val }}')" class="{{ $opt($dateFilter === $val) }}">{{ $lbl }}</button>
                    @endforeach
                </div>
            </div>
            <div class="fm-fs-group">
                <h4><i class="bi bi-cash-stack text-emerald-500"></i>Total comprado (R$)</h4>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" min="0" step="0.01" inputmode="decimal" placeholder="Mínimo" wire:model.live.debounce.500ms="minValue">
                    <input type="number" min="0" step="0.01" inputmode="decimal" placeholder="Máximo" wire:model.live.debounce.500ms="maxValue">
                </div>
            </div>
            <div class="fm-fs-group">
                <h4><i class="bi bi-bag text-sky-500"></i>Número de compras</h4>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" min="0" step="1" inputmode="numeric" placeholder="Mínimo" wire:model.live.debounce.500ms="minSales">
                    <input type="number" min="0" step="1" inputmode="numeric" placeholder="Máximo" wire:model.live.debounce.500ms="maxSales">
                </div>
            </div>
            <div class="fm-fs-group fm-fs-wide">
                <h4><i class="bi bi-arrow-down-up text-amber-500"></i>Ordenar por <span class="font-normal text-slate-400">(toque de novo para inverter)</span></h4>
                <div class="fm-fs-options">
                    @foreach(['created_at' => 'Data de cadastro', 'name' => 'Nome', 'email' => 'E-mail', 'phone' => 'Telefone', 'status' => 'Status'] as $col => $lbl)
                        <button type="button" wire:click="toggleSort('{{ $col }}')" class="{{ $opt($sortBy === $col) }}">{{ $lbl }}@if($sortIcon($col))<i class="bi {{ $sortIcon($col) }}"></i>@endif</button>
                    @endforeach
                </div>
            </div>
        </x-filter-sheet>


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
        <x-delete-dialog show="showDeleteModal" confirm="deleteClient" cancel="cancelDelete" title="Excluir cliente?" confirm-label="Excluir cliente" icon="bi-person-x">
            Os dados e o histórico deste cliente serão apagados. Não dá para desfazer.
        </x-delete-dialog>

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

        <x-pagination-bar :paginator="$clients" :per-page-options="[12, 24, 48, 96]" label="clientes" fit-grid=".clients-grid" />
    </div>


</div>