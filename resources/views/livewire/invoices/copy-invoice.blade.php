<div class="copy-invoice-page w-full mobile-393-base">
    <form wire:submit.prevent="save">
        <x-bank-page-header title="Copiar compra" subtitle="Confira os dados e salve para criar uma compra igual a esta" icon="bi-copy" :bank="$originalInvoice->bank" :back-route="route('invoices.index', ['bankId' => $originalInvoice->id_bank])">
            <x-slot:actions>
                <a href="{{ route('invoices.index', ['bankId' => $originalInvoice->id_bank]) }}" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-x-lg"></i>Cancelar</a>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition disabled:opacity-50">
                    <i class="bi bi-check-lg" wire:loading.remove wire:target="save"></i>
                    <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="save"></i>
                    Salvar cópia
                </button>
            </x-slot:actions>
        </x-bank-page-header>

        <div class="pb-8">
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300 p-4 rounded-2xl mb-4">
                    <div class="font-bold mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Corrija os campos abaixo:</div>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $fieldClass = 'w-full px-4 py-3 rounded-xl border bg-white/80 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20 focus:outline-none transition-all duration-200';
                $labelClass = 'flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2';
            @endphp

            <div class="bg-white dark:bg-slate-900/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 shadow-sm p-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label for="description" class="{{ $labelClass }}">
                            <i class="bi bi-card-text text-purple-500"></i> Descrição <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="description" type="text" id="description" class="{{ $fieldClass }}">
                    </div>

                    <div>
                        <x-money-input model="value" :value="$value" label="Valor" :required="true" />
                    </div>

                    <div>
                        <label for="invoice_date" class="{{ $labelClass }}">
                            <i class="bi bi-calendar-event text-blue-500"></i> Data <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="invoice_date" type="date" id="invoice_date" class="{{ $fieldClass }}">
                    </div>

                    <div>
                        <label for="bankId" class="{{ $labelClass }}">
                            <i class="bi bi-credit-card text-indigo-500"></i> Cartão / banco <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="bankId" id="bankId" class="{{ $fieldClass }}">
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->id_bank }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="category_id" class="{{ $labelClass }}">
                            <i class="bi bi-bookmark text-rose-500"></i> Categoria <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="category_id" id="category_id" class="{{ $fieldClass }}">
                            <option value="">Selecione...</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="client_id" class="{{ $labelClass }}">
                            <i class="bi bi-person text-cyan-500"></i> Cliente <span class="text-slate-400 text-xs">(opcional)</span>
                        </label>
                        <select wire:model="client_id" id="client_id" class="{{ $fieldClass }}">
                            <option value="">Nenhum</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="installments" class="{{ $labelClass }}">
                            <i class="bi bi-layers text-indigo-500"></i> Parcelas <span class="text-slate-400 text-xs">(opcional)</span>
                        </label>
                        <input wire:model="installments" type="text" id="installments" placeholder="1x, 2/10, à vista..." class="{{ $fieldClass }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
