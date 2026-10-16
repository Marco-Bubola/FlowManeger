@props([
    'paginator',
    // Nome do que está sendo listado, no plural ("vendas", "categorias").
    'label' => 'itens',
    'pageName' => 'page',
    'method' => 'loadMore',
])

@php
    // Fim da lista com rolagem infinita (mesmo jeito da lista de produtos): quando chega
    // perto, chama loadMore no componente; "Mostrar mais" fica de reserva.
    $pg = $paginator;
    $hasMore = $pg->hasMorePages();
    $shown = $pg->count();
    $total = method_exists($pg, 'total') ? $pg->total() : null;
    $call = $method . '(' . \Illuminate\Support\Js::from($pageName) . ')';
@endphp

<script>
    window.infiniteFeed = window.infiniteFeed || ((hasMore, call) => ({
        busy: false,
        onScroll: null,
        init() {
            if (!hasMore) return;
            // Checa pela posição (o layout tem áreas com overflow que atrapalham o IntersectionObserver)
            let raf = 0;
            this.onScroll = () => {
                if (raf) return;
                raf = requestAnimationFrame(() => { raf = 0; this.check(); });
            };
            window.addEventListener('scroll', this.onScroll, { passive: true, capture: true });
            window.addEventListener('resize', this.onScroll, { passive: true });
            setTimeout(() => this.check(), 300);
        },
        check() {
            if (this.busy || !this.$el.isConnected) return;
            const r = this.$el.getBoundingClientRect();
            if (r.top - window.innerHeight > 900 || r.height === 0) return;
            this.busy = true;
            Promise.resolve(this.$wire[call.m](...call.a)).finally(() => { this.busy = false; });
        },
        destroy() {
            if (!this.onScroll) return;
            window.removeEventListener('scroll', this.onScroll, { capture: true });
            window.removeEventListener('resize', this.onScroll);
        },
    }));
</script>

@if($total === null || $total > 0)
<div {{ $attributes->merge(['class' => 'infinite-feed mt-6 mb-4 flex flex-col items-center gap-2']) }}
     x-data="infiniteFeed({{ $hasMore ? 'true' : 'false' }}, {{ \Illuminate\Support\Js::from(['m' => $method, 'a' => [$pageName]]) }})"
     wire:key="feed-{{ $pageName }}-{{ $shown }}-{{ $total }}">
    @if($hasMore)
        <div class="inline-flex items-center gap-2 rounded-full bg-white/80 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-700/70 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 shadow-sm">
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-indigo-500 border-t-transparent"></span>
            Carregando mais {{ $label }}...
        </div>
        <button type="button" wire:click="{{ $call }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-300 underline-offset-2 hover:underline">Mostrar mais</button>
    @endif
    @if($total !== null)
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Mostrando {{ number_format($shown, 0, ',', '.') }} de {{ number_format($total, 0, ',', '.') }} {{ $label }}</p>
    @endif
</div>
@endif
