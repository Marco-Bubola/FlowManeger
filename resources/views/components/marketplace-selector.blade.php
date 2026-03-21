{{-- ============================================================
     COMPONENTE: Seletor Visual de Marketplace
     Uso: <x-marketplace-selector wire:model="marketplaces" />
     Parâmetros:
       - $selected: array com os canais ativos ['ml', 'shopee', 'both']
     ============================================================ --}}
@props(['selected' => []])

<div class="marketplace-selector" x-data="{
    selected: @js($selected),
    toggle(channel) {
        const idx = this.selected.indexOf(channel);
        if (idx > -1) {
            this.selected.splice(idx, 1);
        } else {
            this.selected.push(channel);
        }
        $dispatch('marketplace-changed', { channels: this.selected });
    },
    isActive(channel) { return this.selected.includes(channel); }
}">

    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
        Publicar em
    </p>

    <div class="flex gap-2 flex-wrap">

        {{-- ── Mercado Livre ── --}}
        <button type="button" @click="toggle('ml')"
                :class="isActive('ml')
                    ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 shadow-md scale-[1.02]'
                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-yellow-300'"
                class="flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 transition-all duration-200
                       cursor-pointer select-none min-w-[130px]">

            {{-- Logo ML (SVG simplificado) --}}
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background: #FFE600;">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#1A3760">
                    <path d="M12 2L3 7v10l9 5 9-5V7L12 2zm0 2.26L18.74 8 12 11.74 5.26 8 12 4.26zM4 9.09l7 3.88V19.8L4 15.91V9.09zm9 10.72V12.97l7-3.88v6.82l-7 3.89z"/>
                </svg>
            </div>

            <div class="text-left">
                <p class="text-xs font-bold text-gray-900 dark:text-white leading-tight">Mercado Livre</p>
                <p x-show="isActive('ml')" class="text-xs text-yellow-600 dark:text-yellow-400 font-medium">Ativo</p>
                <p x-show="!isActive('ml')" class="text-xs text-gray-400">Inativo</p>
            </div>

            {{-- Checkmark --}}
            <div :class="isActive('ml') ? 'opacity-100' : 'opacity-0'"
                 class="ml-auto w-5 h-5 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0 transition-opacity">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </button>

        {{-- ── Shopee ── --}}
        <button type="button" @click="toggle('shopee')"
                :class="isActive('shopee')
                    ? 'border-orange-400 bg-orange-50 dark:bg-orange-900/20 shadow-md scale-[1.02]'
                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-orange-300'"
                class="flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 transition-all duration-200
                       cursor-pointer select-none min-w-[130px]">

            {{-- Logo Shopee --}}
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background: linear-gradient(135deg,#EE4D2D,#FF6633);">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.686 2 6 4.686 6 8c0 1.657.672 3.157 1.757 4.243A6.956 6.956 0 005 18v2h14v-2a6.956 6.956 0 00-2.757-5.757A5.978 5.978 0 0018 8c0-3.314-2.686-6-6-6z"/>
                </svg>
            </div>

            <div class="text-left">
                <p class="text-xs font-bold text-gray-900 dark:text-white leading-tight">Shopee</p>
                <p x-show="isActive('shopee')" class="text-xs text-orange-600 dark:text-orange-400 font-medium">Ativo</p>
                <p x-show="!isActive('shopee')" class="text-xs text-gray-400">Inativo</p>
            </div>

            {{-- Checkmark --}}
            <div :class="isActive('shopee') ? 'opacity-100' : 'opacity-0'"
                 class="ml-auto w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center flex-shrink-0 transition-opacity">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </button>

    </div>

    {{-- Hint: ambos simultâneos --}}
    <p x-show="isActive('ml') && isActive('shopee')"
       x-transition
       class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Publicando em ambos os canais simultaneamente
    </p>
</div>
