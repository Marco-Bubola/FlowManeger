{{-- Google e Instagram: aparecer na busca, lista de produtos para o Shopping e a loja do Instagram, códigos de verificação. --}}
@php
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-violet-400 focus:border-violet-400';
    $on = $cfg['google'];
    $fix = array_slice(array_merge(array_map(fn ($i) => $i + ['why' => 'Sem foto: o Google e o Instagram não aceitam'], $check['noImg']), array_map(fn ($i) => $i + ['why' => 'Sem descrição: entra, mas aparece menos'], array_filter($check['noDesc'], fn ($i) => $i['image']))), 0, 30);
    $steps = [
        'google' => ['Google Shopping (grátis)', 'bi-google', 'text-sky-600', [
            'Entre em <b>merchants.google.com</b> com a sua conta Google e crie a loja (nome, país Brasil, site <b>flowmaneger.com</b>).',
            'Vá em <b>Produtos › Adicionar produtos › Adicionar vários produtos › Arquivo</b> e escolha <b>“Buscar arquivo em um link”</b> (feed programado).',
            'Cole o link da lista de produtos abaixo e escolha buscar <b>todo dia</b>.',
            'Em <b>Crescimento › Gerenciar programas</b>, ligue as <b>listagens gratuitas</b>. Os produtos aparecem na aba Shopping do Google depois da revisão (alguns dias).',
        ]],
        'meta' => ['Loja do Instagram e do Facebook', 'bi-instagram', 'text-pink-600', [
            'Entre em <b>business.facebook.com/commerce</b> com a conta que cuida do Instagram da loja e crie um <b>catálogo de comércio eletrônico</b>.',
            'No catálogo, vá em <b>Fontes de dados › Adicionar itens › Feed de dados</b> e escolha <b>“Usar um URL” com atualização programada</b>.',
            'Cole o link da lista de produtos abaixo, moeda <b>BRL</b>, atualização <b>diária</b>.',
            'No Instagram (conta profissional), vá em <b>Configurações › Empresa › Compras</b> e ligue o catálogo. Depois dá para marcar os produtos nas fotos e nos reels.',
        ]],
    ];
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
    <x-page-header title="Google e Instagram" icon="bi-google" section="produtos" back-route="promotions.storefront"
                   subtitle="Sua loja na busca do Google, no Google Shopping e na loja do Instagram">
        <x-slot:actions>
            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }}"><i class="bi bi-box-arrow-up-right text-violet-500"></i><span class="hidden sm:inline">Ver catálogo</span></a>
        </x-slot:actions>
    </x-page-header>

    <x-portal-settings-nav current="google">

    <div class="space-y-4" data-testid="marketing-page">
        {{-- Aparecer no Google + situação da lista --}}
        <section class="rounded-2xl overflow-hidden shadow-sm {{ $on ? 'bg-gradient-to-br from-sky-600 to-indigo-600' : 'bg-gradient-to-br from-slate-700 to-slate-900' }} text-white p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center text-2xl"><i class="bi bi-search"></i></div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg sm:text-xl font-black">{{ $on ? 'A loja pode aparecer no Google' : 'A loja está fora da busca do Google' }}</h2>
                    <p class="text-sm text-white/85 mt-1">
                        @if($on)
                            O Google recebe o mapa do site com o catálogo e cada produto (foto, preço, estoque e nota). Quem busca o nome de um produto pode cair na sua loja.
                        @else
                            O site continua aberto para quem tem o link, mas pede ao Google para não mostrar as páginas na busca.
                        @endif
                    </p>
                </div>
                <button type="button" wire:click="toggleGoogle" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}" data-testid="google-toggle"
                        class="relative inline-flex shrink-0 w-14 h-8 rounded-full transition {{ $on ? 'bg-white/90' : 'bg-white/25' }}">
                    <span class="absolute top-1 w-6 h-6 rounded-full shadow transition-all {{ $on ? 'left-7 bg-indigo-600' : 'left-1 bg-white' }}"></span>
                </button>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 mt-5">
                <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Produtos à venda</small><b class="text-xl font-black" data-testid="feed-total">{{ $check['total'] }}</b></div>
                <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Prontos (foto e descrição)</small><b class="text-xl font-black">{{ $check['ok'] }}</b></div>
                <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Sem foto (ficam de fora)</small><b class="text-xl font-black">{{ count($check['noImg']) }}</b></div>
                <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Com código de barras</small><b class="text-xl font-black">{{ $check['gtin'] }}</b></div>
            </div>
        </section>

        <div class="grid gap-4 xl:grid-cols-2 xl:items-start">
            {{-- Lista de produtos --}}
            <section class="{{ $card }} p-4 sm:p-5 space-y-3 min-w-0">
                <div class="flex items-start gap-3">
                    <span class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600 text-lg"><i class="bi bi-list-ul"></i></span>
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-900 dark:text-white">Lista de produtos</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Um link só que o Google e o Instagram leem todo dia: nome, fotos, preço, promoção, estoque, marca e código de barras. Muda sozinho quando você muda um produto.</p>
                    </div>
                </div>
                <div x-data="{ ok: false }" class="flex gap-2">
                    <input type="text" readonly value="{{ $feedUrl }}" class="{{ $input }} font-mono text-xs min-w-0" data-testid="feed-url" @focus="$el.select()">
                    <button type="button" class="{{ $phBtn }} shrink-0" @click="navigator.clipboard.writeText(@js($feedUrl)); ok = true; setTimeout(() => ok = false, 1800)"><i class="bi" :class="ok ? 'bi-check2 text-emerald-500' : 'bi-copy text-violet-500'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span></button>
                </div>
                <a href="{{ $feedUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600 dark:text-violet-300 hover:underline"><i class="bi bi-eye"></i>Abrir a lista</a>
                <div>
                    <label class="{{ $label }}" for="brand">Marca padrão <small class="font-normal text-slate-400">(para produtos sem marca no cadastro)</small></label>
                    <input id="brand" type="text" wire:model.blur="cfg.brand" maxlength="70" placeholder="Ex.: o nome da sua loja" class="{{ $input }}" data-testid="feed-brand">
                </div>
            </section>

            {{-- Google Search Console --}}
            <section class="{{ $card }} p-4 sm:p-5 space-y-3 min-w-0">
                <div class="flex items-start gap-3">
                    <span class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-sky-50 dark:bg-sky-500/15 text-sky-600 text-lg"><i class="bi bi-graph-up"></i></span>
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-900 dark:text-white">Ver como o Google vê a loja</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">No <b>search.google.com/search-console</b>, adicione a propriedade “Prefixo do URL” com o link do catálogo, escolha a verificação por <b>tag HTML</b> e cole aqui o código. Depois, em <b>Sitemaps</b>, envie o mapa do site abaixo.</p>
                    </div>
                </div>
                <div>
                    <label class="{{ $label }}" for="gverify">Código do Google</label>
                    <input id="gverify" type="text" wire:model.blur="cfg.gverify" placeholder='<meta name="google-site-verification" content="...">' class="{{ $input }} font-mono text-xs" data-testid="gverify">
                    @error('cfg.gverify')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    @if($cfg['gverify'])<p class="text-xs text-emerald-600 mt-1"><i class="bi bi-check-circle-fill"></i> Já está no site. Volte ao Google e clique em Verificar.</p>@endif
                </div>
                <div x-data="{ ok: false }">
                    <span class="{{ $label }}">Mapa do site</span>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ $sitemapUrl }}" class="{{ $input }} font-mono text-xs min-w-0" @focus="$el.select()" data-testid="sitemap-url">
                        <button type="button" class="{{ $phBtn }} shrink-0" @click="navigator.clipboard.writeText(@js($sitemapUrl)); ok = true; setTimeout(() => ok = false, 1800)"><i class="bi" :class="ok ? 'bi-check2 text-emerald-500' : 'bi-copy text-violet-500'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span></button>
                    </div>
                    @unless($on)<p class="text-xs text-amber-600 mt-1">Ligue “aparecer no Google” lá em cima para o mapa do site funcionar.</p>@endunless
                </div>
            </section>
        </div>

        {{-- Passo a passo --}}
        <div class="grid gap-4 lg:grid-cols-2 lg:items-start">
            @foreach($steps as $sk => [$sn, $si, $sc, $list])
                <section class="{{ $card }} p-4 sm:p-5 min-w-0" data-testid="steps-{{ $sk }}">
                    <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-3"><i class="bi {{ $si }} {{ $sc }}"></i>{{ $sn }}</h2>
                    <ol class="space-y-2.5">
                        @foreach($list as $i => $txt)
                            <li class="flex gap-3 text-sm text-slate-600 dark:text-slate-300">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-violet-500 text-white text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                                <span class="min-w-0 [overflow-wrap:anywhere]">{!! $txt !!}</span>
                            </li>
                        @endforeach
                    </ol>
                    @if($sk === 'meta')
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <label class="{{ $label }}" for="mverify">Verificação do domínio na Meta <small class="font-normal text-slate-400">(opcional; em Configurações do negócio › Segurança da marca › Domínios › Tag meta)</small></label>
                            <input id="mverify" type="text" wire:model.blur="cfg.mverify" placeholder='<meta name="facebook-domain-verification" content="...">' class="{{ $input }} font-mono text-xs" data-testid="mverify">
                            @error('cfg.mverify')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                            @if($cfg['mverify'])<p class="text-xs text-emerald-600 mt-1"><i class="bi bi-check-circle-fill"></i> Já está no site. Volte à Meta e clique em Verificar.</p>@endif
                        </div>
                    @endif
                </section>
            @endforeach
        </div>

        {{-- Produtos para arrumar --}}
        <section class="{{ $card }} p-4 sm:p-5" data-testid="feed-fix">
            <h2 class="font-bold text-slate-900 dark:text-white mb-1">Produtos para arrumar</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Foto e descrição fazem o produto ser aceito e aparecer mais. O código de barras (EAN) também ajuda o Google a achar o produto.</p>
            @if(empty($fix))
                <p class="text-sm text-emerald-600"><i class="bi bi-check-circle-fill"></i> Todos os produtos à venda têm foto e descrição.</p>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($fix as $i)
                        <li class="flex items-center gap-3 py-2.5">
                            <span class="shrink-0 w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center">@if($i['image'])<img src="{{ $i['image'] }}" alt="" class="w-full h-full object-cover" loading="lazy">@else<i class="bi bi-image text-slate-400"></i>@endif</span>
                            <span class="min-w-0 flex-1">
                                <b class="block text-sm text-slate-800 dark:text-slate-100 truncate">{{ $i['title'] }}</b>
                                <small class="block text-[11px] {{ $i['image'] ? 'text-amber-600' : 'text-rose-600' }}">{{ $i['why'] }}</small>
                            </span>
                            <a href="{{ route('products.edit', $i['id']) }}" class="{{ $phBtn }} shrink-0 !py-1.5"><i class="bi bi-pencil text-violet-500"></i><span class="hidden sm:inline">Arrumar</span></a>
                        </li>
                    @endforeach
                </ul>
                @if(count($check['noImg']) + count($check['noDesc']) > count($fix))<p class="text-xs text-slate-400 mt-2">Mostrando os primeiros {{ count($fix) }}.</p>@endif
            @endif
        </section>
    </div>

    </x-portal-settings-nav>
</div>
