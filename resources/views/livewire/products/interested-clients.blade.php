<div class="interested-clients-page">
    <x-product-page-header title="Clientes interessados" icon="bi-heart" active="interessados"
        subtitle="Quem favoritou no catálogo e pediu aviso. Avise pelo WhatsApp quando o produto entrar em promoção ou voltar ao estoque.">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-pink-50 text-pink-700 dark:bg-pink-500/10 dark:text-pink-300"><i class="bi bi-bell-fill"></i>{{ $waitingTotal }} {{ $waitingTotal === 1 ? 'esperando aviso' : 'esperando aviso' }}</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="bi bi-heart-fill"></i>{{ $productCount }} {{ $productCount === 1 ? 'produto favoritado' : 'produtos favoritados' }}</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700/60 dark:text-slate-200"><i class="bi bi-people-fill"></i>{{ $clientCount }} {{ $clientCount === 1 ? 'cliente' : 'clientes' }}</span>
        </x-slot:meta>
    </x-product-page-header>

    <x-list-toolbar placeholder="Buscar produto por nome ou código...">
        <x-toolbar.group>
            <x-toolbar.chip wire:click="setFilter('esperando')" :active="! $product && $filter !== 'todos'" icon="bi-bell">Esperando aviso <span class="opacity-75">({{ $waitingTotal }})</span></x-toolbar.chip>
            <x-toolbar.chip wire:click="setFilter('todos')" :active="! $product && $filter === 'todos'" icon="bi-heart">Todos os favoritos <span class="opacity-75">({{ $productCount }})</span></x-toolbar.chip>
            @if($product)
                <x-toolbar.chip wire:click="clearProduct" :active="true" icon="bi-x-lg">Só este produto</x-toolbar.chip>
            @endif
        </x-toolbar.group>
        <x-toolbar.pager :paginator="$products" />
    </x-list-toolbar>

    @if($products->isEmpty())
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 px-6 py-12 text-center shadow-sm">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-pink-50 dark:bg-pink-500/10">
                <i class="bi bi-heart text-2xl text-pink-500"></i>
            </div>
            @if($filter !== 'todos' && ! $product && $search === '')
                <h4 class="font-semibold text-slate-800 dark:text-slate-100">Ninguém esperando aviso agora</h4>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Quando um produto favoritado entrar em promoção ou voltar ao estoque, os clientes aparecem aqui e no sino.</p>
                <button type="button" wire:click="setFilter('todos')" class="mt-4 inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition"><i class="bi bi-heart"></i>Ver todos os favoritos</button>
            @else
                <h4 class="font-semibold text-slate-800 dark:text-slate-100">Nenhum produto favoritado encontrado</h4>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Os clientes favoritam tocando no coração dos produtos no catálogo online.</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 2xl:grid-cols-2">
            @foreach($products as $g)
                @php
                    $p = $g['product'];
                    $promo = $p->livePromotion();
                    $stock = (int) $p->stock_quantity;
                    $name = $wishlist->productName($p);
                @endphp
                <section wire:key="ip-{{ $p->id }}" data-testid="interest-product" data-product="{{ $p->id }}"
                         class="rounded-2xl border {{ $g['waiting'] ? 'border-pink-200 dark:border-pink-500/30' : 'border-slate-200/80 dark:border-slate-700/70' }} bg-white dark:bg-slate-900/80 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 p-4 border-b border-slate-100 dark:border-slate-800">
                        <x-product-thumb :product="$p" size="w-14 h-14" rounded="rounded-xl" />
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-slate-800 dark:text-slate-100 truncate" title="{{ $name }}">{{ $name }}</h3>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 bg-pink-50 text-pink-700 dark:bg-pink-500/10 dark:text-pink-300"><i class="bi bi-heart-fill"></i>{{ $g['rows']->count() }} {{ $g['rows']->count() === 1 ? 'cliente' : 'clientes' }}</span>
                                @if($stock > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-box-seam"></i>{{ $stock }} em estoque</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300"><i class="bi bi-x-circle"></i>Esgotado</span>
                                @endif
                                @if($promo)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300"><i class="bi bi-fire"></i>-{{ $promo->discount_percent }}% · R$ {{ number_format((float) $promo->promo_price, 2, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                        @if($g['waiting'] > 1)
                            <button type="button" wire:click="markAllContacted({{ $p->id }})" class="hidden sm:inline-flex shrink-0 items-center gap-1 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Marcar todos como avisados">
                                <i class="bi bi-check2-all"></i>Todos avisados
                            </button>
                        @endif
                    </div>

                    @if($g['waiting'])
                        <div class="px-4 py-2 text-xs font-semibold bg-pink-50/70 dark:bg-pink-500/10 text-pink-700 dark:text-pink-300">
                            <i class="bi bi-bell-fill mr-1"></i>{{ $g['waiting'] }} {{ $g['waiting'] === 1 ? 'cliente esperando' : 'clientes esperando' }}:
                            {{ $g['reason'] === 'promo' ? 'entrou em promoção' : 'voltou ao estoque' }}
                        </div>
                    @endif

                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($g['rows'] as $row)
                            @php
                                $c = $row['client'];
                                $alert = $row['alert'];
                                $text = $wishlist->whatsappMessage($alert ?? $row['favorite'], $p, $c, $storeName);
                                $wa = $wishlist->whatsappUrl($c->phone, $text);
                            @endphp
                            <li wire:key="ic-{{ $p->id }}-{{ $c->id }}" class="flex items-center gap-3 px-4 py-3" data-testid="interest-client" data-client="{{ $c->id }}" data-waiting="{{ $row['waiting'] ? 1 : 0 }}">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-pink-500 to-purple-500 text-sm font-bold text-white">
                                    {{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $c->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                        @if($row['waiting'])
                                            <span class="font-semibold text-pink-600 dark:text-pink-300">{{ $alert->reason === 'promo' ? 'Quer saber da promoção' : 'Esperando voltar ao estoque' }}</span> · {{ $alert->created_at->locale('pt_BR')->diffForHumans() }}
                                        @elseif($alert && $alert->contacted_at)
                                            <i class="bi bi-check2"></i> Avisado {{ $alert->contacted_at->locale('pt_BR')->diffForHumans() }}
                                        @else
                                            Favoritou {{ $row['favorite']->created_at->locale('pt_BR')->diffForHumans() }}
                                            @if(! $row['favorite']->notify_promo && ! $row['favorite']->notify_stock) · sem avisos @endif
                                        @endif
                                    </p>
                                </div>
                                @if($wa)
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" wire:click="markContacted({{ $p->id }}, {{ $c->id }})" data-testid="wa-link"
                                       class="inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold text-white transition {{ $row['waiting'] ? 'bg-emerald-500 hover:bg-emerald-600 shadow-sm' : 'bg-slate-400 hover:bg-slate-500 dark:bg-slate-600' }}">
                                        <i class="bi bi-whatsapp"></i><span class="hidden sm:inline">{{ $row['waiting'] ? 'Avisar' : 'WhatsApp' }}</span>
                                    </a>
                                @else
                                    <span class="shrink-0 text-[11px] text-slate-400" title="Cliente sem celular válido">sem celular</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <div class="mt-4">{{ $products->links() }}</div>
    @endif
</div>
