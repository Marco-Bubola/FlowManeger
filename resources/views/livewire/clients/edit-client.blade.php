<div x-data="{ currentStep: 1 }" class=" w-full ">
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">

        <!-- Header Modernizado -->
        <x-client-create-header
            title="Editar Cliente"
            description="Atualize as informações do cliente {{ $client->name }}"
            :back-route="route('clients.index')"
            :show-steps="false" />

        <!-- Conteúdo Principal -->
        <div class="flex flex-col xl:flex-row gap-8">

            <!-- Lado Esquerdo: Seleção de Avatar (40% em telas grandes) -->
            <div class="w-full xl:w-2/5">
                <x-client-avatar-selector
                    :avatar-options="$avatarOptions"
                    :selected-avatar="$avatar_cliente"
                    wire-model="avatar_cliente" />
            </div>

            <!-- Lado Direito: Formulário (60% em telas grandes) -->
            <div class="w-full xl:w-3/5">
                <x-client-edit-form
                    :name="$name"
                    :email="$email"
                    :phone="$phone"
                    :address="$address"
                    :selected-avatar="$avatar_cliente" />
            </div>
        </div>
    </div>

    <!-- Notificações de Sucesso -->
    @if (session()->has('success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-6 right-6 z-50 max-w-md">
        <div class="bg-gradient-to-r from-emerald-500 to-green-600 text-white px-6 py-4 rounded-2xl shadow-2xl border border-emerald-400 backdrop-blur-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-check-circle-fill text-2xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-semibold">Sucesso!</p>
                    <p class="text-sm opacity-90">{{ session('success') }}</p>
                </div>
                <div class="ml-4">
                    <button @click="show = false" class="text-emerald-200 hover:text-white transition-colors duration-200">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Notificações de Erro -->
    @if (session()->has('error'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 6000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-6 right-6 z-50 max-w-md">
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4 rounded-2xl shadow-2xl border border-red-400 backdrop-blur-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-exclamation-triangle-fill text-2xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-semibold">Erro!</p>
                    <p class="text-sm opacity-90">{{ session('error') }}</p>
                </div>
                <div class="ml-4">
                    <button @click="show = false" class="text-red-200 hover:text-white transition-colors duration-200">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading.delay.long wire:target="update"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-2xl border border-white/20 dark:border-slate-700/50 backdrop-blur-xl">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-emerald-500 border-t-transparent mb-4"></div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2">Atualizando Cliente</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 text-center">
                    Aguarde enquanto salvamos as alterações...
                </p>
            </div>
        </div>
    </div>
</div>
