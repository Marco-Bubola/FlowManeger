{{-- Página da coleção: produtos (escolhidos ou por regra) e capa, com a prévia de como fica no catálogo. --}}
@php
    $front = \App\Services\Portal\StorefrontService::class;
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $iconBtn = 'inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-default';
    $primary = 'inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 shadow-md shadow-violet-500/25 transition whitespace-nowrap';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 focus:outline-none';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $step = 'flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white';
    $num = 'shrink-0 w-6 h-6 rounded-full bg-violet-100 dark:bg-violet-500/20 text-violet-700 dark:text-violet-300 text-xs font-black flex items-center justify-center';
    $chip = fn ($on) => 'inline-flex items-center justify-center gap-1.5 rounded-xl border px-2 py-2 text-xs font-semibold transition ' . ($on ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300');
    $cPreview = $c_image ? $c_image->temporaryUrl() : ($c_image_old ? asset('storage/' . $c_image_old) : null);
    $shown = $items->take($r_limit);
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-28 lg:pb-10 space-y-4" style="--ml-radius: 14px; --ml-blue: #3483fa">
    @include('portal.partials.storefront-styles')
    <x-page-header :title="$colId ? 'Editar coleção' : 'Nova coleção'" icon="bi-collection" section="produtos" back-route="promotions.storefront"
                   subtitle="Uma fileira na página inicial do catálogo e uma página só dela">
        <x-slot:actions>
            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('promotions.storefront', ['tab' => 'colecoes']) }}" class="{{ $phBtn }}">Cancelar</a>
                <button type="button" wire:click="save" class="{{ $primary }}" data-testid="collection-save-top" wire:loading.attr="disabled" wire:target="save,c_image"><i class="bi bi-check2"></i>Salvar coleção</button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <form wire:submit="save" data-testid="collection-form" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(380px,42%)] 2xl:grid-cols-[minmax(0,1fr)_minmax(520px,46%)] items-start">
        {{-- Prévia --}}
        <aside class="lg:order-2 lg:sticky lg:top-4 z-10 min-w-0 space-y-2 {{ $card }} p-3 sm:p-4" data-testid="collection-preview">
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300"><i class="bi bi-eye"></i> Como fica no catálogo</span>
            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white">
                @if($cover === 'imagem')
                    <div class="sf-col-head {{ $cPreview ? 'has-img' : 'no-img' }}" style="--sf-bg: {{ $front::gradient($c_color) }}">
                        @if($cPreview)<img src="{{ $cPreview }}" alt="">@endif
                        <span class="sf-col-txt"><small>Coleção</small><b>{{ trim($c_name) ?: 'Nome da coleção' }}</b>@if($c_description)<span>{{ $c_description }}</span>@endif</span>
                        <span class="sf-col-go">Ver tudo</span>
                    </div>
                @else
                    @include('portal.partials.collection-head', ['c' => $previewCol, 'href' => '#', 'owner' => auth()->id()])
                @endif
                <div class="flex gap-2 overflow-x-auto p-2 [scrollbar-width:thin]" data-testid="preview-rail">
                    @forelse($shown as $p)
                        <div wire:key="pv-{{ $p->id }}" class="shrink-0 w-28 rounded-lg border border-slate-100 p-1.5">
                            <img src="{{ $p->image_url }}" alt="" class="w-full aspect-square object-contain" loading="lazy">
                            <p class="mt-1 text-[11px] leading-tight text-slate-700 line-clamp-2">{{ $p->name }}</p>
                            <p class="text-xs font-bold text-slate-900">{{ $money($p->activePromotion?->promo_price ?? $p->price_sale) }}</p>
                        </div>
                    @empty
                        <p class="w-full p-4 text-center text-xs text-slate-500">Nenhum produto à venda entra nesta coleção ainda.</p>
                    @endforelse
                </div>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400" data-testid="preview-count">
                {{ $items->count() }}{{ $items->count() >= 60 ? '+' : '' }} {{ $items->count() === 1 ? 'produto à venda' : 'produtos à venda' }}.
                Na página inicial aparecem até {{ $r_limit }}; em "Ver tudo", todos.
                @if($rule !== 'manual')<span class="text-emerald-600 dark:text-emerald-400"><i class="bi bi-arrow-repeat"></i> Atualiza sozinha.</span>@endif
            </p>
        </aside>

        <div class="lg:order-1 min-w-0 space-y-4">
            @if(! $colId && $ideas)
                <section class="{{ $card }} p-4 sm:p-5 space-y-2" data-testid="collection-ideas">
                    <p class="{{ $step }}"><i class="bi bi-lightbulb text-amber-500"></i>Comece por uma ideia <span class="text-xs font-normal text-slate-500">({{ \App\Services\Promotions\StoreKind::KINDS[$kind][0] }})</span></p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ideas as $i => [$iname, $idesc, $irule])
                            <button type="button" wire:click="useIdea({{ $i }})" data-testid="idea-{{ $i }}" class="{{ $phBtn }} !py-1.5 {{ $c_name === $iname ? '!border-violet-500 !text-violet-700 dark:!text-violet-300' : '' }}"><i class="bi {{ $front::COLLECTION_RULES[$irule][1] }} text-violet-500"></i>{{ $iname }}</button>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="{{ $card }} p-4 sm:p-5 space-y-6">
                {{-- 1. Nome --}}
                <div class="space-y-3">
                    <p class="{{ $step }}"><span class="{{ $num }}">1</span>Nome e frase</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="{{ $label }}" for="col-name">Nome</label>
                            <input id="col-name" type="text" wire:model.live.debounce.400ms="c_name" maxlength="80" placeholder="Ex.: Dia das Mães" class="{{ $input }}" data-testid="collection-name">
                            @error('c_name')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="{{ $label }}" for="col-desc">Frase curta <span class="font-normal text-slate-400">(opcional)</span></label>
                            <input id="col-desc" type="text" wire:model.live.debounce.400ms="c_description" maxlength="200" placeholder="Ex.: Presentes que ela vai amar" class="{{ $input }}">
                        </div>
                    </div>
                </div>

                {{-- 2. Produtos --}}
                <div class="space-y-3">
                    <p class="{{ $step }}"><span class="{{ $num }}">2</span>Quais produtos entram</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5" data-testid="rules">
                        @foreach($front::COLLECTION_RULES as $k => [$n, $ico])
                            <button type="button" wire:click="setRule('{{ $k }}')" data-testid="rule-{{ $k }}" class="{{ $chip($rule === $k) }}"><i class="bi {{ $ico }}"></i>{{ $n }}</button>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $front::COLLECTION_RULES[$rule][2] }} @if($rule !== 'manual') Produto novo que se encaixa entra sozinho; esgotado sai. @endif</p>

                    @if($rule === 'manual')
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Produtos escolhidos ({{ count($c_products) }})</span>
                            <button type="button" wire:click="openPicker" class="{{ $phBtn }} !py-1.5" data-testid="collection-pick"><i class="bi bi-plus-lg text-violet-500"></i>Escolher produtos</button>
                        </div>
                        @include('livewire.promotions.partials.storefront-picked', ['items' => $manualList, 'move' => 'moveProduct', 'remove' => 'removeProduct', 'cols' => 'grid-cols-1 xl:grid-cols-2', 'empty' => 'Nenhum produto ainda. Toque em Escolher produtos.'])
                        @error('c_products')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    @elseif($rule === 'categoria')
                        <div class="flex flex-wrap gap-1.5" data-testid="rule-cats">
                            @forelse($categories as $cat)
                                <button type="button" wire:click="toggleCat({{ $cat->id_category }})" class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ in_array($cat->id_category, $r_cats, true) ? 'bg-violet-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">{{ $cat->name }}</button>
                            @empty
                                <p class="text-xs text-slate-500">Seus produtos ainda não têm categoria.</p>
                            @endforelse
                        </div>
                        @error('r_cats')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    @elseif($rule === 'preco')
                        <div class="grid grid-cols-2 gap-3 max-w-md">
                            <div>
                                <label class="{{ $label }}" for="r-min">De R$ <span class="font-normal text-slate-400">(opcional)</span></label>
                                <input id="r-min" type="text" inputmode="decimal" wire:model.live.debounce.500ms="r_min" placeholder="0,00" class="{{ $input }}">
                                @error('r_min')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="r-max">Até R$</label>
                                <input id="r-max" type="text" inputmode="decimal" wire:model.live.debounce.500ms="r_max" placeholder="50,00" class="{{ $input }}" data-testid="rule-max">
                                @error('r_max')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @elseif($rule === 'busca')
                        <div class="max-w-md">
                            <label class="{{ $label }}" for="r-term">Palavra no nome do produto</label>
                            <input id="r-term" type="text" wire:model.live.debounce.500ms="r_term" maxlength="60" placeholder="Ex.: perfume, hidratante, mesa" class="{{ $input }}" data-testid="rule-term">
                            @error('r_term')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                    <div class="flex items-center gap-3 flex-wrap">
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300" for="r-limit">Na página inicial, mostrar até</label>
                        <select id="r-limit" wire:model.live="r_limit" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-1.5 text-sm text-slate-900 dark:text-white">
                            @foreach([6, 8, 10, 12, 15, 20, 30] as $n)<option value="{{ $n }}">{{ $n }} produtos</option>@endforeach
                        </select>
                    </div>
                </div>

                {{-- 3. Capa --}}
                <div class="space-y-3">
                    <p class="{{ $step }}"><span class="{{ $num }}">3</span>Capa</p>
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                        @foreach(['modelo' => ['Modelo', 'bi-magic'], 'imagem' => ['Minha imagem', 'bi-image'], 'cor' => ['Só cor', 'bi-palette']] as $k => [$n, $ico])
                            <button type="button" wire:click="setCover('{{ $k }}')" data-testid="cover-{{ $k }}" class="rounded-lg py-2 text-xs sm:text-sm font-semibold {{ $cover === $k ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}"><i class="bi {{ $ico }}"></i> {{ $n }}</button>
                        @endforeach
                    </div>

                    @if($cover === 'modelo')
                        <p class="text-xs text-slate-500 dark:text-slate-400">A capa usa o nome e a frase da coleção e as fotos dos produtos dela.</p>
                        <div class="-mx-4 sm:-mx-5 px-4 sm:px-5 flex gap-2 overflow-x-auto lg:flex-wrap lg:overflow-visible lg:max-h-[260px] lg:overflow-y-auto pb-2 [scrollbar-width:thin]" data-testid="cover-gallery">
                            @foreach($templates->where('group', '!=', 'datas')->merge($templates->where('group', 'datas')) as $key => $t)
                                <button type="button" wire:click="setCoverTemplate('{{ $key }}')" wire:key="ct-{{ $key }}" data-testid="ctpl-{{ $key }}"
                                        class="shrink-0 rounded-xl p-1 text-left transition {{ $d_tpl === $key ? 'bg-violet-50 dark:bg-violet-500/15 ring-2 ring-violet-500' : 'ring-1 ring-slate-200 dark:ring-slate-700 hover:ring-violet-300' }}">
                                    @include('livewire.promotions.partials.storefront-mini', ['kind' => 'strip', 'scale' => 0.34, 'tpl' => $key, 'c1' => $t['c1'], 'c2' => $t['c2'], 'acc' => $t['acc'], 'ink' => $t['ink'],
                                        'badge' => 'COLEÇÃO', 'title' => trim($c_name) ?: $t['name'], 'subtitle' => null, 'button' => null, 'photos' => []])
                                    <span class="block px-1 pt-1 text-[11px] font-semibold {{ $d_tpl === $key ? 'text-violet-700 dark:text-violet-300' : 'text-slate-600 dark:text-slate-300' }}">{{ $t['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="d-badge">Selo <span class="font-normal text-slate-400">(vazio = COLEÇÃO)</span></label>
                                <input id="d-badge" type="text" wire:model.live.debounce.400ms="d_badge" maxlength="30" class="{{ $input }}">
                            </div>
                            <div>
                                <span class="{{ $label }}">Letras</span>
                                <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 text-sm font-semibold">
                                    <button type="button" wire:click="$set('d_ink', 'light')" class="rounded-lg px-3 py-1.5 {{ $d_ink === 'light' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}">Claras</button>
                                    <button type="button" wire:click="$set('d_ink', 'dark')" class="rounded-lg px-3 py-1.5 {{ $d_ink === 'dark' ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}">Escuras</button>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2" data-testid="cover-palettes">
                            @foreach($front::PALETTES as $i => [$p1, $p2, $pa, $pi])
                                @php $on = strtolower($d_c1) === $p1 && strtolower($d_c2) === $p2 && strtolower($d_acc) === $pa; @endphp
                                <button type="button" wire:click="setPalette({{ $i }})" aria-label="Combinação de cores {{ $i + 1 }}" class="relative w-10 h-10 rounded-xl ring-offset-2 dark:ring-offset-slate-900 border border-black/5 {{ $on ? 'ring-2 ring-violet-500' : '' }}" style="background: linear-gradient(135deg, {{ $p1 }}, {{ $p2 }})">
                                    <span class="absolute right-1 bottom-1 w-3 h-3 rounded-full border border-white/70 shadow" style="background: {{ $pa }}"></span>
                                </button>
                            @endforeach
                            @foreach(['d_c1' => 'Fundo', 'd_c2' => 'Fundo (fim)', 'd_acc' => 'Selo'] as $prop => $n)
                                <label class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 px-2 cursor-pointer" title="{{ $n }}">
                                    <input type="color" wire:model.live.debounce.250ms="{{ $prop }}" class="w-7 h-7 rounded border-0 bg-transparent p-0 cursor-pointer"><span class="text-[11px] text-slate-500">{{ $n }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div>
                            <span class="{{ $label }}">Animação da capa</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5" data-testid="cover-anims">
                                @foreach($front::ANIMATIONS as $k => [$n, $ico])
                                    <button type="button" wire:click="setAnim('{{ $k }}')" data-testid="canim-{{ $k }}" class="{{ $chip($d_anim === $k) }}"><i class="bi {{ $ico }}"></i>{{ $n }}</button>
                                @endforeach
                            </div>
                        </div>
                    @elseif($cover === 'imagem')
                        <div x-data="{ over: false }" class="relative rounded-2xl border-2 border-dashed transition overflow-hidden" :class="over ? '!border-violet-500 bg-violet-50 dark:bg-violet-500/10' : 'border-slate-300 dark:border-slate-600 hover:border-violet-400 bg-slate-50 dark:bg-slate-800/40'">
                            <input type="file" wire:model="c_image" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-[1]" @dragenter="over = true" @dragleave="over = false" @drop="over = false" data-testid="collection-image">
                            <div class="flex flex-col items-center justify-center text-center gap-1 px-4 py-6">
                                <i class="bi bi-cloud-arrow-up text-2xl text-violet-500"></i>
                                <b class="text-sm text-slate-800 dark:text-slate-100"><span wire:loading.remove wire:target="c_image">{{ $cPreview ? 'Trocar a imagem' : 'Enviar a imagem da capa' }}</span><span wire:loading wire:target="c_image">Enviando…</span></b>
                                <span class="text-[11px] text-slate-400"><span class="hidden sm:inline">Arraste aqui ou toque. </span>1200 × 300 px · JPG, PNG ou WEBP até 5 MB</span>
                            </div>
                        </div>
                        @if($cPreview)<button type="button" wire:click="removeImage" class="{{ $phBtn }} !py-1.5"><i class="bi bi-trash3 text-rose-500"></i>Tirar a imagem</button>@endif
                        @error('c_image')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($front::COLORS as $k => [$n, $g])
                                <button type="button" wire:click="$set('c_color', '{{ $k }}')" title="{{ $n }}" aria-label="{{ $n }}" class="w-9 h-9 rounded-full ring-offset-2 dark:ring-offset-slate-900 {{ $c_color === $k ? 'ring-2 ring-violet-500' : '' }}" style="background: {{ $g }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- 4. Onde aparece --}}
                <div class="space-y-2">
                    <p class="{{ $step }}"><span class="{{ $num }}">4</span>Onde aparece</p>
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
                </div>
            </div>
        </div>

        <div class="lg:hidden fixed inset-x-0 bottom-0 z-[60] flex gap-2 px-3 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('promotions.storefront', ['tab' => 'colecoes']) }}" class="flex-1 text-center rounded-xl px-4 py-2.5 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</a>
            <button type="submit" class="flex-1 {{ $primary }}" data-testid="collection-save" wire:loading.attr="disabled" wire:target="save,c_image"><span wire:loading.remove wire:target="save">Salvar coleção</span><span wire:loading wire:target="save">Salvando…</span></button>
        </div>
        <div class="hidden lg:flex lg:order-3 lg:col-start-1 justify-end gap-2">
            <a href="{{ route('promotions.storefront', ['tab' => 'colecoes']) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</a>
            <button type="submit" class="{{ $primary }}" data-testid="collection-save-bottom" wire:loading.attr="disabled" wire:target="save,c_image"><span wire:loading.remove wire:target="save">Salvar coleção</span><span wire:loading wire:target="save">Salvando…</span></button>
        </div>
    </form>

    @include('livewire.promotions.partials.storefront-picker')
</div>
