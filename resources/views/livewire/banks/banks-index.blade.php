<div class=" min-h-screen">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Método de Pagamento</h1>
            <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800" data-modal-target="addCardModal" data-modal-toggle="addCardModal">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Adicionar Novo Cartão
            </button>
        </div>
    </div>

    <!-- Cards dos Bancos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        @foreach ($banks as $bank)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex flex-col items-center text-center">
                <img class="w-16 h-16 mb-4 rounded-lg" src="{{ $bank['caminho_icone'] }}" alt="{{ $bank['name'] }}">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ $bank['name'] }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $bank['description'] }}</p>

                <div class="flex space-x-2">
                    <a href="{{ route('invoices.index', ['bank_id' => $bank['id_bank']]) }}"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" title="Visualizar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </a>

                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:ring-4 focus:ring-gray-200 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-gray-700"
                        wire:click="openEditModal({{ $bank['id_bank'] }})" data-modal-target="editBankModal" data-modal-toggle="editBankModal" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>

                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                        wire:click="openDeleteModal({{ $bank['id_bank'] }})" data-modal-target="deleteBankModal" data-modal-toggle="deleteBankModal" title="Excluir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endforeach

        @if (empty($banks))
        <div class="col-span-full">
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 mb-4">
                    <svg class="w-full h-full text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Nenhum Banco ou Cartão Cadastrado</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                    Você ainda não cadastrou nenhum método de pagamento. Adicione um novo banco ou cartão para começar a gerenciar suas finanças!
                </p>
                <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800" data-modal-target="addCardModal" data-modal-toggle="addCardModal">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Adicionar Primeiro Cartão
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Seção de Transações -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 lg:mb-0">Suas Transações</h2>

                <div class="flex items-center justify-center space-x-4">
                    <button wire:click="previousMonth" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-blue-50 focus:ring-4 focus:ring-blue-300 dark:bg-gray-800 dark:text-blue-500 dark:border-blue-500 dark:hover:bg-gray-700 dark:focus:ring-blue-800">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Mês Anterior
                    </button>

                    <div class="flex items-center space-x-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                        </h3>
                    </div>

                    <button wire:click="nextMonth" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-blue-50 focus:ring-4 focus:ring-blue-300 dark:bg-gray-800 dark:text-blue-500 dark:border-blue-500 dark:hover:bg-gray-700 dark:focus:ring-blue-800">
                        Próximo Mês
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gray-900 text-white rounded-lg p-4">
                    <p class="text-sm text-gray-300 mb-1">Total do Mês:</p>
                    <h6 class="text-green-400 font-bold text-lg">
                        R$ {{ number_format($totalMonth, 2) }}
                    </h6>
                </div>
                <div class="bg-gray-900 text-white rounded-lg p-4">
                    <p class="text-sm text-gray-300 mb-1">Maior Fatura:</p>
                    <h6 class="text-red-400 font-bold text-lg">
                        R$ {{ $highestInvoice ? number_format($highestInvoice['value'], 2) : '0,00' }}
                    </h6>
                </div>
                <div class="bg-gray-900 text-white rounded-lg p-4">
                    <p class="text-sm text-gray-300 mb-1">Menor Fatura:</p>
                    <h6 class="text-yellow-400 font-bold text-lg">
                        R$ {{ $lowestInvoice ? number_format($lowestInvoice['value'], 2) : '0,00' }}
                    </h6>
                </div>
                <div class="bg-gray-900 text-white rounded-lg p-4">
                    <p class="text-sm text-gray-300 mb-1">Total de Transações:</p>
                    <h6 class="text-blue-400 font-bold text-lg">
                        {{ $totalTransactions }}
                    </h6>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Lista de Transações -->
                <div class="lg:col-span-2">
                    <div id="transactionsContainer">
                        @if(!empty($groupedInvoices))
                        @foreach($groupedInvoices as $date => $invoices)
                        <div class="mb-6">
                            <h6 class="text-blue-600 font-semibold mb-3 text-lg">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h6>
                            @foreach($invoices as $invoice)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-3 border border-gray-200 dark:border-gray-600">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <strong class="text-gray-900 dark:text-white">{{ $invoice['description'] }}</strong>
                                        <br>
                                        <small class="text-gray-500 dark:text-gray-400">{{ $invoice['bank']['name'] ?? 'N/A' }}</small>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice['value'] > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                            R$ {{ number_format($invoice['value'], 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                        @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">Nenhuma transação encontrada para este mês.</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="lg:col-span-1">
                    <div class="space-y-6">
                        @if(!empty($groupedInvoices))
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <canvas id="updateCategoryChart" class="w-full h-64"></canvas>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <canvas id="lineChart" class="w-full h-64"></canvas>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">Sem dados para exibir no gráfico.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir o modal de criação -->
    @livewire('banks.create-bank')

    <!-- Incluir modais de edição e exclusão -->
    @include('livewire.banks.edit-bank')
    @include('livewire.banks.delet-bank')


</div>