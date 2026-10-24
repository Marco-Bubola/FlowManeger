{{-- Janela de escolher produtos: $pickFor, $pickSearch, $results, $picked --}}
{{-- ===================== Janela: escolher produtos ===================== --}}
@if($pickFor !== '')
    <div class="fixed inset-0 z-[95] flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="closePicker">
        <div class="absolute inset-0" wire:click="closePicker"></div>
        <div class="relative w-full sm:max-w-2xl h-[88vh] sm:h-auto sm:max-h-[86vh] flex flex-col rounded-t-3xl sm:rounded-3xl bg-white dark:bg-slate-900 shadow-2xl" data-testid="product-picker">
            <div class="p-4 sm:p-5 pb-3 space-y-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $pickFor === 'banner' ? 'Escolher o produto' : 'Escolher produtos' }}</h2>
                    <button type="button" wire:click="closePicker" class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" wire:model.live.debounce.300ms="pickSearch" placeholder="Buscar por nome ou código" class="{{ $input }} pl-9" autofocus data-testid="picker-search">
                </div>
            </div>
            <ul class="flex-1 overflow-y-auto overscroll-contain p-2 sm:p-3 grid gap-1">
                @forelse($results as $p)
                    @php $on = in_array($p->id, $picked, true); @endphp
                    <li wire:key="pk-{{ $p->id }}">
                        <button type="button" wire:click="pick({{ $p->id }})" class="w-full flex items-center gap-3 rounded-xl p-2 text-left transition {{ $on ? 'bg-violet-50 dark:bg-violet-500/15 ring-1 ring-violet-300 dark:ring-violet-500/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800' }}" data-testid="picker-item">
                            <img src="{{ $p->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover bg-slate-100 shrink-0" loading="lazy">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-2">{{ $p->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $p->product_code }} · {{ $money($p->price_sale) }}</span>
                            </span>
                            <span class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center {{ $on ? 'bg-violet-500 text-white' : 'border border-slate-300 dark:border-slate-600 text-transparent' }}"><i class="bi bi-check-lg"></i></span>
                        </button>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-slate-500">Nenhum produto encontrado.</li>
                @endforelse
            </ul>
            <div class="p-3 sm:p-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                @if($pickFor !== 'banner')<span class="text-sm text-slate-500">{{ count($picked) }} {{ count($picked) == 1 ? 'escolhido' : 'escolhidos' }}</span>@endif
                <button type="button" wire:click="closePicker" class="ml-auto {{ $primary }}" data-testid="picker-done">Pronto</button>
            </div>
        </div>
    </div>
@endif
