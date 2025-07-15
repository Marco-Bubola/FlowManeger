<div class=" w-full py-2">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('clients.index') }}" 
                   class="mr-4 p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg transition-colors duration-200">
                    <i class="bi bi-arrow-left text-lg"></i>
                </a>
                <div class="flex items-center gap-4">
                    @if($client->caminho_foto)
                        <img src="{{ $client->caminho_foto }}" 
                             alt="Avatar de {{ $client->name }}"
                             class="w-16 h-16 rounded-full border-4 border-indigo-100 dark:border-indigo-600">
                    @else
                        <div class="w-16 h-16 rounded-full flex items-center justify-center bg-indigo-100 dark:bg-indigo-600">
                            <i class="bi bi-person text-indigo-600 dark:text-indigo-100 text-2xl"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Resumo Financeiro
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400">{{ $client->name }}</p>
                        @if($client->email)
                            <p class="text-sm text-gray-500 dark:text-gray-500">
                                <i class="bi bi-envelope mr-1"></i>{{ $client->email }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Resumo Financeiro -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-arrow-up-circle text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Enviado</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                            R$ {{ number_format($totalEnviado, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Faturas -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-receipt text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Faturas</p>
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                            R$ {{ number_format($totalFaturas, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Saldo Atual -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 {{ $saldoAtual >= 0 ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-lg flex items-center justify-center">
                        <i class="bi bi-wallet {{ $saldoAtual >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }} text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Atual</p>
                        <p class="text-2xl font-bold {{ $saldoAtual >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                            R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Categorias e Ações Rápidas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Gráfico de Categorias -->
            <div class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-pie-chart text-indigo-600 dark:text-indigo-400 mr-2"></i>
                    Gastos por Categoria
                </h3>
                @if(count($categories) > 0)
                    <div wire:ignore id="category-chart" class="w-full h-64"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                        <i class="bi bi-pie-chart text-4xl mb-2"></i>
                        <p>Nenhuma categoria com gastos encontrada</p>
                    </div>
                @endif
            </div>

            <!-- Ações Rápidas -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-lightning text-indigo-600 dark:text-indigo-400 mr-2"></i>
                    Ações Rápidas
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('clients.faturas', $client->id) }}" 
                       class="w-full flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors duration-200 group">
                        <div class="flex items-center">
                            <i class="bi bi-receipt text-blue-600 dark:text-blue-400 mr-3"></i>
                            <span class="text-blue-800 dark:text-blue-200 font-medium">Ver Todas as Faturas</span>
                        </div>
                        <i class="bi bi-arrow-right text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition-transform"></i>
                    </a>

                    <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'recebidas']) }}" 
                       class="w-full flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors duration-200 group">
                        <div class="flex items-center">
                            <i class="bi bi-arrow-down text-green-600 dark:text-green-400 mr-3"></i>
                            <span class="text-green-800 dark:text-green-200 font-medium">Transferências Recebidas</span>
                        </div>
                        <i class="bi bi-arrow-right text-green-600 dark:text-green-400 group-hover:translate-x-1 transition-transform"></i>
                    </a>

                    <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'enviadas']) }}" 
                       class="w-full flex items-center justify-between p-3 bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:hover:bg-orange-900/30 rounded-lg transition-colors duration-200 group">
                        <div class="flex items-center">
                            <i class="bi bi-arrow-up text-orange-600 dark:text-orange-400 mr-3"></i>
                            <span class="text-orange-800 dark:text-orange-200 font-medium">Transferências Enviadas</span>
                        </div>
                        <i class="bi bi-arrow-right text-orange-600 dark:text-orange-400 group-hover:translate-x-1 transition-transform"></i>
                    </a>

                    <a href="{{ route('clients.edit', $client->id) }}" 
                       class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200 group">
                        <div class="flex items-center">
                            <i class="bi bi-pencil text-gray-600 dark:text-gray-400 mr-3"></i>
                            <span class="text-gray-800 dark:text-gray-200 font-medium">Editar Cliente</span>
                        </div>
                        <i class="bi bi-arrow-right text-gray-600 dark:text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Cards com Lists Paginadas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Faturas -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700">
                <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <i class="bi bi-receipt text-indigo-600 dark:text-indigo-400 mr-2"></i>
                            Faturas ({{ $faturasTotal }})
                        </h3>
                        <a href="{{ route('clients.faturas', $client->id) }}" 
                           class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                            Ver todas →
                        </a>
                    </div>
                    
                    <!-- Paginação Faturas -->
                    @if($faturasTotal > $perPage)
                        <div class="flex items-center justify-center space-x-1 mt-4">
                            @php
                                $maxFaturasPage = ceil($faturasTotal / $perPage);
                            @endphp
                            
                            <!-- Primeira página -->
                            <button wire:click="$set('faturasPage', 1)" 
                                    class="px-2 py-1 text-sm {{ $faturasPage == 1 ? 'text-gray-400' : 'text-indigo-600 hover:text-indigo-800' }}">
                                &laquo;&laquo;
                            </button>
                            
                            <!-- Página anterior -->
                            <button wire:click="prevFaturasPage" 
                                    class="px-2 py-1 text-sm {{ $faturasPage == 1 ? 'text-gray-400' : 'text-indigo-600 hover:text-indigo-800' }}">
                                &laquo;
                            </button>
                            
                            <!-- Páginas numeradas -->
                            @for($i = max(1, $faturasPage - 2); $i <= min($maxFaturasPage, $faturasPage + 2); $i++)
                                <button wire:click="$set('faturasPage', {{ $i }})" 
                                        class="px-3 py-1 text-sm rounded {{ $i == $faturasPage ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-50' }}">
                                    {{ $i }}
                                </button>
                            @endfor
                            
                            <!-- Próxima página -->
                            <button wire:click="nextFaturasPage" 
                                    class="px-2 py-1 text-sm {{ $faturasPage == $maxFaturasPage ? 'text-gray-400' : 'text-indigo-600 hover:text-indigo-800' }}">
                                &raquo;
                            </button>
                            
                            <!-- Última página -->
                            <button wire:click="$set('faturasPage', {{ $maxFaturasPage }})" 
                                    class="px-2 py-1 text-sm {{ $faturasPage == $maxFaturasPage ? 'text-gray-400' : 'text-indigo-600 hover:text-indigo-800' }}">
                                &raquo;&raquo;
                            </button>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    @if($faturas && is_array($faturas) && count($faturas) > 0)
                        <div class="space-y-3">
                            @foreach($faturas as $fatura)
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-between">
                                        <!-- Informações da fatura -->
                                        <div class="flex items-center space-x-3 flex-1">
                                            @if($fatura['category'] && $fatura['category']['icone'])
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                                                    <i class="{{ $fatura['category']['icone'] }} text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[200]">{{ $fatura['description'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ \Carbon\Carbon::parse($fatura['invoice_date'])->format('d/m/Y') }}
                                                    @if($fatura['category'])
                                                        • {{ $fatura['category']['name'] }}
                                                    @endif
                                                </p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                                    R$ {{ number_format($fatura['dividida'] ? $fatura['value'] / 2 : $fatura['value'], 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Toggle dividida -->
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $fatura['dividida'] ? 'Dividida' : 'Integral' }}
                                            </span>
                                            <button wire:click="toggleDividida({{ $fatura['id'] }})"
                                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $fatura['dividida'] ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600' }}">
                                                <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform {{ $fatura['dividida'] ? 'translate-x-5' : 'translate-x-1' }}"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="bi bi-receipt text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma fatura encontrada</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Transferências Recebidas -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700">
                <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <i class="bi bi-arrow-down-circle text-green-600 dark:text-green-400 mr-2"></i>
                            Recebidas ({{ $recebidasTotal }})
                        </h3>
                        <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'recebidas']) }}" 
                           class="text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium">
                            Ver todas →
                        </a>
                    </div>
                    
                    <!-- Paginação Recebidas -->
                    @if($recebidasTotal > $perPage)
                        <div class="flex items-center justify-center space-x-1 mt-4">
                            @php
                                $maxRecebidasPage = ceil($recebidasTotal / $perPage);
                            @endphp
                            
                            <!-- Primeira página -->
                            <button wire:click="$set('recebidasPage', 1)" 
                                    class="px-2 py-1 text-sm {{ $recebidasPage == 1 ? 'text-gray-400' : 'text-green-600 hover:text-green-800' }}">
                                &laquo;&laquo;
                            </button>
                            
                            <!-- Página anterior -->
                            <button wire:click="prevRecebidasPage" 
                                    class="px-2 py-1 text-sm {{ $recebidasPage == 1 ? 'text-gray-400' : 'text-green-600 hover:text-green-800' }}">
                                &laquo;
                            </button>
                            
                            <!-- Páginas numeradas -->
                            @for($i = max(1, $recebidasPage - 2); $i <= min($maxRecebidasPage, $recebidasPage + 2); $i++)
                                <button wire:click="$set('recebidasPage', {{ $i }})" 
                                        class="px-3 py-1 text-sm rounded {{ $i == $recebidasPage ? 'bg-green-600 text-white' : 'text-green-600 hover:bg-green-50' }}">
                                    {{ $i }}
                                </button>
                            @endfor
                            
                            <!-- Próxima página -->
                            <button wire:click="nextRecebidasPage" 
                                    class="px-2 py-1 text-sm {{ $recebidasPage == $maxRecebidasPage ? 'text-gray-400' : 'text-green-600 hover:text-green-800' }}">
                                &raquo;
                            </button>
                            
                            <!-- Última página -->
                            <button wire:click="$set('recebidasPage', {{ $maxRecebidasPage }})" 
                                    class="px-2 py-1 text-sm {{ $recebidasPage == $maxRecebidasPage ? 'text-gray-400' : 'text-green-600 hover:text-green-800' }}">
                                &raquo;&raquo;
                            </button>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    @if($transferenciasRecebidas && is_array($transferenciasRecebidas) && count($transferenciasRecebidas) > 0)
                        <div class="space-y-3">
                            @foreach($transferenciasRecebidas as $transferencia)
                                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3 flex-1">
                                            <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                                                <i class="bi bi-arrow-down text-green-600 dark:text-green-400 text-sm"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[300px]">{{ $transferencia['description'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ \Carbon\Carbon::parse($transferencia['date'])->format('d/m/Y') }}
                                                </p>
                                                <p class="text-sm font-semibold text-green-600 dark:text-green-400 mt-1">
                                                    + R$ {{ number_format($transferencia['value'], 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="bi bi-arrow-down-circle text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma transferência recebida</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Transferências Enviadas -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700">
                <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <i class="bi bi-arrow-up-circle text-red-600 dark:text-red-400 mr-2"></i>
                            Enviadas ({{ $enviadasTotal }})
                        </h3>
                        <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'enviadas']) }}" 
                           class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">
                            Ver todas →
                        </a>
                    </div>
                    
                    <!-- Paginação Enviadas -->
                    @if($enviadasTotal > $perPage)
                        <div class="flex items-center justify-center space-x-1 mt-4">
                            @php
                                $maxEnviadasPage = ceil($enviadasTotal / $perPage);
                            @endphp
                            
                            <!-- Primeira página -->
                            <button wire:click="$set('enviadasPage', 1)" 
                                    class="px-2 py-1 text-sm {{ $enviadasPage == 1 ? 'text-gray-400' : 'text-red-600 hover:text-red-800' }}">
                                &laquo;&laquo;
                            </button>
                            
                            <!-- Página anterior -->
                            <button wire:click="prevEnviadasPage" 
                                    class="px-2 py-1 text-sm {{ $enviadasPage == 1 ? 'text-gray-400' : 'text-red-600 hover:text-red-800' }}">
                                &laquo;
                            </button>
                            
                            <!-- Páginas numeradas -->
                            @for($i = max(1, $enviadasPage - 2); $i <= min($maxEnviadasPage, $enviadasPage + 2); $i++)
                                <button wire:click="$set('enviadasPage', {{ $i }})" 
                                        class="px-3 py-1 text-sm rounded {{ $i == $enviadasPage ? 'bg-red-600 text-white' : 'text-red-600 hover:bg-red-50' }}">
                                    {{ $i }}
                                </button>
                            @endfor
                            
                            <!-- Próxima página -->
                            <button wire:click="nextEnviadasPage" 
                                    class="px-2 py-1 text-sm {{ $enviadasPage == $maxEnviadasPage ? 'text-gray-400' : 'text-red-600 hover:text-red-800' }}">
                                &raquo;
                            </button>
                            
                            <!-- Última página -->
                            <button wire:click="$set('enviadasPage', {{ $maxEnviadasPage }})" 
                                    class="px-2 py-1 text-sm {{ $enviadasPage == $maxEnviadasPage ? 'text-gray-400' : 'text-red-600 hover:text-red-800' }}">
                                &raquo;&raquo;
                            </button>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    @if($transferenciasEnviadas && is_array($transferenciasEnviadas) && count($transferenciasEnviadas) > 0)
                        <div class="space-y-3">
                            @foreach($transferenciasEnviadas as $transferencia)
                                <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3 flex-1">
                                            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                                                <i class="bi bi-arrow-up text-red-600 dark:text-red-400 text-sm"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[300px]">{{ $transferencia['description'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ \Carbon\Carbon::parse($transferencia['date'])->format('d/m/Y') }}
                                                </p>
                                                <p class="text-sm font-semibold text-red-600 dark:text-red-400 mt-1">
                                                    - R$ {{ number_format($transferencia['value'], 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="bi bi-arrow-up-circle text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma transferência enviada</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

       
    </div>

</div>
