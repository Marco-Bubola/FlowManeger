{{-- Página especial de um produto: blocos, aparência e prévia (computador e celular). --}}
@php
    $svc = \App\Services\Portal\ProductPageService::class;
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $primary = 'inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 shadow-md shadow-violet-500/25 transition whitespace-nowrap disabled:opacity-60';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 focus:outline-none';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $iconBtn = 'inline-flex items-center justify-center w-9 h-9 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-default';
    $seg = fn (bool $on) => 'flex-1 rounded-lg px-2 py-1.5 text-xs font-semibold transition ' . ($on ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500 dark:text-slate-400');
    $price = (float) ($product->activePromotion?->promo_price ?? 0) ?: (float) $product->price_sale;
    $chip = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300';
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-28 lg:pb-10 space-y-4" x-data="{ tab: 'editar', dev: 'pc' }"
     x-on:ppe-scroll.window="$nextTick(() => document.getElementById('blk-' + $event.detail.id)?.scrollIntoView({ behavior: 'smooth', block: 'center' }))">
    @include('portal.partials.product-special-styles')
    <style>
        .ppe-prev { --ml-card: #fff; --ml-text: #1f2937; --ml-muted: #6b7280; --ml-line: #ececec; background: #ededed; border-radius: 16px; padding: 10px; }
        .ppe-prev .ps-sec, .ppe-prev .ps-ban, .ppe-prev .ps-faixa { font-size: 14px; }
        .ppe-mock { background: #fff; border-radius: 14px; padding: 12px; display: grid; gap: 10px; box-shadow: 0 1px 2px rgba(0,0,0,.08); container: mock / inline-size; }
        .ppe-mock img { width: 100%; aspect-ratio: 1; object-fit: contain; background: #fafafa; border-radius: 10px; }
        @container mock (min-width: 520px) { .ppe-mock-in { display: grid; grid-template-columns: 1.2fr 1fr; gap: 14px; align-items: start; } }
        .ppe-ghost { margin-top: 12px; background: #fff; border-radius: 14px; padding: 14px; color: #9ca3af; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .ppe-ghost.off { text-decoration: line-through; opacity: .55; }
    </style>

    <x-page-header title="Página especial" icon="bi-file-earmark-richtext" section="produtos" back-route="promotions.product-pages"
                   :subtitle="\Illuminate\Support\Str::limit($product->name, 80)">
        <x-slot:actions>
            <a href="{{ $pageUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }}" data-testid="ppe-open-site"><i class="bi bi-box-arrow-up-right text-violet-500"></i><span class="hidden sm:inline">Ver no site</span></a>
            @unless($choosing)
                <button type="button" wire:click="save" class="{{ $primary }}" wire:loading.attr="disabled" wire:target="save,uploads" data-testid="ppe-save-top"><i class="bi bi-check2"></i>Salvar página</button>
            @endunless
        </x-slot:actions>
    </x-page-header>

    @if($choosing)
        {{-- Primeiro passo: escolher um modelo pronto --}}
        <section class="{{ $card }} p-4 sm:p-6" data-testid="ppe-templates">
            <div class="flex items-start gap-3 mb-4">
                <span class="shrink-0 w-10 h-10 rounded-2xl bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-300 flex items-center justify-center text-lg"><i class="bi bi-magic"></i></span>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Escolha um modelo para começar</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Tudo pode ser mudado depois: textos, fotos, cores, ordem e quais blocos aparecem. Onde estiver <b>{nome}</b> entra o nome do produto.</p>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($templates as $key => [$tName, $tIcon, $tDesc, $tSet, $tBlocks])
                    <button type="button" wire:click="useTemplate('{{ $key }}')" data-testid="ppe-tpl-{{ $key }}"
                            class="text-left rounded-2xl border border-slate-200 dark:border-slate-700 p-4 hover:border-violet-400 hover:shadow-md transition bg-white dark:bg-slate-900 flex flex-col gap-2">
                        <span class="flex items-center gap-2">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-base" style="background: {{ $tSet['acc'] }}"><i class="bi {{ $tIcon }}"></i></span>
                            <b class="text-sm font-bold text-slate-900 dark:text-white">{{ $tName }}</b>
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $tDesc }}</span>
                        <span class="flex flex-wrap gap-1">
                            @forelse($tBlocks as $tb)<span class="{{ $chip }}"><i class="bi {{ $svc::BLOCKS[$tb['type']][1] }}"></i>{{ $svc::BLOCKS[$tb['type']][0] }}</span>@empty<span class="{{ $chip }}">Sem blocos</span>@endforelse
                        </span>
                    </button>
                @endforeach
            </div>
            @if($exists)
                <div class="mt-4 text-right"><button type="button" wire:click="$set('choosing', false)" class="{{ $phBtn }}">Voltar para a minha página</button></div>
            @endif
        </section>
    @else
        {{-- No celular: Editar | Prévia --}}
        <div class="lg:hidden sticky top-0 z-30 -mx-3 px-3 py-2 bg-slate-50/95 dark:bg-slate-950/95 backdrop-blur">
            <div class="flex rounded-xl bg-slate-200/70 dark:bg-slate-800 p-1">
                <button type="button" @click="tab = 'editar'" :class="tab === 'editar' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="flex-1 rounded-lg py-2 text-sm font-semibold" data-testid="ppe-tab-edit"><i class="bi bi-pencil"></i> Editar</button>
                <button type="button" @click="tab = 'previa'; dev = 'cel'" :class="tab === 'previa' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="flex-1 rounded-lg py-2 text-sm font-semibold" data-testid="ppe-tab-preview"><i class="bi bi-eye"></i> Prévia</button>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(400px,44%)] 2xl:grid-cols-[minmax(0,1fr)_minmax(560px,48%)] items-start">
            {{-- Editor --}}
            <div class="space-y-4 min-w-0" :class="tab === 'editar' ? '' : 'max-lg:hidden'">
                <section class="{{ $card }} p-4 flex flex-wrap items-center gap-3">
                    <label class="flex items-center gap-3 cursor-pointer mr-auto">
                        <input type="checkbox" wire:model.live="active" class="sr-only peer" data-testid="ppe-active">
                        <span class="relative w-11 h-6 rounded-full bg-slate-300 dark:bg-slate-600 peer-checked:bg-emerald-500 transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                        <span><b class="block text-sm text-slate-900 dark:text-white">{{ $active ? 'Ligada no site' : 'Desligada no site' }}</b><small class="text-xs text-slate-500">{{ $active ? 'Os clientes já veem esta página.' : 'Só você vê, pelo botão Ver no site.' }}</small></span>
                    </label>
                    <button type="button" wire:click="$set('choosing', true)" class="{{ $phBtn }}" data-testid="ppe-change-tpl"><i class="bi bi-magic text-violet-500"></i>Trocar modelo</button>
                </section>

                {{-- Aparência --}}
                <section class="{{ $card }} p-4 space-y-4" x-data="{ more: false }">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-palette text-violet-500"></i>Aparência da página</h2>
                    <div>
                        <span class="{{ $label }}">Cor de destaque (títulos, ícones e botões)</span>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($svc::PALETTE as $c)
                                <button type="button" wire:click="$set('settings.acc', '{{ $c }}')" class="w-8 h-8 rounded-full border-2 {{ $settings['acc'] === $c ? 'border-slate-900 dark:border-white scale-110' : 'border-white dark:border-slate-800' }} shadow transition" style="background: {{ $c }}" aria-label="Cor {{ $c }}"></button>
                            @endforeach
                            <input type="color" wire:model.live.debounce.300ms="settings.acc" class="w-9 h-9 rounded-lg border border-slate-200 cursor-pointer" aria-label="Outra cor">
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <span class="{{ $label }}">Seções</span>
                            <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                                <button type="button" wire:click="$set('settings.style', 'cards')" class="{{ $seg($settings['style'] === 'cards') }}">Caixas</button>
                                <button type="button" wire:click="$set('settings.style', 'inteiro')" class="{{ $seg($settings['style'] === 'inteiro') }}">Coladas</button>
                            </div>
                        </div>
                        <div>
                            <span class="{{ $label }}">Cantos</span>
                            <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                                @foreach(['s' => 'Retos', 'm' => 'Médios', 'l' => 'Redondos'] as $k => $n)
                                    <button type="button" wire:click="$set('settings.round', '{{ $k }}')" class="{{ $seg($settings['round'] === $k) }}">{{ $n }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <span class="{{ $label }}">Títulos</span>
                            <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                                @foreach(['s' => 'Menores', 'm' => 'Normal', 'l' => 'Maiores'] as $k => $n)
                                    <button type="button" wire:click="$set('settings.title_size', '{{ $k }}')" class="{{ $seg($settings['title_size'] === $k) }}">{{ $n }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="{{ $label }} !mb-0 mr-1">Fundo das seções</span>
                        <button type="button" wire:click="$set('settings.bg', '')" class="{{ $chip }} !py-1 !px-3 {{ $settings['bg'] === '' ? '!bg-violet-100 !text-violet-700' : '' }}">Do tema do site</button>
                        @foreach(['#ffffff', '#fafaf9', '#fdf2f8', '#f5f3ff', '#ecfeff', '#fefce8', '#f0fdf4', '#1c1917'] as $c)
                            <button type="button" wire:click="$set('settings.bg', '{{ $c }}')" class="w-7 h-7 rounded-full border-2 {{ $settings['bg'] === $c ? 'border-violet-500' : 'border-slate-200 dark:border-slate-700' }}" style="background: {{ $c }}" aria-label="Fundo {{ $c }}"></button>
                        @endforeach
                    </div>
                    <div data-testid="ppe-variants">
                        <span class="{{ $label }}">Variações (tamanho, cor, modelo)</span>
                        <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 max-w-md">
                            <button type="button" wire:click="$set('settings.variants', '')" class="{{ $seg($settings['variants'] === '') }}">Igual à loja</button>
                            <button type="button" wire:click="$set('settings.variants', 'botoes')" class="{{ $seg($settings['variants'] === 'botoes') }}">Botões</button>
                            <button type="button" wire:click="$set('settings.variants', 'lista')" class="{{ $seg($settings['variants'] === 'lista') }}" data-testid="ppe-variants-lista">Lista suspensa</button>
                        </div>
                        @if($settings['variants'] === 'lista' || ($settings['variants'] === '' && $storeVariants['variants'] === 'lista'))
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="text-xs text-slate-500 mr-1">Cores do menu</span>
                                <button type="button" wire:click="$set('settings.variants_theme', '')" class="{{ $chip }} !py-1 !px-3 {{ $settings['variants_theme'] === '' ? '!bg-violet-100 !text-violet-700' : '' }}">Igual à loja</button>
                                @foreach($svc::VARIANT_THEMES as $k => [$n, $c1, $c2])
                                    <button type="button" wire:click="$set('settings.variants_theme', '{{ $k }}')" title="{{ $n }}" aria-label="{{ $n }}"
                                            class="w-8 h-8 rounded-full border-2 {{ $settings['variants_theme'] === $k ? 'border-slate-900 dark:border-white scale-110' : 'border-white dark:border-slate-800' }} shadow transition" style="background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }})"></button>
                                @endforeach
                            </div>
                        @endif
                        <small class="block text-xs text-slate-500 mt-1">Só aparece em produtos com variações. "Igual à loja" segue o que está em Vitrine › Páginas de produto.</small>
                    </div>
                    <div>
                        <span class="{{ $label }}">Esconder partes da página normal</span>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach(['hide_about' => 'Descrição e características', 'hide_together' => 'Compre junto', 'hide_reviews' => 'Avaliações', 'hide_related' => 'Produtos relacionados'] as $k => $n)
                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm cursor-pointer">
                                    <input type="checkbox" wire:model.live="settings.{{ $k }}" class="rounded text-violet-600"> {{ $n }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Blocos --}}
                <section class="space-y-2" data-testid="ppe-blocks">
                    <h2 class="px-1 text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-layers text-violet-500"></i>Blocos da página <span class="text-xs font-semibold text-slate-400">{{ count($blocks) }}/{{ $svc::MAX_BLOCKS }}</span></h2>
                    @if(empty($blocks))
                        <div class="{{ $card }} p-6 text-center text-sm text-slate-500">Nenhum bloco ainda. Adicione o primeiro aqui embaixo.</div>
                    @endif
                    @foreach($blocks as $i => $b)
                        @php
                            [$bName, $bIcon] = $svc::BLOCKS[$b['type']];
                            $isOpen = $open === $b['id'];
                            $sum = trim(strip_tags((string) ($b['title'] ?? $b['text'] ?? '')));
                            $f = "blocks.$i.";
                        @endphp
                        <div class="{{ $card }} overflow-hidden {{ $b['hidden'] ? 'opacity-60' : '' }}" id="blk-{{ $b['id'] }}" wire:key="blk-{{ $b['id'] }}" data-testid="ppe-block-{{ $b['type'] }}">
                            <div class="flex items-center gap-1 pl-3 pr-1 py-1.5">
                                <button type="button" wire:click="toggle('{{ $b['id'] }}')" class="flex-1 min-w-0 flex items-center gap-2.5 py-1.5 text-left">
                                    <span class="shrink-0 w-8 h-8 rounded-xl bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-300 flex items-center justify-center"><i class="bi {{ $bIcon }}"></i></span>
                                    <span class="min-w-0">
                                        <b class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $bName }}
                                            @if(in_array($b['type'], ['banner', 'faixa'], true) && ($b['where'] ?? '') === 'topo')<span class="{{ $chip }} ml-1">no topo</span>@endif
                                            @if($b['hidden'])<span class="{{ $chip }} ml-1">escondido</span>@endif
                                        </b>
                                        @if($sum !== '')<small class="block text-xs text-slate-500 truncate">{{ \Illuminate\Support\Str::limit(str_replace('{nome}', $product->name, $sum), 70) }}</small>@endif
                                    </span>
                                    <i class="bi {{ $isOpen ? 'bi-chevron-up' : 'bi-chevron-down' }} ml-auto text-slate-400"></i>
                                </button>
                                <div class="flex items-center">
                                    <button type="button" wire:click="move('{{ $b['id'] }}', -1)" class="{{ $iconBtn }}" @disabled($i === 0) aria-label="Subir"><i class="bi bi-arrow-up"></i></button>
                                    <button type="button" wire:click="move('{{ $b['id'] }}', 1)" class="{{ $iconBtn }}" @disabled($i === count($blocks) - 1) aria-label="Descer"><i class="bi bi-arrow-down"></i></button>
                                    <div class="relative" x-data="{ m: false }">
                                        <button type="button" @click="m = ! m" class="{{ $iconBtn }}" aria-label="Mais"><i class="bi bi-three-dots-vertical"></i></button>
                                        <div x-show="m" x-cloak @click.outside="m = false" class="absolute right-0 top-10 z-20 w-44 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl py-1 text-sm">
                                            <button type="button" wire:click="toggleHidden('{{ $b['id'] }}')" @click="m = false" class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi {{ $b['hidden'] ? 'bi-eye' : 'bi-eye-slash' }} mr-2"></i>{{ $b['hidden'] ? 'Mostrar' : 'Esconder' }}</button>
                                            <button type="button" wire:click="duplicate('{{ $b['id'] }}')" @click="m = false" class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi bi-copy mr-2"></i>Duplicar</button>
                                            <button type="button" wire:click="remove('{{ $b['id'] }}')" @click="m = false" class="w-full text-left px-3 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10"><i class="bi bi-trash3 mr-2"></i>Tirar bloco</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($isOpen)
                                <div class="border-t border-slate-100 dark:border-slate-800 p-4 space-y-3">
                                    @if(array_key_exists('badge', $b))
                                        <div><label class="{{ $label }}">Selo</label><input type="text" wire:model.live.debounce.400ms="{{ $f }}badge" class="{{ $input }}" maxlength="40" placeholder="Ex.: Novidade"></div>
                                    @endif
                                    @if(array_key_exists('title', $b))
                                        <div><label class="{{ $label }}">Título</label><input type="text" wire:model.live.debounce.400ms="{{ $f }}title" class="{{ $input }}" maxlength="120" data-testid="ppe-f-title"></div>
                                    @endif
                                    @if(array_key_exists('text', $b) && $b['type'] !== 'faixa')
                                        <div><label class="{{ $label }}">Texto</label><textarea wire:model.live.debounce.500ms="{{ $f }}text" rows="{{ $b['type'] === 'texto' ? 5 : 3 }}" class="{{ $input }}" maxlength="2000"></textarea></div>
                                    @endif
                                    @if($b['type'] === 'faixa')
                                        <div><label class="{{ $label }}">Aviso</label><input type="text" wire:model.live.debounce.400ms="{{ $f }}text" class="{{ $input }}" maxlength="140"></div>
                                        <div><span class="{{ $label }}">Ícone</span>
                                            <div class="flex flex-wrap gap-1">@foreach($svc::ICONS as $ic)<button type="button" wire:click="setIcon('{{ $b['id'] }}', -1, '{{ $ic }}')" class="w-8 h-8 rounded-lg {{ $b['icon'] === $ic ? 'bg-violet-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}"><i class="bi {{ $ic }}"></i></button>@endforeach</div>
                                        </div>
                                    @endif

                                    @if(in_array($b['type'], ['banner', 'foto_texto', 'medidas'], true))
                                        @php $img = $svc::imageUrl($b['image']); @endphp
                                        <div>
                                            <span class="{{ $label }}">Foto {{ $b['type'] === 'medidas' ? '(desenho com as medidas, opcional)' : '' }}</span>
                                            <div class="flex items-center gap-3">
                                                <div class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0">
                                                    @if($img)<img src="{{ $img }}" class="w-full h-full object-cover" alt="">@elseif($b['type'] !== 'medidas' && ($photos[max(0, (int) ($b['photo'] ?? 1) - 1)] ?? $photos[0] ?? null))<img src="{{ $photos[max(0, (int) ($b['photo'] ?? 1) - 1)] ?? $photos[0] }}" class="w-full h-full object-contain" alt="">@else<i class="bi bi-image text-slate-400"></i>@endif
                                                </div>
                                                <div class="flex flex-wrap gap-2">
                                                    <label class="{{ $phBtn }} cursor-pointer"><i class="bi bi-upload"></i>Enviar foto<input type="file" accept="image/*" class="hidden" wire:model="uploads.{{ $b['id'] }}-image"></label>
                                                    @if($img)<button type="button" wire:click="clearImage('{{ $b['id'] }}')" class="{{ $phBtn }}"><i class="bi bi-x-lg"></i>{{ $b['type'] === 'medidas' ? 'Tirar' : 'Usar foto do produto' }}</button>@endif
                                                </div>
                                            </div>
                                            <div wire:loading wire:target="uploads.{{ $b['id'] }}-image" class="text-xs text-violet-600 mt-1">Enviando…</div>
                                            @if(! $img && $b['type'] === 'foto_texto' && count($photos) > 1)
                                                <div class="mt-2 flex gap-1.5 overflow-x-auto">
                                                    @foreach($photos as $pi => $ph)
                                                        <button type="button" wire:click="$set('{{ $f }}photo', {{ $pi + 1 }})" class="w-12 h-12 shrink-0 rounded-lg overflow-hidden border-2 {{ (int) $b['photo'] === $pi + 1 ? 'border-violet-500' : 'border-transparent' }} bg-slate-100"><img src="{{ $ph }}" class="w-full h-full object-contain" alt=""></button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($b['type'] === 'banner')
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div><span class="{{ $label }}">Onde fica</span>
                                                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                                                    <button type="button" wire:click="$set('{{ $f }}where', 'topo')" class="{{ $seg($b['where'] === 'topo') }}">No topo</button>
                                                    <button type="button" wire:click="$set('{{ $f }}where', 'meio')" class="{{ $seg($b['where'] !== 'topo') }}">Depois da compra</button>
                                                </div>
                                            </div>
                                            <div><span class="{{ $label }}">Foto</span>
                                                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                                                    @foreach(['esq' => 'Esquerda', 'dir' => 'Direita', 'fundo' => 'Fundo'] as $k => $n)<button type="button" wire:click="$set('{{ $f }}lay', '{{ $k }}')" class="{{ $seg($b['lay'] === $k) }}">{{ $n }}</button>@endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-end gap-3">
                                            <div><span class="{{ $label }}">Cores</span><div class="flex gap-2"><input type="color" wire:model.live.debounce.300ms="{{ $f }}c1" class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer"><input type="color" wire:model.live.debounce.300ms="{{ $f }}c2" class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer"></div></div>
                                            <div><span class="{{ $label }}">Letra</span>
                                                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 w-44">
                                                    <button type="button" wire:click="$set('{{ $f }}ink', 'light')" class="{{ $seg($b['ink'] !== 'dark') }}">Clara</button>
                                                    <button type="button" wire:click="$set('{{ $f }}ink', 'dark')" class="{{ $seg($b['ink'] === 'dark') }}">Escura</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(in_array($b['type'], ['faixa', 'contagem', 'chamada'], true))
                                        <div><span class="{{ $label }}">Cor</span>
                                            <div class="flex flex-wrap items-center gap-2">
                                                @foreach($svc::PALETTE as $c)<button type="button" wire:click="$set('{{ $f }}c1', '{{ $c }}')" class="w-7 h-7 rounded-full border-2 {{ $b['c1'] === $c ? 'border-slate-900 dark:border-white' : 'border-white dark:border-slate-800' }} shadow" style="background: {{ $c }}"></button>@endforeach
                                                <input type="color" wire:model.live.debounce.300ms="{{ $f }}c1" class="w-8 h-8 rounded-lg border border-slate-200 cursor-pointer">
                                            </div>
                                        </div>
                                    @endif
                                    @if($b['type'] === 'faixa')
                                        <div><span class="{{ $label }}">Onde fica</span>
                                            <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 max-w-xs">
                                                <button type="button" wire:click="$set('{{ $f }}where', 'topo')" class="{{ $seg($b['where'] === 'topo') }}">No topo</button>
                                                <button type="button" wire:click="$set('{{ $f }}where', 'meio')" class="{{ $seg($b['where'] !== 'topo') }}">Depois da compra</button>
                                            </div>
                                        </div>
                                    @endif
                                    @if(array_key_exists('button', $b))
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div><label class="{{ $label }}">Texto do botão {{ $b['type'] === 'banner' ? '(vazio = sem botão)' : '' }}</label><input type="text" wire:model.live.debounce.400ms="{{ $f }}button" class="{{ $input }}" maxlength="40"></div>
                                            <div><span class="{{ $label }}">O botão leva para</span>
                                                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                                                    <button type="button" wire:click="$set('{{ $f }}action', 'comprar')" class="{{ $seg($b['action'] !== 'whatsapp') }}"><i class="bi bi-bag"></i> Comprar</button>
                                                    <button type="button" wire:click="$set('{{ $f }}action', 'whatsapp')" class="{{ $seg($b['action'] === 'whatsapp') }}"><i class="bi bi-whatsapp"></i> WhatsApp</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @switch($b['type'])
                                        @case('texto')
                                            <div><span class="{{ $label }}">Alinhamento</span>
                                                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 max-w-xs">
                                                    <button type="button" wire:click="$set('{{ $f }}align', 'esq')" class="{{ $seg($b['align'] !== 'centro') }}">À esquerda</button>
                                                    <button type="button" wire:click="$set('{{ $f }}align', 'centro')" class="{{ $seg($b['align'] === 'centro') }}">No centro</button>
                                                </div>
                                            </div>
                                            @break
                                        @case('foto_texto')
                                            <div><span class="{{ $label }}">Foto de qual lado (no computador)</span>
                                                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 max-w-xs">
                                                    <button type="button" wire:click="$set('{{ $f }}side', 'esq')" class="{{ $seg($b['side'] !== 'dir') }}">Esquerda</button>
                                                    <button type="button" wire:click="$set('{{ $f }}side', 'dir')" class="{{ $seg($b['side'] === 'dir') }}">Direita</button>
                                                </div>
                                            </div>
                                            @break
                                        @case('destaques')
                                            <div class="space-y-2">
                                                @foreach($b['items'] as $j => $it)
                                                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 space-y-2" x-data="{ icons: false }" wire:key="it-{{ $b['id'] }}-{{ $j }}">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" @click="icons = ! icons" class="w-10 h-10 shrink-0 rounded-xl bg-violet-50 dark:bg-violet-500/15 text-violet-600 text-lg" aria-label="Trocar ícone"><i class="bi {{ $it['icon'] ?? 'bi-star' }}"></i></button>
                                                            <input type="text" wire:model.live.debounce.400ms="{{ $f }}items.{{ $j }}.title" class="{{ $input }}" maxlength="60" placeholder="Título">
                                                            <button type="button" wire:click="removeItem('{{ $b['id'] }}', 'items', {{ $j }})" class="{{ $iconBtn }} text-rose-500" aria-label="Tirar"><i class="bi bi-x-lg"></i></button>
                                                        </div>
                                                        <div x-show="icons" x-cloak class="flex flex-wrap gap-1">@foreach($svc::ICONS as $ic)<button type="button" wire:click="setIcon('{{ $b['id'] }}', {{ $j }}, '{{ $ic }}')" @click="icons = false" class="w-8 h-8 rounded-lg {{ ($it['icon'] ?? '') === $ic ? 'bg-violet-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}"><i class="bi {{ $ic }}"></i></button>@endforeach</div>
                                                        <input type="text" wire:model.live.debounce.400ms="{{ $f }}items.{{ $j }}.text" class="{{ $input }}" maxlength="140" placeholder="Explicação curta">
                                                    </div>
                                                @endforeach
                                                <button type="button" wire:click="addItem('{{ $b['id'] }}', 'items')" class="{{ $phBtn }}"><i class="bi bi-plus-lg"></i>Mais um destaque</button>
                                            </div>
                                            @break
                                        @case('galeria')
                                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                                                @foreach($b['photos'] as $j => $ph)
                                                    <div class="relative aspect-square rounded-xl overflow-hidden bg-slate-100" wire:key="ph-{{ $b['id'] }}-{{ $j }}">
                                                        <img src="{{ $svc::imageUrl($ph) }}" class="w-full h-full object-cover" alt="">
                                                        <button type="button" wire:click="removePhoto('{{ $b['id'] }}', {{ $j }})" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 text-white text-xs" aria-label="Tirar foto"><i class="bi bi-x"></i></button>
                                                    </div>
                                                @endforeach
                                                <label class="aspect-square rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 flex flex-col items-center justify-center text-xs text-slate-500 cursor-pointer hover:border-violet-400">
                                                    <i class="bi bi-plus-lg text-lg"></i>Fotos
                                                    <input type="file" accept="image/*" multiple class="hidden" wire:model="uploads.{{ $b['id'] }}-photos">
                                                </label>
                                            </div>
                                            <div wire:loading wire:target="uploads.{{ $b['id'] }}-photos" class="text-xs text-violet-600">Enviando…</div>
                                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="{{ $f }}product_photos" class="rounded text-violet-600"> Mostrar também as fotos do produto</label>
                                            <div><span class="{{ $label }}">Colunas no computador</span>
                                                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 max-w-xs">@foreach([2, 3, 4] as $n)<button type="button" wire:click="$set('{{ $f }}cols', {{ $n }})" class="{{ $seg((int) $b['cols'] === $n) }}">{{ $n }}</button>@endforeach</div>
                                            </div>
                                            @break
                                        @case('video')
                                            <div><label class="{{ $label }}">Link do vídeo</label><input type="url" wire:model.live.debounce.600ms="{{ $f }}url" class="{{ $input }}" placeholder="https://www.youtube.com/watch?v=..." inputmode="url">
                                                <small class="text-xs {{ trim($b['url']) !== '' && ! $svc::video($b['url']) ? 'text-rose-600' : 'text-slate-500' }}">{{ trim($b['url']) !== '' && ! $svc::video($b['url']) ? 'Esse link não abre aqui. Use YouTube, Vimeo, Instagram ou um arquivo .mp4.' : 'YouTube (também Shorts), Vimeo, Instagram (post ou reels) ou arquivo .mp4.' }}</small>
                                            </div>
                                            @break
                                        @case('medidas')
                                            <div class="space-y-2">
                                                @foreach($b['rows'] as $j => $r)
                                                    <div class="flex items-center gap-2" wire:key="row-{{ $b['id'] }}-{{ $j }}">
                                                        <input type="text" wire:model.live.debounce.400ms="{{ $f }}rows.{{ $j }}.k" class="{{ $input }} !w-2/5" maxlength="40" placeholder="Ex.: Altura">
                                                        <input type="text" wire:model.live.debounce.400ms="{{ $f }}rows.{{ $j }}.v" class="{{ $input }}" maxlength="120" placeholder="Ex.: 75 cm">
                                                        <button type="button" wire:click="removeItem('{{ $b['id'] }}', 'rows', {{ $j }})" class="{{ $iconBtn }} text-rose-500" aria-label="Tirar linha"><i class="bi bi-x-lg"></i></button>
                                                    </div>
                                                @endforeach
                                                <button type="button" wire:click="addItem('{{ $b['id'] }}', 'rows')" class="{{ $phBtn }}"><i class="bi bi-plus-lg"></i>Mais uma linha</button>
                                            </div>
                                            @break
                                        @case('perguntas')
                                            <div class="space-y-2">
                                                @foreach($b['items'] as $j => $it)
                                                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 space-y-2" wire:key="q-{{ $b['id'] }}-{{ $j }}">
                                                        <div class="flex items-center gap-2"><input type="text" wire:model.live.debounce.400ms="{{ $f }}items.{{ $j }}.q" class="{{ $input }}" maxlength="160" placeholder="Pergunta">
                                                            <button type="button" wire:click="removeItem('{{ $b['id'] }}', 'items', {{ $j }})" class="{{ $iconBtn }} text-rose-500" aria-label="Tirar"><i class="bi bi-x-lg"></i></button></div>
                                                        <textarea wire:model.live.debounce.500ms="{{ $f }}items.{{ $j }}.a" rows="2" class="{{ $input }}" maxlength="800" placeholder="Resposta"></textarea>
                                                    </div>
                                                @endforeach
                                                <button type="button" wire:click="addItem('{{ $b['id'] }}', 'items')" class="{{ $phBtn }}"><i class="bi bi-plus-lg"></i>Mais uma pergunta</button>
                                            </div>
                                            @break
                                        @case('depoimentos')
                                            <div class="space-y-2">
                                                @foreach($b['items'] as $j => $it)
                                                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 space-y-2" wire:key="d-{{ $b['id'] }}-{{ $j }}">
                                                        <textarea wire:model.live.debounce.500ms="{{ $f }}items.{{ $j }}.text" rows="2" class="{{ $input }}" maxlength="400" placeholder="O que a cliente disse"></textarea>
                                                        <div class="flex items-center gap-2"><input type="text" wire:model.live.debounce.400ms="{{ $f }}items.{{ $j }}.name" class="{{ $input }}" maxlength="40" placeholder="Nome (ex.: Ana P.)">
                                                            <button type="button" wire:click="removeItem('{{ $b['id'] }}', 'items', {{ $j }})" class="{{ $iconBtn }} text-rose-500" aria-label="Tirar"><i class="bi bi-x-lg"></i></button></div>
                                                    </div>
                                                @endforeach
                                                <button type="button" wire:click="addItem('{{ $b['id'] }}', 'items')" class="{{ $phBtn }}"><i class="bi bi-plus-lg"></i>Mais um depoimento</button>
                                            </div>
                                            @break
                                        @case('contagem')
                                            <div><label class="{{ $label }}">Acaba em</label><input type="datetime-local" wire:model.live="{{ $f }}ends" class="{{ $input }} max-w-xs"></div>
                                            @break
                                        @case('espaco')
                                            <div class="flex flex-wrap items-center gap-3">
                                                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 w-56">@foreach(['s' => 'Pequeno', 'm' => 'Médio', 'l' => 'Grande'] as $k => $n)<button type="button" wire:click="$set('{{ $f }}size', '{{ $k }}')" class="{{ $seg($b['size'] === $k) }}">{{ $n }}</button>@endforeach</div>
                                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="{{ $f }}line" class="rounded text-violet-600"> Com linha</label>
                                            </div>
                                            @break
                                    @endswitch
                                </div>
                            @endif
                        </div>
                    @endforeach
                </section>

                {{-- Adicionar bloco --}}
                <section class="{{ $card }} p-4" data-testid="ppe-add">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2"><i class="bi bi-plus-circle text-violet-500"></i>Adicionar bloco</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2">
                        @foreach($svc::BLOCKS as $type => [$bName, $bIcon, $bDesc])
                            <button type="button" wire:click="addBlock('{{ $type }}')" data-testid="ppe-add-{{ $type }}" @disabled(count($blocks) >= $svc::MAX_BLOCKS)
                                    class="text-left rounded-xl border border-slate-200 dark:border-slate-700 p-3 hover:border-violet-400 hover:bg-violet-50/50 dark:hover:bg-violet-500/10 transition">
                                <i class="bi {{ $bIcon }} text-violet-500 text-lg"></i>
                                <b class="block text-sm font-semibold text-slate-900 dark:text-white mt-1">{{ $bName }}</b>
                                <small class="block text-[11px] leading-snug text-slate-500">{{ $bDesc }}</small>
                            </button>
                        @endforeach
                    </div>
                </section>

                @if($exists)
                    <div class="text-center" x-data="{ ask: false }">
                        <button type="button" x-show="! ask" @click="ask = true" class="text-sm font-semibold text-rose-600 hover:underline"><i class="bi bi-trash3"></i> Apagar a página especial</button>
                        <div x-show="ask" x-cloak class="{{ $card }} p-4 inline-flex flex-wrap items-center justify-center gap-2 text-sm">
                            <span>O produto volta para a página normal. Apagar?</span>
                            <button type="button" @click="ask = false" class="{{ $phBtn }}">Não</button>
                            <button type="button" wire:click="deletePage" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700">Apagar</button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Prévia --}}
            <aside class="min-w-0 lg:sticky lg:top-4 space-y-2" :class="tab === 'previa' ? '' : 'max-lg:hidden'" data-testid="ppe-preview">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 mr-auto"><i class="bi bi-eye"></i> Como fica no site</span>
                    <div class="inline-flex rounded-lg bg-slate-200/70 dark:bg-slate-800 p-0.5 text-xs font-semibold">
                        <button type="button" @click="dev = 'pc'" :class="dev === 'pc' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="rounded-md px-2.5 py-1"><i class="bi bi-display"></i> Computador</button>
                        <button type="button" @click="dev = 'cel'" :class="dev === 'cel' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500'" class="rounded-md px-2.5 py-1"><i class="bi bi-phone"></i> Celular</button>
                    </div>
                </div>
                <div class="lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto rounded-2xl">
                    <div class="ppe-prev mx-auto transition-all" :class="dev === 'cel' ? 'max-w-[390px]' : ''">
                        @include('portal.partials.product-special', ['where' => 'topo', 'name' => $product->name, 'photos' => $photos, 'phone' => null, 'editor' => true])
                        <div class="ppe-mock">
                            <div class="ppe-mock-in">
                                <div>
                                    <small class="text-[11px] text-slate-500">{{ $product->category?->name ?: 'Produto' }}</small>
                                    <b class="block text-[15px] font-semibold leading-snug text-slate-900 mb-2">{{ $product->name }}</b>
                                    @if($photos)<img src="{{ $photos[0] }}" alt="">@else<div class="aspect-square rounded-xl bg-slate-100 flex items-center justify-center text-slate-300 text-3xl"><i class="bi bi-image"></i></div>@endif
                                </div>
                                <div class="pt-2">
                                    <b class="block text-2xl font-normal text-slate-900">R$ {{ number_format($price, 2, ',', '.') }}</b>
                                    <span class="mt-3 block rounded-md bg-[#3483fa] text-white text-center text-sm font-semibold py-2.5">Comprar agora</span>
                                    <span class="mt-2 block rounded-md bg-[#e3edfb] text-[#3483fa] text-center text-sm font-semibold py-2.5">Adicionar ao carrinho</span>
                                </div>
                            </div>
                        </div>
                        @include('portal.partials.product-special', ['where' => 'meio', 'name' => $product->name, 'photos' => $photos, 'phone' => null, 'editor' => true])
                        @foreach(['hide_together' => 'Compre junto', 'hide_about' => 'Descrição e características', 'hide_reviews' => 'Avaliações', 'hide_related' => 'Produtos relacionados'] as $k => $n)
                            <div class="ppe-ghost {{ $settings[$k] ? 'off' : '' }}"><i class="bi {{ $settings[$k] ? 'bi-eye-slash' : 'bi-check2-circle' }}"></i>{{ $n }}{{ $settings[$k] ? ' (escondido)' : '' }}</div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>

    @endif
</div>
