@props([
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'selectedAvatar' => null
])

<div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-8 shadow-lg border border-white/20 dark:border-slate-700/50">
    <!-- Header do Formulário -->
    <div class="mb-8">
        <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-800 via-orange-700 to-amber-700 dark:from-slate-100 dark:via-orange-300 dark:to-amber-300 bg-clip-text text-transparent flex items-center">
            <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-500 rounded-lg flex items-center justify-center mr-3">
                <i class="bi bi-person-gear text-white text-sm"></i>
            </div>
            Atualizar Dados do Cliente
        </h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
            Modifique as informações do cliente (* campos obrigatórios)
        </p>
    </div>

    <!-- Formulário -->
    <form wire:submit="update" class="space-y-6" x-data="{
        formatPhone(value) {
            // Remove tudo que não é número
            let cleaned = value.replace(/\D/g, '');

            // Aplica a máscara baseada no tamanho
            if (cleaned.length <= 10) {
                // Telefone fixo: (XX) XXXX-XXXX
                cleaned = cleaned.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else {
                // Celular: (XX) XXXXX-XXXX
                cleaned = cleaned.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
            }

            return cleaned;
        }
    }">
        <!-- Grid Principal -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Nome Completo -->
            <div class="lg:col-span-2">
                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    <div class="flex items-center">
                        <div class="w-5 h-5 bg-gradient-to-br from-orange-500 to-amber-500 rounded flex items-center justify-center mr-2">
                            <i class="bi bi-person text-white text-xs"></i>
                        </div>
                        Nome Completo *
                    </div>
                </label>
                <div class="relative group">
                    <input type="text"
                           wire:model="name"
                           id="name"
                           placeholder="Digite o nome completo do cliente"
                           class="w-full pl-12 pr-4 py-4 bg-gradient-to-r from-white via-slate-50 to-orange-50 dark:from-slate-800 dark:via-slate-700 dark:to-orange-900/30
                                  border-2 {{ $errors->has('name') ? 'border-red-300 dark:border-red-500' : 'border-slate-200/50 dark:border-slate-600/50' }} rounded-xl
                                  text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400
                                  focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 dark:focus:border-orange-400
                                  transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm
                                  text-base font-medium group-hover:border-orange-300">

                    <!-- Ícone -->
                    <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                        <i class="bi bi-person text-slate-500 dark:text-slate-400 text-lg group-focus-within:text-orange-500 transition-colors duration-200"></i>
                    </div>

                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-orange-500/10 via-transparent to-amber-500/10 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                </div>
                @error('name')
                    <div class="mt-3 p-3 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg border border-red-200 dark:border-red-700">
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                            <i class="bi bi-exclamation-circle mr-2"></i>{{ $message }}
                        </p>
                    </div>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    <div class="flex items-center">
                        <div class="w-5 h-5 bg-gradient-to-br from-blue-500 to-indigo-500 rounded flex items-center justify-center mr-2">
                            <i class="bi bi-envelope text-white text-xs"></i>
                        </div>
                        Email
                    </div>
                </label>
                <div class="relative group">
                    <input type="email"
                           wire:model="email"
                           id="email"
                           placeholder="exemplo@email.com"
                           class="w-full pl-12 pr-4 py-4 bg-gradient-to-r from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900/30
                                  border-2 {{ $errors->has('email') ? 'border-red-300 dark:border-red-500' : 'border-slate-200/50 dark:border-slate-600/50' }} rounded-xl
                                  text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400
                                  focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:focus:border-blue-400
                                  transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm
                                  text-base font-medium group-hover:border-blue-300">

                    <!-- Ícone -->
                    <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                        <i class="bi bi-envelope text-slate-500 dark:text-slate-400 text-lg group-focus-within:text-blue-500 transition-colors duration-200"></i>
                    </div>

                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-500/10 via-transparent to-indigo-500/10 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                </div>
                @error('email')
                    <div class="mt-3 p-3 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg border border-red-200 dark:border-red-700">
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                            <i class="bi bi-exclamation-circle mr-2"></i>{{ $message }}
                        </p>
                    </div>
                @enderror
            </div>

            <!-- Telefone com máscara -->
            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    <div class="flex items-center">
                        <div class="w-5 h-5 bg-gradient-to-br from-purple-500 to-pink-500 rounded flex items-center justify-center mr-2">
                            <i class="bi bi-telephone text-white text-xs"></i>
                        </div>
                        Telefone
                    </div>
                </label>
                <div class="relative group">
                    <input type="tel"
                           wire:model="phone"
                           id="phone"
                           placeholder="(11) 99999-9999"
                           x-on:input="$event.target.value = formatPhone($event.target.value); $wire.set('phone', $event.target.value)"
                           class="w-full pl-12 pr-4 py-4 bg-gradient-to-r from-white via-slate-50 to-purple-50 dark:from-slate-800 dark:via-slate-700 dark:to-purple-900/30
                                  border-2 {{ $errors->has('phone') ? 'border-red-300 dark:border-red-500' : 'border-slate-200/50 dark:border-slate-600/50' }} rounded-xl
                                  text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400
                                  focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:focus:border-purple-400
                                  transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm
                                  text-base font-medium group-hover:border-purple-300">

                    <!-- Ícone -->
                    <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                        <i class="bi bi-telephone text-slate-500 dark:text-slate-400 text-lg group-focus-within:text-purple-500 transition-colors duration-200"></i>
                    </div>

                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-purple-500/10 via-transparent to-pink-500/10 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                </div>
                @error('phone')
                    <div class="mt-3 p-3 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg border border-red-200 dark:border-red-700">
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                            <i class="bi bi-exclamation-circle mr-2"></i>{{ $message }}
                        </p>
                    </div>
                @enderror
            </div>

            <!-- Endereço -->
            <div class="lg:col-span-2">
                <label for="address" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    <div class="flex items-center">
                        <div class="w-5 h-5 bg-gradient-to-br from-red-500 to-pink-500 rounded flex items-center justify-center mr-2">
                            <i class="bi bi-geo-alt text-white text-xs"></i>
                        </div>
                        Endereço Completo
                    </div>
                </label>
                <div class="relative group">
                    <textarea wire:model="address"
                              id="address"
                              rows="4"
                              placeholder="Digite o endereço completo (rua, número, bairro, cidade, CEP)"
                              class="w-full pl-12 pr-4 py-4 bg-gradient-to-r from-white via-slate-50 to-red-50 dark:from-slate-800 dark:via-slate-700 dark:to-red-900/30
                                     border-2 {{ $errors->has('address') ? 'border-red-300 dark:border-red-500' : 'border-slate-200/50 dark:border-slate-600/50' }} rounded-xl
                                     text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400
                                     focus:ring-4 focus:ring-red-500/20 focus:border-red-500 dark:focus:border-red-400
                                     transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm
                                     text-base font-medium group-hover:border-red-300 resize-none"></textarea>

                    <!-- Ícone -->
                    <div class="absolute left-4 top-6 transform -translate-y-1/2">
                        <i class="bi bi-geo-alt text-slate-500 dark:text-slate-400 text-lg group-focus-within:text-red-500 transition-colors duration-200"></i>
                    </div>

                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-red-500/10 via-transparent to-pink-500/10 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                </div>
                @error('address')
                    <div class="mt-3 p-3 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg border border-red-200 dark:border-red-700">
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                            <i class="bi bi-exclamation-circle mr-2"></i>{{ $message }}
                        </p>
                    </div>
                @enderror
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex flex-col sm:flex-row sm:justify-end gap-4 pt-6 border-t border-slate-200/50 dark:border-slate-700/50">
            <a href="{{ route('clients.index') }}"
               class="group relative inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-slate-100 to-slate-200 hover:from-slate-200 hover:to-slate-300 dark:from-slate-700 dark:to-slate-600 dark:hover:from-slate-600 dark:hover:to-slate-500
                      text-slate-700 dark:text-slate-200 font-semibold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 border border-slate-300 dark:border-slate-600">
                <i class="bi bi-x-circle mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                Cancelar
                <!-- Efeito hover ring -->
                <div class="absolute inset-0 rounded-2xl bg-slate-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>

            <button type="submit"
                    class="group relative inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 hover:from-orange-700 hover:via-amber-700 hover:to-yellow-700 text-white font-bold rounded-2xl transition-all duration-300 shadow-xl shadow-orange-500/25 hover:shadow-2xl hover:shadow-orange-500/40 transform hover:scale-105 border border-orange-400"
                    wire:loading.attr="disabled">
                <div wire:loading.remove wire:target="update" class="flex items-center">
                    <i class="bi bi-floppy mr-2 text-xl group-hover:scale-110 transition-transform duration-200"></i>
                    Atualizar Cliente
                </div>
                <div wire:loading wire:target="update" class="flex items-center">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent mr-2"></div>
                    Atualizando...
                </div>
                <!-- Efeito hover ring -->
                <div class="absolute inset-0 rounded-2xl bg-orange-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </button>
        </div>
    </form>
</div>
