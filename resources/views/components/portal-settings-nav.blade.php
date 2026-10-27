@props([
    // Parte aberta agora (inicio, banners, destaques, colecoes, aviso, automacoes, kits, cupons, pagamentos, pedidos, avaliacoes)
    'current' => 'geral',
    // Na Vitrine as partes dela trocam sem recarregar (wire:click); nas outras telas viram links.
    'wire' => false,
    // Uma linha de situação por parte (ex.: "3 banners no ar"), quando a tela tiver.
    'info' => [],
])

@php
    // Todas as configurações do catálogo online num lugar só, separadas em grupos.
    $groups = [
        'Comece aqui' => [
            'geral' => ['Visão geral', 'bi-speedometer2', 'O que está pronto e o que falta', 'tab', 'resumo checklist pronto falta inicio'],
        ],
        'Aparência da loja' => [
            'identidade' => ['Identidade da loja', 'bi-person-badge', 'Logo, nome, contatos e páginas', 'tab', 'logo whatsapp instagram facebook tiktok fonte rodape sobre trocas entrega contato endereco horario nome'],
            'inicio' => ['Página inicial', 'bi-house', 'Tema, cores e ordem dos blocos', 'tab', 'tema cores estilo fundo desenho cards botoes blocos ordem largura titulos natal black'],
            'banners' => ['Banners', 'bi-images', 'Carrossel, faixas e laterais', 'tab', 'carrossel faixa lateral imagem foto modelo'],
            'destaques' => ['Destaques', 'bi-star', 'Produtos da fileira de destaque', 'tab', 'fileira produtos estrela'],
            'colecoes' => ['Coleções', 'bi-collection', 'Grupos de produtos com capa', 'tab', 'categoria grupo capa'],
            'aviso' => ['Aviso no topo', 'bi-megaphone', 'Uma linha em todas as páginas', 'tab', 'frete gratis mensagem topo faixa'],
        ],
        'Automático' => [
            'automacoes' => ['Automações', 'bi-robot', 'O site se arruma sozinho', 'tab', 'automatico sozinho data selo novidade ordem lista'],
        ],
        'Vendas pelo catálogo' => [
            'kits' => ['Monte seu kit', 'bi-gift', 'Cestas, caixas e enfeites', 'promotions.kits', 'cesta caixa presente balao doces cartao'],
            'cupons' => ['Cupons', 'bi-ticket-perforated', 'Códigos de desconto', 'promotions.coupons', 'desconto codigo cupom'],
            'pixpontos' => ['Pix e pontos', 'bi-stars', 'Desconto no Pix e fidelidade', 'promotions.rewards', 'pix desconto pontos fidelidade cashback programa clientes premio'],
            'pagamentos' => ['Pagamento e frete', 'bi-credit-card', 'Pix, cartão, boleto e entrega', 'settings.payments', 'pix cartao boleto mercado pago frete cep retirada entrega'],
            'pedidos' => ['Pedidos do portal', 'bi-bag-check', 'Pedidos que chegaram', 'sales.portal-orders', 'orcamento pedido entrega rastreio envio'],
            'carrinhos' => ['Carrinhos abandonados', 'bi-cart-x', 'Lembrar quem não fechou o pedido', 'promotions.carts', 'carrinho abandonado esquecido lembrete lembranca whatsapp recuperar'],
        ],
        'Divulgar' => [
            'google' => ['Google e Instagram', 'bi-google', 'Aparecer na busca e no Shopping', 'promotions.marketing', 'google shopping instagram facebook meta busca sitemap merchant feed lista produtos divulgar seo'],
        ],
        'Resultados' => [
            'relatorio' => ['Relatório do site', 'bi-graph-up-arrow', 'Visitas, mais vistos e pedidos', 'promotions.site-report', 'visitas acessos relatorio mais vistos conversao origem instagram'],
        ],
        'Clientes' => [
            'avaliacoes' => ['Avaliações', 'bi-star-half', 'Notas e comentários', 'promotions.reviews', 'estrelas comentario nota'],
            'leis' => ['Site dentro da lei', 'bi-shield-check', 'Privacidade, termos e dados da loja', 'tab', 'lei lgpd privacidade termos cnpj cpf arrependimento codigo defesa consumidor cookies'],
        ],
    ];
    $now = collect($groups)->flatMap(fn ($g) => $g)->get($current) ?? $groups['Comece aqui']['geral'];
    $href = fn ($key, $item) => $item[3] === 'tab'
        ? route('promotions.storefront', $key === 'geral' ? [] : ['tab' => $key])
        : route($item[3]);
@endphp

<div class="lg:grid lg:grid-cols-[250px_minmax(0,1fr)] 2xl:grid-cols-[270px_minmax(0,1fr)] lg:gap-5 lg:items-start" x-data="{ menu: false, q: '', has(t) { return this.q.trim() === '' || t.includes(this.q.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')); } }" data-testid="portal-settings">
    {{-- Celular e iPad: um botão com a parte aberta, que abre a lista de todas --}}
    <button type="button" @click="menu = true" data-testid="portal-nav-open"
            class="lg:hidden w-full mb-4 flex items-center gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm p-3 text-left">
        <span class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-300 text-lg"><i class="bi {{ $now[1] }}"></i></span>
        <span class="min-w-0 flex-1">
            <small class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Configurar o catálogo</small>
            <b class="block text-sm text-slate-900 dark:text-white truncate">{{ $now[0] }}</b>
        </span>
        <span class="shrink-0 inline-flex items-center gap-1 text-xs font-semibold text-violet-600 dark:text-violet-300">Trocar<i class="bi bi-chevron-down"></i></span>
    </button>

    {{-- Lista das partes: fixa do lado no computador, janela de baixo no celular --}}
    <div x-cloak x-show="menu" x-transition.opacity class="lg:hidden fixed inset-0 z-[96] bg-slate-900/50 backdrop-blur-sm" @click="menu = false"></div>
    <nav :class="menu ? 'max-lg:translate-y-0' : 'max-lg:translate-y-full max-lg:invisible'" aria-label="Configurações do catálogo"
         class="max-lg:fixed max-lg:inset-x-0 max-lg:bottom-0 max-lg:z-[97] max-lg:max-h-[85vh] max-lg:overflow-y-auto max-lg:rounded-t-3xl max-lg:pb-[calc(1rem+env(safe-area-inset-bottom))] max-lg:transition-transform max-lg:duration-200
                lg:sticky lg:top-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm p-2.5">
        <div class="lg:hidden flex items-center justify-between px-2 pt-1 pb-2">
            <b class="text-base text-slate-900 dark:text-white">Configurar o catálogo</b>
            <button type="button" @click="menu = false" class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
        </div>
        <label class="relative block px-1 pt-1 pb-1.5">
            <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input type="search" x-model="q" placeholder="Buscar configuração" aria-label="Buscar configuração" data-testid="portal-nav-search"
                   class="w-full rounded-xl border-0 bg-slate-100 dark:bg-slate-800 pl-9 pr-3 py-2 text-sm text-slate-700 dark:text-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-violet-300">
        </label>
        @foreach($groups as $gname => $items)
            @php $gWords = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(collect($items)->map(fn ($i) => $i[0] . ' ' . $i[2] . ' ' . ($i[4] ?? ''))->join(' '))); @endphp
            <p class="px-2.5 pt-2.5 pb-1 text-[11px] font-bold uppercase tracking-wide text-slate-400" x-show="has(@js($gWords))">{{ $gname }}</p>
            <ul class="grid gap-0.5">
                @foreach($items as $key => $item)
                    @php
                        $on = $key === $current;
                        $cls = 'w-full flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-left transition ' . ($on ? 'bg-violet-50 dark:bg-violet-500/15 ring-1 ring-violet-200 dark:ring-violet-500/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/70');
                        $sub = $info[$key] ?? $item[2];
                    @endphp
                    <li x-show="has(@js(\Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($item[0] . ' ' . $item[2] . ' ' . ($item[4] ?? '')))))">
                        @if($wire && $item[3] === 'tab')
                            <button type="button" wire:click="$set('tab', '{{ $key }}')" @click="menu = false" class="{{ $cls }}" data-testid="nav-{{ $key }}">
                        @else
                            <a href="{{ $href($key, $item) }}" wire:navigate class="{{ $cls }}" data-testid="nav-{{ $key }}">
                        @endif
                                <span class="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ $on ? 'bg-violet-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}"><i class="bi {{ $item[1] }}"></i></span>
                                <span class="min-w-0 flex-1">
                                    <b class="block text-sm {{ $on ? 'text-violet-700 dark:text-violet-200' : 'text-slate-700 dark:text-slate-200' }}">{{ $item[0] }}</b>
                                    <small class="block text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $sub }}</small>
                                </span>
                                @if($item[3] !== 'tab')<i class="bi bi-chevron-right text-xs text-slate-300 dark:text-slate-600"></i>@endif
                        @if($wire && $item[3] === 'tab')
                            </button>
                        @else
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    <div class="min-w-0 space-y-4">
        {{ $slot }}
    </div>
</div>
