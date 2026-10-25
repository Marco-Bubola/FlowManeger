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
    $tabs = ['inicio' => ['Página inicial', 'bi-house'], 'banners' => ['Banners', 'bi-images'], 'destaques' => ['Destaques', 'bi-star'], 'colecoes' => ['Coleções', 'bi-collection'], 'aviso' => ['Aviso no topo', 'bi-megaphone']];
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4" style="--ml-radius: 14px; --ml-blue: #3483fa">
    @include('portal.partials.storefront-styles')
    <x-page-header title="Vitrine do catálogo" icon="bi-shop-window" section="produtos" back-route="promotions.index"
                   subtitle="Tudo do seu catálogo online: aparência, vendas e clientes">
        <x-slot:actions>
            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" title="Abrir o catálogo" class="{{ $phBtn }}" data-testid="open-catalog"><i class="bi bi-box-arrow-up-right text-violet-500"></i><span class="hidden sm:inline">Ver catálogo</span></a>
        </x-slot:actions>
    </x-page-header>

    <x-portal-settings-nav :current="$tab" :wire="true" :info="$navInfo">

    {{-- ===================== Página inicial ===================== --}}
    @if($tab === 'inicio')
        @php $home = \App\Services\Portal\HomeLayout::class; $cur = $themes[$home_theme] ?? null; @endphp
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] items-start" data-testid="home-builder">
            <div class="space-y-4 min-w-0">
                {{-- 1. Tema --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-3" data-testid="home-themes">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-lg"><i class="bi bi-palette"></i></div>
                        <div class="min-w-0">
                            <h2 class="font-bold text-slate-900 dark:text-white">Tema da loja</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Escolha um evento e a página inteira muda de uma vez: cores do topo, cantos, ordem dos blocos, banner do evento e aviso. Depois você ajusta o que quiser.</p>
                        </div>
                        @if($cur)<span class="ml-auto shrink-0 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tones['emerald'] }}" data-testid="theme-current">{{ $cur['emoji'] }} {{ $cur['name'] }}</span>@endif
                    </div>
                    <div class="grid grid-cols-2 min-[520px]:grid-cols-3 xl:grid-cols-4 gap-2">
                        @foreach($themes as $k => $t)
                            <button type="button" wire:click="pickTheme('{{ $k }}')" wire:key="th-{{ $k }}" data-testid="theme-{{ $k }}"
                                    class="relative text-left rounded-xl border p-2 transition {{ $pendingTheme === $k ? 'border-violet-500 ring-2 ring-violet-200 dark:ring-violet-500/30' : ($home_theme === $k ? 'border-emerald-400' : 'border-slate-200 dark:border-slate-700 hover:border-violet-300') }}">
                                <span class="block h-9 rounded-lg mb-1.5 relative overflow-hidden" style="background: linear-gradient(90deg, {{ $t['colors'][0] }}, {{ $t['colors'][1] }})">
                                    <span class="absolute right-1.5 bottom-1.5 w-4 h-4 rounded-full border-2 border-white" style="background: {{ $t['colors'][2] }}"></span>
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-lg">{{ $t['emoji'] }}</span>
                                </span>
                                <b class="block text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $t['name'] }}</b>
                                <small class="block text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $t['when'] ? ($t['when']['now'] ? 'Agora, até ' . $t['when']['end']->format('d/m') : 'De ' . $t['when']['start']->format('d/m') . ' a ' . $t['when']['end']->format('d/m')) : ($k === 'padrao' ? 'O dia a dia' : 'Quando quiser') }}</small>
                                @if($home_theme === $k)<i class="bi bi-check-circle-fill absolute top-1 right-1 text-emerald-500 bg-white rounded-full"></i>@endif
                            </button>
                        @endforeach
                    </div>
                    @if($pendingTheme !== '' && isset($themes[$pendingTheme]))
                        @php $pt = $themes[$pendingTheme]; @endphp
                        <div class="rounded-xl bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/30 p-3 space-y-2" data-testid="theme-apply-box">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $pt['emoji'] }} Usar o tema {{ $pt['name'] }}?</p>
                            <p class="text-xs text-slate-600 dark:text-slate-300">Troca as cores do topo, os cantos e a ordem dos blocos ({{ collect($pt['order'])->map(fn ($k) => $home::SECTIONS[$k][0])->take(3)->join(', ') }}…).</p>
                            @if($pt['tpl'])
                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" wire:model="withBanner" class="rounded text-violet-500 focus:ring-violet-400" data-testid="theme-with-banner"> Criar o banner do evento no começo do carrossel{{ $pt['when'] ? ' (sai do ar em ' . $pt['when']['end']->format('d/m') . ')' : ' (por 7 dias)' }}</label>
                            @endif
                            @if($pt['notice'])
                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" wire:model="withNotice" class="rounded text-violet-500 focus:ring-violet-400"> Trocar o aviso do topo por “{{ $pt['notice'][$kind === 'moveis' ? 1 : 0] }}”</label>
                            @endif
                            <div class="flex gap-2 pt-1">
                                <button type="button" wire:click="applyTheme" wire:loading.attr="disabled" class="{{ $primary }}" data-testid="theme-apply"><i class="bi bi-magic"></i>Aplicar tema</button>
                                <button type="button" wire:click="pickTheme('{{ $pendingTheme }}')" class="{{ $phBtn }}">Cancelar</button>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- 2. Cores e cantos --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-3" data-testid="home-colors">
                    <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-droplet-half text-violet-500"></i>Cores e cantos</h2>
                    <div class="grid grid-cols-1 min-[480px]:grid-cols-3 gap-2">
                        @foreach(['Topo, começo', 'Topo, fim', 'Botões e links'] as $i => $cname)
                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-2.5 py-2 cursor-pointer">
                                <input type="color" wire:model.live.debounce.400ms="home_colors.{{ $i }}" class="shrink-0 w-9 h-9 rounded-lg border-0 bg-transparent p-0 cursor-pointer" data-testid="home-color-{{ $i }}">
                                <span class="min-w-0"><b class="block text-xs text-slate-700 dark:text-slate-200">{{ $cname }}</b><small class="block text-[11px] text-slate-400 uppercase">{{ $home_colors[$i] ?? '' }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="h-3 rounded-full" style="background: linear-gradient(90deg, {{ $home_colors[0] ?? '#ec4899' }}, {{ $home_colors[1] ?? '#3b82f6' }})"></div>
                    <div class="grid grid-cols-3 gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                        @foreach($home::STYLES as $k => [$n, $r, $ico])
                            <button type="button" wire:click="setStyle('{{ $k }}')" data-testid="style-{{ $k }}" class="rounded-lg py-2 text-xs sm:text-sm font-semibold {{ $home_style === $k ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}"><i class="bi {{ $ico }}"></i> {{ $n }}</button>
                        @endforeach
                    </div>
                </section>

                {{-- 3. Blocos --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-2" data-testid="home-sections">
                    <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-list-ol text-violet-500"></i>O que aparece e em que ordem</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">De cima para baixo, como o cliente vê. O aviso fica sempre no alto e a lista de todos os produtos sempre no fim.</p>
                    <ol class="space-y-1.5">
                        @foreach($home_order as $k)
                            @php [$sname, $sico, $sdesc] = $home::SECTIONS[$k]; $on = ! in_array($k, $home_off, true); [$sinfo, $stab] = $secInfo[$k] ?? ['', null]; @endphp
                            <li wire:key="sec-{{ $k }}" class="flex items-center gap-2 rounded-xl border px-2.5 py-2 {{ $on ? 'border-slate-200 dark:border-slate-700' : 'border-dashed border-slate-300 dark:border-slate-700 opacity-60' }}" data-testid="sec-{{ $k }}">
                                <span class="shrink-0 w-6 text-center text-xs font-bold text-slate-400">{{ $loop->iteration }}</span>
                                <span class="shrink-0 w-9 h-9 rounded-lg flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600"><i class="bi {{ $sico }}"></i></span>
                                <span class="min-w-0 flex-1">
                                    <b class="block text-sm text-slate-800 dark:text-slate-100 truncate">{{ $sname }}</b>
                                    <small class="block text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $sinfo ?: $sdesc }}@if($stab) · <a href="#" wire:click.prevent="$set('tab', '{{ $stab }}')" class="text-violet-600 font-semibold">mudar</a>@endif</small>
                                </span>
                                <button type="button" wire:click="moveSection('{{ $k }}', -1)" class="{{ $iconBtn }} !w-8 !h-8" title="Subir" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button>
                                <button type="button" wire:click="moveSection('{{ $k }}', 1)" class="{{ $iconBtn }} !w-8 !h-8" title="Descer" @disabled($loop->last) data-testid="down-{{ $k }}"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" wire:click="toggleSection('{{ $k }}')" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}" title="{{ $on ? 'Esconder' : 'Mostrar' }}" data-testid="toggle-{{ $k }}"
                                        class="shrink-0 relative w-10 h-6 rounded-full transition {{ $on ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}"><span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all {{ $on ? 'left-[18px]' : 'left-0.5' }}"></span></button>
                            </li>
                        @endforeach
                    </ol>
                </section>
            </div>

            {{-- Prévia ao vivo do catálogo --}}
            <aside class="min-w-0 lg:sticky lg:top-4 {{ $card }} p-3 space-y-2" x-data="{ dev: 'pc', w: 0 }" x-init="w = $refs.box.clientWidth; new ResizeObserver(() => w = $refs.box.clientWidth).observe($refs.box)" data-testid="home-preview">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-eye text-violet-500"></i> Como fica</span>
                    <div class="ml-auto inline-flex rounded-lg bg-slate-100 dark:bg-slate-800 p-0.5 text-xs font-semibold">
                        <button type="button" @click="dev = 'pc'" :class="dev === 'pc' ? 'bg-white dark:bg-slate-700 shadow text-violet-600' : 'text-slate-500'" class="rounded-md px-2.5 py-1"><i class="bi bi-display"></i> Computador</button>
                        <button type="button" @click="dev = 'cel'" :class="dev === 'cel' ? 'bg-white dark:bg-slate-700 shadow text-violet-600' : 'text-slate-500'" class="rounded-md px-2.5 py-1"><i class="bi bi-phone"></i> Celular</button>
                    </div>
                </div>
                <div x-ref="box" class="relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 h-[560px] lg:h-[calc(100vh-150px)] lg:max-h-[820px]">
                    @php $pv = $catalogUrl . (str_contains($catalogUrl, '?') ? '&' : '?') . 'previa=' . $previewKey; @endphp
                    <iframe wire:key="pv-{{ $previewKey }}" src="{{ $pv }}" title="Prévia do catálogo" loading="lazy" data-testid="home-preview-frame"
                            class="absolute top-0 border-0 bg-white origin-top-left"
                            :style="(() => { const iw = dev === 'pc' ? 1280 : 390; const sc = Math.min(1, w / iw); const left = Math.max(0, (w - iw * sc) / 2); return `width:${iw}px;height:${Math.round($refs.box.clientHeight / sc)}px;transform:scale(${sc});left:${left}px`; })()"></iframe>
                </div>
                <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }} w-full justify-center"><i class="bi bi-box-arrow-up-right text-violet-500"></i>Abrir o catálogo</a>
            </aside>
        </div>
    @endif

    {{-- ===================== Banners ===================== --}}
    @if($tab === 'banners')
        <div class="space-y-4">
            <section class="{{ $card }} p-4 sm:p-5 overflow-hidden" data-testid="tpl-row">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-lg"><i class="bi bi-magic"></i></div>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-bold text-slate-900 dark:text-white">Modelos prontos</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Toque em um modelo para criar o banner. Você muda textos, cores, fotos e arrumação, e usa no topo, na faixa ou na lateral. Os de data já entram e saem do ar sozinhos.</p>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 flex-wrap" data-testid="store-kind">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Minha loja é de</span>
                    @foreach(\App\Services\Promotions\StoreKind::KINDS as $k => [$n, $ico, $ex])
                        <button type="button" wire:click="setKind('{{ $k }}')" title="{{ $ex }}" data-testid="kind-{{ $k }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $kind === $k ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/15 text-violet-700 dark:text-violet-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}"><i class="bi {{ $ico }}"></i>{{ $n }}</button>
                    @endforeach
                </div>
                <div class="mt-3 flex gap-1 overflow-x-auto [scrollbar-width:none]">
                    @foreach($front::TEMPLATE_GROUPS as $g => [$gn, $gi])
                        <button type="button" wire:click="$set('galleryGroup', '{{ $g }}')" data-testid="rowgroup-{{ $g }}"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $galleryGroup === $g ? 'bg-violet-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                            <i class="bi {{ $gi }}"></i>{{ $gn }} <span class="opacity-70">{{ $front::templates($kind)->where('group', $g)->count() }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="-mx-4 sm:-mx-5 px-4 sm:px-5 scroll-px-4 sm:scroll-px-5 mt-3 flex gap-3 overflow-x-auto pb-2 snap-x [scrollbar-width:thin] items-start">
                    @foreach($front::templatesIn($galleryGroup, $kind) as $key => $t)
                        @php
                            $tp = array_slice($t['src'] !== 'manual' ? ($auto[$t['src']] ?? []) : $samples, 0, $front::picsFor($key, $t['fmt'] ?? null));
                            $td = $t['date'] ? \App\Services\Promotions\CommercialCalendar::next($t['date']) : null;
                        @endphp
                        <a href="{{ route('promotions.storefront.banner.create', ['modelo' => $key]) }}" wire:key="tplrow-{{ $key }}" data-testid="tplrow-{{ $key }}"
                                class="group snap-start shrink-0 rounded-2xl p-1.5 text-left ring-1 ring-slate-200 dark:ring-slate-700 hover:ring-2 hover:ring-violet-400 transition">
                            @include('livewire.promotions.partials.storefront-mini', ['kind' => 'hero', 'scale' => 0.36, 'tpl' => $key, 'fmt' => $t['fmt'] ?? null, 'c1' => $t['c1'], 'c2' => $t['c2'], 'acc' => $t['acc'], 'ink' => $t['ink'],
                                'badge' => $t['badge'], 'title' => $t['title'], 'subtitle' => $t['subtitle'], 'button' => $t['button'], 'photos' => $tp])
                            <span class="flex items-center justify-between gap-2 px-1 pt-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $t['name'] }}<span class="text-violet-600 dark:text-violet-300 opacity-0 group-hover:opacity-100 transition"><i class="bi bi-plus-lg"></i> Usar</span></span>
                            @if($td)<small class="block px-1 text-[11px] text-slate-500">{{ $td['emoji'] }} {{ $td['now'] ? 'Agora, até ' . $td['end']->format('d/m') : \App\Services\Promotions\CommercialCalendar::label($td) }}</small>
                            @elseif($t['src'] !== 'manual')<small class="block px-1 text-[11px] text-emerald-600 dark:text-emerald-400"><i class="bi bi-arrow-repeat"></i> {{ $front::PHOTO_SOURCES[$t['src']][0] }}, atualiza sozinho</small>@endif
                        </a>
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
                        <a href="{{ route('promotions.storefront.banner.create', ['lugar' => $place]) }}" class="{{ $phBtn }}" data-testid="banner-new-{{ $place }}"><i class="bi bi-plus-lg text-violet-500"></i>Adicionar</a>
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
                                        @php $dz = $b->design + ['c1' => null, 'c2' => null, 'acc' => null, 'ink' => 'light', 'badge' => null, 'lay' => 'esq'];
                                            $dArgs = ['tpl' => $dz['tpl'], 'c1' => $dz['c1'], 'c2' => $dz['c2'], 'acc' => $dz['acc'], 'ink' => $dz['ink'], 'badge' => $dz['badge'], 'lay' => $dz['lay'] ?? 'esq', 'anim' => $dz['anim'] ?? 'none', 'fmt' => $dz['fmt'] ?? null, 'ends' => $b->ends_at, 'title' => $b->title, 'subtitle' => $b->subtitle, 'button' => $b->button_text, 'photos' => $b->designPhotos()]; @endphp
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
                                            <a href="{{ route('promotions.storefront.banner.edit', $b->id) }}" class="{{ $iconBtn }} ml-auto" title="Editar" data-testid="banner-edit"><i class="bi bi-pencil text-indigo-500"></i></a>
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
        @php $fmax = \App\Livewire\Promotions\PortalStorefront::FEATURED_MAX; $fcount = $featured->count(); @endphp
        <section class="{{ $card }} p-4 sm:p-5 space-y-4" data-testid="featured-panel">
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                <div class="flex items-start gap-3 min-w-0 flex-1">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-500/15 text-amber-500 text-lg"><i class="bi bi-star-fill"></i></div>
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-900 dark:text-white">Produtos em destaque</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Uma fileira na página inicial, na ordem que você montar (até {{ $fmax }}). Esgotado some sozinho e volta quando tiver estoque. Tudo que você muda aqui já fica salvo.</p>
                    </div>
                </div>
                <button type="button" wire:click="openPicker('featured')" class="{{ $primary }} w-full sm:w-auto" data-testid="featured-pick"><i class="bi bi-plus-lg"></i>Escolher produtos</button>
            </div>

            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                <div>
                    <label class="{{ $label }}" for="sf-ft">Título da fileira</label>
                    <input id="sf-ft" type="text" wire:model.blur="featured_title" maxlength="60" placeholder="Destaques da loja" class="{{ $input }}">
                    @error('featured_title')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 sm:pb-2.5">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 {{ $fcount >= $fmax ? $tones['amber'] : $tones['slate'] }}">{{ $fcount }} de {{ $fmax }}</span>
                    <span wire:loading.flex wire:target="pick,moveFeatured,removeFeatured,fillFeatured,clearFeatured,featured_title" class="items-center gap-1 text-violet-500"><i class="bi bi-arrow-repeat animate-spin"></i>Salvando</span>
                    <span wire:loading.remove wire:target="pick,moveFeatured,removeFeatured,fillFeatured,clearFeatured,featured_title" class="inline-flex items-center gap-1 text-emerald-600"><i class="bi bi-cloud-check"></i>Salvo</span>
                </div>
            </div>

            <div class="flex gap-2 overflow-x-auto -mx-1 px-1 pb-1 [scrollbar-width:none]">
                <span class="shrink-0 self-center text-xs font-semibold text-slate-500">Completar com:</span>
                @foreach(['vendidos' => ['Mais vendidos', 'bi-fire'], 'ofertas' => ['Em promoção', 'bi-percent'], 'novos' => ['Novidades', 'bi-stars']] as $src => [$sname, $sico])
                    <button type="button" wire:click="fillFeatured('{{ $src }}')" class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:border-violet-400 hover:text-violet-600" data-testid="featured-fill-{{ $src }}"><i class="bi {{ $sico }} text-violet-500"></i>{{ $sname }}</button>
                @endforeach
                @if($fcount)
                    <button type="button" wire:click="clearFeatured" wire:confirm="Tirar todos os produtos dos destaques?" class="shrink-0 ml-auto inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10"><i class="bi bi-trash3"></i>Limpar</button>
                @endif
            </div>

            @if($featured->isEmpty())
                <button type="button" wire:click="openPicker('featured')" class="w-full rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 p-8 text-center hover:border-violet-300 transition">
                    <i class="bi bi-star text-3xl text-amber-400"></i>
                    <b class="block mt-2 text-slate-800 dark:text-slate-100">Nenhum produto em destaque</b>
                    <small class="block text-slate-500">Toque para escolher, ou use "Completar com" acima.</small>
                </button>
            @else
                <ol class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5 sm:gap-3" data-testid="featured-list">
                    @foreach($featured as $p)
                        @php $promo = $p->livePromotion(); @endphp
                        <li wire:key="ft-{{ $p->id }}" class="min-w-0 relative flex flex-col rounded-2xl border {{ $p->sf_available ? 'border-slate-200 dark:border-slate-700' : 'border-amber-300 dark:border-amber-500/40' }} bg-white dark:bg-slate-900 overflow-hidden" data-testid="featured-item">
                            <div class="relative aspect-square bg-slate-50 dark:bg-slate-800">
                                <img src="{{ $p->image_url }}" alt="" class="w-full h-full object-contain {{ $p->sf_available ? '' : 'opacity-50 grayscale' }}" loading="lazy">
                                <span class="absolute left-2 top-2 min-w-[26px] h-[26px] px-1.5 rounded-full bg-slate-900/80 text-white text-xs font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                                <button type="button" wire:click="removeFeatured({{ $p->id }})" class="absolute right-2 top-2 w-8 h-8 rounded-full bg-white/95 dark:bg-slate-900/90 shadow text-rose-500 hover:bg-rose-50" title="Tirar dos destaques" data-testid="featured-remove"><i class="bi bi-x-lg"></i></button>
                                @if($promo)
                                    <span class="absolute left-2 bottom-2 rounded-md bg-emerald-500 text-white text-[10px] font-bold px-1.5 py-0.5">PROMOÇÃO</span>
                                @endif
                                @unless($p->sf_available)
                                    <span class="absolute inset-x-2 bottom-2 rounded-md bg-amber-500 text-white text-[10px] font-bold px-1.5 py-1 text-center">Esgotado: não aparece agora</span>
                                @endunless
                            </div>
                            <div class="p-2.5 flex-1 flex flex-col">
                                <b class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-2 leading-snug">{{ $p->name }}</b>
                                <span class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                                    @if($promo)<s class="text-[11px] font-normal text-slate-400 mr-1">{{ $money($p->price_sale) }}</s>{{ $money($promo->promo_price) }}@else{{ $money($p->price_sale) }}@endif
                                </span>
                                <span class="text-[11px] text-slate-400 truncate">{{ $p->product_code }}</span>
                                <div class="mt-auto pt-2 flex items-center gap-1">
                                    <button type="button" wire:click="moveFeatured({{ $loop->index }}, -1)" @disabled($loop->first) class="flex-1 h-8 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-25" title="Mover para antes"><i class="bi bi-arrow-left"></i></button>
                                    <button type="button" wire:click="moveFeatured({{ $loop->index }}, 1)" @disabled($loop->last) class="flex-1 h-8 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-25" title="Mover para depois"><i class="bi bi-arrow-right"></i></button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                    @if($fcount < $fmax)
                        <li class="min-w-0">
                            <button type="button" wire:click="openPicker('featured')" class="w-full h-full min-h-[180px] rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 text-slate-400 hover:border-violet-300 hover:text-violet-500 flex flex-col items-center justify-center gap-1 transition"><i class="bi bi-plus-circle text-2xl"></i><span class="text-xs font-semibold">Adicionar</span></button>
                        </li>
                    @endif
                </ol>
            @endif
        </section>
    @endif

    {{-- ===================== Coleções ===================== --}}
    @if($tab === 'colecoes')
        <div class="space-y-3">
            <div class="flex items-center gap-3 flex-wrap">
                <p class="text-sm text-slate-500 dark:text-slate-400 flex-1 min-w-[220px]">Grupos de produtos com banner próprio, como "Dia das Mães" ou "Kits para presente". Cada coleção vira uma fileira na página inicial e tem a sua página.</p>
                <a href="{{ route('promotions.storefront.collection.create') }}" class="{{ $primary }}" data-testid="collection-new"><i class="bi bi-plus-lg"></i>Nova coleção</a>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto [scrollbar-width:none]" data-testid="collection-idea-row">
                <span class="shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-400"><i class="bi bi-lightbulb text-amber-500"></i> Ideias:</span>
                @foreach($front::COLLECTION_IDEAS[$kind] ?? [] as $i => [$iname, $idesc, $irule])
                    <a href="{{ route('promotions.storefront.collection.create', ['ideia' => $i]) }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-slate-200 dark:border-slate-700 px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:border-violet-400"><i class="bi {{ $front::COLLECTION_RULES[$irule][1] }} text-violet-500"></i>{{ $iname }}</a>
                @endforeach
            </div>
            @if($missingCats->isNotEmpty())
                <div class="{{ $card }} p-3 sm:p-4 flex items-center gap-3 flex-wrap border-violet-200 dark:border-violet-500/30" data-testid="missing-cats">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600 text-lg"><i class="bi bi-tags"></i></div>
                    <p class="flex-1 min-w-[200px] text-sm text-slate-600 dark:text-slate-300"><b class="text-slate-900 dark:text-white">{{ $missingCats->count() === 1 ? '1 categoria ainda sem coleção' : $missingCats->count() . ' categorias ainda sem coleção' }}</b>
                        <span class="block text-xs text-slate-500 dark:text-slate-400 truncate">{{ $missingCats->pluck('name')->take(6)->join(', ') }}{{ $missingCats->count() > 6 ? '…' : '' }}. Cada uma vira uma coleção automática com capa própria.</span></p>
                    <button type="button" wire:click="createCategoryCollections" wire:loading.attr="disabled" class="{{ $primary }}" data-testid="create-cat-collections"><i class="bi bi-magic"></i>Criar as coleções</button>
                </div>
            @endif
            @if($collections->isEmpty())
                <x-empty-state icon="bi-collection" tone="violet" title="Nenhuma coleção ainda"
                               text="Escolha os produtos ou use uma regra (categoria, em promoção, mais vendidos, faixa de preço) e a coleção se atualiza sozinha." />
            @else
                <div class="grid gap-3 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" data-testid="collection-list">
                    @foreach($collections as $c)
                        <article wire:key="col-{{ $c->id }}" class="{{ $card }} overflow-hidden flex flex-col {{ $c->active ? '' : 'opacity-70' }}">
                            @include('portal.partials.collection-head', ['c' => $c, 'href' => null, 'owner' => auth()->id()])
                            <div class="p-3 flex flex-col gap-2 flex-1">
                                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 flex-wrap">@php $rn = $front::COLLECTION_RULES[$c->rule ?? 'manual'] ?? $front::COLLECTION_RULES['manual']; @endphp
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $c->isAuto() ? $tones['emerald'] : $tones['slate'] }}"><i class="bi {{ $c->isAuto() ? 'bi-arrow-repeat' : $rn[1] }}"></i>{{ $c->isAuto() ? $rn[0] . ', automática' : 'Escolhidos à mão' }}</span>
                                    <span><i class="bi bi-box-seam"></i> {{ (int) ($counts[$c->id] ?? 0) }} à venda</span></p>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" wire:click="toggleCollection({{ $c->id }}, 'active')" class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $c->active ? $tones['emerald'] : $tones['slate'] }}"><i class="bi {{ $c->active ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i> {{ $c->active ? 'Ligada' : 'Desligada' }}</button>
                                    <button type="button" wire:click="toggleCollection({{ $c->id }}, 'show_on_home')" class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $c->show_on_home ? $tones['sky'] : $tones['slate'] }}"><i class="bi {{ $c->show_on_home ? 'bi-house-check' : 'bi-house-slash' }}"></i> {{ $c->show_on_home ? 'Na página inicial' : 'Fora da página inicial' }}</button>
                                </div>
                                <div class="flex items-center gap-1.5 mt-auto pt-1">
                                    <button type="button" wire:click="moveCollection({{ $c->id }}, -1)" class="{{ $iconBtn }}" title="Mover para cima" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button>
                                    <button type="button" wire:click="moveCollection({{ $c->id }}, 1)" class="{{ $iconBtn }}" title="Mover para baixo" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button>
                                    <a href="{{ route('portal.catalog', ['userId' => auth()->id(), 'colecao' => $c->slug]) }}" target="_blank" rel="noopener" class="{{ $iconBtn }}" title="Ver no catálogo"><i class="bi bi-box-arrow-up-right"></i></a>
                                    <a href="{{ route('promotions.storefront.collection.edit', $c->id) }}" class="{{ $phBtn }} !py-1.5 ml-auto" data-testid="collection-edit"><i class="bi bi-pencil text-indigo-500"></i>Editar</a>
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

    </x-portal-settings-nav>

    @include('livewire.promotions.partials.storefront-picker')
</div>
