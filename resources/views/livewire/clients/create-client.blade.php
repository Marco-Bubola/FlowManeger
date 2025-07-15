
<div class="min-h-screen w-full bg-gray-50 dark:bg-zinc-900 py-8">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('clients.index') }}" 
                   class="mr-4 p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg transition-colors duration-200">
                    <i class="bi bi-arrow-left text-lg"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <i class="bi bi-person-plus text-indigo-600 dark:text-indigo-400 mr-3"></i>Novo Cliente
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Preencha as informações para cadastrar um novo cliente</p>
                </div>
            </div>
        </div>

        <!-- Formulário -->

        <div class="w-full flex flex-col lg:flex-row gap-8">
            <!-- Avatares -->
            <div class="w-full lg:w-2/4">
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-8 h-full">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                        <i class="bi bi-image text-gray-400 mr-2"></i>Selecione um Avatar
                    </label>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                        @foreach($this->avatarOptions as $index => $avatar)
                            <label class="relative cursor-pointer group block">
                                <input type="radio" 
                                       wire:model="avatar_cliente" 
                                       value="{{ $avatar }}" 
                                       class="sr-only peer">
                                <div class="w-20 h-20 rounded-full border-4 border-gray-200 dark:border-zinc-600 overflow-hidden peer-checked:border-indigo-500 peer-checked:ring-2 peer-checked:ring-indigo-200 dark:peer-checked:ring-indigo-400 group-hover:border-indigo-300 transition-all duration-200 flex items-center justify-center bg-gray-100 dark:bg-zinc-700">
                                    <img src="{{ $avatar }}"
                                         alt="Avatar Option {{ $index + 1 }}"
                                         class="w-full h-full object-cover"
                                         loading="eager"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-zinc-600 rounded-full" style="display: none;">
                                        <i class="bi bi-person text-gray-400 text-3xl"></i>
                                    </div>
                                </div>
                                <i class="bi bi-check absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200 text-lg font-bold bg-indigo-600 rounded-full w-6 h-6 flex items-center justify-center"></i>
                            </label>
                        @endforeach
                    </div>
                    @error('avatar_cliente')
                        <p class="mt-4 text-sm text-red-600 dark:text-red-400">
                            <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Formulário -->
            <div class="w-full lg:w-2/4">
                <form wire:submit="store" class="space-y-8">
                    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Nome -->
                            <div class="lg:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-person text-gray-400 mr-2"></i>Nome do Cliente *
                                </label>
                                <input type="text" 
                                       wire:model="name"
                                       id="name"
                                       placeholder="Digite o nome completo"
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 {{ $errors->has('name') ? 'border-red-300 dark:border-red-500' : 'border-gray-300 dark:border-zinc-600' }}">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-envelope text-gray-400 mr-2"></i>Email
                                </label>
                                <input type="email" 
                                       wire:model="email"
                                       id="email"
                                       placeholder="exemplo@email.com"
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 {{ $errors->has('email') ? 'border-red-300 dark:border-red-500' : 'border-gray-300 dark:border-zinc-600' }}">
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Telefone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-telephone text-gray-400 mr-2"></i>Telefone
                                </label>
                                <input type="tel" 
                                       wire:model="phone"
                                       id="phone"
                                       placeholder="(11) 99999-9999"
                                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 {{ $errors->has('phone') ? 'border-red-300 dark:border-red-500' : 'border-gray-300 dark:border-zinc-600' }}">
                                @error('phone')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Endereço -->
                            <div class="lg:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="bi bi-geo-alt text-gray-400 mr-2"></i>Endereço
                                </label>
                                <textarea wire:model="address"
                                          id="address"
                                          rows="3"
                                          placeholder="Digite o endereço completo"
                                          class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 {{ $errors->has('address') ? 'border-red-300 dark:border-red-500' : 'border-gray-300 dark:border-zinc-600' }}"></textarea>
                                @error('address')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 px-8 py-6">
                        <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                            <a href="{{ route('clients.index') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-zinc-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                <i class="bi bi-x mr-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <div wire:loading.remove wire:target="store">
                                    <i class="bi bi-floppy mr-2"></i>
                                    Salvar Cliente
                                </div>
                                <div wire:loading wire:target="store">
                                    <i class="bi bi-arrow-clockwise me-2 animate-spin"></i>
                                    Salvando...
                                </div>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Preview do Cliente -->
                @if($name || $avatar_cliente)
                    <div class="mt-8 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            <i class="bi bi-eye text-gray-400 mr-2"></i>Preview do Cliente
                        </h3>
                        <div class="flex items-center space-x-4">
                            @if($avatar_cliente)
                                <img src="{{ $avatar_cliente }}" 
                                     alt="Preview Avatar"
                                     class="w-16 h-16 rounded-full border-4 border-indigo-100 dark:border-indigo-600">
                            @else
                                <div class="w-16 h-16 rounded-full flex items-center justify-center bg-transparent">
                                    <i class="bi bi-person text-gray-400 text-2xl"></i>
                                </div>
                            @endif
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $name ?: 'Nome do Cliente' }}</h4>
                                @if($email)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <i class="bi bi-envelope mr-1"></i>{{ $email }}
                                    </p>
                                @endif
                                @if($phone)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <i class="bi bi-telephone mr-1"></i>{{ $phone }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
