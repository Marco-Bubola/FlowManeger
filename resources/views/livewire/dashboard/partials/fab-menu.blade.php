{{-- FAB Menu - Floating Action Button para ações rápidas --}}
<div x-data="{ open: false }"
     class="fixed bottom-8 right-8 z-50">

    {{-- Backdrop quando menu aberto --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-black/20 backdrop-blur-sm"
         style="display: none;"></div>

    {{-- Botões de ação (aparecem quando menu abre) --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="absolute bottom-20 right-0 flex flex-col gap-3"
         style="display: none;">

        {{-- Nova Venda --}}
        <a href="{{ route('sales.create') }}"
           class="group flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-x-2">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-shopping-cart text-xl"></i>
            </div>
            <span class="font-semibold pr-2">Nova Venda</span>
        </a>

        {{-- Novo Lançamento --}}
        <a href="{{ route('cashbook.create') }}"
           class="group flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-x-2">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-dollar-sign text-xl"></i>
            </div>
            <span class="font-semibold pr-2">Novo Lançamento</span>
        </a>

        {{-- Novo Cliente --}}
        <a href="{{ route('clients.create') }}"
           class="group flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-x-2">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-plus text-xl"></i>
            </div>
            <span class="font-semibold pr-2">Novo Cliente</span>
        </a>

        {{-- Novo Produto --}}
        <a href="{{ route('products.create') }}"
           class="group flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-x-2">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-box text-xl"></i>
            </div>
            <span class="font-semibold pr-2">Novo Produto</span>
        </a>

        {{-- Nova Fatura --}}
        @if(isset($banks) && $banks->count() > 0)
        <a href="{{ route('invoices.create', $banks->first()->id_bank) }}"
           class="group flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-x-2">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-credit-card text-xl"></i>
            </div>
            <span class="font-semibold pr-2">Nova Fatura</span>
        </a>
        @endif
    </div>

    {{-- Botão Principal --}}
    <button @click="open = !open"
            class="group relative w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 text-white rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-110 hover:rotate-90 flex items-center justify-center ring-4 ring-white dark:ring-slate-800">
        <i :class="open ? 'fa-times' : 'fa-plus'" class="fas text-2xl transition-transform duration-300"></i>

        {{-- Pulso animado --}}
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-400 to-purple-400 rounded-full opacity-75 animate-ping"></div>
    </button>

    {{-- Tooltip --}}
    <div x-show="!open"
         class="absolute bottom-20 right-0 px-3 py-2 bg-slate-800 dark:bg-slate-700 text-white text-sm font-medium rounded-lg shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
        Ações Rápidas
        <div class="absolute top-full right-6 -mt-1 w-3 h-3 bg-slate-800 dark:bg-slate-700 transform rotate-45"></div>
    </div>
</div>

<style>
    /* Animação personalizada para o pulso */
    @keyframes ping {
        75%, 100% {
            transform: scale(1.3);
            opacity: 0;
        }
    }
</style>
