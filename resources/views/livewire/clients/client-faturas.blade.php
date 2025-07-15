<div class="min-h-screen w-full bg-gray-50 dark:bg-zinc-900 py-8">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('clients.resumo', $client->id) }}" 
                   class="mr-4 p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg transition-colors duration-200">
                    <i class="bi bi-arrow-left text-lg"></i>
                </a>
                <div class="flex items-center gap-4">
                    @if($client->caminho_foto)
                        <img src="{{ $client->caminho_foto }}" 
                             alt="Avatar de {{ $client->name }}"
                             class="w-12 h-12 rounded-full border-2 border-indigo-100 dark:border-indigo-600">
                    @else
                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-indigo-100 dark:bg-indigo-600">
                            <i class="bi bi-person text-indigo-600 dark:text-indigo-100 text-lg"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            <i class="bi bi-receipt text-orange-600 dark:text-orange-400 mr-3"></i>
                            Faturas do Cliente
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400">{{ $client->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas das Faturas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total de Faturas -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-receipt text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Faturas</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $faturas->total() }}</p>
                    </div>
                </div>
            </div>

            <!-- Valor Total Original -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-currency-dollar text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Valor Total Original</p>
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                            R$ {{ number_format($faturas->sum('value'), 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Valor Atual (com divisões) -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-calculator text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Valor Atual</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            R$ {{ number_format($faturas->sum(function($f) { return $f->dividida ? $f->value / 2 : $f->value; }), 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Faturas -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
            @if($faturas->count() > 0)
                <!-- Header da tabela -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Lista de Faturas
                    </h3>
                </div>

                <!-- Grid de Faturas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    @foreach($faturas as $fatura)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                            <!-- Header da fatura -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    @if($fatura->category && $fatura->category->icone)
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                            <i class="{{ $fatura->category->icone }} text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($fatura->invoice_date)->format('d/m/Y') }}
                                        </p>
                                        @if($fatura->category)
                                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ $fatura->category->name }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Toggle Dividida -->
                                <button wire:click="toggleDividida({{ $fatura->id }})" 
                                        class="flex items-center space-x-1 px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200 {{ $fatura->dividida ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300' }}">
                                    <i class="bi bi-{{ $fatura->dividida ? 'check' : 'x' }}"></i>
                                    <span>{{ $fatura->dividida ? 'Dividida' : 'Integral' }}</span>
                                </button>
                            </div>

                            <!-- Descrição -->
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                {{ $fatura->description }}
                            </h4>

                            <!-- Valores -->
                            <div class="space-y-1">
                                @if($fatura->dividida)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Valor Original:</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 line-through">
                                            R$ {{ number_format($fatura->value, 2, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor Dividido:</span>
                                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                            R$ {{ number_format($fatura->value / 2, 2, ',', '.') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor:</span>
                                        <span class="text-sm font-bold text-orange-600 dark:text-orange-400">
                                            R$ {{ number_format($fatura->value, 2, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Paginação -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-zinc-700">
                    {{ $faturas->links() }}
                </div>
            @else
                <!-- Estado vazio -->
                <div class="text-center py-16">
                    <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-6">
                        <i class="bi bi-receipt text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Nenhuma fatura encontrada</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Este cliente ainda não possui faturas cadastradas.</p>
                    <a href="{{ route('invoices.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <i class="bi bi-plus mr-2"></i>
                        Criar Nova Fatura
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2" x-init="setTimeout(() => show = false, 5000)" class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg">
            <div class="flex items-center">
                <i class="bi bi-check-circle-fill mr-2"></i>
                {{ session('success') }}
                <button @click="show = false" class="ml-4 text-green-500 hover:text-green-700">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    @endif
</div>
