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
                            <i class="bi bi-arrow-left-right text-indigo-600 dark:text-indigo-400 mr-3"></i>
                            Transferências do Cliente
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400">{{ $client->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros e Estatísticas -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <!-- Filtros -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-funnel text-indigo-600 dark:text-indigo-400 mr-2"></i>
                    Filtros
                </h3>
                <div class="space-y-2">
                    <button wire:click="setTipo('all')" 
                            class="w-full text-left px-3 py-2 rounded-lg transition-colors duration-200 {{ $tipo === 'all' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        <i class="bi bi-list mr-2"></i>
                        Todas
                    </button>
                    <button wire:click="setTipo('recebidas')" 
                            class="w-full text-left px-3 py-2 rounded-lg transition-colors duration-200 {{ $tipo === 'recebidas' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        <i class="bi bi-arrow-down mr-2"></i>
                        Recebidas
                    </button>
                    <button wire:click="setTipo('enviadas')" 
                            class="w-full text-left px-3 py-2 rounded-lg transition-colors duration-200 {{ $tipo === 'enviadas' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        <i class="bi bi-arrow-up mr-2"></i>
                        Enviadas
                    </button>
                </div>
            </div>

            <!-- Total Recebido -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-arrow-down-circle text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Recebido</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            R$ {{ number_format($totalRecebido, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Enviado -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-arrow-up-circle text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Enviado</p>
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                            R$ {{ number_format($totalEnviado, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Saldo -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 {{ ($totalRecebido - $totalEnviado) >= 0 ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-lg flex items-center justify-center">
                        <i class="bi bi-calculator {{ ($totalRecebido - $totalEnviado) >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }} text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Transferências</p>
                        <p class="text-2xl font-bold {{ ($totalRecebido - $totalEnviado) >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                            R$ {{ number_format($totalRecebido - $totalEnviado, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Transferências -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
            @if($transferencias->count() > 0)
                <!-- Header da tabela -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Lista de Transferências
                        @if($tipo !== 'all')
                            - {{ ucfirst($tipo) }}
                        @endif
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $transferencias->total() }} registros)</span>
                    </h3>
                </div>

                <!-- Grid de Transferências -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    @foreach($transferencias as $transferencia)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 border-l-4 {{ $transferencia->type_id == 1 ? 'border-green-500' : 'border-orange-500' }}">
                            <!-- Header da transferência -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 rounded-full {{ $transferencia->type_id == 1 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-orange-100 dark:bg-orange-900/30' }} flex items-center justify-center">
                                        <i class="bi bi-arrow-{{ $transferencia->type_id == 1 ? 'down' : 'up' }} {{ $transferencia->type_id == 1 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }} text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($transferencia->date)->format('d/m/Y') }}
                                        </p>
                                        @if($transferencia->category)
                                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ $transferencia->category->name }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Tipo da transferência -->
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transferencia->type_id == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' }}">
                                    {{ $transferencia->type_id == 1 ? 'Recebido' : 'Enviado' }}
                                </span>
                            </div>

                            <!-- Descrição -->
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 line-clamp-2">
                                {{ $transferencia->description }}
                            </h4>

                            <!-- Valor -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor:</span>
                                <span class="text-lg font-bold {{ $transferencia->type_id == 1 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    {{ $transferencia->type_id == 1 ? '+' : '-' }} R$ {{ number_format($transferencia->value, 2, ',', '.') }}
                                </span>
                            </div>

                            <!-- Categoria com cor -->
                            @if($transferencia->category && $transferencia->category->hexcolor_category)
                                <div class="mt-2 flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $transferencia->category->hexcolor_category }}"></div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $transferencia->category->name }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Paginação -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-zinc-700">
                    {{ $transferencias->links() }}
                </div>
            @else
                <!-- Estado vazio -->
                <div class="text-center py-16">
                    <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-6">
                        <i class="bi bi-arrow-left-right text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Nenhuma transferência encontrada
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        @if($tipo === 'all')
                            Este cliente ainda não possui transferências cadastradas.
                        @elseif($tipo === 'recebidas')
                            Este cliente ainda não possui transferências recebidas.
                        @else
                            Este cliente ainda não possui transferências enviadas.
                        @endif
                    </p>
                    <a href="{{ route('cashbook.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <i class="bi bi-plus mr-2"></i>
                        Criar Nova Transferência
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
