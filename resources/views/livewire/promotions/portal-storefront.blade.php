@php
    $front = \App\Services\Portal\StorefrontService::class;
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $iconBtn = 'inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-default';
    $primary = 'inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 shadow-md shadow-violet-500/25 transition whitespace-nowrap';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 focus:outline-none';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'sky' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300',
        'amber' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200',
    ];
    $bState = function ($b) {
        $today = now()->startOfDay();
        return match (true) {
            ! $b->active => ['Desligado', 'slate'],
            $b->ends_at && $today->gt($b->ends_at) => ['Vencido', 'rose'],
            $b->starts_at && $today->lt($b->starts_at) => ['Agendado', 'sky'],
            default => ['No ar', 'emerald'],
        };
    };
    $sizes = [
        'hero'  => ['Topo (carrossel)', 'Aparece no alto da página inicial e passa sozinho. Imagem 1200 × 400 px; para o celular, 1080 × 608 px (opcional).', 'bi-images'],
        'strip' => ['Faixas entre as seções', 'Uma faixa depois das ofertas e outra depois de cada coleção. Imagem 1200 × 200 px; celular 900 × 300 px (opcional).', 'bi-distribute-vertical'],
        'side'  => ['Laterais', 'Ficam fixas dos lados do catálogo em monitores largos (Full HD e ultrawide). Imagem 160 × 600 px.', 'bi-layout-sidebar-inset'],
    ];
    $linkLabel = function ($b) use ($collections) {
        return match ($b->link_type) {
            'offers' => 'Leva para Ofertas',
            'collection' => 'Leva para a coleção ' . ($collections->firstWhere('slug', $b->link_value)?->name ?? $b->link_value),
            'category' => 'Leva para uma categoria',
            'product' => 'Leva para um produto',
            'url' => 'Leva para ' . parse_url((string) $b->link_value, PHP_URL_HOST),
            default => 'Sem link',
        };
    };
    $tabs = ['banners' => ['Banners', 'bi-images'], 'destaques' => ['Destaques', 'bi-star'], 'colecoes' => ['Coleções', 'bi-collection'], 'aviso' => ['Aviso no topo', 'bi-megaphone']];
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4" style="--ml-radius: 14px; --ml-blue: #3483fa">
    @include('portal.partials.storefront-styles')
    <x-page-header title="Vitrine do catálogo" icon="bi-shop-window" section="produtos" back-route="promotions.index"
                   subtitle="Banners, produtos em destaque e coleções da página inicial do seu catálogo">
        <x-slot:actions>
            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" title="Abrir o catálogo" class="{{ $phBtn }}" data-testid="open-catalog"><i class="bi bi-box-arrow-up-right text-violet-500"></i><span class="hidden sm:inline">Ver catálogo</span></a>
        </x-slot:actions>
    </x-page-header>

    <nav class="flex gap-1 overflow-x-auto rounded-2xl bg-slate-100 dark:bg-slate-800/70 p-1 [scrollbar-width:none]" aria-label="Partes da vitrine">
        @foreach($tabs as $key => [$name, $ico])
            <button type="button" wire:click="$set('tab', '{{ $key }}')" data-testid="tab-{{ $key }}"
                    class="flex-1 min-w-max inline-flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition {{ $tab === $key ? 'bg-white dark:bg-slate-900 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                <i class="bi {{ $ico }}"></i>{{ $name }}
            </button>
        @endforeach
    </nav>

    {{-- ===================== Banners ===================== --}}
    @if($tab === 'banners')
        <div class="space-y-4">
            <section class="{{ $card }} p-4 sm:p-5 overflow-hidden" data-testid="tpl-row">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-lg"><i class="bi bi-magic"></i></div>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-bold text-slate-900 dark:text-white">Modelos prontos</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Toque em um modelo para criar o banner do topo. Você muda textos, cores e fotos, e pode usar em faixa ou lateral também.</p>
                    </div>
                </div>
                <div class="-mx-4 sm:-mx-5 px-4 sm:px-5 scroll-px-4 sm:scroll-px-5 mt-3 flex gap-3 overflow-x-auto pb-2 snap-x [scrollbar-width:thin]">
                    @foreach($front::TEMPLATES as $key => $t)
                        <button type="button" wire:click="newBanner('hero', '{{ $key }}')" wire:key="tplrow-{{ $key }}" data-testid="tplrow-{{ $key }}"
                                class="group snap-start shrink-0 rounded-2xl p-1.5 text-left ring-1 ring-slate-200 dark:ring-slate-700 hover:ring-2 hover:ring-violet-400 transition">
                            @include('livewire.promotions.partials.storefront-mini', ['kind' => 'hero', 'scale' => 0.36, 'tpl' => $key, 'c1' => $t['c1'], 'c2' => $t['c2'], 'acc' => $t['acc'], 'ink' => $t['ink'],
                                'badge' => $t['badge'], 'title' => $t['title'], 'subtitle' => $t['subtitle'], 'button' => $t['button'], 'photos' => array_slice($samples, 0, $key === 'vitrine' ? 3 : 1)])
                            <span class="flex items-center justify-between gap-2 px-1 pt-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $t['name'] }}<span class="text-violet-600 dark:text-violet-300 opacity-0 group-hover:opacity-100 transition"><i class="bi bi-plus-lg"></i> Usar</span></span>
                        </button>
                    @endforeach
                </div>
            </section>
            @foreach($sizes as $place => [$title, $help, $ico])
                @php $list = $banners[$place] ?? collect(); @endphp
                <section class="{{ $card }} p-4 sm:p-5" data-testid="banners-{{ $place }}">
                    <div class="flex items-start gap-3 flex-wrap">
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-300 text-lg"><i class="bi {{ $ico }}"></i></div>
                        <div class="min-w-0 flex-1 self-center">
                            <h2 class="font-bold text-slate-900 dark:text-white">{{ $title }} <span class="text-sm font-normal text-slate-400">({{ $list->count() }})</span></h2>
                        </div>
                        <button type="button" wire:click="newBanner('{{ $place }}')" class="{{ $phBtn }}" data-testid="banner-new-{{ $place }}"><i class="bi bi-plus-lg text-violet-500"></i>Adicionar</button>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 sm:pl-[52px]">{{ $help }}</p>

                    @if($list->isEmpty())
                        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 text-center">
                            @if($place === 'hero') Sem banner, o topo mostra o destaque das ofertas da semana. @else Nenhum banner aqui ainda. @endif
                        </p>
                    @else
                        <div class="mt-4 grid gap-3 grid-cols-1 md:grid-cols-2 2xl:grid-cols-3">
                            @foreach($list as $b)
                                @php [$st, $tone] = $bState($b); @endphp
                                <article wire:key="bn-{{ $b->id }}" class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden flex {{ $place === 'side' ? 'flex-row' : 'flex-col' }} {{ $b->active ? '' : 'opacity-70' }}">
                                    @if($b->isDesign())
                                        @php $dz = $b->design + ['c1' => null, 'c2' => null, 'acc' => null, 'ink' => 'light', 'badge' => null];
                                            $dArgs = ['tpl' => $dz['tpl'], 'c1' => $dz['c1'], 'c2' => $dz['c2'], 'acc' => $dz['acc'], 'ink' => $dz['ink'], 'badge' => $dz['badge'], 'title' => $b->title, 'subtitle' => $b->subtitle, 'button' => $b->button_text, 'photos' => $b->designPhotos()]; @endphp
                                        @if($place === 'side')
                                            <div class="shrink-0 bg-slate-50 dark:bg-slate-800/50 p-1.5">@include('livewire.promotions.partials.storefront-mini', $dArgs + ['kind' => 'side', 'scale' => 0.4])</div>
                                        @else
                                            <div class="sf-ban sf-{{ $place }} has-design !rounded-none">@include('portal.partials.storefront-design', $dArgs)</div>
                                        @endif
                                    @else
                                    <div class="relative {{ $place === 'side' ? 'w-16 shrink-0 aspect-[4/15]' : ($place === 'strip' ? 'aspect-[6/1] min-h-[56px]' : 'aspect-[3/1]') }} overflow-hidden" style="background: {{ $front::gradient($b->color) }}">
                                        @if($b->image)
                                            <img src="{{ $b->imageUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                                        @endif
                                        @if($b->title && $place !== 'side')
                                            <div class="absolute inset-0 flex flex-col justify-center px-3 text-white {{ $b->image ? 'bg-gradient-to-r from-black/50 to-transparent' : '' }}">
                                                <b class="text-sm leading-tight line-clamp-2">{{ $b->title }}</b>
                                                @if($b->subtitle && $place !== 'strip')<span class="text-[11px] opacity-90 line-clamp-1">{{ $b->subtitle }}</span>@endif
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="p-3 flex flex-col gap-2 flex-1 min-w-0">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 min-w-0"><span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones[$tone] }}">{{ $st }}</span><i class="bi bi-link-45deg"></i><span class="truncate">{{ $linkLabel($b) }}</span>@if($b->isDesign())<span class="ml-auto shrink-0 text-[11px] text-violet-600 dark:text-violet-300"><i class="bi bi-magic"></i> {{ $front::TEMPLATES[$b->design['tpl']]['name'] ?? 'Modelo' }}</span>@endif</p>
                                        @if($b->starts_at || $b->ends_at)
                                            <p class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-calendar3"></i> {{ $b->starts_at ? 'de ' . $b->starts_at->format('d/m') : '' }} {{ $b->ends_at ? 'até ' . $b->ends_at->format('d/m/Y') : '' }}</p>
                                        @endif
                                        <div class="flex items-center gap-1.5 mt-auto flex-wrap">
                                            <button type="button" wire:click="moveBanner({{ $b->id }}, -1)" class="{{ $iconBtn }}" title="Mover para antes" @disabled($loop->first)><i class="bi {{ $place === 'side' ? 'bi-arrow-up' : 'bi-arrow-left' }}"></i></button>
                                            <button type="button" wire:click="moveBanner({{ $b->id }}, 1)" class="{{ $iconBtn }}" title="Mover para depois" @disabled($loop->last)><i class="bi {{ $place === 'side' ? 'bi-arrow-down' : 'bi-arrow-right' }}"></i></button>
                                            <button type="button" wire:click="toggleBanner({{ $b->id }})" class="{{ $iconBtn }}" title="{{ $b->active ? 'Desligar' : 'Ligar' }}"><i class="bi {{ $b->active ? 'bi-toggle-on text-emerald-500' : 'bi-toggle-off' }} text-lg"></i></button>
                                            <button type="button" wire:click="editBanner({{ $b->id }})" class="{{ $iconBtn }} ml-auto" title="Editar" data-testid="banner-edit"><i class="bi bi-pencil text-indigo-500"></i></button>
                                            <button type="button" x-data @click="fmConfirm({ title: 'Excluir este banner?', message: 'Ele sai do catálogo na hora.', confirmText: 'Excluir' }).then(ok => ok && $wire.deleteBanner({{ $b->id }}))" class="{{ $iconBtn }}" title="Excluir"><i class="bi bi-trash3 text-rose-500"></i></button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    @endif

    {{-- ===================== Destaques ===================== --}}
    @if($tab === 'destaques')
        <section class="{{ $card }} p-4 sm:p-5 space-y-4" data-testid="featured-panel">
            <div class="flex items-start gap-3 flex-wrap">
                <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-500/15 text-amber-500 text-lg"><i class="bi bi-star-fill"></i></div>
                <div class="min-w-0 flex-1">
                    <h2 class="font-bold text-slate-900 dark:text-white">Produtos em destaque</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Uma fileira logo abaixo das categorias, na ordem que você montar. Produto esgotado some sozinho e volta quando tiver estoque.</p>
                </div>
                <button type="button" wire:click="openPicker('featured')" class="{{ $phBtn }}" data-testid="featured-pick"><i class="bi bi-plus-lg text-violet-500"></i>Escolher produtos</button>
            </div>
            <div class="max-w-md">
                <label class="{{ $label }}" for="sf-ft">Título da fileira</label>
                <input id="sf-ft" type="text" wire:model="featured_title" maxlength="60" placeholder="Destaques da loja" class="{{ $input }}">
                @error('featured_title')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>
            @include('livewire.promotions.partials.storefront-picked', ['items' => $featured, 'move' => 'moveFeatured', 'remove' => 'removeFeatured', 'empty' => 'Nenhum produto em destaque. Toque em Escolher produtos.'])
            <div class="flex justify-end">
                <button type="button" wire:click="saveFeatured" class="{{ $primary }}" data-testid="featured-save"><i class="bi bi-check2"></i><span wire:loading.remove wire:target="saveFeatured">Salvar destaques</span><span wire:loading wire:target="saveFeatured">Salvando…</span></button>
            </div>
        </section>
    @endif

    {{-- ===================== Coleções ===================== --}}
    @if($tab === 'colecoes')
        <div class="space-y-3">
            <div class="flex items-center gap-3 flex-wrap">
                <p class="text-sm text-slate-500 dark:text-slate-400 flex-1 min-w-[220px]">Grupos de produtos com banner próprio, como "Dia das Mães" ou "Kits para presente". Cada coleção vira uma fileira na página inicial e tem a sua página.</p>
                <button type="button" wire:click="newCollection" class="{{ $primary }}" data-testid="collection-new"><i class="bi bi-plus-lg"></i>Nova coleção</button>
            </div>
            @if($collections->isEmpty())
                <x-empty-state icon="bi-collection" tone="violet" title="Nenhuma coleção ainda"
                               text="Monte uma coleção escolhendo os produtos, dê um nome e, se quiser, uma imagem de capa." />
            @else
                <div class="grid gap-3 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" data-testid="collection-list">
                    @foreach($collections as $c)
                        <article wire:key="col-{{ $c->id }}" class="{{ $card }} overflow-hidden flex flex-col {{ $c->active ? '' : 'opacity-70' }}">
                            <div class="relative aspect-[4/1] min-h-[84px] overflow-hidden text-white" style="background: {{ $front::gradient($c->color) }}">
                                @if($c->image)<img src="{{ $c->imageUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover"><div class="absolute inset-0 bg-gradient-to-r from-black/55 to-transparent"></div>@endif
                                <div class="relative h-full flex flex-col justify-center px-4">
                                    <b class="text-base leading-tight line-clamp-1">{{ $c->name }}</b>
                                    @if($c->description)<span class="text-xs opacity-90 line-clamp-1">{{ $c->description }}</span>@endif
                                </div>
                            </div>
                            <div class="p-3 flex flex-col gap-2 flex-1">
                                <p class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-box-seam"></i> {{ (int) ($counts[$c->id] ?? 0) }} {{ ($counts[$c->id] ?? 0) == 1 ? 'produto' : 'produtos' }}</p>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" wire:click="toggleCollection({{ $c->id }}, 'active')" class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $c->active ? $tones['emerald'] : $tones['slate'] }}"><i class="bi {{ $c->active ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i> {{ $c->active ? 'Ligada' : 'Desligada' }}</button>
                                    <button type="button" wire:click="toggleCollection({{ $c->id }}, 'show_on_home')" class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $c->show_on_home ? $tones['sky'] : $tones['slate'] }}"><i class="bi {{ $c->show_on_home ? 'bi-house-check' : 'bi-house-slash' }}"></i> {{ $c->show_on_home ? 'Na página inicial' : 'Fora da página inicial' }}</button>
                                </div>
                                <div class="flex items-center gap-1.5 mt-auto pt-1">
                                    <button type="button" wire:click="moveCollection({{ $c->id }}, -1)" class="{{ $iconBtn }}" title="Mover para cima" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button>
                                    <button type="button" wire:click="moveCollection({{ $c->id }}, 1)" class="{{ $iconBtn }}" title="Mover para baixo" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button>
                                    <a href="{{ route('portal.catalog', ['userId' => auth()->id(), 'colecao' => $c->slug]) }}" target="_blank" rel="noopener" class="{{ $iconBtn }}" title="Ver no catálogo"><i class="bi bi-box-arrow-up-right"></i></a>
                                    <button type="button" wire:click="editCollection({{ $c->id }})" class="{{ $phBtn }} !py-1.5 ml-auto" data-testid="collection-edit"><i class="bi bi-pencil text-indigo-500"></i>Editar</button>
                                    <button type="button" x-data @click="fmConfirm({ title: 'Excluir a coleção {{ addslashes($c->name) }}?', message: 'Os produtos continuam no catálogo; só o grupo some.', confirmText: 'Excluir' }).then(ok => ok && $wire.deleteCollection({{ $c->id }}))" class="{{ $iconBtn }}" title="Excluir"><i class="bi bi-trash3 text-rose-500"></i></button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ===================== Aviso no topo ===================== --}}
    @if($tab === 'aviso')
        <section class="{{ $card }} p-4 sm:p-5 space-y-4 max-w-2xl" data-testid="notice-panel">
            <div class="flex items-start gap-3">
                <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-500/15 text-amber-600 text-lg"><i class="bi bi-megaphone"></i></div>
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white">Aviso no topo do catálogo</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Uma linha curta que aparece em todas as páginas do catálogo. Ex.: "Entrega grátis no bairro acima de R$ 150" ou "Use o cupom BEMVINDA10".</p>
                </div>
            </div>
            <div>
                <label class="{{ $label }}" for="sf-nt">Texto do aviso</label>
                <input id="sf-nt" type="text" wire:model.live.debounce.400ms="notice_text" maxlength="160" placeholder="Entrega grátis no bairro acima de R$ 150" class="{{ $input }}">
                @error('notice_text')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $label }}" for="sf-nl">Link ao tocar <span class="font-normal text-slate-400">(opcional)</span></label>
                <input id="sf-nl" type="url" wire:model="notice_link" placeholder="https://" class="{{ $input }}">
                @error('notice_link')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                <input type="checkbox" wire:model="notice_active" class="mt-0.5 rounded text-violet-500 focus:ring-violet-400">
                <span class="text-sm text-slate-700 dark:text-slate-200"><b>Mostrar o aviso</b><small class="block text-xs text-slate-500">Desligado, o texto fica guardado aqui.</small></span>
            </label>
            @if(trim($notice_text) !== '')
                <div>
                    <span class="{{ $label }}">Como fica</span>
                    <p class="flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-3 py-2 text-sm font-semibold text-center"><i class="bi bi-megaphone-fill text-amber-500"></i>{{ $notice_text }}</p>
                </div>
            @endif
            <div class="flex justify-end">
                <button type="button" wire:click="saveNotice" class="{{ $primary }}" data-testid="notice-save"><i class="bi bi-check2"></i>Salvar aviso</button>
            </div>
        </section>
    @endif

    {{-- ===================== Janela: banner ===================== --}}
    @if($showBanner)
        @include('livewire.promotions.partials.banner-form')
    @endif

    {{-- ===================== Janela: coleção ===================== --}}
    @if($showCollection)
        @php $cPreview = $c_image ? $c_image->temporaryUrl() : ($c_image_old ? asset('storage/' . $c_image_old) : null); @endphp
        <div class="fixed inset-0 z-[90] flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="$set('showCollection', false)">
            <div class="absolute inset-0" wire:click="$set('showCollection', false)"></div>
            <form wire:submit="saveCollection" class="relative w-full sm:max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl bg-white dark:bg-slate-900 shadow-2xl p-5 sm:p-6 space-y-4 pb-[calc(1.25rem+env(safe-area-inset-bottom))]" data-testid="collection-form">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $colId ? 'Editar coleção' : 'Nova coleção' }}</h2>
                    <button type="button" wire:click="$set('showCollection', false)" class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="relative aspect-[4/1] min-h-[84px] rounded-xl overflow-hidden text-white" style="background: {{ $front::gradient($c_color) }}">
                    @if($cPreview)<img src="{{ $cPreview }}" alt="" class="absolute inset-0 w-full h-full object-cover"><div class="absolute inset-0 bg-gradient-to-r from-black/55 to-transparent"></div>@endif
                    <div class="relative h-full flex flex-col justify-center px-4">
                        <small class="text-[10px] font-bold uppercase tracking-widest opacity-80">Coleção</small>
                        <b class="text-lg sm:text-xl font-black leading-tight line-clamp-1">{{ $c_name ?: 'Nome da coleção' }}</b>
                        @if($c_description)<span class="text-xs opacity-90 line-clamp-1">{{ $c_description }}</span>@endif
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}" for="col-name">Nome</label>
                        <input id="col-name" type="text" wire:model.live.debounce.400ms="c_name" maxlength="80" placeholder="Ex.: Dia das Mães" class="{{ $input }}" data-testid="collection-name">
                        @error('c_name')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <span class="{{ $label }}">Imagem de capa <span class="font-normal text-slate-400">(1200 × 300 px, opcional)</span></span>
                        <div class="flex items-center gap-2">
                            <label class="{{ $phBtn }} cursor-pointer flex-1 justify-center"><i class="bi bi-upload text-violet-500"></i><span wire:loading.remove wire:target="c_image">{{ $cPreview ? 'Trocar imagem' : 'Escolher imagem' }}</span><span wire:loading wire:target="c_image">Enviando…</span>
                                <input type="file" wire:model="c_image" accept="image/jpeg,image/png,image/webp" class="sr-only"></label>
                            @if($cPreview)<button type="button" wire:click="removeCollectionImage" class="{{ $iconBtn }}" title="Tirar a imagem"><i class="bi bi-x-lg text-rose-500"></i></button>@endif
                        </div>
                        @error('c_image')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}" for="col-desc">Frase curta <span class="font-normal text-slate-400">(opcional)</span></label>
                        <input id="col-desc" type="text" wire:model.live.debounce.400ms="c_description" maxlength="200" placeholder="Ex.: Presentes que ela vai amar" class="{{ $input }}">
                    </div>
                </div>
                @if(! $cPreview)
                    <div>
                        <span class="{{ $label }}">Cor do fundo</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($front::COLORS as $k => [$n, $g])
                                <button type="button" wire:click="$set('c_color', '{{ $k }}')" title="{{ $n }}" aria-label="{{ $n }}" class="w-9 h-9 rounded-full ring-offset-2 dark:ring-offset-slate-900 {{ $c_color === $k ? 'ring-2 ring-violet-500' : '' }}" style="background: {{ $g }}"></button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Produtos da coleção ({{ count($c_products) }})</span>
                        <button type="button" wire:click="openPicker('collection')" class="{{ $phBtn }} !py-1.5" data-testid="collection-pick"><i class="bi bi-plus-lg text-violet-500"></i>Escolher produtos</button>
                    </div>
                    @include('livewire.promotions.partials.storefront-picked', ['items' => $colProducts, 'move' => 'moveCollectionProduct', 'remove' => 'removeCollectionProduct', 'cols' => 'grid-cols-1', 'empty' => 'Nenhum produto ainda. Toque em Escolher produtos.'])
                    @error('c_products')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                        <input type="checkbox" wire:model="c_active" class="mt-0.5 rounded text-violet-500 focus:ring-violet-400">
                        <span class="text-sm text-slate-700 dark:text-slate-200"><b>Ligada</b><small class="block text-xs text-slate-500">Desligada, a coleção some do catálogo.</small></span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                        <input type="checkbox" wire:model="c_home" class="mt-0.5 rounded text-violet-500 focus:ring-violet-400">
                        <span class="text-sm text-slate-700 dark:text-slate-200"><b>Na página inicial</b><small class="block text-xs text-slate-500">Fora dela, só abre por banner ou link.</small></span>
                    </label>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="button" wire:click="$set('showCollection', false)" class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</button>
                    <button type="submit" class="flex-1 {{ $primary }}" data-testid="collection-save" wire:loading.attr="disabled" wire:target="saveCollection,c_image"><span wire:loading.remove wire:target="saveCollection">Salvar coleção</span><span wire:loading wire:target="saveCollection">Salvando…</span></button>
                </div>
            </form>
        </div>
    @endif

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
</div>
