{{-- Páginas especiais de produto no catálogo. --}}
@php
    $svc = \App\Services\Portal\ProductPageService::class;
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $img = fn ($p) => collect((array) $p->all_images)->filter()->first();
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
    <x-page-header title="Páginas de produto" icon="bi-file-earmark-richtext" section="produtos" back-route="promotions.storefront"
                   subtitle="Uma página especial para os produtos que você quer destacar" />

    <x-portal-settings-nav current="paginas">

    <section class="{{ $card }} p-4 space-y-3" data-testid="pp-pages-search">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-plus-circle text-violet-500"></i>Criar página para um produto</h2>
        <div class="relative">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Busque pelo nome ou código do produto" data-testid="pp-pages-q"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white focus:border-violet-400 focus:ring-2 focus:ring-violet-100 focus:outline-none">
        </div>
        @if(trim($search) !== '')
            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($found as $p)
                    <a href="{{ route('promotions.product-page.edit', $p->id) }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-2 hover:border-violet-400 transition">
                        <span class="w-12 h-12 shrink-0 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden">@if($i = $img($p))<img src="{{ $i }}" class="w-full h-full object-contain" alt="">@endif</span>
                        <span class="min-w-0 flex-1"><b class="block text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $p->name }}</b><small class="text-xs text-slate-500">{{ $p->product_code }}</small></span>
                        <span class="text-xs font-semibold {{ in_array($p->id, $withPage) ? 'text-emerald-600' : 'text-violet-600' }}">{{ in_array($p->id, $withPage) ? 'Editar' : 'Criar' }} <i class="bi bi-chevron-right"></i></span>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">Nenhum produto com esse nome.</p>
                @endforelse
            </div>
        @endif
    </section>

    <section class="{{ $card }} p-4 space-y-4" data-testid="pp-variants-setting">
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-ui-radios text-violet-500"></i>Variações na página do produto</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Como o cliente escolhe tamanho, cor ou modelo. Vale para todos os produtos; a página especial de um produto pode usar outro jeito.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach($svc::VARIANT_MODES as $k => [$n, $ic, $desc])
                <button type="button" wire:click="setVariants('{{ $k }}')" data-testid="pp-variants-{{ $k }}"
                        class="text-left rounded-2xl border-2 p-3 flex gap-3 items-start transition {{ $variants === $k ? 'border-violet-500 bg-violet-50/60 dark:bg-violet-500/10' : 'border-slate-200 dark:border-slate-700 hover:border-violet-300' }}">
                    <span class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $variants === $k ? 'bg-violet-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}"><i class="bi {{ $ic }}"></i></span>
                    <span><b class="block text-sm text-slate-900 dark:text-white">{{ $n }}</b><small class="text-xs text-slate-500">{{ $desc }}</small></span>
                    @if($variants === $k)<i class="bi bi-check-circle-fill text-violet-600 ml-auto"></i>@endif
                </button>
            @endforeach
        </div>
        @if($variants === 'lista')
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_340px] items-start">
                <div>
                    <span class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Cores do menu</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($svc::VARIANT_THEMES as $k => [$n, $c1, $c2])
                            <button type="button" wire:click="setVariants('lista', '{{ $k }}')" data-testid="pp-vtheme-{{ $k }}"
                                    class="inline-flex items-center gap-2 rounded-full pl-1 pr-3 py-1 text-xs font-semibold border-2 {{ $variantsTheme === $k ? 'border-violet-500 text-violet-700 dark:text-violet-300' : 'border-transparent bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                                <span class="w-7 h-7 rounded-full" style="background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }})"></span>{{ $n }}
                            </button>
                        @endforeach
                    </div>
                </div>
                @include('livewire.promotions.partials.variants-demo', ['theme' => $variantsTheme])
            </div>
        @endif
    </section>

    @if($pages->isEmpty())
        <x-empty-state icon="bi-file-earmark-richtext" tone="violet" title="Nenhuma página especial ainda"
                       text="Busque um produto acima para montar a página dele com banner, fotos, vídeo, medidas, perguntas e depoimentos." />
    @else
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" data-testid="pp-pages-list">
            @foreach($pages as $pg)
                @php $p = $pg->product; @endphp
                <div class="{{ $card }} p-3 flex flex-col gap-3" wire:key="pg-{{ $pg->id }}">
                    <div class="flex items-center gap-3">
                        <span class="w-14 h-14 shrink-0 rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden">@if($i = $img($p))<img src="{{ $i }}" class="w-full h-full object-contain" alt="">@endif</span>
                        <span class="min-w-0">
                            <b class="block text-sm font-semibold text-slate-900 dark:text-white line-clamp-2">{{ $p->name }}</b>
                            <small class="text-xs text-slate-500">{{ count($pg->blocks ?? []) }} blocos · mudada {{ $pg->updated_at?->diffForHumans() }}</small>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="toggle({{ $pg->id }})" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $pg->active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800' }}">
                            <i class="bi {{ $pg->active ? 'bi-broadcast' : 'bi-pause-circle' }}"></i>{{ $pg->active ? 'No ar' : 'Desligada' }}
                        </button>
                        <a href="{{ \App\Http\Controllers\ClientPortalController::productUrl($p) }}?previa=1" target="_blank" rel="noopener" class="{{ $phBtn }} !py-1.5 ml-auto"><i class="bi bi-box-arrow-up-right"></i>Ver</a>
                        <a href="{{ route('promotions.product-page.edit', $p->id) }}" wire:navigate class="{{ $phBtn }} !py-1.5"><i class="bi bi-pencil text-violet-500"></i>Editar</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    </x-portal-settings-nav>
</div>
