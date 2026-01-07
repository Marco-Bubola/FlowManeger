<div>
    <button
        wire:click="openModal"
        type="button"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-purple-700 bg-purple-100 rounded-lg hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-900/50 transition-colors"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Registrar Produtos
    </button>

    <!-- Modal -->
    @if($showModal)
    <flux:modal name="register-contemplation-{{ $contemplationId }}" wire:model="showModal" class="max-w-3xl">
        <form wire:submit="saveRedemption" class="space-y-6">
            <div>
                <flux:heading size="lg">Registrar Resgate da Contemplação</flux:heading>
                <flux:subheading class="mt-2">
                    Escolha o tipo de resgate e os produtos (se aplicável)
                </flux:subheading>
            </div>

            <!-- Tipo de Resgate -->
            <div>
                <flux:field>
                    <flux:label>Tipo de Resgate</flux:label>
                    <flux:select wire:model.live="redemptionType">
                        <option value="pending">Pendente</option>
                        <option value="cash">Dinheiro</option>
                        <option value="products">Produtos</option>
                    </flux:select>
                </flux:field>
            </div>

            <!-- Dinheiro -->
            @if($redemptionType === 'cash')
                <div>
                    <flux:field>
                        <flux:label>Valor em Dinheiro</flux:label>
                        <flux:input
                            wire:model="redemptionValue"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                        />
                    </flux:field>
                </div>
            @endif

            <!-- Produtos -->
            @if($redemptionType === 'products')
                <div class="space-y-4">
                    <!-- Lista de Produtos Disponíveis -->
                    <div>
                        <flux:label>Adicionar Produtos</flux:label>
                        <div class="mt-2 max-h-48 overflow-y-auto space-y-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3">
                            @foreach($availableProducts as $product)
                                <button
                                    type="button"
                                    wire:click="addProduct({{ $product->id }})"
                                    class="w-full flex items-center justify-between px-3 py-2 bg-white dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 border border-slate-200 dark:border-slate-700 rounded-lg transition-colors"
                                >
                                    <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $product->name }}</span>
                                    <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                        R$ {{ number_format($product->value, 2, ',', '.') }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Produtos Selecionados -->
                    <div>
                        <flux:label>Produtos Selecionados</flux:label>
                        @if(count($selectedProducts) > 0)
                            <div class="mt-2 space-y-2">
                                @foreach($selectedProducts as $index => $product)
                                    <div class="flex items-center justify-between px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <i class="bi bi-check-circle-fill text-emerald-600 dark:text-emerald-400"></i>
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-slate-100">{{ $product['name'] }}</p>
                                                <p class="text-sm text-emerald-600 dark:text-emerald-400">
                                                    R$ {{ number_format($product['value'], 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="removeProduct({{ $index }})"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach

                                <!-- Total -->
                                <div class="flex items-center justify-between px-4 py-3 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                    <span class="font-bold text-slate-900 dark:text-slate-100">Total:</span>
                                    <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                        R$ {{ number_format($redemptionValue, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 italic">
                                Nenhum produto selecionado. Clique nos produtos acima para adicionar.
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Botões -->
            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:button variant="filled" wire:click="closeModal" type="button">
                    Cancelar
                </flux:button>

                <flux:button variant="primary" type="submit">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Salvar Resgate
                </flux:button>
            </div>
        </form>
    </flux:modal>
    @endif
</div>
