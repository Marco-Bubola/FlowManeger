{{-- Montar a página do produto: vale para todos os produtos da loja. --}}
@php
    $L = \App\Services\Portal\ProductLayout::class;
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $primary = 'inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 shadow-sm disabled:opacity-60';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $h2 = 'text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2';
    $lbl = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-violet-400 focus:ring-2 focus:ring-violet-100 focus:outline-none';
    $seg = function (string $key) use ($L, $layout) {
        $h = '<div class="flex flex-wrap gap-1.5" role="radiogroup">';
        foreach ($L::CHOICES[$key] as $v => $n) {
            $on = $layout[$key] === $v;
            $h .= '<button type="button" role="radio" aria-checked="' . ($on ? 'true' : 'false') . '" wire:click="choose(\'' . $key . '\', \'' . $v . '\')" data-testid="ppl-' . $key . '-' . $v . '" class="rounded-lg px-3 py-1.5 text-xs font-semibold border transition '
                . ($on ? 'bg-violet-600 border-violet-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-violet-300') . '">' . e($n) . '</button>';
        }

        return $h . '</div>';
    };
    $flag = function (string $key, string $label) use ($layout) {
        $on = (bool) $layout[$key];

        return '<button type="button" role="switch" aria-checked="' . ($on ? 'true' : 'false') . '" wire:click="flip(\'' . $key . '\')" data-testid="ppl-flag-' . $key . '" class="w-full flex items-center gap-3 py-1.5 text-left">'
            . '<span class="relative shrink-0 w-10 h-6 rounded-full transition ' . ($on ? 'bg-violet-600' : 'bg-slate-300 dark:bg-slate-600') . '"><span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all ' . ($on ? 'left-[18px]' : 'left-0.5') . '"></span></span>'
            . '<span class="text-sm text-slate-700 dark:text-slate-200">' . e($label) . '</span></button>';
    };
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-28 lg:pb-10 space-y-4" x-data="{ tab: 'editar', dev: window.innerWidth < 768 ? 'cel' : 'pc' }">
    <x-page-header title="Montar a página do produto" icon="bi-layout-text-window-reverse" section="produtos" back-route="promotions.product-pages"
                   subtitle="Ordem, nome, fotos, preço e botões. Vale para todos os produtos">
        <x-slot:actions>
            @if($siteUrl)
                <a href="{{ $siteUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }}" data-testid="ppl-open-site"><i class="bi bi-box-arrow-up-right text-violet-500"></i><span class="hidden sm:inline">Ver no site</span></a>
            @endif
            <button type="button" wire:click="save" class="{{ $primary }}" wire:loading.attr="disabled" wire:target="save" data-testid="ppl-save">
                <i class="bi bi-check2"></i>{{ $dirty ? 'Salvar' : 'Salvo' }}
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Celular: Editar / Prévia --}}
    <div class="lg:hidden inline-flex w-full rounded-xl bg-slate-200/70 dark:bg-slate-800 p-1 text-sm font-semibold">
        <button type="button" @click="tab = 'editar'" :class="tab === 'editar' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="flex-1 rounded-lg py-2"><i class="bi bi-sliders"></i> Montar</button>
        <button type="button" @click="tab = 'previa'; dev = 'cel'" :class="tab === 'previa' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="flex-1 rounded-lg py-2" data-testid="ppl-tab-preview"><i class="bi bi-eye"></i> Prévia</button>
    </div>

    @if($dirty)
        <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-500/10 dark:border-amber-500/30 px-3 py-2 text-xs text-amber-800 dark:text-amber-200 flex items-center gap-2" data-testid="ppl-dirty">
            <i class="bi bi-info-circle"></i><span class="mr-auto">Você mudou coisas que ainda não estão no site. Toque em <b>Salvar</b> para valer.</span>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,400px)_minmax(0,1fr)] 2xl:grid-cols-[440px_minmax(0,1fr)] items-start">
        <div class="space-y-4 min-w-0" :class="tab === 'editar' ? '' : 'max-lg:hidden'">

            {{-- Ordem ao lado da foto --}}
            @foreach(['parts' => ['Ao lado da foto', 'bi-layout-sidebar-reverse', 'O que aparece junto do preço, na ordem de cima para baixo.', $L::PARTS], 'sections' => ['Embaixo da página', 'bi-view-stacked', 'As partes que vêm depois da foto e do preço.', $L::SECTIONS]] as $list => [$title, $ic, $hint, $all])
                <section class="{{ $card }} p-4 space-y-3" data-testid="ppl-{{ $list }}">
                    <div>
                        <h2 class="{{ $h2 }}"><i class="bi {{ $ic }} text-violet-500"></i>{{ $title }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $hint }} Use as setas para mudar a ordem e o olho para esconder.</p>
                    </div>
                    <ol class="space-y-1.5">
                        @foreach($layout[$list] as $i => $k)
                            @php [$n, $kic, $desc] = $all[$k]; $off = in_array($k, $layout[$list . '_off'], true); @endphp
                            <li wire:key="ppl-{{ $list }}-{{ $k }}" data-testid="ppl-item-{{ $k }}"
                                class="flex items-center gap-2 rounded-xl border px-2 py-1.5 transition {{ $off ? 'border-dashed border-slate-300 dark:border-slate-600 opacity-60' : 'border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/50' }}">
                                <span class="w-6 text-center text-xs font-bold text-slate-400">{{ $i + 1 }}</span>
                                <span class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center {{ $off ? 'bg-slate-100 dark:bg-slate-800 text-slate-400' : 'bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-300' }}"><i class="bi {{ $kic }}"></i></span>
                                <span class="min-w-0 flex-1 leading-tight">
                                    <b class="block text-sm font-semibold text-slate-800 dark:text-slate-100 truncate {{ $off ? 'line-through' : '' }}">{{ $n }}</b>
                                    <small class="block text-[11px] text-slate-500 truncate">{{ $off ? 'Escondido' : $desc }}</small>
                                </span>
                                <button type="button" wire:click="move('{{ $list }}', '{{ $k }}', -1)" @disabled($i === 0) class="w-8 h-8 rounded-lg text-slate-500 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-25" aria-label="Subir {{ $n }}" data-testid="ppl-up-{{ $k }}"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" wire:click="move('{{ $list }}', '{{ $k }}', 1)" @disabled($i === count($layout[$list]) - 1) class="w-8 h-8 rounded-lg text-slate-500 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-25" aria-label="Descer {{ $n }}" data-testid="ppl-down-{{ $k }}"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" wire:click="toggleOff('{{ $list }}', '{{ $k }}')" class="w-8 h-8 rounded-lg {{ $off ? 'text-slate-400' : 'text-violet-600 dark:text-violet-300' }} hover:bg-white dark:hover:bg-slate-700" aria-label="{{ $off ? 'Mostrar' : 'Esconder' }} {{ $n }}" data-testid="ppl-eye-{{ $k }}"><i class="bi {{ $off ? 'bi-eye-slash' : 'bi-eye' }}"></i></button>
                            </li>
                        @endforeach
                    </ol>
                    @if($list === 'parts')
                        <p class="text-[11px] text-slate-500"><i class="bi bi-lightbulb"></i> Variações em botões ou em lista colorida: em <a href="{{ route('promotions.product-pages') }}#variacoes" wire:navigate class="font-semibold text-violet-600">Páginas de produto</a>.</p>
                    @endif
                </section>

                @if($list === 'parts')
                    {{-- Nome --}}
                    <section class="{{ $card }} p-4 space-y-3" data-testid="ppl-title">
                        <h2 class="{{ $h2 }}"><i class="bi bi-type-h1 text-violet-500"></i>Nome do produto</h2>
                        <div><span class="{{ $lbl }}">Lugar no celular</span>{!! $seg('title_pos') !!}</div>
                        <div><span class="{{ $lbl }}">Tamanho</span>{!! $seg('title_size') !!}</div>
                        <div><span class="{{ $lbl }}">Alinhamento</span>{!! $seg('title_align') !!}</div>
                        <div class="pt-1">{!! $flag('show_cat', 'Mostrar a categoria em cima do nome') !!}{!! $flag('show_rating', 'Mostrar as estrelas das avaliações') !!}</div>
                    </section>

                    {{-- Fotos --}}
                    <section class="{{ $card }} p-4 space-y-3" data-testid="ppl-photos">
                        <h2 class="{{ $h2 }}"><i class="bi bi-images text-violet-500"></i>Fotos</h2>
                        <div><span class="{{ $lbl }}">Formato</span>{!! $seg('photo_ratio') !!}</div>
                        <div><span class="{{ $lbl }}">Encaixe</span>{!! $seg('photo_fit') !!}</div>
                        <div><span class="{{ $lbl }}">Miniaturas (computador)</span>{!! $seg('thumbs') !!}</div>
                        <div><span class="{{ $lbl }}">Lado da foto (computador e iPad)</span>{!! $seg('photo_side') !!}</div>
                        <div><span class="{{ $lbl }}">Tamanho da foto (computador e iPad)</span>{!! $seg('photo_size') !!}</div>
                        <div class="pt-1">{!! $flag('photo_count', 'Mostrar o contador "1 / 5" na foto') !!}</div>
                    </section>

                    {{-- Preço --}}
                    <section class="{{ $card }} p-4 space-y-3" data-testid="ppl-price">
                        <h2 class="{{ $h2 }}"><i class="bi bi-tag text-violet-500"></i>Preço</h2>
                        <div><span class="{{ $lbl }}">Tamanho do preço</span>{!! $seg('price_size') !!}</div>
                        <div class="pt-1">{!! $flag('show_deal', 'Selo "Oferta do dia" quando tem desconto') !!}{!! $flag('show_pix', 'Preço no Pix') !!}{!! $flag('show_points', 'Pontos que o cliente ganha') !!}</div>
                    </section>

                    {{-- Botões --}}
                    <section class="{{ $card }} p-4 space-y-3" data-testid="ppl-buttons">
                        <h2 class="{{ $h2 }}"><i class="bi bi-cursor text-violet-500"></i>Botões</h2>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                            <label class="block"><span class="{{ $lbl }}">Texto do botão principal</span>
                                <input type="text" maxlength="30" wire:model.live.debounce.700ms="layout.buy_text" class="{{ $input }}" placeholder="Comprar agora" data-testid="ppl-buy-text"></label>
                            <label class="block"><span class="{{ $lbl }}">Texto do carrinho</span>
                                <input type="text" maxlength="30" wire:model.live.debounce.700ms="layout.cart_text" class="{{ $input }}" placeholder="Adicionar ao carrinho"></label>
                        </div>
                        <div>{!! $flag('show_cart', 'Botão "Adicionar ao carrinho"') !!}{!! $flag('show_whatsapp', 'Botão "Perguntar pelo WhatsApp"') !!}{!! $flag('buybar', 'Barra de compra fixa embaixo no celular') !!}</div>
                    </section>
                @endif
            @endforeach

            <button type="button" wire:click="resetDefaults" class="{{ $phBtn }} w-full justify-center" data-testid="ppl-reset"><i class="bi bi-arrow-counterclockwise"></i>Voltar ao jeito padrão</button>
        </div>

        {{-- Prévia: a página de verdade, com as mudanças ainda não salvas --}}
        <aside class="min-w-0 lg:sticky lg:top-4 space-y-2" :class="tab === 'previa' ? '' : 'max-lg:hidden'" data-testid="ppl-preview">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 mr-auto"><i class="bi bi-eye"></i> Como fica no site</span>
                @if($samples->count() > 1)
                    <select wire:model.live="sampleId" class="rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-xs py-1.5 pl-2 pr-7 max-w-[200px] text-slate-700 dark:text-slate-200" aria-label="Produto da prévia" data-testid="ppl-sample">
                        @foreach($samples as $s)<option value="{{ $s->id }}">{{ \Illuminate\Support\Str::limit($s->name, 40) }}</option>@endforeach
                    </select>
                @endif
                <div class="inline-flex rounded-lg bg-slate-200/70 dark:bg-slate-800 p-0.5 text-xs font-semibold">
                    <button type="button" @click="dev = 'pc'" :class="dev === 'pc' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="rounded-md px-2.5 py-1" data-testid="ppl-dev-pc"><i class="bi bi-display"></i> Computador</button>
                    <button type="button" @click="dev = 'cel'" :class="dev === 'cel' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="rounded-md px-2.5 py-1" data-testid="ppl-dev-cel"><i class="bi bi-phone"></i> Celular</button>
                </div>
            </div>
            @if($previewUrl)
                <div class="rounded-2xl bg-slate-200/70 dark:bg-slate-800 p-2 sm:p-3 overflow-hidden"
                     x-data="{ w: 0, get base() { return dev === 'pc' ? 1280 : 390 }, get s() { return Math.min(1, this.w / this.base) }, get h() { return Math.max(520, Math.min(window.innerHeight - 140, 900)) } }"
                     x-init="const ro = new ResizeObserver(() => w = $el.clientWidth - 16); ro.observe($el); w = $el.clientWidth - 16">
                    <div class="mx-auto overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/5" :style="'width:' + (base * s) + 'px;height:' + h + 'px'">
                        <iframe src="{{ $previewUrl }}" wire:key="ppl-frame-{{ md5($previewUrl) }}" title="Prévia da página do produto" loading="lazy" data-testid="ppl-frame"
                                class="border-0 origin-top-left bg-white" :style="'width:' + base + 'px;height:' + (h / s) + 'px;transform:scale(' + s + ')'"></iframe>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 text-center">A prévia mostra as mudanças antes de salvar. Só você vê.</p>
            @else
                <div class="{{ $card }} p-6 text-center text-sm text-slate-500">Cadastre um produto ativo para ver a prévia.</div>
            @endif
        </aside>
    </div>
</div>
