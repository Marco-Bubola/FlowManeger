{{-- Lista de produtos escolhidos (destaques ou coleção), com ordem e remover. --}}
@if($items->isEmpty())
    <p class="text-sm text-slate-500 dark:text-slate-400 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 text-center">{{ $empty }}</p>
@else
    <ol class="grid grid-cols-1 gap-2 {{ $cols ?? 'sm:grid-cols-2 2xl:grid-cols-3' }}" data-testid="picked-list">
        @foreach($items as $p)
            <li wire:key="pd-{{ $move }}-{{ $p->id }}" class="min-w-0 flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2">
                <span class="w-6 text-center text-xs font-bold text-slate-400">{{ $loop->iteration }}</span>
                <img src="{{ $p->image_url }}" alt="" class="w-11 h-11 rounded-lg object-cover bg-slate-100 shrink-0" loading="lazy">
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $p->name }}</span>
                    @if($p->sf_available)
                        <span class="block text-xs text-slate-500">{{ $p->product_code }}</span>
                    @else
                        <span class="block text-xs font-semibold text-amber-600"><i class="bi bi-exclamation-triangle"></i> Esgotado, não aparece agora</span>
                    @endif
                </span>
                <span class="flex items-center gap-0.5 shrink-0">
                    <button type="button" wire:click="{{ $move }}({{ $loop->index }}, -1)" class="w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-25" title="Subir" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button>
                    <button type="button" wire:click="{{ $move }}({{ $loop->index }}, 1)" class="w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-25" title="Descer" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button>
                    <button type="button" wire:click="{{ $remove }}({{ $p->id }})" class="w-8 h-8 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10" title="Tirar"><i class="bi bi-x-lg"></i></button>
                </span>
            </li>
        @endforeach
    </ol>
@endif
