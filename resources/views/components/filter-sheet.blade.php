@props([
    'show' => 'showFilters',     // variável Alpine que abre/fecha
    'title' => 'Filtros',
    'subtitle' => null,
    'clear' => 'clearFilters',   // ação Livewire do botão Limpar (vazio = sem botão)
    'active' => false,           // mostra o selo "Filtros ativos"
    'target' => null,            // wire:target do aviso "Atualizando..."
])

{{-- Janela única de filtros das listas: sobe de baixo no celular e fica centralizada no iPad/computador
     (mesmo modelo dos filtros de Produtos e Vendas). O conteúdo vem no slot, em blocos .fm-fs-group. --}}
<template x-teleport="body">
    <div x-show="{{ $show }}" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @keydown.escape.window="{{ $show }} = false"
         class="fm-fs-root fixed inset-0 z-[9999]" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" @click="{{ $show }} = false"></div>

        <div class="relative h-full flex items-end md:items-center justify-center md:p-6 pointer-events-none">
            <div x-show="{{ $show }}"
                 x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 translate-y-8 md:translate-y-0 md:scale-95" x-transition:enter-end="opacity-100 translate-y-0 md:scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-8 md:translate-y-0 md:scale-95"
                 class="fm-fs-panel pointer-events-auto">
                <div class="fm-fs-grab md:hidden"></div>
                <div class="fm-fs-head">
                    <span class="fm-fs-head-icon"><i class="bi bi-funnel"></i></span>
                    <div class="min-w-0 flex-1">
                        <h3 class="fm-fs-title">{{ $title }}</h3>
                        @if($subtitle)<p class="fm-fs-sub">{{ $subtitle }}</p>@endif
                    </div>
                    @if($active)<span class="fm-fs-badge"><i class="bi bi-check-circle"></i> Filtros ativos</span>@endif
                    <button type="button" class="fm-fs-x" @click="{{ $show }} = false" title="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="fm-fs-body">
                    {{ $slot }}
                    @if($target)
                        <div wire:loading.flex wire:target="{{ $target }}" class="fm-fs-loading">
                            <i class="bi bi-arrow-repeat animate-spin"></i> Atualizando...
                        </div>
                    @endif
                </div>

                <div class="fm-fs-foot">
                    @if($clear)
                        <button type="button" class="fm-fs-btn" wire:click="{{ $clear }}"><i class="bi bi-eraser"></i> Limpar</button>
                    @endif
                    <button type="button" class="fm-fs-btn is-primary" @click="{{ $show }} = false"><i class="bi bi-check2-circle"></i> Ver resultados</button>
                </div>
            </div>
        </div>
    </div>
</template>

@once
<style>
    .fm-fs-panel { width: 100%; max-height: 90dvh; display: flex; flex-direction: column; overflow: hidden;
        border-radius: 1.5rem 1.5rem 0 0; background: #fff; border: 1px solid rgb(226 232 240); box-shadow: 0 -20px 50px -20px rgb(15 23 42 / .45); }
    @media (min-width: 768px) { .fm-fs-panel { max-width: 56rem; border-radius: 1.5rem; max-height: 88vh; } }
    .dark .fm-fs-panel { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .fm-fs-grab { width: 2.5rem; height: .3rem; border-radius: 999px; background: rgb(203 213 225); margin: .55rem auto 0; }
    .fm-fs-head { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border-bottom: 1px solid rgb(241 245 249);
        background: linear-gradient(90deg, rgb(255 255 255), rgb(238 242 255 / .8)); }
    .dark .fm-fs-head { border-bottom-color: rgb(30 41 59); background: linear-gradient(90deg, rgb(15 23 42), rgb(49 46 129 / .25)); }
    .fm-fs-head-icon { width: 2.25rem; height: 2.25rem; flex-shrink: 0; border-radius: .75rem; display: flex; align-items: center; justify-content: center; color: #fff;
        background: linear-gradient(135deg, rgb(99 102 241), rgb(168 85 247)); }
    .fm-fs-title { font-size: 1rem; font-weight: 800; color: rgb(15 23 42); }
    .dark .fm-fs-title { color: #fff; }
    .fm-fs-sub { font-size: .75rem; color: rgb(100 116 139); }
    .fm-fs-badge { flex-shrink: 0; font-size: .68rem; font-weight: 700; padding: .2rem .55rem; border-radius: 999px; color: rgb(4 120 87); background: rgb(209 250 229); }
    .dark .fm-fs-badge { color: rgb(110 231 183); background: rgb(6 78 59 / .5); }
    .fm-fs-x { width: 2.25rem; height: 2.25rem; flex-shrink: 0; border-radius: .75rem; display: flex; align-items: center; justify-content: center; color: rgb(100 116 139); background: rgb(241 245 249); }
    .dark .fm-fs-x { background: rgb(30 41 59); color: rgb(148 163 184); }
    .fm-fs-body { flex: 1; overflow-y: auto; padding: .9rem 1rem; display: grid; gap: .75rem; background: rgb(248 250 252); }
    @media (min-width: 768px) { .fm-fs-body { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 1rem 1.25rem; } }
    .dark .fm-fs-body { background: rgb(2 6 23 / .4); }
    .fm-fs-body > .fm-fs-wide { grid-column: 1 / -1; }
    .fm-fs-group { border-radius: 1rem; border: 1px solid rgb(226 232 240); background: #fff; padding: .75rem; min-width: 0; }
    .dark .fm-fs-group { border-color: rgb(51 65 85); background: rgb(15 23 42 / .7); }
    .fm-fs-group > h4 { display: flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 700; color: rgb(51 65 85); margin-bottom: .55rem; }
    .dark .fm-fs-group > h4 { color: rgb(203 213 225); }
    .fm-fs-options { display: flex; flex-wrap: wrap; gap: .4rem; }
    .fm-fs-opt { display: inline-flex; align-items: center; gap: .35rem; padding: .45rem .75rem; border-radius: .75rem; font-size: .8rem; font-weight: 600;
        color: rgb(51 65 85); background: rgb(248 250 252); border: 1.5px solid rgb(226 232 240); transition: border-color .15s, background .15s; }
    .fm-fs-opt:hover { border-color: rgb(165 180 252); }
    .fm-fs-opt.is-on { color: rgb(67 56 202); background: rgb(238 242 255); border-color: rgb(99 102 241); }
    .dark .fm-fs-opt { color: rgb(226 232 240); background: rgb(30 41 59); border-color: rgb(51 65 85); }
    .dark .fm-fs-opt.is-on { color: rgb(199 210 254); background: rgb(49 46 129 / .45); border-color: rgb(129 140 248); }
    .fm-fs-group select, .fm-fs-group input:not([type=checkbox]):not([type=radio]) { width: 100%; padding: .55rem .75rem; border-radius: .75rem; font-size: .85rem;
        border: 1px solid rgb(226 232 240); background: #fff; color: rgb(15 23 42); }
    .dark .fm-fs-group select, .dark .fm-fs-group input:not([type=checkbox]):not([type=radio]) { border-color: rgb(51 65 85); background: rgb(30 41 59); color: rgb(241 245 249); }
    .fm-fs-loading { grid-column: 1 / -1; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 600; color: rgb(79 70 229); }
    .fm-fs-foot { display: flex; gap: .5rem; padding: .75rem 1rem calc(.75rem + env(safe-area-inset-bottom)); border-top: 1px solid rgb(241 245 249); background: #fff; }
    .dark .fm-fs-foot { border-top-color: rgb(30 41 59); background: rgb(15 23 42); }
    .fm-fs-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .7rem 1rem; border-radius: .9rem; font-size: .875rem; font-weight: 700;
        color: rgb(51 65 85); background: rgb(241 245 249); border: 1px solid rgb(226 232 240); }
    .dark .fm-fs-btn { color: rgb(226 232 240); background: rgb(30 41 59); border-color: rgb(51 65 85); }
    .fm-fs-btn.is-primary { color: #fff; border-color: transparent; background: linear-gradient(90deg, rgb(79 70 229), rgb(147 51 234)); }
    @media (min-width: 768px) { .fm-fs-btn { flex: 0 0 auto; } .fm-fs-foot { justify-content: flex-end; } }
</style>
@endonce
