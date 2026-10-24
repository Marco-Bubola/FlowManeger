{{-- Página do banner: modelo pronto (tudo editável, com animação) ou imagem própria, com a prévia ao lado. --}}
@php
    $front = \App\Services\Portal\StorefrontService::class;
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $iconBtn = 'inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-default';
    $primary = 'inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 shadow-md shadow-violet-500/25 transition whitespace-nowrap';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 focus:outline-none';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $preview = $b_image ? $b_image->temporaryUrl() : ($b_image_old ? asset('storage/' . $b_image_old) : null);
    $previewM = $b_image_mobile ? $b_image_mobile->temporaryUrl() : ($b_mobile_old ? asset('storage/' . $b_mobile_old) : null);
    $modelo = $b_mode === 'modelo';
    $miniKind = 'hero';
    $miniScale = 0.28;
    $tplDate = $tplData['date'] ? \App\Services\Promotions\CommercialCalendar::next($tplData['date']) : null;
    $designArgs = ['tpl' => $b_tpl, 'lay' => $b_lay, 'anim' => $b_anim, 'fmt' => $b_fmt, 'ends' => $b_ends ?: ($tplData['date'] ? \App\Services\Promotions\CommercialCalendar::next($tplData['date'])['end'] : null), 'c1' => $b_c1, 'c2' => $b_c2, 'acc' => $b_acc, 'ink' => $b_ink, 'badge' => trim($b_badge), 'title' => trim($b_title),
        'subtitle' => trim($b_subtitle), 'button' => trim($b_button), 'photos' => array_values(array_filter($bPhotoUrls))];
    $kindCls = $b_placement === 'side' ? 'sf-sb' : 'sf-' . $b_placement;
    $ratio = ['hero' => 'aspect-[3/1]', 'strip' => 'aspect-[6/1]', 'side' => 'aspect-[4/15]'][$b_placement] ?? 'aspect-[3/1]';
    $ratioM = ['hero' => 'aspect-[16/9]', 'strip' => 'aspect-[3/1]'][$b_placement] ?? 'aspect-[3/1]';
    $step = 'flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white';
    $num = 'shrink-0 w-6 h-6 rounded-full bg-violet-100 dark:bg-violet-500/20 text-violet-700 dark:text-violet-300 text-xs font-black flex items-center justify-center';
    $kinds = \App\Services\Promotions\StoreKind::KINDS;
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-28 lg:pb-10 space-y-4" style="--ml-radius: 14px; --ml-blue: #3483fa">
    @include('portal.partials.storefront-styles')
    <x-page-header :title="$bannerId ? 'Editar banner' : 'Novo banner'" icon="bi-images" section="produtos" back-route="promotions.storefront"
                   :subtitle="$modelo ? 'Escolha um modelo e mude o que quiser: textos, cores, fotos e animação.' : 'Envie a sua arte pronta.'">
        <x-slot:actions>
            <div class="hidden lg:flex items-center gap-2">
                <button type="button" wire:click="cancel" class="{{ $phBtn }}">Cancelar</button>
                <button type="button" wire:click="saveBanner" class="{{ $primary }}" data-testid="banner-save-top" wire:loading.attr="disabled" wire:target="saveBanner,b_image,b_image_mobile,b_photo"><i class="bi bi-check2"></i>Salvar banner</button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <form wire:submit="saveBanner" x-data="{ dev: 'pc' }" data-testid="banner-form"
          class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(380px,40%)] 2xl:grid-cols-[minmax(0,1fr)_minmax(520px,44%)] items-start">
        {{-- Prévia: presa no alto (no celular fica em cima, no computador ao lado) --}}
        <aside class="lg:order-2 min-w-0 sticky top-0 lg:top-4 z-20 -mx-3 sm:mx-0 bg-slate-50/95 dark:bg-slate-950/95 backdrop-blur sm:rounded-2xl border-b sm:border border-slate-200/80 dark:border-slate-800 px-3 sm:px-4 py-3 space-y-2" data-testid="banner-preview-box">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 mr-auto"><i class="bi bi-eye"></i> Como fica no catálogo</span>
                    @if($b_placement !== 'side')
                        <div class="inline-flex rounded-lg bg-slate-200/70 dark:bg-slate-800 p-0.5 text-xs font-semibold">
                            <button type="button" @click="dev = 'pc'" :class="dev === 'pc' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="rounded-md px-2.5 py-1" data-testid="prev-pc"><i class="bi bi-display"></i> Computador</button>
                            <button type="button" @click="dev = 'cel'" :class="dev === 'cel' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="rounded-md px-2.5 py-1" data-testid="prev-cel"><i class="bi bi-phone"></i> Celular</button>
                        </div>
                    @endif
                </div>
                <div class="flex justify-center" data-testid="banner-preview">
                    <div class="w-full max-lg:max-w-[560px] transition-all" :class="dev === 'cel' ? 'max-w-[340px]' : ''" style="{{ $b_placement === 'side' ? 'max-width: 160px' : '' }}">
                        @if($modelo)
                            @if($b_placement === 'side')
                                <div class="flex justify-center">@include('livewire.promotions.partials.storefront-mini', $designArgs + ['kind' => 'side', 'scale' => 0.6])</div>
                            @else
                                <div class="sf-ban {{ $kindCls }} has-design shadow-sm">
                                    @include('portal.partials.storefront-design', $designArgs)
                                </div>
                            @endif
                        @else
                            <div class="relative w-full rounded-xl overflow-hidden text-white {{ $b_placement === 'side' ? 'aspect-[4/15] max-h-72' : '' }}" style="background: {{ $front::gradient($b_color) }}"
                                 @if($b_placement !== 'side') :class="dev === 'cel' && {{ $previewM ? 'true' : 'false' }} ? '{{ $ratioM }}' : '{{ $ratio }}'" @endif>
                                @if($preview)
                                    <img src="{{ $preview }}" alt="" class="absolute inset-0 w-full h-full object-cover" @if($previewM) x-show="dev === 'pc'" @endif>
                                    @if($previewM)<img src="{{ $previewM }}" alt="" class="absolute inset-0 w-full h-full object-cover" x-show="dev === 'cel'" x-cloak>@endif
                                @endif
                                @if($b_title || $b_subtitle || $b_button)
                                    <div class="absolute inset-0 flex flex-col {{ $b_placement === 'side' ? 'justify-end' : 'justify-center' }} gap-1 px-4 py-3 {{ $preview ? 'bg-gradient-to-r from-black/55 to-transparent' : '' }}">
                                        @if($b_title)<b class="{{ $b_placement === 'hero' ? 'text-lg sm:text-2xl' : 'text-sm sm:text-base' }} font-black leading-tight line-clamp-2">{{ $b_title }}</b>@endif
                                        @if($b_subtitle && $b_placement !== 'strip')<span class="text-xs opacity-90 line-clamp-2">{{ $b_subtitle }}</span>@endif
                                        @if($b_button)<span class="self-start mt-1 rounded-full bg-white text-slate-800 text-xs font-bold px-3 py-1">{{ $b_button }} →</span>@endif
                                    </div>
                                @elseif(! $preview)
                                    <div class="absolute inset-0 flex items-center justify-center text-sm opacity-80 text-center px-2">Envie a imagem do banner</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                @if($b_placement === 'side')<p class="text-[11px] text-center text-slate-500">Prévia menor. No catálogo a lateral tem 160 px de largura e aparece em monitores largos.</p>@endif
            @if($modelo)
                <p class="hidden lg:flex items-center gap-1.5 text-[11px] text-slate-500"><i class="bi bi-magic text-violet-500"></i>{{ $tplData['name'] }} · {{ $front::ANIMATIONS[$b_anim][0] }}</p>
            @endif
        </aside>

        <div class="lg:order-1 min-w-0 space-y-4">
            <section class="{{ $card }} p-4 sm:p-5 flex items-center gap-3 flex-wrap" data-testid="store-kind">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 mr-1"><i class="bi bi-shop text-violet-500"></i> Modelos para</span>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($kinds as $k => [$n, $ico, $ex])
                        <button type="button" wire:click="setKind('{{ $k }}')" title="{{ $ex }}" data-testid="kind-{{ $k }}"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $kind === $k ? 'bg-violet-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}"><i class="bi {{ $ico }}"></i>{{ $n }}</button>
                    @endforeach
                </div>
                <span class="text-[11px] text-slate-400 basis-full sm:basis-auto">{{ $kinds[$kind][2] }}. Muda os modelos, textos e datas sugeridas.</span>
            </section>

            <div class="{{ $card }} p-4 sm:p-5 space-y-6">
                {{-- 1. Onde aparece --}}
                <div class="space-y-2">
                    <p class="{{ $step }}"><span class="{{ $num }}">1</span>Onde aparece</p>
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                        @foreach(['hero' => ['Topo', 'bi-images'], 'strip' => ['Faixa', 'bi-distribute-vertical'], 'side' => ['Lateral', 'bi-layout-sidebar-inset']] as $k => [$n, $ico])
                            <button type="button" wire:click="$set('b_placement', '{{ $k }}')" class="rounded-lg py-2 text-sm font-semibold {{ $b_placement === $k ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}"><i class="bi {{ $ico }}"></i> {{ $n }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- 2. Modelo pronto ou imagem própria --}}
                <div class="space-y-2">
                    <p class="{{ $step }}"><span class="{{ $num }}">2</span>Como quer montar</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['modelo' => ['Modelo pronto', 'Escolha um e edite tudo', 'bi-magic'], 'imagem' => ['Minha imagem', 'Envie uma arte pronta', 'bi-image']] as $k => [$n, $d, $ico])
                            <button type="button" wire:click="setMode('{{ $k }}')" data-testid="mode-{{ $k }}"
                                    class="flex items-center gap-3 rounded-2xl border-2 p-3 text-left transition {{ $b_mode === $k ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10' : 'border-slate-200 dark:border-slate-700 hover:border-violet-300' }}">
                                <span class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $b_mode === $k ? 'bg-violet-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}"><i class="bi {{ $ico }}"></i></span>
                                <span class="min-w-0"><b class="block text-sm text-slate-900 dark:text-white">{{ $n }}</b><small class="hidden min-[420px]:block text-xs text-slate-500 dark:text-slate-400">{{ $d }}</small></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if($modelo)
                    {{-- 3. Galeria de modelos --}}
                    <div class="space-y-2">
                        <p class="{{ $step }}"><span class="{{ $num }}">3</span>Escolha o modelo</p>
                        <div class="flex gap-1 overflow-x-auto [scrollbar-width:none]" role="tablist">
                            @foreach($front::TEMPLATE_GROUPS as $g => [$gn, $gi])
                                <button type="button" wire:click="$set('galleryGroup', '{{ $g }}')" data-testid="tplgroup-{{ $g }}"
                                        class="shrink-0 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $galleryGroup === $g ? 'bg-violet-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                                    <i class="bi {{ $gi }}"></i>{{ $gn }} <span class="opacity-70">{{ $templates->where('group', $g)->count() }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="-mx-4 sm:-mx-5 px-4 sm:px-5 scroll-px-4 sm:scroll-px-5 flex gap-3 overflow-x-auto lg:flex-wrap lg:overflow-visible lg:max-h-[420px] lg:overflow-y-auto pb-2 snap-x [scrollbar-width:thin] items-start" data-testid="tpl-gallery"
                             x-data x-init="$nextTick(() => { const on = $el.querySelector('.ring-violet-500'); if (on) $el.scrollLeft = on.offsetLeft - $el.offsetLeft - 20 })">
                            @foreach($front::templatesIn($galleryGroup, $kind) as $key => $t)
                                @php
                                    $tp = array_slice($t['src'] !== 'manual' ? ($auto[$t['src']] ?? []) : $samples, 0, $front::picsFor($key, $t['fmt'] ?? null));
                                    $td = $t['date'] ? \App\Services\Promotions\CommercialCalendar::next($t['date']) : null;
                                @endphp
                                <button type="button" wire:click="applyTemplate('{{ $key }}')" wire:key="tpl-{{ $key }}" data-testid="tpl-{{ $key }}"
                                        class="snap-start shrink-0 rounded-2xl p-1.5 text-left transition {{ $b_tpl === $key ? 'bg-violet-50 dark:bg-violet-500/15 ring-2 ring-violet-500' : 'hover:bg-slate-50 dark:hover:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700' }}">
                                    @include('livewire.promotions.partials.storefront-mini', ['kind' => $miniKind, 'scale' => $miniScale, 'tpl' => $key, 'fmt' => $t['fmt'] ?? null, 'c1' => $t['c1'], 'c2' => $t['c2'], 'acc' => $t['acc'], 'ink' => $t['ink'],
                                        'badge' => $t['badge'], 'title' => $t['title'], 'subtitle' => $t['subtitle'], 'button' => $t['button'], 'photos' => $tp])
                                    <span class="block px-1 pt-1.5 max-w-[180px]">
                                        <span class="flex items-center gap-1 text-xs font-semibold {{ $b_tpl === $key ? 'text-violet-700 dark:text-violet-300' : 'text-slate-700 dark:text-slate-200' }}">@if($b_tpl === $key)<i class="bi bi-check-circle-fill"></i>@endif<span class="truncate">{{ $t['name'] }}</span></span>
                                        @if($td)<small class="block text-[10px] text-slate-500">{{ $td['emoji'] }} {{ \App\Services\Promotions\CommercialCalendar::label($td) }}</small>
                                        @elseif($t['src'] !== 'manual')<small class="block text-[10px] text-emerald-600 dark:text-emerald-400"><i class="bi bi-arrow-repeat"></i> {{ $front::PHOTO_SOURCES[$t['src']][0] }}</small>@endif
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        @if($tplDate)
                            <p class="text-xs rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 px-3 py-2" data-testid="tpl-date-note">{{ $tplDate['emoji'] }} Período de {{ $tplDate['name'] }}: {{ $tplDate['start']->format('d/m') }} a {{ $tplDate['end']->format('d/m/Y') }}. O banner entra e sai sozinho nessas datas (dá para mudar no fim da janela).</p>
                        @endif
                    </div>

                    {{-- Layout --}}
                    <div class="space-y-2">
                        <span class="{{ $label }}">Arrumação</span>
                        <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                            @foreach($front::LAYOUTS as $k => [$n, $ico])
                                <button type="button" wire:click="setLayout('{{ $k }}')" data-testid="lay-{{ $k }}" class="rounded-lg py-2 px-1 text-xs sm:text-sm font-semibold {{ $b_lay === $k ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}"><i class="bi {{ $ico }}"></i> {{ $n }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Formato --}}
                    <div class="space-y-2">
                        <span class="{{ $label }}">Formato <span class="font-normal text-slate-400">(muda o desenho do banner; completo no Topo)</span></span>
                        <div class="grid grid-cols-2 min-[480px]:grid-cols-3 xl:grid-cols-5 gap-1.5" data-testid="formats">
                            @foreach($front::FORMATS as $k => [$n, $ico, $fp])
                                <button type="button" wire:click="setFormat('{{ $k }}')" data-testid="fmt-{{ $k }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border px-2 py-2 text-xs font-semibold transition {{ $b_fmt === $k ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}"><i class="bi {{ $ico }}"></i>{{ $n }}</button>
                            @endforeach
                        </div>
                        @if($b_fmt === 'contagem')<p class="text-[11px] text-slate-500">A contagem vai até o dia em "Termina em" (passo 8). {{ $b_ends ? '' : 'Coloque a data de fim para os números aparecerem.' }}</p>@endif
                        @if($b_fmt === 'preco')<p class="text-[11px] text-slate-500">O selo vira o preço grande. Ex.: "R$ 49,90" ou "ATÉ 50% OFF".</p>@endif
                    </div>

                    {{-- 4. Textos --}}
                    <div class="space-y-3">
                        <p class="{{ $step }}"><span class="{{ $num }}">4</span>Textos</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="bn-badge">Selo <span class="font-normal text-slate-400">(ex.: 40% OFF, NOVIDADE, o cupom)</span></label>
                                <input id="bn-badge" type="text" wire:model.live.debounce.300ms="b_badge" maxlength="30" class="{{ $input }}" data-testid="banner-badge">
                                @error('b_badge')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="bn-btn">Texto do botão <span class="font-normal text-slate-400">(vazio = sem botão)</span></label>
                                <input id="bn-btn" type="text" wire:model.live.debounce.300ms="b_button" maxlength="40" class="{{ $input }}">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="bn-title">Título</label>
                                <input id="bn-title" type="text" wire:model.live.debounce.300ms="b_title" maxlength="120" class="{{ $input }}" data-testid="banner-title">
                                @error('b_title')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="bn-sub">Texto menor <span class="font-normal text-slate-400">(na faixa só aparece no computador)</span></label>
                                <input id="bn-sub" type="text" wire:model.live.debounce.300ms="b_subtitle" maxlength="200" class="{{ $input }}">
                            </div>
                        </div>
                    </div>

                    {{-- 5. Cores --}}
                    <div class="space-y-3">
                        <p class="{{ $step }}"><span class="{{ $num }}">5</span>Cores</p>
                        <div class="flex flex-wrap gap-2" data-testid="palettes">
                            @foreach($front::PALETTES as $i => [$p1, $p2, $pa, $pi])
                                @php $on = strtolower($b_c1) === $p1 && strtolower($b_c2) === $p2 && strtolower($b_acc) === $pa; @endphp
                                <button type="button" wire:click="setPalette({{ $i }})" title="Usar estas cores" aria-label="Combinação de cores {{ $i + 1 }}"
                                        class="relative w-11 h-11 rounded-xl ring-offset-2 dark:ring-offset-slate-900 border border-black/5 {{ $on ? 'ring-2 ring-violet-500' : '' }}" style="background: linear-gradient(135deg, {{ $p1 }}, {{ $p2 }})">
                                    <span class="absolute right-1 bottom-1 w-3.5 h-3.5 rounded-full border border-white/70 shadow" style="background: {{ $pa }}"></span>
                                </button>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-1 min-[420px]:grid-cols-3 gap-2">
                            @foreach(['b_c1' => 'Fundo (início)', 'b_c2' => 'Fundo (fim)', 'b_acc' => 'Selo e botão'] as $prop => $n)
                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 p-2 cursor-pointer">
                                    <input type="color" wire:model.live.debounce.250ms="{{ $prop }}" class="shrink-0 w-9 h-9 rounded-lg border-0 bg-transparent p-0 cursor-pointer" data-testid="color-{{ $prop }}">
                                    <span class="min-w-0"><small class="block text-[11px] font-semibold text-slate-500">{{ $n }}</small><span class="block text-xs font-mono text-slate-700 dark:text-slate-200 uppercase">{{ $this->{$prop} }}</span></span>
                                </label>
                            @endforeach
                        </div>
                        @error('b_c1')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Letras</span>
                            <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 text-sm font-semibold">
                                <button type="button" wire:click="$set('b_ink', 'light')" class="rounded-lg px-3 py-1.5 {{ $b_ink === 'light' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}">Claras</button>
                                <button type="button" wire:click="$set('b_ink', 'dark')" class="rounded-lg px-3 py-1.5 {{ $b_ink === 'dark' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}">Escuras</button>
                            </div>
                            <span class="text-[11px] text-slate-400">Use escuras em fundo claro.</span>
                        </div>
                    </div>

                    {{-- 6. Fotos --}}
                    <div class="space-y-3 {{ $b_lay === 'centro' ? 'opacity-60' : '' }}">
                        <p class="{{ $step }}"><span class="{{ $num }}">6</span>{{ $photoMax > 1 ? 'Fotos (até ' . $photoMax . ')' : 'Foto' }} @if($b_lay === 'centro')<span class="text-xs font-normal text-slate-500">(o layout "só texto" não mostra foto)</span>@endif</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5" data-testid="photo-sources">
                            @foreach($front::PHOTO_SOURCES as $k => [$n, $ico])
                                <button type="button" wire:click="setSource('{{ $k }}')" data-testid="src-{{ $k }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border px-2 py-2 text-xs font-semibold transition {{ $b_src === $k ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}"><i class="bi {{ $ico }}"></i>{{ $n }}</button>
                            @endforeach
                        </div>
                        @if($b_src !== 'manual')
                            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-500/10 p-3 space-y-2">
                                <p class="text-xs text-emerald-800 dark:text-emerald-200"><i class="bi bi-arrow-repeat"></i> <b>Atualiza sozinho.</b>
                                    {{ ['novos' => 'Mostra os últimos produtos que você cadastrou.', 'vendidos' => 'Mostra os que mais venderam nos últimos 90 dias.', 'ofertas' => 'Mostra os produtos que estão em promoção agora.'][$b_src] }}
                                    Produto esgotado sai e entra o próximo.</p>
                                <div class="flex gap-2">
                                    @forelse(array_slice($bPhotoUrls, 0, $photoMax) as $u)
                                        <span class="w-16 h-16 rounded-xl bg-white border border-emerald-100 p-1"><img src="{{ $u }}" alt="" class="w-full h-full object-contain"></span>
                                    @empty
                                        <span class="text-xs text-slate-500">Nenhum produto com foto ainda.</span>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" wire:click="openPicker('photos')" class="{{ $phBtn }} !py-1.5" data-testid="photo-product"><i class="bi bi-box-seam text-violet-500"></i>Foto de um produto</button>
                                <label class="{{ $phBtn }} !py-1.5 cursor-pointer"><i class="bi bi-upload text-violet-500"></i><span wire:loading.remove wire:target="b_photo">Enviar foto</span><span wire:loading wire:target="b_photo">Enviando…</span>
                                    <input type="file" wire:model="b_photo" accept="image/jpeg,image/png,image/webp" class="sr-only" data-testid="photo-upload"></label>
                            </div>
                            @if($b_photos)
                                <div class="flex flex-wrap gap-3">
                                    @foreach($b_photos as $i => $ph)
                                        <div wire:key="ph-{{ $i }}-{{ $ph['p'] ?? $ph['f'] }}" class="relative w-24">
                                            <div class="w-24 h-24 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white p-1.5 overflow-hidden">
                                                @if($bPhotoUrls[$i] ?? null)<img src="{{ $bPhotoUrls[$i] }}" alt="" class="w-full h-full object-contain">@else<span class="w-full h-full flex items-center justify-center text-slate-300"><i class="bi bi-image text-2xl"></i></span>@endif
                                            </div>
                                            <button type="button" wire:click="removePhoto({{ $i }})" class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-white dark:bg-slate-800 shadow border border-slate-200 dark:border-slate-700 text-rose-500" title="Tirar a foto"><i class="bi bi-x-lg text-xs"></i></button>
                                            @if(count($b_photos) > 1)
                                                <div class="flex justify-center gap-1 mt-1">
                                                    <button type="button" wire:click="movePhoto({{ $i }}, -1)" class="{{ $iconBtn }} !w-7 !h-7" @disabled($loop->first) title="Para a esquerda"><i class="bi bi-arrow-left text-xs"></i></button>
                                                    <button type="button" wire:click="movePhoto({{ $i }}, 1)" class="{{ $iconBtn }} !w-7 !h-7" @disabled($loop->last) title="Para a direita"><i class="bi bi-arrow-right text-xs"></i></button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-500 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-3 text-center">Sem foto, o banner fica só com o texto.</p>
                            @endif
                            @error('b_photo')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            <p class="text-[11px] text-slate-400">Fotos de produto acompanham a foto atual do produto. Fotos com fundo branco ou sem fundo ficam melhores.</p>
                        @endif
                    </div>
                    {{-- 7. Animação --}}
                    <div class="space-y-2">
                        <p class="{{ $step }}"><span class="{{ $num }}">7</span>Animação</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5" data-testid="anims">
                            @foreach($front::ANIMATIONS as $k => [$n, $ico, $rot])
                                <button type="button" wire:click="setAnim('{{ $k }}')" data-testid="anim-{{ $k }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border px-2 py-2.5 text-xs font-semibold transition {{ $b_anim === $k ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}"><i class="bi {{ $ico }}"></i>{{ $n }}</button>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ [
                            'none' => 'O banner fica parado.',
                            'fotos' => 'As fotos vão se trocando sozinhas, mostrando mais produtos no mesmo espaço.',
                            'deslizar' => 'As fotos passam deslizando de um lado para o outro.',
                            'zoom' => 'As fotos se trocam e chegam mais perto devagar.',
                            'entrada' => 'Selo, título e botão entram um depois do outro quando a página abre.',
                            'vivo' => 'Textos entrando, fotos trocando com zoom, selo pulsando e brilho no botão.',
                        ][$b_anim] }} @if($front::ANIMATIONS[$b_anim][2] && $b_lay !== 'centro') Usa até {{ $photoMax }} fotos. @endif Quem pediu menos movimento no celular vê o banner parado.</p>
                    </div>
                @else
                    {{-- 3. Imagem própria --}}
                    <div class="space-y-3">
                        <p class="{{ $step }}"><span class="{{ $num }}">3</span>Imagem</p>
                        <div class="grid gap-3 {{ $b_placement === 'side' ? '' : 'sm:grid-cols-[1fr_200px]' }}">
                            @foreach(array_filter(['main' => ['b_image', $preview, $b_placement === 'side' ? 'Imagem' : 'Computador e iPad', ['hero' => '1200 × 400 px', 'strip' => '1200 × 200 px', 'side' => '160 × 600 px'][$b_placement], 'bi-display'],
                                'mobile' => $b_placement === 'side' ? null : ['b_image_mobile', $previewM, 'Celular (opcional)', ['hero' => '1080 × 608 px', 'strip' => '900 × 300 px'][$b_placement] ?? '', 'bi-phone']]) as $which => [$prop, $src, $n, $size, $ico])
                                <div x-data="{ over: false, prog: 0 }" x-on:livewire-upload-start="prog = 2" x-on:livewire-upload-finish="prog = 0" x-on:livewire-upload-error="prog = 0" x-on:livewire-upload-progress="prog = $event.detail.progress"
                                     class="relative rounded-2xl border-2 border-dashed transition overflow-hidden {{ $src ? 'border-transparent' : '' }}"
                                     :class="over ? '!border-violet-500 bg-violet-50 dark:bg-violet-500/10' : '{{ $src ? '' : 'border-slate-300 dark:border-slate-600 hover:border-violet-400 bg-slate-50 dark:bg-slate-800/40' }}'">
                                    <input type="file" wire:model="{{ $prop }}" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-[1]" title=""
                                           @dragenter="over = true" @dragleave="over = false" @drop="over = false" @if($which === 'main') data-testid="banner-image" @endif>
                                    @if($src)
                                        <div class="relative {{ $which === 'mobile' ? 'aspect-[16/9]' : ($b_placement === 'side' ? 'aspect-[4/15] max-h-60 w-24 mx-auto' : ($b_placement === 'strip' ? 'aspect-[6/1] min-h-[64px]' : 'aspect-[3/1]')) }} rounded-xl overflow-hidden bg-slate-100">
                                            <img src="{{ $src }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                                            <span class="absolute left-2 bottom-2 rounded-full bg-black/60 text-white text-[11px] font-semibold px-2 py-0.5"><i class="bi {{ $ico }}"></i> {{ $n }}</span>
                                            <span class="absolute right-2 bottom-2 rounded-full bg-white/90 text-slate-700 text-[11px] font-semibold px-2 py-0.5"><i class="bi bi-arrow-repeat"></i> Trocar</span>
                                        </div>
                                        <button type="button" wire:click="removeBannerImage('{{ $which }}')" class="absolute top-2 right-2 z-[2] w-8 h-8 rounded-full bg-white/95 shadow text-rose-500" title="Tirar a imagem"><i class="bi bi-trash3 text-sm"></i></button>
                                    @else
                                        <div class="flex flex-col items-center justify-center text-center gap-1 px-4 {{ $which === 'mobile' ? 'py-5' : 'py-7' }}">
                                            <span class="w-11 h-11 rounded-2xl bg-white dark:bg-slate-900 shadow-sm flex items-center justify-center text-violet-500 text-xl"><i class="bi {{ $which === 'mobile' ? 'bi-phone' : 'bi-cloud-arrow-up' }}"></i></span>
                                            <b class="text-sm text-slate-800 dark:text-slate-100 mt-1">{{ $n }}</b>
                                            <span class="text-xs text-slate-500"><span class="hidden sm:inline">Arraste aqui ou </span>toque para escolher</span>
                                            <span class="text-[11px] text-slate-400">{{ $size }} · JPG, PNG ou WEBP até 5 MB</span>
                                        </div>
                                    @endif
                                    <div x-show="prog > 0" x-cloak class="absolute inset-x-0 bottom-0 h-1.5 bg-violet-100"><div class="h-full bg-violet-500 transition-all" :style="`width: ${prog}%`"></div></div>
                                </div>
                            @endforeach
                        </div>
                        @error('b_image')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        @error('b_image_mobile')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        @if(! $preview)
                            <div>
                                <span class="{{ $label }}">Cor do fundo (sem imagem)</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($front::COLORS as $k => [$n, $g])
                                        <button type="button" wire:click="$set('b_color', '{{ $k }}')" title="{{ $n }}" aria-label="{{ $n }}" class="w-9 h-9 rounded-full ring-offset-2 dark:ring-offset-slate-900 {{ $b_color === $k ? 'ring-2 ring-violet-500' : '' }}" style="background: {{ $g }}"></button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="bn-title">Título <span class="font-normal text-slate-400">(se a imagem já tem texto, deixe vazio)</span></label>
                                <input id="bn-title" type="text" wire:model.live.debounce.400ms="b_title" maxlength="120" placeholder="Ex.: Semana do perfume com até 30% OFF" class="{{ $input }}" data-testid="banner-title">
                                @error('b_title')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            @if($b_placement !== 'strip')
                                <div class="sm:col-span-2">
                                    <label class="{{ $label }}" for="bn-sub">Texto menor <span class="font-normal text-slate-400">(opcional)</span></label>
                                    <input id="bn-sub" type="text" wire:model.live.debounce.400ms="b_subtitle" maxlength="200" class="{{ $input }}">
                                </div>
                            @endif
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="bn-btn">Texto do botão <span class="font-normal text-slate-400">(opcional)</span></label>
                                <input id="bn-btn" type="text" wire:model.live.debounce.400ms="b_button" maxlength="40" placeholder="Ver ofertas" class="{{ $input }}">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Link, período e ligado --}}
                <div class="space-y-3">
                    <p class="{{ $step }}"><span class="{{ $num }}">{{ $modelo ? 8 : 4 }}</span>Ao tocar e quando aparece</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}" for="bn-link">Ao tocar, leva para</label>
                            <select id="bn-link" wire:model.live="b_link_type" class="{{ $input }}" data-testid="banner-link-type">
                                @foreach($front::LINK_TYPES as $k => $n)
                                    <option value="{{ $k }}">{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($b_link_type === 'collection')
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="bn-col">Coleção</label>
                                <select id="bn-col" wire:model="b_link_value" class="{{ $input }}">
                                    <option value="">Escolha…</option>
                                    @foreach($collections as $c)<option value="{{ $c->slug }}">{{ $c->name }}</option>@endforeach
                                </select>
                                @if($collections->isEmpty())<p class="mt-1 text-xs text-slate-500">Crie uma coleção na aba Coleções primeiro.</p>@endif
                            </div>
                        @elseif($b_link_type === 'category')
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="bn-cat">Categoria</label>
                                <select id="bn-cat" wire:model="b_link_value" class="{{ $input }}">
                                    <option value="">Escolha…</option>
                                    @foreach($categories as $cat)<option value="{{ $cat->id_category }}">{{ $cat->name }}</option>@endforeach
                                </select>
                            </div>
                        @elseif($b_link_type === 'product')
                            <div class="sm:col-span-2">
                                <span class="{{ $label }}">Produto</span>
                                <button type="button" wire:click="openPicker('banner')" class="{{ $phBtn }} w-full justify-start">
                                    @if($bannerProduct)
                                        <img src="{{ $bannerProduct->image_url }}" alt="" class="w-8 h-8 rounded-md object-cover"><span class="truncate">{{ $bannerProduct->name }}</span><span class="ml-auto text-violet-600 text-xs">Trocar</span>
                                    @else
                                        <i class="bi bi-search text-violet-500"></i>Escolher produto
                                    @endif
                                </button>
                            </div>
                        @elseif($b_link_type === 'url')
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="bn-url">Endereço</label>
                                <input id="bn-url" type="url" wire:model="b_link_value" placeholder="https://" class="{{ $input }}">
                            </div>
                        @endif
                        @error('b_link_value')<p class="sm:col-span-2 -mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        <div>
                            <label class="{{ $label }}" for="bn-start">Começa em <span class="font-normal text-slate-400">(opcional)</span></label>
                            <input id="bn-start" type="date" wire:model="b_starts" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}" for="bn-end">Termina em <span class="font-normal text-slate-400">(opcional)</span></label>
                            <input id="bn-end" type="date" wire:model="b_ends" class="{{ $input }}">
                            @error('b_ends')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                        <input type="checkbox" wire:model="b_active" class="mt-0.5 rounded text-violet-500 focus:ring-violet-400">
                        <span class="text-sm text-slate-700 dark:text-slate-200"><b>Ligado</b><small class="block text-xs text-slate-500">Desligado, o banner fica guardado aqui e não aparece no catálogo.</small></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Barra de salvar (celular e iPad) --}}
        <div class="lg:hidden fixed inset-x-0 bottom-0 z-[60] flex gap-2 px-3 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-slate-200 dark:border-slate-800">
            <button type="button" wire:click="cancel" class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="submit" class="flex-1 {{ $primary }}" data-testid="banner-save" wire:loading.attr="disabled" wire:target="saveBanner,b_image,b_image_mobile,b_photo"><span wire:loading.remove wire:target="saveBanner">Salvar banner</span><span wire:loading wire:target="saveBanner">Salvando…</span></button>
        </div>
        <div class="hidden lg:flex lg:order-3 lg:col-start-1 justify-end gap-2">
            <button type="button" wire:click="cancel" class="rounded-xl px-4 py-2.5 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="submit" class="{{ $primary }}" data-testid="banner-save-bottom" wire:loading.attr="disabled" wire:target="saveBanner,b_image,b_image_mobile,b_photo"><span wire:loading.remove wire:target="saveBanner">Salvar banner</span><span wire:loading wire:target="saveBanner">Salvando…</span></button>
        </div>
    </form>

    @include('livewire.promotions.partials.storefront-picker')
</div>
