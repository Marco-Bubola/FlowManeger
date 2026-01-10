<div class="w-full space-y-4" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
    <!-- Header Padrão -->
    <x-sales-header title="Adicionar Participantes"
        subtitle="{{ $consortium->name }} @if ($consortium->max_participants) • <strong class='text-amber-300'>Máx: {{ $consortium->max_participants }} participantes</strong> @endif"
        icon="bi-person-plus-fill" :backRoute="route('consortiums.show', $consortium)">

        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('consortiums.index') }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="bi bi-collection-fill mr-1"></i>Consórcios
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('consortiums.show', $consortium) }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    {{ $consortium->name }}
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Adicionar Participantes</span>
            </div>
        </x-slot>

        <x-slot name="actions">
            <div class="flex items-center gap-4 flex-wrap">
                <!-- Métricas Estilo Bank-Index -->
                <div class="flex items-center gap-4">
                    <!-- Selecionados -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-white/70 uppercase tracking-wide">Selecionados</span>
                        <span class="text-2xl font-black text-white">
                            {{ count($selectedClients) }}
                        </span>
                        <span class="text-xs text-white/60">Participantes</span>
                    </div>

                    <!-- Divider -->
                    <div class="h-12 w-px bg-gradient-to-b from-transparent via-white/30 to-transparent"></div>

                    <!-- Data Entrada -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-white/70 uppercase tracking-wide">Data Entrada</span>
                        <span class="text-2xl font-black text-white">
                            {{ \Carbon\Carbon::parse($entry_date)->format('d/m/Y') }}
                        </span>
                        <span class="text-xs text-white/60">Início</span>
                    </div>

                    <!-- Divider -->
                    <div class="h-12 w-px bg-gradient-to-b from-transparent via-white/30 to-transparent"></div>

                    <!-- Mensalidade -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-white/70 uppercase tracking-wide">Mensalidade</span>
                        <span
                            class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-200 to-teal-200">
                            R$ {{ number_format($consortium->monthly_value, 2, ',', '.') }}
                        </span>
                        <span class="text-xs text-white/60">Valor fixo</span>
                    </div>
                </div>

                <!-- Divider -->
                <div class="hidden lg:block h-16 w-px bg-gradient-to-b from-transparent via-white/30 to-transparent">
                </div>

                <!-- Botões Ultra Modernos -->
                <div class="flex items-center gap-3">
                    <!-- Botão Cancelar -->
                    <a href="{{ route('consortiums.show', $consortium) }}"
                        class="group relative inline-flex items-center justify-center gap-2 px-5 py-2.5 overflow-hidden rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-white/30">
                        <!-- Fundo -->
                        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
                        <div
                            class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>

                        <!-- Borda -->
                        <div class="absolute inset-0 rounded-xl border-2 border-white/30"></div>

                        <!-- Conteúdo -->
                        <div class="relative flex items-center gap-2 z-10">
                            <i
                                class="bi bi-x-lg text-white text-base group-hover:rotate-90 transition-transform duration-300"></i>
                            <span class="font-black text-sm text-white tracking-wider uppercase">Cancelar</span>
                        </div>
                    </a>

                    <!-- Botão Salvar - Ultra Moderno -->
                    <button wire:click="save"
                        class="group relative inline-flex items-center justify-center gap-2.5 px-6 py-2.5 overflow-hidden rounded-xl transition-all duration-500 transform hover:-translate-y-1 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-white/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:scale-100"
                        @disabled(count($selectedClients) === 0)>
                        <!-- Fundo gradiente animado -->
                        <div class="absolute inset-0 bg-white"></div>
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-white via-emerald-50 to-white opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                        </div>

                        <!-- Brilho superior -->
                        <div
                            class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-300 to-transparent">
                        </div>

                        <!-- Efeito de brilho animado -->
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-transparent via-emerald-200/50 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                            </div>
                        </div>

                        <!-- Shadow glow -->
                        <div
                            class="absolute -inset-1 bg-white rounded-xl blur-lg opacity-50 group-hover:opacity-100 transition-opacity duration-500 -z-10">
                        </div>

                        <!-- Conteúdo -->
                        <div class="relative flex items-center gap-2.5 z-10">
                            <i
                                class="bi bi-check-circle-fill text-xl text-emerald-600 group-hover:scale-110 transition-transform duration-300"></i>
                            <span class="font-black text-sm text-emerald-600 tracking-wider uppercase">
                                Salvar {{ count($selectedClients) > 0 ? '(' . count($selectedClients) . ')' : '' }}
                            </span>
                        </div>

                        <!-- Partículas decorativas -->
                        <div
                            class="absolute top-1 right-1 w-2 h-2 bg-emerald-400/60 rounded-full group-hover:scale-150 group-hover:opacity-0 transition-all duration-500">
                        </div>
                        <div
                            class="absolute bottom-1 left-1 w-1.5 h-1.5 bg-emerald-300/40 rounded-full group-hover:scale-150 group-hover:opacity-0 transition-all duration-700">
                        </div>
                    </button>
                </div>
            </div>
        </x-slot>
    </x-sales-header>

    <!-- Flash Messages -->
    @if (session()->has('error'))
        <div class="animate-fade-in">
            <div
                class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border-l-4 border-red-500 rounded-xl p-4 shadow-lg">
                <div class="flex items-center gap-3">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 text-2xl"></i>
                    <p class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 transition-all duration-700 delay-100"
        :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">

        <!-- Left: Clientes Disponíveis (Full Width) -->
        <div class="xl:col-span-2 space-y-4">


            <!-- Grid de Clientes com Pesquisa no Header -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <!-- Header com Pesquisa Integrada -->
                <div
                    class="p-6 bg-gradient-to-r from-slate-50 via-blue-50 to-purple-50 dark:from-slate-700 dark:via-blue-900/30 dark:to-purple-900/30 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div
                                    class="absolute -inset-1 bg-gradient-to-br from-emerald-400 via-teal-400 to-cyan-400 rounded-2xl blur-lg opacity-50 animate-pulse">
                                </div>
                                <div
                                    class="relative w-14 h-14 bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 rounded-2xl flex items-center justify-center shadow-2xl">
                                    <i class="bi bi-people-fill text-white text-2xl"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">
                                    Clientes Disponíveis
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-3">
                                    <span class="flex items-center gap-1.5">
                                        <i class="bi bi-check2-circle text-emerald-500"></i>
                                        {{ $availableClients->total() }} disponíveis
                                    </span>
                                    @if ($consortium->max_participants)
                                        <span class="text-slate-300 dark:text-slate-600">•</span>
                                        <span class="flex items-center gap-1.5">
                                            <i class="bi bi-trophy-fill text-amber-500"></i>
                                            <strong class="text-amber-600 dark:text-amber-400">Máx:
                                                {{ $consortium->max_participants }}</strong> participantes
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Search Bar Dinâmica -->
                        <div class="relative flex-1 md:max-w-md">
                            <i
                                class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Pesquisar por nome, email ou telefone..."
                                class="w-full pl-12 pr-12 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all font-medium shadow-sm">
                            @if ($search)
                                <button wire:click="$set('search', '')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center transition-all hover:scale-110">
                                    <i class="bi bi-x text-slate-600 dark:text-slate-300 font-bold"></i>
                                </button>
                            @endif
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" wire:loading
                                wire:target="search">
                                <div
                                    class="w-5 h-5 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($availableClients->count() > 0)
                    <div class="p-6">
                        <!-- Grid Responsivo: progressão suave até 5 colunas -->
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-5 ultrawind:grid-cols-8 gap-4">
                            @foreach ($availableClients as $client)
                                @php
                                    $isSelected = in_array($client->id, $selectedClients);
                                    $avatarColors = [
                                        'from-pink-500 to-rose-600',
                                        'from-purple-500 to-indigo-600',
                                        'from-blue-500 to-cyan-600',
                                        'from-emerald-500 to-teal-600',
                                        'from-amber-500 to-orange-600',
                                        'from-red-500 to-pink-600',
                                    ];
                                    $colorIndex = ord(strtoupper(substr($client->name, 0, 1))) % count($avatarColors);
                                    $avatarGradient = $avatarColors[$colorIndex];
                                @endphp

                                <div wire:key="client-{{ $client->id }}"
                                    wire:click="toggleClient({{ $client->id }})"
                                    class="group relative cursor-pointer bg-white dark:bg-slate-700 rounded-2xl p-3 border-2 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 {{ $isSelected ? 'border-emerald-500 shadow-xl shadow-emerald-500/20 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 scale-[1.02]' : 'border-slate-200 dark:border-slate-600 hover:border-emerald-400' }}">

                                    <!-- Badge de Status -->
                                    <div class="absolute -top-2 -right-2 z-10">
                                        <div
                                            class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300 shadow-lg {{ $isSelected ? 'bg-gradient-to-br from-emerald-500 to-teal-600 scale-110 rotate-12' : 'bg-white dark:bg-slate-600 border-2 border-slate-200 dark:border-slate-500 group-hover:scale-110 group-hover:bg-emerald-50 dark:group-hover:bg-emerald-900/30' }}">
                                            @if ($isSelected)
                                                <i class="bi bi-check-lg text-white font-bold text-base"></i>
                                            @else
                                                <i
                                                    class="bi bi-plus-lg text-slate-400 dark:text-slate-500 group-hover:text-emerald-600 text-base font-bold"></i>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Avatar Grande e Moderno -->
                                    <div class="flex flex-col items-center mb-2">
                                        <div class="relative mb-1.5">
                                            <div
                                                class="absolute -inset-1 bg-gradient-to-br {{ $avatarGradient }} rounded-2xl blur opacity-40 group-hover:opacity-70 transition-opacity">
                                            </div>
                                            <div
                                                class="relative w-14 h-14 bg-gradient-to-br {{ $avatarGradient }} rounded-2xl flex items-center justify-center shadow-xl transform transition-transform group-hover:scale-110 {{ $isSelected ? 'scale-110' : '' }} overflow-hidden">
                                                @if ($client->caminho_foto && file_exists(public_path('storage/' . $client->caminho_foto)))
                                                    <img src="{{ asset('storage/' . $client->caminho_foto) }}"
                                                        alt="{{ $client->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <span
                                                        class="text-white font-black text-xl">{{ strtoupper(substr($client->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <!-- Anel de Pulso -->
                                            @if ($isSelected)
                                                <div
                                                    class="absolute -inset-2 border-2 border-emerald-500 rounded-2xl animate-pulse">
                                                </div>
                                            @endif
                                        </div>

                                        <h4
                                            class="font-black text-slate-900 dark:text-slate-100 text-center text-sm mb-0.5 line-clamp-2">
                                            {{ $client->name }}
                                        </h4>
                                    </div>

                                    <!-- Informações -->
                                    <div class="space-y-1 mb-1">
                                        @if ($client->email)
                                            <div
                                                class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 rounded-lg px-2 py-1">
                                                <i
                                                    class="bi bi-envelope text-{{ $isSelected ? 'emerald' : 'slate' }}-500 text-xs"></i>
                                                <span class="truncate">{{ $client->email }}</span>
                                            </div>
                                        @endif
                                        @if ($client->phone)
                                            <div
                                                class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 rounded-lg px-2 py-1">
                                                <i
                                                    class="bi bi-telephone text-{{ $isSelected ? 'emerald' : 'slate' }}-500 text-xs"></i>
                                                <span class="font-medium">{{ $client->phone }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                @endforeach
            </div>

            <!-- Paginação Ultra Moderna -->
            @if ($availableClients->hasPages())
                <div class="mt-6 px-6 pb-6">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <!-- Info de Página -->
                        <div class="text-sm text-slate-600 dark:text-slate-400 font-medium">
                            Mostrando
                            <span
                                class="font-black text-emerald-600 dark:text-emerald-400">{{ $availableClients->firstItem() ?? 0 }}</span>
                            até
                            <span
                                class="font-black text-emerald-600 dark:text-emerald-400">{{ $availableClients->lastItem() ?? 0 }}</span>
                            de
                            <span
                                class="font-black text-emerald-600 dark:text-emerald-400">{{ $availableClients->total() }}</span>
                            clientes
                        </div>

                        <!-- Botões de Paginação -->
                        <div class="flex items-center gap-2">
                            {{-- Botão Anterior --}}
                            @if ($availableClients->onFirstPage())
                                <div
                                    class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 rounded-xl font-bold text-sm cursor-not-allowed flex items-center gap-2">
                                    <i class="bi bi-chevron-left"></i>
                                    <span class="hidden sm:inline">Anterior</span>
                                </div>
                            @else
                                <button wire:click="previousPage"
                                    class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg hover:shadow-xl hover:scale-105 flex items-center gap-2">
                                    <i class="bi bi-chevron-left"></i>
                                    <span class="hidden sm:inline">Anterior</span>
                                </button>
                            @endif

                            {{-- Números de Página --}}
                            <div class="flex items-center gap-2">
                                @foreach (range(1, $availableClients->lastPage()) as $page)
                                    @if ($page == $availableClients->currentPage())
                                        <div
                                            class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-xl font-black text-sm flex items-center justify-center shadow-lg scale-110">
                                            {{ $page }}
                                        </div>
                                    @elseif($page == 1 || $page == $availableClients->lastPage() || abs($page - $availableClients->currentPage()) <= 1)
                                        <button wire:click="gotoPage({{ $page }})"
                                            class="w-10 h-10 bg-white dark:bg-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 rounded-xl font-bold text-sm flex items-center justify-center border-2 border-slate-200 dark:border-slate-600 transition-all hover:scale-110 hover:border-emerald-400">
                                            {{ $page }}
                                        </button>
                                    @elseif(abs($page - $availableClients->currentPage()) == 2)
                                        <div class="text-slate-400 dark:text-slate-600 font-black px-1">•••</div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Botão Próximo --}}
                            @if ($availableClients->hasMorePages())
                                <button wire:click="nextPage"
                                    class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg hover:shadow-xl hover:scale-105 flex items-center gap-2">
                                    <span class="hidden sm:inline">Próximo</span>
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            @else
                                <div
                                    class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 rounded-xl font-bold text-sm cursor-not-allowed flex items-center gap-2">
                                    <span class="hidden sm:inline">Próximo</span>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="p-16 text-center">
            <div
                class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl">
                <i class="bi bi-inbox text-5xl text-slate-400 dark:text-slate-500"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-600 dark:text-slate-400 mb-3">Nenhum cliente disponível</h3>
            <p class="text-slate-500 dark:text-slate-500 text-lg">
                @if ($search)
                    Nenhum cliente encontrado com "<strong>{{ $search }}</strong>"
                @else
                    Todos os clientes já participam deste consórcio
                @endif
            </p>
        </div>
        @endif
        </div>
    </div>

        <!-- Right: Clientes Selecionados (Sticky) -->
    <div class="xl:col-span-1">
    <div class="sticky top-18">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border-2 border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Header com Gradiente -->
            <div
                class="relative p-4 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 text-white overflow-hidden">
                <div class="absolute inset-0 bg-grid-white/10"></div>
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative flex items-center justify-between mb-2">
                    <h3 class="text-xl font-black flex items-center gap-2">
                        <i class="bi bi-people-fill"></i>
                        Selecionados
                    </h3>
                    <div
                        class="w-12 h-12 bg-white/20 backdrop-blur-xl rounded-xl flex items-center justify-center font-black text-xl border-2 border-white/30 shadow-xl">
                        {{ count($selectedClients) }}
                    </div>
                </div>
            </div>

            <!-- Grid Responsivo -->
            <div class="max-h-[calc(100vh-24rem)] overflow-y-auto">
                @if (count($selectedClients) > 0)
                    <div
                        class="p-4 grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-2 ultrawind:grid-cols-3 gap-3">
                        @foreach ($selectedClientsData as $index => $client)
                            @php
                                $avatarColors = [
                                    'from-pink-500 to-rose-600',
                                    'from-purple-500 to-indigo-600',
                                    'from-blue-500 to-cyan-600',
                                    'from-emerald-500 to-teal-600',
                                    'from-amber-500 to-orange-600',
                                    'from-red-500 to-pink-600',
                                ];
                                $colorIndex = ord(strtoupper(substr($client->name, 0, 1))) % count($avatarColors);
                                $avatarGradient = $avatarColors[$colorIndex];
                            @endphp

                            <div wire:key="selected-{{ $client->id }}"
                                class="group relative bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 dark:from-emerald-900/20 dark:via-teal-900/20 dark:to-cyan-900/20 border-2 border-emerald-300 dark:border-emerald-700 rounded-2xl p-4 hover:shadow-xl transition-all duration-300 hover:scale-[1.02]">

                                <!-- Número do Participante -->
                                <div
                                    class="absolute -top-3 -left-3 w-8 h-8 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-xl flex items-center justify-center font-black text-white text-sm shadow-lg z-10">
                                    {{ $index + 1 }}
                                </div>

                                <!-- Botão Remover (Sempre visível em dispositivos pequenos) -->
                                <button wire:click="removeClient({{ $client->id }})"
                                    class="absolute -top-3 -right-3 w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-xl flex items-center justify-center transition-all hover:scale-110 shadow-lg z-10">
                                    <i class="bi bi-x-lg text-sm font-bold"></i>
                                </button>

                                <!-- Avatar centralizado -->
                                <div class="flex flex-col items-center mb-3">
                                    <div class="relative mb-2">
                                        <div
                                            class="absolute -inset-1 bg-gradient-to-br {{ $avatarGradient }} rounded-2xl blur opacity-50">
                                        </div>
                                        <div
                                            class="relative w-16 h-16 bg-gradient-to-br {{ $avatarGradient }} rounded-2xl flex items-center justify-center shadow-xl overflow-hidden">
                                            @if ($client->caminho_foto && file_exists(public_path('storage/' . $client->caminho_foto)))
                                                <img src="{{ asset('storage/' . $client->caminho_foto) }}"
                                                    alt="{{ $client->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span
                                                    class="text-white font-bold text-2xl">{{ strtoupper(substr($client->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <h4
                                        class="font-black text-slate-900 dark:text-slate-100 text-center text-sm line-clamp-2 mb-1 w-full px-2">
                                        {{ $client->name }}
                                    </h4>
                                    @if ($client->email)
                                        <p
                                            class="text-xs text-slate-600 dark:text-slate-400 text-center truncate w-full px-2">
                                            {{ $client->email }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Campo de observações compacto -->
                                <div class="relative mt-3">
                                    <textarea wire:model.blur="notes.{{ $client->id }}" rows="2" placeholder="Observações..."
                                        class="w-full px-3 py-2 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all resize-none"></textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="relative inline-block mb-6">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-3xl blur-xl opacity-20">
                            </div>
                            <div
                                class="relative w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 rounded-3xl flex items-center justify-center shadow-xl">
                                <i class="bi bi-person-plus text-5xl text-slate-400"></i>
                            </div>
                        </div>
                        <h4 class="font-black text-slate-700 dark:text-slate-300 mb-3 text-lg">Nenhum cliente
                            selecionado</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-500 leading-relaxed">
                            Clique nos clientes ao lado<br />para selecioná-los
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

</div>
