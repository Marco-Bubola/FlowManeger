<div class="w-full">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold mb-6 bg-gradient-to-r from-blue-700 via-purple-600 to-pink-500 bg-clip-text text-transparent flex items-center">
            <svg class="w-8 h-8 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-4a4 4 0 10-8 0 4 4 0 008 0z" /></svg>
            Dashboard de Clientes
        </h1>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('tab', 'overview')" class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none" :class="{'text-blue-600 border-blue-600': tab === 'overview', 'text-gray-500 border-transparent': tab !== 'overview'}">
                    <span class="inline-flex items-center"><svg class="w-5 h-5 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3m-4 4h4m-2 0v4m0-4V5" /></svg>Visão Geral</span>
                </button>
                <button wire:click="$set('tab', 'inadimplentes')" class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none" :class="{'text-red-600 border-red-600': tab === 'inadimplentes', 'text-gray-500 border-transparent': tab !== 'inadimplentes'}">
                    <span class="inline-flex items-center"><svg class="w-5 h-5 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414M6.343 17.657l-1.414-1.414M5.636 5.636l1.414 1.414M17.657 17.657l1.414-1.414M12 8v4m0 4h.01" /></svg>Inadimplentes</span>
                </button>
                <button wire:click="$set('tab', 'aniversariantes')" class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none" :class="{'text-yellow-600 border-yellow-600': tab === 'aniversariantes', 'text-gray-500 border-transparent': tab !== 'aniversariantes'}">
                    <span class="inline-flex items-center"><svg class="w-5 h-5 mr-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" /></svg>Aniversariantes</span>
                </button>
                <button wire:click="$set('tab', 'acoes')" class="tab-link py-4 px-1 border-b-2 font-medium text-sm focus:outline-none" :class="{'text-green-600 border-green-600': tab === 'acoes', 'text-gray-500 border-transparent': tab !== 'acoes'}">
                    <span class="inline-flex items-center"><svg class="w-5 h-5 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>Ações</span>
                </button>
            </nav>
        </div>

        <!-- Tabs Content -->
        <div x-data="{ tab: 'overview' }" x-init="$watch('tab', value => $wire.set('tab', value))">
            <!-- Visão Geral -->
            <div x-show="tab === 'overview'">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-400 via-blue-600 to-indigo-600 rounded-xl shadow-lg p-6 text-white flex flex-col items-start">
                        <span class="mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-4a4 4 0 10-8 0 4 4 0 008 0z" /></svg></span>
                        <p class="text-sm font-medium">Total de Clientes</p>
                        <p class="text-2xl font-bold">{{ $totalClientes }}</p>
                    </div>
                    <div class="bg-gradient-to-r from-green-400 via-teal-500 to-blue-500 rounded-xl shadow-lg p-6 text-white flex flex-col items-start">
                        <span class="mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></span>
                        <p class="text-sm font-medium">Novos no mês</p>
                        <p class="text-2xl font-bold">{{ $clientesNovosMes }}</p>
                    </div>
                    <div class="bg-gradient-to-r from-red-400 via-pink-500 to-yellow-500 rounded-xl shadow-lg p-6 text-white flex flex-col items-start">
                        <span class="mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414M6.343 17.657l-1.414-1.414M5.636 5.636l1.414 1.414M17.657 17.657l1.414-1.414M12 8v4m0 4h.01" /></svg></span>
                        <p class="text-sm font-medium">Inadimplentes</p>
                        <p class="text-2xl font-bold">{{ $clientesInadimplentes }}</p>
                    </div>
                    <div class="bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 rounded-xl shadow-lg p-6 text-white flex flex-col items-start">
                        <span class="mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" /></svg></span>
                        <p class="text-sm font-medium">Aniversariantes do mês</p>
                        <p class="text-2xl font-bold">{{ $clientesAniversariantes }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mt-8">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Gráficos e KPIs</h2>
                    <div class="w-full h-40 flex items-center justify-center text-gray-400 dark:text-gray-500">
                        <span>[Gráficos de clientes em breve]</span>
                    </div>
                </div>
            </div>
            <!-- Inadimplentes -->
            <div x-show="tab === 'inadimplentes'">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-red-200 dark:border-red-700 p-6 mb-8">
                    <h2 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-2 flex items-center"><svg class="w-5 h-5 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414M6.343 17.657l-1.414-1.414M5.636 5.636l1.414 1.414M17.657 17.657l1.414-1.414M12 8v4m0 4h.01" /></svg>Clientes Inadimplentes</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Lista dos clientes com vendas pendentes.</p>
                    <livewire:clientes-inadimplentes />
                </div>
            </div>
            <!-- Aniversariantes -->
            <div x-show="tab === 'aniversariantes'">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-yellow-200 dark:border-yellow-700 p-6 mb-8">
                    <h2 class="text-lg font-semibold text-yellow-600 dark:text-yellow-400 mb-2 flex items-center"><svg class="w-5 h-5 mr-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" /></svg>Aniversariantes do mês</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Clientes que fazem aniversário este mês.</p>
                    <livewire:clientes-aniversariantes />
                </div>
            </div>
            <!-- Ações -->
            <div x-show="tab === 'acoes'">
                <div class="flex flex-wrap gap-3 mb-6">
                    <a href="{{ url('/clients/create') }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-md">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Novo Cliente
                    </a>
                    <a href="{{ url('/clients') }}" class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-pink-500 via-red-500 to-yellow-500 text-white text-xs font-semibold rounded-lg hover:from-pink-600 hover:to-yellow-600 transition-all duration-200 shadow-md">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Listar Clientes
                    </a>
                    <a href="#" class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 text-white text-xs font-semibold rounded-lg hover:from-green-500 hover:to-purple-700 transition-all duration-200 shadow-md">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Exportar Clientes
                    </a>
                    <a href="#" class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 text-white text-xs font-semibold rounded-lg hover:from-yellow-500 hover:to-pink-600 transition-all duration-200 shadow-md">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3zm3 3h12v12H6V6z"></path></svg>
                        Importar Clientes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
