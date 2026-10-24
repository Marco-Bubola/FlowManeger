@php
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'sky' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'amber' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200',
        'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300',
    ];
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:border-rose-400 focus:ring-2 focus:ring-rose-100 dark:focus:ring-rose-900/30 focus:outline-none';
    $valid = $coupons->filter(fn ($c) => \App\Livewire\Promotions\PortalCoupons::state($c, (int) ($uses[$c->code]->n ?? 0))[0] === 'Valendo')->count();
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
    <x-page-header title="Cupons do catálogo" icon="bi-ticket-perforated" section="produtos" back-route="promotions.index"
                   subtitle="Códigos de desconto que o cliente digita no carrinho do catálogo">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones['emerald'] }}"><i class="bi bi-check2-circle"></i>{{ $valid }} valendo</span>
            @if($uses->sum('n'))
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones['sky'] }}"><i class="bi bi-bag-check"></i>{{ $uses->sum('n') }} {{ $uses->sum('n') == 1 ? 'pedido' : 'pedidos' }} com cupom</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" title="Abrir o catálogo" class="{{ $phBtn }}"><i class="bi bi-shop text-violet-500"></i><span class="hidden sm:inline">Catálogo</span></a>
            <button type="button" wire:click="create" data-testid="coupon-new"
                    class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-rose-500 to-orange-500 hover:from-rose-600 hover:to-orange-600 shadow-md shadow-rose-500/25 transition whitespace-nowrap"><i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Novo cupom</span></button>
        </x-slot:actions>
    </x-page-header>

    @if($coupons->isEmpty())
        <x-empty-state icon="bi-ticket-perforated" tone="rose" title="Nenhum cupom ainda"
                       text="Crie um código, como BEMVINDA10, e mande para seus clientes. Eles digitam no carrinho do catálogo e o desconto aparece na hora.">
            <button type="button" wire:click="create" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-rose-500 to-orange-500 shadow-md"><i class="bi bi-plus-lg"></i>Criar primeiro cupom</button>
        </x-empty-state>
    @else
        <div class="grid gap-3 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" data-testid="coupon-list">
            @foreach($coupons as $c)
                @php
                    $u = $uses[$c->code] ?? null;
                    $n = (int) ($u->n ?? 0);
                    [$st, $tone] = \App\Livewire\Promotions\PortalCoupons::state($c, $n);
                    $rules = array_filter([
                        $c->min_total ? 'Pedido a partir de ' . $money($c->min_total) : null,
                        $c->starts_at || $c->ends_at ? trim(($c->starts_at ? 'de ' . $c->starts_at->format('d/m/Y') : '') . ' ' . ($c->ends_at ? 'até ' . $c->ends_at->format('d/m/Y') : '')) : 'Sem prazo',
                        $c->max_uses ? $n . ' de ' . $c->max_uses . ' usos' : null,
                        $c->once_per_client ? '1 vez por cliente' : 'Cliente pode repetir',
                    ]);
                @endphp
                <article wire:key="cp-{{ $c->id }}" class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm p-4 flex flex-col gap-3 {{ $c->active ? '' : 'opacity-70' }}">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center bg-gradient-to-br from-rose-500 to-orange-500 text-white text-lg"><i class="bi bi-ticket-perforated"></i></div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-mono font-bold text-base tracking-wide text-slate-900 dark:text-white break-all">{{ $c->code }}</p>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones[$tone] }}">{{ $st }}</span>
                            </div>
                            <p class="text-sm font-semibold text-rose-600 dark:text-rose-300">{{ $c->label() }}</p>
                        </div>
                    </div>
                    <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                        @foreach($rules as $r)<li class="flex items-center gap-1.5"><i class="bi bi-dot"></i>{{ $r }}</li>@endforeach
                    </ul>
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        @if($n)
                            Usado em <b>{{ $n }}</b> {{ $n == 1 ? 'pedido' : 'pedidos' }} · <b>{{ $money($u->total) }}</b> de desconto dado
                        @else
                            Ainda não foi usado
                        @endif
                    </p>
                    <div class="flex items-center gap-2 mt-auto pt-1">
                        <button type="button" wire:click="toggle({{ $c->id }})" class="{{ $phBtn }} !py-1.5" title="{{ $c->active ? 'Desligar' : 'Ligar' }}">
                            <i class="bi {{ $c->active ? 'bi-toggle-on text-emerald-500' : 'bi-toggle-off text-slate-400' }} text-lg"></i>{{ $c->active ? 'Ligado' : 'Desligado' }}
                        </button>
                        <button type="button" x-data @click="navigator.clipboard?.writeText(@js($c->code)); window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Código copiado' } }))" class="{{ $phBtn }} !py-1.5" title="Copiar código"><i class="bi bi-copy"></i></button>
                        <button type="button" wire:click="edit({{ $c->id }})" class="{{ $phBtn }} !py-1.5 ml-auto" title="Editar"><i class="bi bi-pencil text-indigo-500"></i><span>Editar</span></button>
                        <button type="button" x-data @click="fmConfirm({ title: 'Excluir o cupom {{ $c->code }}?', message: 'Os pedidos que já usaram continuam com o desconto.', confirmText: 'Excluir' }).then(ok => ok && $wire.delete({{ $c->id }}))" class="{{ $phBtn }} !py-1.5" title="Excluir"><i class="bi bi-trash3 text-rose-500"></i></button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    {{-- Criar / editar --}}
    @if($showForm)
        <div class="fixed inset-0 z-[90] flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="$set('showForm', false)">
            <div class="absolute inset-0" wire:click="$set('showForm', false)"></div>
            <form wire:submit="save" class="relative w-full sm:max-w-lg max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl bg-white dark:bg-slate-900 shadow-2xl p-5 sm:p-6 space-y-4" data-testid="coupon-form">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $editingId ? 'Editar cupom' : 'Novo cupom' }}</h2>
                    <button type="button" wire:click="$set('showForm', false)" class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>

                <div>
                    <label class="{{ $label }}" for="cp-code">Código que o cliente digita</label>
                    <input id="cp-code" type="text" wire:model="code" maxlength="40" placeholder="Ex.: BEMVINDA10" class="{{ $input }} uppercase font-mono tracking-wide" autocomplete="off">
                    @error('code')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <span class="{{ $label }}">Tipo de desconto</span>
                        <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                            <button type="button" wire:click="$set('kind', 'percent')" class="rounded-lg py-2 text-sm font-semibold {{ $kind === 'percent' ? 'bg-white dark:bg-slate-700 shadow text-rose-600 dark:text-rose-300' : 'text-slate-500' }}">%</button>
                            <button type="button" wire:click="$set('kind', 'fixed')" class="rounded-lg py-2 text-sm font-semibold {{ $kind === 'fixed' ? 'bg-white dark:bg-slate-700 shadow text-rose-600 dark:text-rose-300' : 'text-slate-500' }}">R$</button>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $label }}" for="cp-value">{{ $kind === 'percent' ? 'Desconto (%)' : 'Desconto (R$)' }}</label>
                        <input id="cp-value" type="text" inputmode="decimal" wire:model="value" placeholder="{{ $kind === 'percent' ? '10' : '15,00' }}" class="{{ $input }}">
                        @error('value')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}" for="cp-min">Pedido mínimo <span class="font-normal text-slate-400">(opcional)</span></label>
                        <input id="cp-min" type="text" inputmode="decimal" wire:model="min_total" placeholder="R$ 0,00" class="{{ $input }}">
                        @error('min_total')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="cp-max">Limite de usos <span class="font-normal text-slate-400">(opcional)</span></label>
                        <input id="cp-max" type="number" min="1" inputmode="numeric" wire:model="max_uses" placeholder="Sem limite" class="{{ $input }}">
                        @error('max_uses')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}" for="cp-start">Começa em <span class="font-normal text-slate-400">(opcional)</span></label>
                        <input id="cp-start" type="date" wire:model="starts_at" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}" for="cp-end">Termina em <span class="font-normal text-slate-400">(opcional)</span></label>
                        <input id="cp-end" type="date" wire:model="ends_at" class="{{ $input }}">
                        @error('ends_at')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                    <input type="checkbox" wire:model="once_per_client" class="mt-0.5 rounded text-rose-500 focus:ring-rose-400">
                    <span class="text-sm text-slate-700 dark:text-slate-200"><b>Uma vez por cliente</b><small class="block text-xs text-slate-500">Cada cliente usa este cupom em um pedido só.</small></span>
                </label>
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                    <input type="checkbox" wire:model="active" class="mt-0.5 rounded text-rose-500 focus:ring-rose-400">
                    <span class="text-sm text-slate-700 dark:text-slate-200"><b>Ligado</b><small class="block text-xs text-slate-500">Desligado, o carrinho avisa que o cupom não está valendo.</small></span>
                </label>

                <p class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-info-circle"></i> O desconto vale sobre os produtos do pedido (a taxa de entrega não entra) e não passa do valor dos produtos.</p>

                <div class="flex gap-2 pt-1">
                    <button type="button" wire:click="$set('showForm', false)" class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-rose-500 to-orange-500 shadow-md" data-testid="coupon-save"><span wire:loading.remove wire:target="save">Salvar cupom</span><span wire:loading wire:target="save">Salvando…</span></button>
                </div>
            </form>
        </div>
    @endif
</div>
