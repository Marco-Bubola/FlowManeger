{{-- Pix e pontos: desconto automático no Pix e programa de pontos de fidelidade do catálogo. --}}
@php
    $R = \App\Services\Portal\Rewards::class;
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $primary = 'inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 shadow-sm hover:opacity-95 disabled:opacity-50';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:ring-violet-400 focus:border-violet-400';
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $num = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    $pixOn = $cfg['pix'] > 0;
    $ptsOn = $cfg['pts'];
    // Exemplo do programa: numa compra de R$ 100.
    $exPts = (int) floor(100 * $cfg['earn']);
    $exBack = round($exPts * $cfg['worth'] / 100, 2);
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
    <x-page-header title="Pix e pontos" icon="bi-stars" section="produtos" back-route="promotions.storefront"
                   subtitle="Desconto para quem paga no Pix e pontos que viram desconto na próxima compra" />

    <x-portal-settings-nav current="pixpontos">

    <div class="space-y-4" data-testid="rewards-page">
        <div class="grid gap-4 xl:grid-cols-2 xl:items-start">
            {{-- Desconto no Pix --}}
            <section class="rounded-2xl overflow-hidden shadow-sm text-white p-5 sm:p-6 {{ $pixOn ? 'bg-gradient-to-br from-teal-600 to-emerald-600' : 'bg-gradient-to-br from-slate-700 to-slate-900' }}" data-testid="pix-box">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center text-2xl"><i class="bi bi-qr-code"></i></div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg sm:text-xl font-black">{{ $pixOn ? $pct($cfg['pix']) . '% de desconto no Pix' : 'Sem desconto no Pix' }}</h2>
                        <p class="text-sm text-white/85 mt-1">O desconto sai do valor dos produtos (o frete não entra) e aparece no carrinho na hora em que o cliente escolhe Pix. O Pix não tem taxa de cartão, então o desconto costuma sair barato.</p>
                    </div>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-wide text-white/70 mt-4 mb-1.5">Quanto de desconto</p>
                <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5">
                    @foreach([0, 3, 5, 7, 10, 12, 15] as $opt)
                        <button type="button" wire:click="setPix({{ $opt }})" data-testid="pix-{{ $opt }}"
                                class="rounded-xl py-2 text-sm font-bold transition {{ (float) $cfg['pix'] === (float) $opt ? 'bg-white text-teal-700 shadow' : 'bg-white/15 hover:bg-white/25' }}">{{ $opt ? $opt . '%' : 'Não' }}</button>
                    @endforeach
                </div>
                <label class="flex items-center gap-2 mt-3 text-sm text-white/90">
                    Outro valor:
                    <input type="number" step="0.5" min="0" max="30" wire:model.blur="cfg.pix" class="w-20 rounded-lg border-0 bg-white/15 text-white px-2 py-1 text-sm focus:ring-2 focus:ring-white/60" data-testid="pix-custom"> %
                </label>
                <label class="flex items-center gap-2 mt-2 text-sm text-white/90">
                    <input type="checkbox" wire:model.live="cfg.pix_card" class="rounded text-teal-600 focus:ring-white/60"> Mostrar “ou R$ X no Pix” nos cards e na página do produto
                </label>
                @if($pixOn && ! $pixOnline)
                    <p class="mt-3 rounded-xl bg-white/15 px-3 py-2 text-xs">O Pix pelo site está desligado. O desconto aparece para quem escolhe “Pix” ao combinar com a loja, e você confirma o pedido com o valor já descontado. <a href="{{ route('settings.payments') }}" class="underline font-semibold">Ligar o Pix pelo site</a></p>
                @endif
                <div class="grid grid-cols-2 gap-2 sm:gap-3 mt-4">
                    <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Pedidos no Pix com desconto (mês)</small><b class="text-xl font-black">{{ $stats['pixOrders'] }}</b></div>
                    <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Desconto dado (mês)</small><b class="text-xl font-black">{{ $money($stats['pixOff']) }}</b></div>
                </div>
            </section>

            {{-- Pontos --}}
            <section class="rounded-2xl overflow-hidden shadow-sm text-white p-5 sm:p-6 {{ $ptsOn ? 'bg-gradient-to-br from-amber-500 to-orange-600' : 'bg-gradient-to-br from-slate-700 to-slate-900' }}" data-testid="points-box">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center text-2xl"><i class="bi bi-star-fill"></i></div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg sm:text-xl font-black">{{ $ptsOn ? 'Pontos de fidelidade ligados' : 'Pontos de fidelidade desligados' }}</h2>
                        <p class="text-sm text-white/85 mt-1">O cliente com conta ganha pontos quando o pedido vira venda e troca por desconto no próximo pedido. Pedido recusado devolve os pontos usados.</p>
                    </div>
                    <button type="button" wire:click="togglePoints" role="switch" aria-checked="{{ $ptsOn ? 'true' : 'false' }}" data-testid="points-toggle" @disabled(! $ready)
                            class="relative inline-flex shrink-0 w-14 h-8 rounded-full transition {{ $ptsOn ? 'bg-white/90' : 'bg-white/25' }}">
                        <span class="absolute top-1 w-6 h-6 rounded-full shadow transition-all {{ $ptsOn ? 'left-7 bg-orange-600' : 'left-1 bg-white' }}"></span>
                    </button>
                </div>
                <div class="mt-4 rounded-xl bg-white/15 px-3 py-2.5 text-sm" data-testid="points-example">
                    <i class="bi bi-lightbulb"></i> Numa compra de R$ 100,00 o cliente ganha <b>{{ $num($exPts) }} pontos</b>, que valem <b>{{ $money($exBack) }}</b> na próxima ({{ $pct($exBack) }}% de volta).
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 mt-4">
                    <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Pontos com os clientes</small><b class="text-xl font-black">{{ $num($stats['circ']) }}</b></div>
                    <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Valem em desconto</small><b class="text-xl font-black">{{ $money($stats['circ'] * $cfg['worth'] / 100) }}</b></div>
                    <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Dados no mês</small><b class="text-xl font-black">{{ $num($stats['earned']) }}</b></div>
                    <div class="rounded-xl bg-white/12 p-3"><small class="block text-[11px] text-white/75">Usados no mês</small><b class="text-xl font-black">{{ $num($stats['used']) }}</b></div>
                </div>
            </section>
        </div>

        {{-- Regras dos pontos --}}
        <section class="{{ $card }} p-4 sm:p-5 space-y-3" data-testid="points-rules">
            <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-sliders text-violet-500"></i>Regras dos pontos</h2>
            <div class="grid gap-3 grid-cols-1 min-[520px]:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
                <div><label class="{{ $label }}" for="r-earn">Pontos por R$ 1,00 comprado</label><input id="r-earn" type="number" step="0.5" min="0.1" max="100" wire:model.blur="cfg.earn" class="{{ $input }}" data-testid="rule-earn"></div>
                <div><label class="{{ $label }}" for="r-worth">100 pontos valem (R$)</label><input id="r-worth" type="number" step="0.5" min="0.5" max="100" wire:model.blur="cfg.worth" class="{{ $input }}" data-testid="rule-worth"></div>
                <div><label class="{{ $label }}" for="r-min">Mínimo para usar (pontos)</label><input id="r-min" type="number" step="10" min="0" wire:model.blur="cfg.min_use" class="{{ $input }}"></div>
                <div><label class="{{ $label }}" for="r-max">Pagar até (% dos produtos)</label><input id="r-max" type="number" step="5" min="5" max="100" wire:model.blur="cfg.max_pct" class="{{ $input }}"></div>
                <div><label class="{{ $label }}" for="r-welcome">Bônus ao criar a conta</label><input id="r-welcome" type="number" step="10" min="0" wire:model.blur="cfg.welcome" class="{{ $input }}"></div>
                <div><label class="{{ $label }}" for="r-exp">Os pontos vencem</label>
                    <select id="r-exp" wire:model.live="cfg.expire" class="{{ $input }}">
                        @foreach($R::EXPIRE as $d => $dn)<option value="{{ $d }}">{{ $dn }}</option>@endforeach
                    </select>
                </div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">O frete não dá pontos e não pode ser pago com pontos. Os pontos aparecem para o cliente em Meus dados, no menu da conta e no carrinho.</p>
        </section>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_420px] xl:items-start">
            {{-- Quem tem pontos e últimas movimentações --}}
            <div class="space-y-4 min-w-0">
                <section class="{{ $card }} p-4 sm:p-5" data-testid="points-top">
                    <h2 class="font-bold text-slate-900 dark:text-white mb-3">Clientes com mais pontos</h2>
                    @if($top->isEmpty())
                        <p class="text-sm text-slate-400">Ninguém tem pontos ainda. Eles aparecem quando o primeiro pedido com os pontos ligados virar venda.</p>
                    @else
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($top as $t)
                                <li class="flex items-center gap-3 py-2">
                                    <span class="shrink-0 w-8 h-8 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200 flex items-center justify-center text-xs font-black">{{ mb_strtoupper(mb_substr($t['name'], 0, 1)) }}</span>
                                    <span class="min-w-0 flex-1 text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $t['name'] }}</span>
                                    <span class="text-right shrink-0"><b class="block text-sm text-slate-900 dark:text-white">{{ $num($t['bal']) }} pts</b><small class="text-[11px] text-slate-400">{{ $money($t['bal'] * $cfg['worth'] / 100) }}</small></span>
                                    <button type="button" wire:click="pickClient({{ $t['id'] }})" class="shrink-0 text-xs font-semibold text-violet-600 dark:text-violet-300 hover:underline">Ajustar</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
                <section class="{{ $card }} p-4 sm:p-5">
                    <h2 class="font-bold text-slate-900 dark:text-white mb-3">Últimas movimentações</h2>
                    @if($recent->isEmpty())
                        <p class="text-sm text-slate-400">Nada por aqui ainda.</p>
                    @else
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($recent as $r)
                                @php [$kn, $ki, $kc] = $R::KINDS[$r->kind] ?? [$r->kind, 'bi-dot', 'text-slate-500']; @endphp
                                <li class="flex items-center gap-3 py-2">
                                    <i class="bi {{ $ki }} {{ $kc }} text-lg shrink-0"></i>
                                    <span class="min-w-0 flex-1"><b class="block text-sm text-slate-800 dark:text-slate-100 truncate">{{ $r->client?->name ?? 'Cliente' }}</b><small class="block text-[11px] text-slate-500 truncate">{{ $kn }}@if($r->note) · {{ $r->note }}@endif · {{ $r->created_at->format('d/m H:i') }}</small></span>
                                    <b class="shrink-0 text-sm {{ $r->points >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $r->points >= 0 ? '+' : '' }}{{ $num($r->points) }}</b>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            {{-- Ajuste manual --}}
            <aside class="{{ $card }} p-4 sm:p-5 space-y-3 min-w-0" data-testid="points-adjust">
                <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-plus-slash-minus text-violet-500"></i>Dar ou tirar pontos</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Para uma compra feita fora do site, um presente ou uma correção.</p>
                @if($picked)
                    <div class="flex items-center gap-2 rounded-xl bg-violet-50 dark:bg-violet-500/10 px-3 py-2">
                        <span class="min-w-0 flex-1"><b class="block text-sm text-slate-800 dark:text-slate-100 truncate">{{ $picked->name }}</b><small class="text-xs text-slate-500">Tem {{ $num($pickedBal) }} pontos</small></span>
                        <button type="button" wire:click="$set('adjClient', '')" class="text-xs font-semibold text-violet-600">Trocar</button>
                    </div>
                @else
                    <div class="relative">
                        <label class="{{ $label }}" for="adj-q">Cliente</label>
                        <input id="adj-q" type="search" wire:model.live.debounce.300ms="adjSearch" placeholder="Digite o nome" class="{{ $input }}" data-testid="adjust-search" autocomplete="off">
                        @if($found->isNotEmpty())
                            <ul class="absolute z-10 inset-x-0 mt-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg overflow-hidden">
                                @foreach($found as $f)
                                    <li><button type="button" wire:click="pickClient({{ $f->id }})" class="w-full text-left px-3 py-2 text-sm hover:bg-violet-50 dark:hover:bg-slate-700">{{ $f->name }} <small class="text-slate-400">{{ $f->phone }}</small></button></li>
                                @endforeach
                            </ul>
                        @endif
                        @error('adjClient')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div>
                    <label class="{{ $label }}" for="adj-p">Pontos <small class="font-normal text-slate-400">(use − para tirar)</small></label>
                    <input id="adj-p" type="number" step="1" wire:model="adjPoints" placeholder="Ex.: 200 ou -50" class="{{ $input }}" data-testid="adjust-points">
                    @error('adjPoints')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="adj-n">Motivo <small class="font-normal text-slate-400">(o cliente vê)</small></label>
                    <input id="adj-n" type="text" maxlength="160" wire:model="adjNote" placeholder="Ex.: compra na loja física" class="{{ $input }}">
                </div>
                <button type="button" wire:click="adjust" wire:loading.attr="disabled" class="{{ $primary }} w-full" data-testid="adjust-save" @disabled(! $ready)><i class="bi bi-check2"></i>Salvar</button>
            </aside>
        </div>
    </div>

    </x-portal-settings-nav>
</div>
