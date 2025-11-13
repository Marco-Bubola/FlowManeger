<div class="w-full">

    <!-- Header Moderno -->
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-indigo-50/90 to-purple-50/80 dark:from-slate-800/90 dark:via-indigo-900/30 dark:to-purple-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
        <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-indigo-400/20 via-purple-400/20 to-pink-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-blue-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

        <div class="relative px-6 py-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div class="flex items-center gap-4">
                    <!-- Botão Voltar -->
                    <a href="{{ route('clients.index') }}"
                       class="group relative inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-white to-indigo-50 dark:from-slate-800 dark:to-slate-700 hover:from-indigo-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-md hover:shadow-lg border border-white/50 dark:border-slate-600/50">
                        <i class="fas fa-arrow-left text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform duration-200"></i>
                    </a>

                    <!-- Avatar e Info -->
                    <div class="relative">
                        @if($client->caminho_foto)
                            <div class="w-16 h-16 rounded-xl overflow-hidden shadow-lg ring-4 ring-white/50 dark:ring-slate-700/50">
                                <img src="{{ $client->caminho_foto }}"
                                     alt="{{ $client->name }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-xl shadow-lg flex items-center justify-center ring-4 ring-white/50 dark:ring-slate-700/50">
                                <i class="fas fa-user text-white text-2xl"></i>
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                            </div>
                        @endif
                        <!-- Badge Online -->
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white dark:border-slate-800 shadow-sm"></div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-1">
                            <a href="{{ route('clients.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <i class="fas fa-users mr-1"></i>Clientes
                            </a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Resumo Financeiro</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-slate-100">
                            {{ $client->name }}
                        </h1>
                        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                            @if($client->email)
                                <span class="flex items-center">
                                    <i class="fas fa-envelope mr-1"></i>{{ $client->email }}
                                </span>
                            @endif
                            @if($client->phone)
                                <span class="flex items-center">
                                    <i class="fas fa-phone mr-1"></i>{{ $client->phone }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('clients.edit', $client->id) }}"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg transition-all duration-200 font-semibold shadow-md hover:shadow-lg">
                        <i class="fas fa-edit mr-2"></i>
                        Editar Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>

        <!-- Cards de Resumo Financeiro (linha única, mais compactos) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Recebido -->
            <div class="group relative bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-3 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-14 h-14 bg-green-400/10 rounded-full transform translate-x-5 -translate-y-5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                                <i class="fas fa-arrow-down text-white text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-green-800 dark:text-green-300 truncate">Total Recebido</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <p class="text-base font-bold text-green-700 dark:text-green-400 whitespace-nowrap">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</p>
                            <span class="text-xs font-semibold text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 px-2 py-0.5 rounded-full">+{{ number_format((($totalRecebido / max($totalRecebido + $totalEnviado, 1)) * 100), 0) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Enviado -->
            <div class="group relative bg-gradient-to-br from-red-50 to-rose-100 dark:from-red-900/20 dark:to-rose-900/30 rounded-xl shadow-lg border border-red-200 dark:border-red-800 p-3 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-14 h-14 bg-red-400/10 rounded-full transform translate-x-5 -translate-y-5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                                <i class="fas fa-arrow-up text-white text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-red-800 dark:text-red-300 truncate">Total Enviado</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <p class="text-base font-bold text-red-700 dark:text-red-400 whitespace-nowrap">R$ {{ number_format($totalEnviado, 2, ',', '.') }}</p>
                            <span class="text-xs font-semibold text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 px-2 py-0.5 rounded-full">{{ number_format((($totalEnviado / max($totalRecebido + $totalEnviado, 1)) * 100), 0) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Faturas -->
            <div class="group relative bg-gradient-to-br from-orange-50 to-amber-100 dark:from-orange-900/20 dark:to-amber-900/30 rounded-xl shadow-lg border border-orange-200 dark:border-orange-800 p-3 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-14 h-14 bg-orange-400/10 rounded-full transform translate-x-5 -translate-y-5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                                <i class="fas fa-file-invoice-dollar text-white text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-orange-800 dark:text-orange-300 truncate">Total Faturas</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <p class="text-base font-bold text-orange-700 dark:text-orange-400 whitespace-nowrap">R$ {{ number_format($totalFaturas, 2, ',', '.') }}</p>
                            <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30 px-2 py-0.5 rounded-full"><i class="fas fa-receipt"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Saldo Atual -->
            <div class="group relative bg-gradient-to-br from-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-50 to-{{ $saldoAtual >= 0 ? 'indigo' : 'rose' }}-100 dark:from-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-900/20 dark:to-{{ $saldoAtual >= 0 ? 'indigo' : 'rose' }}-900/30 rounded-xl shadow-lg border border-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-200 dark:border-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-800 p-3 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-14 h-14 bg-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-400/10 rounded-full transform translate-x-5 -translate-y-5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 bg-gradient-to-br from-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-500 to-{{ $saldoAtual >= 0 ? 'indigo' : 'rose' }}-600 rounded-lg flex items-center justify-center shadow-md shadow-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-500/25 group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                                <i class="fas fa-wallet text-white text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-800 dark:text-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-300 truncate">Saldo Atual</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <p class="text-base font-bold text-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-700 dark:text-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-400 whitespace-nowrap">R$ {{ number_format($saldoAtual, 2, ',', '.') }}</p>
                            <span class="text-xs font-semibold text-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-600 dark:text-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-400 bg-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-100 dark:bg-{{ $saldoAtual >= 0 ? 'blue' : 'red' }}-900/30 px-2 py-0.5 rounded-full">{{ $saldoAtual >= 0 ? 'Positivo' : 'Negativo' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Cards com Lists Paginadas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Faturas -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-file-invoice mr-2"></i>
                            Faturas ({{ $faturasTotal }})
                        </h3>
                        <a href="{{ route('clients.faturas', $client->id) }}"
                           class="text-sm text-blue-100 hover:text-white font-medium transition-colors group flex items-center">
                            Ver todas
                            <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition-transform duration-200"></i>
                        </a>
                    </div>

                    <!-- Paginação Faturas -->
                    @if($faturasTotal > $perPage)
                        <div class="flex items-center justify-center space-x-1 mt-3">
                            @php
                                $maxFaturasPage = ceil($faturasTotal / $perPage);
                            @endphp

                            <!-- Primeira página -->
                            <button wire:click="$set('faturasPage', 1)"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $faturasPage == 1 ? 'text-blue-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-double-left"></i>
                            </button>

                            <!-- Página anterior -->
                            <button wire:click="prevFaturasPage"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $faturasPage == 1 ? 'text-blue-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-left"></i>
                            </button>

                            <!-- Páginas numeradas -->
                            @for($i = max(1, $faturasPage - 2); $i <= min($maxFaturasPage, $faturasPage + 2); $i++)
                                <button wire:click="$set('faturasPage', {{ $i }})"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-semibold transition-all duration-200 {{ $i == $faturasPage ? 'bg-white text-indigo-600 shadow-md' : 'text-white hover:bg-white/20' }}">
                                    {{ $i }}
                                </button>
                            @endfor

                            <!-- Próxima página -->
                            <button wire:click="nextFaturasPage"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $faturasPage == $maxFaturasPage ? 'text-blue-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-right"></i>
                            </button>

                            <!-- Última página -->
                            <button wire:click="$set('faturasPage', {{ $maxFaturasPage }})"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $faturasPage == $maxFaturasPage ? 'text-blue-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    @if($faturas && is_array($faturas) && count($faturas) > 0)
                        <div class="space-y-3">
                            @foreach($faturas as $fatura)
                                <div class="group p-4 bg-gradient-to-br from-slate-50 to-gray-50 dark:from-slate-700 dark:to-slate-600 rounded-xl border border-slate-200 dark:border-slate-600 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-center justify-between gap-3">
                                        <!-- Informações da fatura -->
                                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                                            @if($fatura['category'] && $fatura['category']['icone'])
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-200">
                                                    <i class="{{ $fatura['category']['icone'] }} text-white"></i>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $fatura['description'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($fatura['invoice_date'])->format('d/m/Y') }}
                                                    @if($fatura['category'])
                                                        <span class="mx-1">•</span>
                                                        {{ $fatura['category']['name'] }}
                                                    @endif
                                                </p>
                                                <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                                                    R$ {{ number_format($fatura['dividida'] ? $fatura['value'] / 2 : $fatura['value'], 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Toggle dividida -->
                                        <div class="flex flex-col items-end space-y-2 flex-shrink-0">
                                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                                                {{ $fatura['dividida'] ? 'Dividida' : 'Integral' }}
                                            </span>
                                            <button wire:click="toggleDividida({{ $fatura['id'] }})"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-sm {{ $fatura['dividida'] ? 'bg-gradient-to-r from-indigo-500 to-purple-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform {{ $fatura['dividida'] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400 dark:text-slate-500">
                            <i class="fas fa-file-invoice text-5xl mb-3 opacity-30"></i>
                            <p class="text-sm font-medium">Nenhuma fatura encontrada</p>
                            <p class="text-xs mt-1">As faturas aparecerão aqui</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Transferências Recebidas -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 dark:from-green-600 dark:to-emerald-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-arrow-down mr-2"></i>
                            Recebidas ({{ $recebidasTotal }})
                        </h3>
                        <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'recebidas']) }}"
                           class="text-sm text-green-100 hover:text-white font-medium transition-colors group flex items-center">
                            Ver todas
                            <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition-transform duration-200"></i>
                        </a>
                    </div>

                    <!-- Paginação Recebidas -->
                    @if($recebidasTotal > $perPage)
                        <div class="flex items-center justify-center space-x-1 mt-3">
                            @php
                                $maxRecebidasPage = ceil($recebidasTotal / $perPage);
                            @endphp

                            <!-- Primeira página -->
                            <button wire:click="$set('recebidasPage', 1)"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $recebidasPage == 1 ? 'text-green-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-double-left"></i>
                            </button>

                            <!-- Página anterior -->
                            <button wire:click="prevRecebidasPage"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $recebidasPage == 1 ? 'text-green-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-left"></i>
                            </button>

                            <!-- Páginas numeradas -->
                            @for($i = max(1, $recebidasPage - 2); $i <= min($maxRecebidasPage, $recebidasPage + 2); $i++)
                                <button wire:click="$set('recebidasPage', {{ $i }})"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-semibold transition-all duration-200 {{ $i == $recebidasPage ? 'bg-white text-emerald-600 shadow-md' : 'text-white hover:bg-white/20' }}">
                                    {{ $i }}
                                </button>
                            @endfor

                            <!-- Próxima página -->
                            <button wire:click="nextRecebidasPage"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $recebidasPage == $maxRecebidasPage ? 'text-green-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-right"></i>
                            </button>

                            <!-- Última página -->
                            <button wire:click="$set('recebidasPage', {{ $maxRecebidasPage }})"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $recebidasPage == $maxRecebidasPage ? 'text-green-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    @if($transferenciasRecebidas && is_array($transferenciasRecebidas) && count($transferenciasRecebidas) > 0)
                        <div class="space-y-3">
                            @foreach($transferenciasRecebidas as $transferencia)
                                <div class="group p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl border border-green-200 dark:border-green-800 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3 flex-1">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform duration-200">
                                                <i class="fas fa-arrow-down text-white"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $transferencia['description'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($transferencia['date'])->format('d/m/Y') }}
                                                </p>
                                                <p class="text-sm font-bold text-green-600 dark:text-green-400 mt-1 flex items-center">
                                                    <i class="fas fa-plus mr-1"></i>
                                                    R$ {{ number_format($transferencia['value'], 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400 dark:text-slate-500">
                            <i class="fas fa-arrow-down text-5xl mb-3 opacity-30"></i>
                            <p class="text-sm font-medium">Nenhuma transferência recebida</p>
                            <p class="text-xs mt-1">As transferências aparecerão aqui</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Transferências Enviadas -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-rose-600 dark:from-red-600 dark:to-rose-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-arrow-up mr-2"></i>
                            Enviadas ({{ $enviadasTotal }})
                        </h3>
                        <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'enviadas']) }}"
                           class="text-sm text-red-100 hover:text-white font-medium transition-colors group flex items-center">
                            Ver todas
                            <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition-transform duration-200"></i>
                        </a>
                    </div>

                    <!-- Paginação Enviadas -->
                    @if($enviadasTotal > $perPage)
                        <div class="flex items-center justify-center space-x-1 mt-3">
                            @php
                                $maxEnviadasPage = ceil($enviadasTotal / $perPage);
                            @endphp

                            <!-- Primeira página -->
                            <button wire:click="$set('enviadasPage', 1)"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $enviadasPage == 1 ? 'text-red-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-double-left"></i>
                            </button>

                            <!-- Página anterior -->
                            <button wire:click="prevEnviadasPage"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $enviadasPage == 1 ? 'text-red-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-left"></i>
                            </button>

                            <!-- Páginas numeradas -->
                            @for($i = max(1, $enviadasPage - 2); $i <= min($maxEnviadasPage, $enviadasPage + 2); $i++)
                                <button wire:click="$set('enviadasPage', {{ $i }})"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-semibold transition-all duration-200 {{ $i == $enviadasPage ? 'bg-white text-rose-600 shadow-md' : 'text-white hover:bg-white/20' }}">
                                    {{ $i }}
                                </button>
                            @endfor

                            <!-- Próxima página -->
                            <button wire:click="nextEnviadasPage"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $enviadasPage == $maxEnviadasPage ? 'text-red-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-right"></i>
                            </button>

                            <!-- Última página -->
                            <button wire:click="$set('enviadasPage', {{ $maxEnviadasPage }})"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-all duration-200 {{ $enviadasPage == $maxEnviadasPage ? 'text-red-300 cursor-not-allowed' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    @if($transferenciasEnviadas && is_array($transferenciasEnviadas) && count($transferenciasEnviadas) > 0)
                        <div class="space-y-3">
                            @foreach($transferenciasEnviadas as $transferencia)
                                <div class="group p-4 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/30 rounded-xl border border-red-200 dark:border-red-800 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3 flex-1">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform duration-200">
                                                <i class="fas fa-arrow-up text-white"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $transferencia['description'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($transferencia['date'])->format('d/m/Y') }}
                                                </p>
                                                <p class="text-sm font-bold text-red-600 dark:text-red-400 mt-1 flex items-center">
                                                    <i class="fas fa-minus mr-1"></i>
                                                    R$ {{ number_format($transferencia['value'], 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400 dark:text-slate-500">
                            <i class="fas fa-arrow-up text-5xl mb-3 opacity-30"></i>
                            <p class="text-sm font-medium">Nenhuma transferência enviada</p>
                            <p class="text-xs mt-1">As transferências aparecerão aqui</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Gráfico de Categorias e Ações Rápidas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <!-- Gráfico de Categorias -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 dark:from-indigo-600 dark:to-purple-700 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Gastos por Categoria
                    </h3>
                    <p class="text-indigo-100 text-sm mt-1">Análise detalhada dos seus gastos</p>
                </div>
                <div class="p-6">
                    @if(count($categories) > 0)
                        <div wire:ignore id="category-chart" class="w-full h-64"></div>
                    @else
                        <div class="flex flex-col items-center justify-center h-64 text-slate-400 dark:text-slate-500">
                            <i class="fas fa-chart-pie text-6xl mb-4 opacity-30"></i>
                            <p class="text-lg font-medium">Nenhuma categoria com gastos</p>
                            <p class="text-sm">Os dados aparecerão quando houver transações</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-bolt mr-2"></i>
                        Ações Rápidas
                    </h3>
                    <p class="text-emerald-100 text-sm mt-1">Acesso rápido às funcionalidades</p>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('clients.faturas', $client->id) }}"
                       class="group flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/30 hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-900/30 dark:hover:to-indigo-900/40 rounded-xl border border-blue-200 dark:border-blue-800 transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-file-invoice text-white"></i>
                            </div>
                            <span class="ml-3 text-blue-800 dark:text-blue-200 font-semibold">Ver Todas as Faturas</span>
                        </div>
                        <i class="fas fa-arrow-right text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition-transform duration-200"></i>
                    </a>

                    <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'recebidas']) }}"
                       class="group flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/30 hover:from-green-100 hover:to-emerald-100 dark:hover:from-green-900/30 dark:hover:to-emerald-900/40 rounded-xl border border-green-200 dark:border-green-800 transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-arrow-down text-white"></i>
                            </div>
                            <span class="ml-3 text-green-800 dark:text-green-200 font-semibold">Transferências Recebidas</span>
                        </div>
                        <i class="fas fa-arrow-right text-green-600 dark:text-green-400 group-hover:translate-x-1 transition-transform duration-200"></i>
                    </a>

                    <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'enviadas']) }}"
                       class="group flex items-center justify-between p-4 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/30 hover:from-orange-100 hover:to-amber-100 dark:hover:from-orange-900/30 dark:hover:to-amber-900/40 rounded-xl border border-orange-200 dark:border-orange-800 transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-arrow-up text-white"></i>
                            </div>
                            <span class="ml-3 text-orange-800 dark:text-orange-200 font-semibold">Transferências Enviadas</span>
                        </div>
                        <i class="fas fa-arrow-right text-orange-600 dark:text-orange-400 group-hover:translate-x-1 transition-transform duration-200"></i>
                    </a>

                    <a href="{{ route('clients.edit', $client->id) }}"
                       class="group flex items-center justify-between p-4 bg-gradient-to-r from-slate-50 to-gray-100 dark:from-slate-700 dark:to-slate-600 hover:from-slate-100 hover:to-gray-200 dark:hover:from-slate-600 dark:hover:to-slate-500 rounded-xl border border-slate-200 dark:border-slate-600 transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-slate-500 to-gray-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-edit text-white"></i>
                            </div>
                            <span class="ml-3 text-slate-800 dark:text-slate-200 font-semibold">Editar Cliente</span>
                        </div>
                        <i class="fas fa-arrow-right text-slate-600 dark:text-slate-400 group-hover:translate-x-1 transition-transform duration-200"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
