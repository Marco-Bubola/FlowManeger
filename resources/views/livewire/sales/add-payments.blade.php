<div class="w-full">
    <!-- Header modernizado usando o mesmo padrão do sales-header -->
    <x-add-payments-header :sale="$sale" />

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Container principal -->
    <div class="container mx-auto px-6 py-8">
        <div class="mx-auto">
            <!-- Componente de Resumo da Venda -->
            <x-sale-summary-header :sale="$sale" :remainingAmount="$this->remainingAmount" />

        <!-- Formulário de Pagamentos -->
        <form wire:submit.prevent="addPayments" class="space-y-6">
            <!-- Cabeçalho da Seção de Pagamentos -->
            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl shadow-lg">
                            <i class="bi bi-wallet2 text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Adicionar Pagamentos
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Configure os pagamentos para esta venda
                            </p>
                        </div>
                    </div>
                    <button type="button"
                            wire:click="addPaymentRow"
                            class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-plus-lg"></i>
                        Adicionar Mais
                    </button>
                </div>
            </div>

            <!-- Grid de Cartões de Pagamento -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                @foreach($payments as $index => $payment)
                    <x-payment-form-card
                        :index="$index"
                        :payment="$payment"
                        :showRemove="count($payments) > 1" />
                @endforeach
            </div>

            <!-- Resumo dos Pagamentos -->
            @if(count($payments) > 0)
                <div class="bg-gradient-to-r from-blue-50 via-white to-indigo-50 dark:from-blue-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 rounded-2xl p-6 shadow-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-blue-500/10 rounded-xl">
                            <i class="bi bi-calculator text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                            Resumo dos Pagamentos
                        </h4>
                    </div>

                    @php
                        $totalPayments = collect($payments)->sum(function($payment) {
                            return is_numeric($payment['amount_paid'] ?? 0) ? (float)$payment['amount_paid'] : 0;
                        });
                        $newRemainingAmount = $this->remainingAmount - $totalPayments;
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-white/60 dark:bg-zinc-800/60 rounded-xl">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total dos Pagamentos</p>
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                R$ {{ number_format($totalPayments, 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-center p-4 bg-white/60 dark:bg-zinc-800/60 rounded-xl">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Valor Restante</p>
                            <p class="text-xl font-bold {{ $newRemainingAmount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                R$ {{ number_format($newRemainingAmount, 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-center p-4 bg-white/60 dark:bg-zinc-800/60 rounded-xl">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</p>
                            <p class="text-sm font-bold {{ $newRemainingAmount <= 0 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                {{ $newRemainingAmount <= 0 ? '✅ Quitado' : '⏳ Pendente' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Botões de Ação -->
            <div class="flex items-center justify-end gap-4 pt-6">
                <a href="{{ route('sales.show', $sale->id) }}"
                   class="px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-200 rounded-xl transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg">
                    <i class="bi bi-x-lg"></i>
                    Cancelar
                </a>
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="bi bi-check-lg" wire:loading.remove></i>
                    <i class="bi bi-arrow-clockwise animate-spin" wire:loading></i>
                    <span wire:loading.remove>Confirmar Pagamentos</span>
                    <span wire:loading>Processando...</span>
                </button>
            </div>
        </form>

        <!-- Scripts para notificações -->
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('success', (message) => {
                    // Criar notificação toast moderna
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300';
                    toast.innerHTML = `
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>${message}</span>
                        </div>
                    `;
                    document.body.appendChild(toast);

                    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
                    setTimeout(() => {
                        toast.classList.add('translate-x-full');
                        setTimeout(() => document.body.removeChild(toast), 300);
                    }, 3000);
                });

                Livewire.on('error', (message) => {
                    // Criar notificação toast de erro
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300';
                    toast.innerHTML = `
                        <div class="flex items-center gap-2">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>${message}</span>
                        </div>
                    `;
                    document.body.appendChild(toast);

                    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
                    setTimeout(() => {
                        toast.classList.add('translate-x-full');
                        setTimeout(() => document.body.removeChild(toast), 300);
                    }, 3000);
                });
            });
        </script>

        <style>
            .hover-lift {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .hover-lift:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            /* Animações de entrada */
            .grid > * {
                animation: slideInUp 0.6s ease-out forwards;
                opacity: 0;
                transform: translateY(30px);
            }

            .grid > *:nth-child(1) { animation-delay: 0.1s; }
            .grid > *:nth-child(2) { animation-delay: 0.2s; }
            .grid > *:nth-child(3) { animation-delay: 0.3s; }
            .grid > *:nth-child(4) { animation-delay: 0.4s; }
            .grid > *:nth-child(5) { animation-delay: 0.5s; }
            .grid > *:nth-child(6) { animation-delay: 0.6s; }

            @keyframes slideInUp {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Efeitos de foco aprimorados */
            input:focus, select:focus {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                transform: scale(1.01);
            }
        </style>
        </div>
    </div>
</div>
