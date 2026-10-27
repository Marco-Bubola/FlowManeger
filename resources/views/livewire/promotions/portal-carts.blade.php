{{-- Carrinhos abandonados: liga/desliga a lembrança e lista quem não fechou o pedido. --}}
@php
    $C = \App\Services\Portal\CartRecovery::class;
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $sel = 'w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-violet-400 focus:border-violet-400';
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $on = $settings['on'];
    $filters = ['abertos' => ['Esperando', 'bi-cart'], 'lembrados' => ['Já lembrados', 'bi-whatsapp'], 'recuperados' => ['Viraram pedido', 'bi-bag-check']];
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
    <x-page-header title="Carrinhos abandonados" icon="bi-cart-x" section="produtos" back-route="promotions.storefront"
                   subtitle="Lembre quem deixou produtos no carrinho e não fechou o pedido" />

    <x-portal-settings-nav current="carrinhos">

    <div class="space-y-4" data-testid="carts-page">
        {{-- Liga e desliga --}}
        <section class="rounded-2xl overflow-hidden shadow-sm {{ $on ? 'bg-gradient-to-br from-emerald-600 to-teal-600' : 'bg-gradient-to-br from-slate-700 to-slate-900' }} text-white p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center text-2xl"><i class="bi bi-cart-check"></i></div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg sm:text-xl font-black">{{ $on ? 'Lembrete ligado' : 'Lembrete desligado' }}</h2>
                    <p class="text-sm text-white/85 mt-1">
                        @if($on)
                            Quem entrar no site, puser produtos no carrinho e não fechar o pedido em {{ $C::HOURS[$settings['hours']] }} recebe uma lembrança no WhatsApp{{ $settings['mode'] === 'auto' ? ' mandada sozinha' : ' (você manda em um clique)' }}.
                        @else
                            Ligue para lembrar no WhatsApp quem deixou produtos no carrinho. Só vale para clientes que entraram com a conta (o site sabe o celular).
                        @endif
                    </p>
                </div>
                <button type="button" wire:click="toggle" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}" data-testid="carts-toggle"
                        class="relative inline-flex shrink-0 w-14 h-8 rounded-full transition {{ $on ? 'bg-white/90' : 'bg-white/25' }}">
                    <span class="absolute top-1 w-6 h-6 rounded-full shadow transition-all {{ $on ? 'left-7 bg-emerald-600' : 'left-1 bg-white' }}"></span>
                </button>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 mt-5">
                <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Esperando agora</small><b class="text-xl font-black" data-testid="carts-open">{{ $stats['open'] }}</b></div>
                <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Valor parado</small><b class="text-xl font-black">{{ $money($stats['openValue']) }}</b></div>
                <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Recuperados no mês</small><b class="text-xl font-black">{{ $stats['recovered'] }}</b></div>
                <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Vendido com o lembrete</small><b class="text-xl font-black">{{ $money($stats['recoveredValue']) }}</b></div>
            </div>
        </section>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_400px] xl:items-start">
            {{-- Lista --}}
            <div class="space-y-3 min-w-0 order-2 xl:order-1">
                <nav class="flex gap-1 overflow-x-auto rounded-2xl bg-slate-100 dark:bg-slate-800/70 p-1 [scrollbar-width:none]" aria-label="Filtro">
                    @foreach($filters as $k => [$n, $ico])
                        <button type="button" wire:click="$set('filter', '{{ $k }}')" data-testid="carts-filter-{{ $k }}"
                                class="flex-1 min-w-max inline-flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition {{ $filter === $k ? 'bg-white dark:bg-slate-900 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500 dark:text-slate-400' }}">
                            <i class="bi {{ $ico }}"></i>{{ $n }} <span class="text-xs opacity-70">{{ $counts[$k] }}</span>
                        </button>
                    @endforeach
                </nav>

                @forelse($list as $cart)
                    @php
                        $items = collect($cart->items ?? []);
                        $wa = $C::waUrl($cart, $settings);
                        $late = $cart->last_activity_at && $cart->last_activity_at->lte(now()->subHours($settings['hours']));
                    @endphp
                    <article class="{{ $card }} p-4" wire:key="cart-{{ $cart->id }}" data-testid="cart-row">
                        <div class="flex items-start gap-3">
                            <x-client-avatar :name="$cart->client->name ?? '?'" :photo="$cart->client->caminho_foto ?? null" size="w-11 h-11 text-sm" rounded="rounded-2xl" />
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-slate-900 dark:text-white truncate">{{ $cart->client->name ?? 'Cliente' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Mexeu no carrinho {{ $cart->last_activity_at?->diffForHumans() }}
                                    @if($cart->recovered_at) · <span class="text-emerald-600 font-semibold">virou o pedido #{{ $cart->quote_id }}</span>
                                    @elseif($cart->reminded_at) · lembrado {{ $cart->reminded_at->diffForHumans() }}{{ $cart->reminder_via === 'whatsapp' ? ' pelo WhatsApp conectado' : '' }}
                                    @elseif(! $late) · ainda no prazo
                                    @endif
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <b class="block text-slate-900 dark:text-white">{{ $money($cart->total) }}</b>
                                <span class="text-xs text-slate-500">{{ $items->sum('qty') }} {{ $items->sum('qty') === 1 ? 'item' : 'itens' }}</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300 line-clamp-2">{{ $items->map(fn ($i) => ($i['qty'] > 1 ? $i['qty'] . 'x ' : '') . $i['name'])->implode(', ') }}</p>
                        @unless($cart->recovered_at)
                            <div class="flex flex-wrap gap-2 mt-3">
                                @if($wa)
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" wire:click="markSent({{ $cart->id }})" data-testid="cart-remind"
                                       class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-white bg-[#25d366] hover:bg-[#1ebe5b] shadow-sm"><i class="bi bi-whatsapp"></i>{{ $cart->reminded_at ? 'Lembrar de novo' : 'Mandar lembrete' }}</a>
                                @else
                                    <span class="text-xs text-amber-600 dark:text-amber-400 self-center"><i class="bi bi-exclamation-triangle"></i> Cliente sem celular válido no cadastro.</span>
                                @endif
                                <button type="button" wire:click="dismiss({{ $cart->id }})" class="{{ $phBtn }}"><i class="bi bi-x-lg"></i>Tirar da lista</button>
                            </div>
                        @endunless
                    </article>
                @empty
                    <x-empty-state icon="bi-cart" tone="violet"
                        :title="$filter === 'recuperados' ? 'Nenhum carrinho recuperado ainda' : 'Nenhum carrinho esperando'"
                        text="Aparecem aqui os clientes logados que puseram produtos no carrinho e ainda não mandaram o pedido." />
                @endforelse
            </div>

            {{-- Como lembrar --}}
            <aside class="min-w-0 space-y-3 order-1 xl:order-2 xl:sticky xl:top-4" data-testid="carts-settings">
                <section class="{{ $card }} p-4 sm:p-5 space-y-4 {{ $on ? '' : 'opacity-75' }}">
                    <h2 class="font-bold text-slate-900 dark:text-white">Como lembrar</h2>
                    <div>
                        <label class="{{ $label }}">Mandar depois de</label>
                        <select wire:model.live="cfg.hours" class="{{ $sel }}" data-testid="carts-hours">
                            @foreach($C::HOURS as $h => $hn)<option value="{{ $h }}">{{ $hn }} sem fechar o pedido</option>@endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <span class="{{ $label }}">Quem manda</span>
                        @foreach($C::MODES as $mk => [$mn, $md])
                            <button type="button" wire:click="setMode('{{ $mk }}')" data-testid="carts-mode-{{ $mk }}"
                                    class="w-full text-left flex items-start gap-3 rounded-xl border p-3 transition {{ $settings['mode'] === $mk ? 'border-violet-400 bg-violet-50 dark:bg-violet-500/15' : 'border-slate-200 dark:border-slate-700' }}">
                                <i class="bi {{ $settings['mode'] === $mk ? 'bi-record-circle-fill text-violet-500' : 'bi-circle text-slate-400' }} mt-0.5"></i>
                                <span class="min-w-0"><b class="block text-sm text-slate-800 dark:text-slate-100">{{ $mn }}</b><small class="block text-xs text-slate-500 dark:text-slate-400">{{ $md }}</small></span>
                            </button>
                        @endforeach
                        @if($settings['mode'] === 'auto' && ! $waReady)
                            <p class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 rounded-xl p-2.5"><i class="bi bi-exclamation-triangle"></i> O WhatsApp ainda não está conectado. Enquanto isso, o app avisa você no sino. <a href="{{ route('settings.whatsapp') }}" class="font-semibold underline">Conectar</a></p>
                        @elseif($settings['mode'] === 'auto')
                            <p class="text-xs text-slate-500 dark:text-slate-400">A Meta só deixa mandar texto livre para quem falou com o seu número nas últimas 24 horas. Para os outros, o app avisa você para mandar em um clique.</p>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="{{ $label }} !mb-0">Mensagem</label><button type="button" wire:click="resetMsg" class="text-xs font-semibold text-violet-600">Voltar a original</button></div>
                        <textarea wire:model.blur="cfg.msg" rows="4" maxlength="700" class="{{ $sel }}" data-testid="carts-msg"></textarea>
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            @foreach($C::VARS as $vk => $vd)<span class="rounded-md bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 text-[11px] text-slate-600 dark:text-slate-300" title="{{ $vd }}">{{ $vk }}</span>@endforeach
                        </div>
                    </div>
                    <div>
                        <span class="{{ $label }}">Como o cliente recebe</span>
                        <div class="rounded-2xl bg-[#e5ddd5] dark:bg-slate-800 p-3">
                            <div class="ml-auto max-w-[92%] rounded-xl rounded-tr-sm bg-[#dcf8c6] dark:bg-emerald-900/60 px-3 py-2 text-[13px] text-slate-800 dark:text-emerald-50 whitespace-pre-line [overflow-wrap:anywhere] shadow-sm" data-testid="carts-preview">{{ $preview }}</div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    </x-portal-settings-nav>
</div>
