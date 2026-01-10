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
            <div class="fixed inset-0 bg-gradient-to-br from-black/70 via-slate-900/85 to-emerald-900/50 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

            <!-- Modal Content -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative w-full max-w-4xl bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 rounded-3xl shadow-2xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">

                    <!-- Header com gradiente -->
                    <div class="relative bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-8 overflow-hidden">
                        <div class="absolute inset-0 bg-grid-white/10 [mask-image:linear-gradient(0deg,transparent,black)]"></div>
                        <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                        <div class="absolute -left-8 -bottom-8 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>

                        <div class="relative flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-white/20 backdrop-blur-xl rounded-2xl flex items-center justify-center shadow-lg">
                                    <i class="bi bi-people-fill text-3xl text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white">
                                        Adicionar Participantes
                                    </h3>
                                    <p class="text-emerald-100 text-sm mt-1">
                                        Adicione um ou mais participantes ao consórcio
                                    </p>
                                </div>
                            </div>
                            <button wire:click="closeModal"
                                class="w-10 h-10 bg-white/20 hover:bg-white/30 backdrop-blur-xl rounded-xl flex items-center justify-center transition-all duration-200 hover:scale-110">
                                <i class="bi bi-x-lg text-2xl text-white"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto">

                        <!-- Mensagem temporária de sucesso -->
                        @if (session()->has('success_temp'))
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 rounded-xl p-4 animate-fade-in">
                                <div class="flex items-center gap-3">
                                    <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400 text-xl"></i>
                                    <p class="text-green-800 dark:text-green-200 font-medium">{{ session('success_temp') }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Lista de participantes adicionados -->
                        @if (count($participants) > 0)
                            <div class="space-y-3">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                        <i class="bi bi-list-check text-emerald-600"></i>
                                        Participantes na Lista
                                        <span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm px-3 py-1 rounded-full font-semibold">
                                            {{ count($participants) }}
                                        </span>
                                    </h4>
                                </div>

                                <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                    @foreach ($participants as $index => $participant)
                                        <div class="group relative bg-gradient-to-r from-white to-slate-50 dark:from-slate-700 dark:to-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 hover:shadow-lg transition-all duration-200">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex items-start gap-3 flex-1">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-lg">
                                                        <span class="text-white font-bold text-sm">{{ $index + 1 }}</span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <i class="bi bi-person-fill text-emerald-600 dark:text-emerald-400"></i>
                                                            <h5 class="font-bold text-slate-800 dark:text-slate-100 truncate">
                                                                {{ $participant['client_name'] }}
                                                            </h5>
                                                        </div>
                                                        <div class="flex flex-wrap gap-3 text-sm">
                                                            <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                                                <i class="bi bi-calendar-event"></i>
                                                                <span>{{ \Carbon\Carbon::parse($participant['entry_date'])->format('d/m/Y') }}</span>
                                                            </div>
                                                            @if ($participant['notes'])
                                                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                                                    <i class="bi bi-sticky"></i>
                                                                    <span class="truncate max-w-[200px]">{{ $participant['notes'] }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" wire:click="removeFromList({{ $index }})"
                                                    class="w-9 h-9 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 rounded-lg flex items-center justify-center transition-all duration-200 opacity-0 group-hover:opacity-100 flex-shrink-0">
                                                    <i class="bi bi-trash text-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-slate-300 dark:border-slate-600"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-4 bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 text-slate-500 dark:text-slate-400 font-medium">
                                        Adicionar mais participantes
                                    </span>
                                </div>
                            </div>
                        @endif
                        <!-- Formulário de adição -->
                        <form wire:submit.prevent="addToList" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Cliente -->
                                <div class="md:col-span-2">
                                    <label for="client_id" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                        <i class="bi bi-person-badge text-emerald-600"></i>
                                        Selecione o Cliente *
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
                                            class="w-full flex items-center justify-between px-5 py-4 rounded-xl border-2 bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-slate-900 dark:text-slate-100 font-medium transition-all duration-200 hover:border-emerald-500 hover:shadow-lg focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20">
                                            <span class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                                    <i class="bi bi-person text-emerald-600 dark:text-emerald-400 text-xl"></i>
                                                </div>
                                                <span x-text="selectedName" class="font-semibold"></span>
                                            </span>
                                            <i class="bi bi-chevron-down text-slate-400 text-lg transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                        </button>

                                        <div x-show="open" x-transition @click.away="open = false"
                                            class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border-2 border-emerald-500/50 dark:border-emerald-600 rounded-2xl shadow-2xl overflow-hidden">
                                            <div class="p-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border-b border-slate-200 dark:border-slate-700">
                                                <div class="relative">
                                                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                                    <input type="text" x-model="search" wire:model.debounce.300ms="search" @click.stop
                                                        placeholder="Pesquisar cliente..."
                                                        class="w-full pl-12 pr-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                                </div>
                                            </div>

                                            <div class="max-h-64 overflow-y-auto">
                                                <template x-for="client in filteredClients" :key="client.id">
                                                    <button type="button" @click="selectClient(client)"
                                                        class="w-full flex items-center gap-3 px-5 py-3 text-left hover:bg-gradient-to-r hover:from-emerald-50 hover:to-teal-50 dark:hover:from-emerald-900/20 dark:hover:to-teal-900/20 transition-all duration-150 border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                                        <div class="w-9 h-9 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                                            <i class="bi bi-person text-emerald-600 dark:text-emerald-400"></i>
                                                        </div>
                                                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="client.name"></span>
                                                    </button>
                                                </template>
                                                <div x-show="filteredClients.length === 0" class="px-5 py-8 text-center">
                                                    <i class="bi bi-inbox text-4xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Nenhum cliente encontrado</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @error('client_id')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                            <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Data de Entrada -->
                                <div>
                                    <label for="entry_date" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                        <i class="bi bi-calendar-check text-emerald-600"></i>
                                        Data de Entrada *
                                    </label>
                                    <div class="relative">
                                        <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                        <input type="date" wire:model="entry_date" id="entry_date"
                                            class="w-full pl-12 pr-4 py-4 bg-white dark:bg-slate-700 border-2 border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 font-medium focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all hover:border-emerald-400">
                                    </div>
                                    @error('entry_date')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                            <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Botão de adicionar à lista (mobile) -->
                                <div class="md:hidden">
                                    <label class="block text-sm font-bold text-transparent mb-3">.</label>
                                    <button type="submit"
                                        class="w-full px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]">
                                        <i class="bi bi-plus-circle text-xl"></i> Adicionar à Lista
                                    </button>
                                </div>
                            </div>

                            <!-- Observações -->
                            <div>
                                <label for="notes" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                    <i class="bi bi-journal-text text-emerald-600"></i>
                                    Observações
                                </label>
                                <div class="relative">
                                    <textarea wire:model="notes" id="notes" rows="3"
                                        class="w-full px-5 py-4 bg-white dark:bg-slate-700 border-2 border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all resize-none hover:border-emerald-400"
                                        placeholder="Adicione observações sobre o participante (opcional)..."></textarea>
                                </div>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Botão de adicionar à lista (desktop) -->
                            <div class="hidden md:block">
                                <button type="submit"
                                    class="w-full px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                                    <i class="bi bi-plus-circle text-xl"></i>
                                    <span>Adicionar à Lista</span>
                                </button>
                            </div>
                        </form>
                        </form>

                        <!-- Informações do Consórcio -->
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-2xl p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-info-circle-fill text-blue-600 dark:text-blue-400 text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-bold text-blue-900 dark:text-blue-100 mb-3 text-lg">Informações do Consórcio</h5>
                                    <div class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-hash text-blue-600 dark:text-blue-400"></i>
                                            <span><strong>Número de participação:</strong> Gerado automaticamente</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-check-circle text-blue-600 dark:text-blue-400"></i>
                                            <span><strong>Status inicial:</strong> Ativo</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-cash-coin text-blue-600 dark:text-blue-400"></i>
                                            <span><strong>Valor mensalidade:</strong> R$ {{ number_format($consortium->monthly_value, 2, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-calendar-range text-blue-600 dark:text-blue-400"></i>
                                            <span><strong>Duração:</strong> {{ $consortium->duration_months }} meses</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-8 py-6 border-t border-slate-200 dark:border-slate-700">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="text-sm text-slate-600 dark:text-slate-400 font-medium">
                                @if (count($participants) > 0)
                                    <span class="flex items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                        {{ count($participants) }} participante(s) pronto(s) para adicionar
                                    </span>
                                @else
                                    <span class="flex items-center gap-2">
                                        <i class="bi bi-info-circle text-slate-400"></i>
                                        Preencha os dados e clique em "Adicionar à Lista"
                                    </span>
                                @endif
                            </div>

                            <div class="flex gap-3 w-full sm:w-auto">
                                <button type="button" wire:click="closeModal"
                                    class="flex-1 sm:flex-none px-6 py-3 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition-all border-2 border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>

                                @if (count($participants) > 0)
                                    <button type="button" wire:click="addParticipant"
                                        class="flex-1 sm:flex-none px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                                        <i class="bi bi-check-circle-fill text-xl"></i>
                                        <span>Salvar Todos ({{ count($participants) }})</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
