<div class="flex flex-col">
    <div class="flex flex-col lg:flex-row gap-4">
        <!-- Calendário (1/4) -->
        <div class="w-full lg:w-1/4 flex flex-col">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-2">
                <!-- Navegação do Calendário com selects e botões -->
                <div class="flex items-center justify-between mb-4">
                    <button wire:click="previousMonth"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <div class="flex items-center space-x-2">
                        <select wire:model="month" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white text-xs">
                            @foreach(range(1,12) as $m)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->isoFormat('MMMM') }}</option>
                            @endforeach
                        </select>
                        <select wire:model="year" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white text-xs">
                            @foreach(range(now()->year-5, now()->year+2) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button wire:click="nextMonth"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                <!-- Cabeçalho do calendário -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-1">D</div>
                    <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-1">S</div>
                    <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-1">T</div>
                    <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-1">Q</div>
                    <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-1">Q</div>
                    <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-1">S</div>
                    <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-1">S</div>
                </div>
                <!-- Dias do calendário -->
                <div class="grid grid-cols-7 gap-1">
                    @foreach($calendarDays as $day)
                    <div wire:click="selectDate('{{ $day['date'] }}')"
                        class="relative min-h-[32px] p-1 border border-gray-200 dark:border-gray-600 rounded cursor-pointer transition-all duration-200 {{ $day['isCurrentMonth'] ? 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' : 'bg-gray-50 dark:bg-gray-700' }} {{ $day['isToday'] ? 'ring-1 ring-blue-500' : '' }} {{ $selectedDate === $day['date'] ? 'ring-1 ring-green-500 bg-green-50 dark:bg-green-900/20' : '' }}">
                        <div class="text-xs font-medium {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }} {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }} {{ $selectedDate === $day['date'] ? 'text-green-600 dark:text-green-400' : '' }}">
                            {{ $day['day'] }}
                        </div>
                        @if(!empty($day['invoices']))
                        <div class="w-1 h-1 bg-blue-500 rounded-full mx-auto mt-1"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if($selectedDate)
                <div class="mt-3">
                    <button wire:click="clearDateSelection"
                        class="w-full text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/20 rounded p-2">
                        Limpar Filtro
                    </button>
                </div>
                @endif
            </div>
            <!-- Informações embaixo do calendário -->
            <div class="mt-2 space-y-2">
            <div class="flex items-center bg-gray-50 dark:bg-gray-700 rounded-xl p-4 gap-4 shadow-sm">
            <span class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 7v9"/></svg>
            </span>
            <div>
            <p class="text-base text-gray-600 dark:text-gray-300 font-semibold">Total do Mês</p>
            <p class="text-lg font-bold text-green-600 dark:text-green-400">R$ {{ number_format($totalMonth, 2) }}</p>
            </div>
            </div>
            <div class="flex items-center bg-gray-50 dark:bg-gray-700 rounded-xl p-4 gap-4 shadow-sm">
            <span class="flex items-center justify-center w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"/></svg>
            </span>
            <div>
            <p class="text-base text-gray-600 dark:text-gray-300 font-semibold">Maior Fatura</p>
            <p class="text-lg font-bold text-red-600 dark:text-red-400">R$ {{ $highestInvoice ? number_format($highestInvoice['value'], 2) : '0,00' }}</p>
            </div>
            </div>
            <div class="flex items-center bg-gray-50 dark:bg-gray-700 rounded-xl p-4 gap-4 shadow-sm">
            <span class="flex items-center justify-center w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
            </span>
            <div>
            <p class="text-base text-gray-600 dark:text-gray-300 font-semibold">Menor Fatura</p>
            <p class="text-lg font-bold text-yellow-600 dark:text-yellow-400">R$ {{ $lowestInvoice ? number_format($lowestInvoice['value'], 2) : '0,00' }}</p>
            </div>
            </div>
            <div class="flex items-center bg-gray-50 dark:bg-gray-700 rounded-xl p-4 gap-4 shadow-sm">
            <span class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </span>
            <div>
            <p class="text-base text-gray-600 dark:text-gray-300 font-semibold">Total de Transações</p>
            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $totalTransactions }}</p>
            </div>
            </div>
            </div>
            <!-- Gráfico Pie ApexCharts -->
            <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Gastos por Categoria</h4>
                <div wire:ignore id="pie-category-chart" class="w-full h-64"></div>
            </div>
        </div>
        <!-- Invoices (meio, 2/4) -->
        <div class="w-full lg:w-2/4 flex flex-col">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex flex-col h-full">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    @if($selectedDate)
                    Transações de {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                    @else
                    Transações do Mês
                    @endif
                </h2>
                <div id="transactionsContainer" class="flex-grow overflow-y-auto pr-2 max-h-[800px] grid grid-cols-1 xl:grid-cols-2 gap-4">
                    @if($selectedDate)
                    @if(isset($calendarInvoices[$selectedDate]))
                    @foreach($calendarInvoices[$selectedDate] as $invoice)
                    <a href="#" class="group block">
                        <div class="relative bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.02] overflow-hidden">
                            <div class="absolute left-0 top-0 h-full w-1.5 {{ $invoice['value'] > 0 ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            <div class="flex justify-between items-start w-full ml-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center {{ $invoice['value'] > 0 ? 'bg-green-100 dark:bg-green-900/50' : 'bg-red-100 dark:bg-red-900/50' }}">
                                        @if(isset($invoice['category']) && $invoice['category']['icon'])
                                        @if(Str::startsWith($invoice['category']['icon'], 'icons8-'))
                                        <span class="w-6 h-6 {{ $invoice['category']['icon'] }}"></span>
                                        @else
                                        <img class="w-6 h-6" src="{{ $invoice['category']['icon'] }}" alt="{{ $invoice['category']['name'] ?? 'Categoria' }}">
                                        @endif
                                        @else
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate" title="{{ $invoice['description'] }}">{{ $invoice['description'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice['category']['name'] ?? 'Sem Categoria' }} &middot; {{ $invoice['bank']['name'] ?? 'N/A' }} &middot; {{ \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') }}</p>
                                        <span class="font-bold text-base {{ $invoice['value'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $invoice['value'] > 0 ? '+' : '' }} R$ {{ number_format(abs($invoice['value']), 2, ',', '.') }}
                                        </span>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                    @else
                    <div class="text-center py-8 lg:col-span-2">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">Nenhuma transação encontrada para {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}.</p>
                    </div>
                    @endif
                    @else
                    @if(!empty($allInvoices))
                    @foreach($allInvoices as $invoice)
                    <a href="#" class="group block">
                        <div class="relative bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.02] overflow-hidden">
                            <div class="absolute left-0 top-0 h-full w-1.5 {{ $invoice['value'] > 0 ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            <div class="flex justify-between items-start w-full ml-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center {{ $invoice['value'] > 0 ? 'bg-green-100 dark:bg-green-900/50' : 'bg-red-100 dark:bg-red-900/50' }}">
                                        @if(isset($invoice['category']) && $invoice['category']['icon'])
                                        @if(Str::startsWith($invoice['category']['icon'], 'icons8-'))
                                        <span class="w-6 h-6 {{ $invoice['category']['icon'] }}"></span>
                                        @else
                                        <img class="w-6 h-6" src="{{ $invoice['category']['icon'] }}" alt="{{ $invoice['category']['name'] ?? 'Categoria' }}">
                                        @endif
                                        @else
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate" title="{{ $invoice['description'] }}">{{ $invoice['description'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice['category']['name'] ?? 'Sem Categoria' }} &middot; {{ $invoice['bank']['name'] ?? 'N/A' }} &middot; {{ \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') }}</p>
                                        <span class="font-bold text-base {{ $invoice['value'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $invoice['value'] > 0 ? '+' : '' }} R$ {{ number_format(abs($invoice['value']), 2, ',', '.') }}
                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                    @else
                    <div class="text-center py-8 lg:col-span-2">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">Nenhuma transação encontrada para este mês.</p>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        <!-- Cards de banco (1/4) -->
        <div class="w-full lg:w-1/4">
            <!-- Cards dos Bancos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Meus Cartões</h3>
                    <a href="{{ route('banks.create') }}"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Novo
                    </a>
                </div>
                <div class="space-y-4">
                    @foreach ($paginatedBanks as $bank)
                    @php
                    $cardColors = [
                    'inter' => 'from-orange-500 via-orange-600 to-orange-700',
                    'nubank' => 'from-purple-500 via-purple-600 to-purple-700',
                    'santander' => 'from-red-500 via-red-600 to-red-700',
                    'caixa' => 'from-blue-500 via-blue-600 to-blue-700',
                    'bb' => 'from-yellow-400 via-yellow-500 to-yellow-600',
                    'itau' => 'from-blue-600 via-blue-700 to-blue-800',
                    'bradesco' => 'from-red-600 via-red-700 to-red-800'
                    ];
                    $cardColor = 'from-gray-900 via-gray-800 to-gray-900';
                    foreach ($cardColors as $bankName => $color) {
                    if (stripos($bank->name, $bankName) !== false || stripos($bank->caminho_icone, $bankName) !== false) {
                    $cardColor = $color;
                    break;
                    }
                    }
                    @endphp
                    <div class="group relative">
                        <div class="relative bg-gradient-to-br {{ $cardColor }} rounded-xl shadow-lg border border-gray-700 dark:border-gray-600 p-4 h-32 transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl">
                            <div class="absolute inset-0 bg-gradient-to-br from-transparent via-white/5 to-transparent rounded-xl"></div>
                            <div class="absolute top-3 right-3">
                                <div class="w-12 h-12 rounded-lg bg-white/20 dark:bg-black/30 p-2 flex items-center justify-center">
                                    <img class="w-8 h-8" src="{{ asset($bank->caminho_icone) }}" alt="{{ $bank->name }}">
                                </div>
                            </div>
                            <div class="absolute top-8 left-3">
                                <div class="w-8 h-6 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-md flex items-center justify-center">
                                    <div class="w-5 h-3 bg-yellow-300 rounded-sm"></div>
                                </div>
                            </div>
                            <div class="absolute top-8 left-16">
                                <div class="text-white/80 text-xs font-mono tracking-wider">
                                    •••• {{ preg_replace('/[^0-9]/', '', $bank->description) ? substr(preg_replace('/[^0-9]/', '', $bank->description), -4) : '1234' }}
                                </div>
                            </div>
                            <div class="absolute bottom-8 left-3">
                                <h3 class="text-white font-semibold text-sm">{{ $bank->name }}</h3>
                            </div>
                            <div class="absolute bottom-3 right-3">
                                <div class="text-white/60 text-xs font-semibold">VISA</div>
                            </div>
                        </div>
                        <div class="absolute inset-0 bg-black/50 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center space-x-1">
                            <a href="{{ route('invoices.index', ['bank_id' => $bank->id_bank]) }}"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-blue-600/90 backdrop-blur-sm rounded hover:bg-blue-700 transition-colors duration-200"
                                title="Visualizar">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('banks.edit', $bank->id_bank) }}"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-gray-600/90 backdrop-blur-sm rounded hover:bg-gray-700 transition-colors duration-200"
                                title="Editar">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-red-600/90 backdrop-blur-sm rounded hover:bg-red-700 transition-colors duration-200"
                                wire:click="openDeleteModal({{ $bank->id_bank }})" data-modal-target="deleteBankModal" data-modal-toggle="deleteBankModal" title="Excluir">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @if ($paginatedBanks->isEmpty())
                    <div class="text-center py-8">
                        <div class="mx-auto w-16 h-16 mb-4">
                            <svg class="w-full h-full text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Nenhum Cartão</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mb-4">
                            Adicione seu primeiro cartão para começar!
                        </p>
                        <a href="{{ route('banks.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded text-xs px-3 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Adicionar
                        </a>
                    </div>
                    @endif
                    @if($paginatedBanks->hasPages())
                    <div class="mt-4 flex justify-center space-x-2">
                    @if($paginatedBanks->onFirstPage())
                    <button class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-500 cursor-not-allowed" disabled>&laquo;</button>
                    @else
                    <button class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700" wire:click="goToPage({{ $paginatedBanks->currentPage() - 1 }})">&laquo;</button>
                    @endif
                    @for($i = 1; $i <= $paginatedBanks->lastPage(); $i++)
                    <button class="px-3 py-1 rounded {{ $paginatedBanks->currentPage() === $i ? 'bg-blue-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white' }}"
                    wire:click="goToPage({{ $i }})">{{ $i }}</button>
                    @endfor
                    @if($paginatedBanks->hasMorePages())
                    <button class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700" wire:click="goToPage({{ $paginatedBanks->currentPage() + 1 }})">&raquo;</button>
                    @else
                    <button class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-500 cursor-not-allowed" disabled>&raquo;</button>
                    @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('livewire.banks.delete-bank', ['showDeleteModal' => $showDeleteModal])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let chart = null;

            function renderPieChart(data) {
                // Sempre busca o elemento atualizado do DOM
                const chartDiv = document.querySelector("#pie-category-chart");
                if (!chartDiv) return;

                // Sempre destrói o gráfico anterior antes de criar um novo
                if (chart) {
                    chart.destroy();
                    chart = null;
                }

                const categories = Array.isArray(data) ? data.map(item => item.name) : [];
                const values = Array.isArray(data) ? data.map(item => parseFloat(item.total)) : [];

                if (values.length === 0 || values.every(v => v === 0)) {
                    chartDiv.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full py-8 text-gray-400">
                            <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2M8 17V9a4 4 0 118 0v8M12 13v4" />
                            </svg>
                            <span class="text-base font-medium">Nenhum dado para exibir</span>
                        </div>
                    `;
                    return;
                }

                chartDiv.innerHTML = '';
                const options = {
                    series: values,
                    chart: {
                        type: 'donut',
                        height: 250
                    },
                    labels: categories,
                    legend: {
                        show: false
                    },
                    colors: ['#34d399', '#fbbf24', '#f87171', '#60a5fa', '#a78bfa', '#f472b6', '#facc15', '#38bdf8'],
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            const seriesValue = opts.w.globals.series[opts.seriesIndex];
                            return 'R$ ' + seriesValue.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            }
                        }
                    }]
                };
                chart = new ApexCharts(chartDiv, options);
                chart.render();
            }

            // Inicializa com os dados do backend
            renderPieChart(@json($pieData ?? []));

            // Atualiza quando o Livewire emitir o evento
            if (window.Livewire) {
                Livewire.on('update-pie-chart', ({ data }) => {
                    renderPieChart(data);
                });
            }
        });
    </script>


</div>