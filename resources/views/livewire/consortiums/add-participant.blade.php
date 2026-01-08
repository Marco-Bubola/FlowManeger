<div>
    <!-- Modal -->
    @if ($showModal)
        <div x-data="{ show: @entangle('showModal') }"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[99999] overflow-y-auto"
             style="display: none;">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-slate-900/80 to-emerald-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

            <!-- Modal Content -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl">

                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            <i class="bi bi-person-plus text-emerald-600 dark:text-emerald-400"></i>
                            Adicionar Participante
                        </h3>
                        <button wire:click="closeModal"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <i class="bi bi-x-lg text-2xl"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <form wire:submit.prevent="addParticipant" class="p-6 space-y-4">
                        <!-- Cliente -->
                        <div class="space-y-2">
                            <label for="client_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Cliente *
                            </label>

                            <div class="relative" x-data="{
                                open: false,
                                search: '',
                                selectedId: @entangle('client_id'),
                                clients: @js($clients->map(fn($c) => ['id' => $c->id, 'name' => $c->name])),
                                get selectedName() {
                                    const found = this.clients.find(c => String(c.id) === String(this.selectedId));
                                    return found ? found.name : 'Selecione um cliente';
                                },
                                get filteredClients() {
                                    if (!this.search) return this.clients;
                                    return this.clients.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                selectClient(client) {
                                    this.selectedId = client.id;
                                    this.search = '';
                                    this.open = false;
                                    $wire.set('client_id', client.id);
                                }
                            }">
                                <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl border bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-slate-900 dark:text-slate-100 font-medium transition-all duration-200 hover:border-emerald-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                                    <span class="flex items-center gap-2">
                                        <i class="bi bi-person text-emerald-600"></i>
                                        <span x-text="selectedName"></span>
                                    </span>
                                    <i class="bi bi-chevron-down text-slate-400" :class="{ 'rotate-180': open }"></i>
                                </button>

                                <div x-show="open" x-transition @click.away="open = false"
                                    class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                    <div class="p-3 border-b border-slate-200 dark:border-slate-700">
                                        <div class="relative">
                                            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                            <input type="text" x-model="search" wire:model.debounce.300ms="search" @click.stop
                                                placeholder="Pesquisar cliente..."
                                                class="w-full pl-10 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-emerald-500 focus:outline-none">
                                        </div>
                                    </div>

                                    <div class="max-h-60 overflow-y-auto">
                                        <template x-for="client in filteredClients" :key="client.id">
                                            <button type="button" @click="selectClient(client)"
                                                class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                                <i class="bi bi-person text-emerald-500"></i>
                                                <span class="text-sm font-medium text-slate-800 dark:text-slate-100" x-text="client.name"></span>
                                            </button>
                                        </template>
                                        <div x-show="filteredClients.length === 0" class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 text-center">
                                            Nenhum cliente encontrado
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('client_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Data de Entrada -->
                        <div>
                            <label for="entry_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Data de Entrada *
                            </label>
                            <input type="date" wire:model="entry_date" id="entry_date"
                                class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            @error('entry_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observações -->
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Observações
                            </label>
                            <textarea wire:model="notes" id="notes" rows="3"
                                class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all resize-none"
                                placeholder="Observações adicionais sobre o participante..."></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Informações -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-info-circle text-blue-600 dark:text-blue-400 text-lg mt-0.5"></i>
                                <div class="text-sm text-blue-800 dark:text-blue-200">
                                    <p class="font-semibold mb-1">Informações</p>
                                    <p>• Número de participação será gerado automaticamente</p>
                                    <p>• Status inicial: Ativo</p>
                                    <p>• Valor mensalidade: R$ {{ number_format($consortium->monthly_value, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal"
                                class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-semibold rounded-xl transition-all">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl transition-all shadow-lg">
                                <i class="bi bi-check-lg"></i> Adicionar Participante
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
