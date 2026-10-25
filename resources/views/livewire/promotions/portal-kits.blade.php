@php
    $K = \App\Services\Portal\KitService::class;
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $primary = 'inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-rose-500 to-fuchsia-500 hover:from-rose-600 hover:to-fuchsia-600 shadow-md shadow-rose-500/25 transition whitespace-nowrap';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:border-rose-400 focus:ring-2 focus:ring-rose-100 dark:focus:ring-rose-900/30 focus:outline-none';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $err = 'mt-1 text-xs font-semibold text-rose-600';
    $activeTypes = $types->where('active', true)->count();
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
    <x-page-header title="Monte seu kit" icon="bi-gift" section="produtos" back-route="promotions.storefront"
                   subtitle="O cliente escolhe a cesta, os produtos da loja e os enfeites, e manda o pedido">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300"><i class="bi bi-gift"></i>{{ $activeTypes }} {{ $activeTypes == 1 ? 'kit no ar' : 'kits no ar' }}</span>
            @if($kitOrders)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-bag-check"></i>{{ $kitOrders }} {{ $kitOrders == 1 ? 'pedido de kit' : 'pedidos de kit' }}</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ $builderUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }}" data-testid="kit-open-builder"><i class="bi bi-box-arrow-up-right text-rose-500"></i><span class="hidden sm:inline">Ver no catálogo</span></a>
            <button type="button" wire:click="{{ $tab === 'enfeites' ? 'newExtra' : 'newType' }}" class="{{ $primary }}" data-testid="kit-new">
                <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">{{ $tab === 'enfeites' ? 'Novo enfeite' : 'Novo tipo de kit' }}</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    <x-portal-settings-nav current="kits">

    {{-- Abas --}}
    <div class="flex items-center gap-2 flex-wrap">
        <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 text-sm font-semibold">
            <button type="button" wire:click="$set('tab', 'tipos')" class="rounded-lg px-3.5 py-1.5 {{ $tab === 'tipos' ? 'bg-white dark:bg-slate-700 shadow text-rose-600 dark:text-rose-300' : 'text-slate-500' }}" data-testid="kit-tab-tipos"><i class="bi bi-basket"></i> Tipos de kit <span class="text-xs opacity-70">{{ $types->count() }}</span></button>
            <button type="button" wire:click="$set('tab', 'enfeites')" class="rounded-lg px-3.5 py-1.5 {{ $tab === 'enfeites' ? 'bg-white dark:bg-slate-700 shadow text-rose-600 dark:text-rose-300' : 'text-slate-500' }}" data-testid="kit-tab-enfeites"><i class="bi bi-balloon-heart"></i> Enfeites e doces <span class="text-xs opacity-70">{{ $extras->count() }}</span></button>
        </div>
        @if($types->isNotEmpty() || $extras->isNotEmpty())
            <button type="button" wire:click="seed" class="{{ $phBtn }} !py-1.5 ml-auto" title="Cria os exemplos que ainda não existem"><i class="bi bi-magic text-fuchsia-500"></i>Adicionar exemplos</button>
        @endif
    </div>

    @if($types->isEmpty() && $extras->isEmpty())
        {{-- Começo: exemplos prontos --}}
        <section class="relative overflow-hidden rounded-3xl p-6 sm:p-8 text-white bg-gradient-to-br from-rose-500 via-fuchsia-500 to-violet-600 shadow-lg" data-testid="kit-empty">
            <div class="absolute -right-6 -top-6 text-[120px] leading-none opacity-20 select-none" aria-hidden="true">🎁</div>
            <div class="relative max-w-xl space-y-3">
                <p class="text-xs font-bold uppercase tracking-wider text-white/80">Novo no catálogo</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold leading-tight">Seus clientes montam o próprio kit de presente</h2>
                <p class="text-sm sm:text-base text-white/90">Eles escolhem a cesta, a banheira ou a caixa, colocam os produtos da loja e os enfeites como balão, chocolate e laço. O pedido chega pronto, com o valor de tudo.</p>
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="button" wire:click="seed" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold bg-white text-rose-600 shadow" data-testid="kit-seed"><i class="bi bi-magic"></i>Criar exemplos prontos</button>
                    <button type="button" wire:click="newType" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold bg-white/15 hover:bg-white/25"><i class="bi bi-plus-lg"></i>Começar do zero</button>
                </div>
                <p class="text-xs text-white/75">Os exemplos trazem 5 tipos de kit e 11 enfeites com preços sugeridos. Depois é só ajustar.</p>
            </div>
        </section>
    @elseif($tab === 'tipos')
        @if($types->isEmpty())
            <x-empty-state icon="bi-basket" tone="rose" title="Nenhum tipo de kit" text="Crie a cesta, a caixa ou a banheira que o cliente vai montar.">
                <button type="button" wire:click="newType" class="{{ $primary }}"><i class="bi bi-plus-lg"></i>Novo tipo de kit</button>
            </x-empty-state>
        @else
            <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" data-testid="kit-types">
                @foreach($types as $t)
                    @php [$sName, $sEmoji, $sIcon, $c1, $c2] = $K::style($t->style); $img = $K::imageUrl($t->image); @endphp
                    <article wire:key="kt-{{ $t->id }}" class="{{ $card }} overflow-hidden flex flex-col {{ $t->active ? '' : 'opacity-70' }}">
                        <div class="relative aspect-[16/9] overflow-hidden" style="background:linear-gradient(135deg, {{ $c1 }}, {{ $c2 }})">
                            @if($img)
                                <img src="{{ $img }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                            @else
                                <span class="absolute inset-0 flex items-center justify-center text-6xl drop-shadow-lg select-none" aria-hidden="true">{{ $sEmoji }}</span>
                                <span class="absolute left-4 top-3 text-2xl opacity-40 select-none" aria-hidden="true">✨</span>
                            @endif
                            <span class="absolute left-3 bottom-3 inline-flex items-center gap-1 rounded-full bg-white/90 text-slate-800 px-2.5 py-1 text-[11px] font-bold shadow">{{ $sEmoji }} {{ $sName }}</span>
                            @unless($t->active)<span class="absolute right-3 top-3 rounded-full bg-slate-900/70 text-white px-2 py-0.5 text-[11px] font-semibold">Desligado</span>@endunless
                        </div>
                        <div class="p-4 flex flex-col gap-2 flex-1">
                            <h3 class="font-bold text-slate-900 dark:text-white leading-snug">{{ $t->name }}</h3>
                            @if($t->description)<p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">{{ $t->description }}</p>@endif
                            <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                                <span class="rounded-full px-2 py-0.5 bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">Base {{ $money($t->base_price) }}</span>
                                <span class="rounded-full px-2 py-0.5 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $t->min_items == $t->max_items ? $t->min_items : $t->min_items . ' a ' . $t->max_items }} produtos</span>
                                @if($t->options)<span class="rounded-full px-2 py-0.5 bg-fuchsia-50 text-fuchsia-700 dark:bg-fuchsia-500/15 dark:text-fuchsia-300">{{ count($t->options) }} {{ count($t->options) == 1 ? 'opção' : 'opções' }}</span>@endif
                                @if($t->setting('photos'))<span class="rounded-full px-2 py-0.5 bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-images"></i> fotos</span>@endif
                                @if($t->category_ids)<span class="rounded-full px-2 py-0.5 bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">{{ count($t->category_ids) }} {{ count($t->category_ids) == 1 ? 'categoria' : 'categorias' }}</span>@endif
                            </div>
                            <div class="flex items-center gap-1.5 mt-auto pt-2">
                                <button type="button" wire:click="toggleType({{ $t->id }})" class="{{ $phBtn }} !px-2.5 !py-1.5" title="{{ $t->active ? 'Desligar' : 'Ligar' }}"><i class="bi {{ $t->active ? 'bi-toggle-on text-emerald-500' : 'bi-toggle-off text-slate-400' }} text-lg"></i></button>
                                <button type="button" wire:click="moveType({{ $t->id }}, -1)" class="{{ $phBtn }} !px-2.5 !py-1.5" title="Para a esquerda" @disabled($loop->first)><i class="bi bi-arrow-left"></i></button>
                                <button type="button" wire:click="moveType({{ $t->id }}, 1)" class="{{ $phBtn }} !px-2.5 !py-1.5" title="Para a direita" @disabled($loop->last)><i class="bi bi-arrow-right"></i></button>
                                <button type="button" wire:click="editType({{ $t->id }})" class="{{ $phBtn }} !py-1.5 ml-auto" data-testid="kit-edit-{{ $t->id }}"><i class="bi bi-pencil text-indigo-500"></i>Editar</button>
                                <button type="button" x-data @click="fmConfirm({ title: @js('Excluir o kit ' . $t->name . '?'), message: 'Os pedidos que já chegaram continuam iguais.', confirmText: 'Excluir' }).then(ok => ok && $wire.deleteType({{ $t->id }}))" class="{{ $phBtn }} !px-2.5 !py-1.5" title="Excluir"><i class="bi bi-trash3 text-rose-500"></i></button>
                            </div>
                        </div>
                    </article>
                @endforeach
                <button type="button" wire:click="newType" class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-rose-300 hover:bg-rose-50/40 dark:hover:bg-rose-500/5 min-h-[220px] flex flex-col items-center justify-center gap-2 text-slate-500 dark:text-slate-400 transition">
                    <span class="w-12 h-12 rounded-2xl flex items-center justify-center bg-rose-50 dark:bg-rose-500/15 text-rose-500 text-xl"><i class="bi bi-plus-lg"></i></span>
                    <b class="text-sm">Novo tipo de kit</b>
                </button>
            </div>
        @endif
    @else
        @if($extras->isEmpty())
            <x-empty-state icon="bi-balloon-heart" tone="rose" title="Nenhum enfeite ainda" text="Cadastre balões, chocolates, laços, cartões e o que mais o cliente puder colocar no kit.">
                <button type="button" wire:click="newExtra" class="{{ $primary }}"><i class="bi bi-plus-lg"></i>Novo enfeite</button>
            </x-empty-state>
        @else
            <div class="space-y-4" data-testid="kit-extras">
                @foreach($K::GROUPS as $g => [$gName, $gEmoji])
                    @php $rows = $extras->where('group', $g)->values(); @endphp
                    @continue($rows->isEmpty())
                    <section class="{{ $card }} p-3 sm:p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl" aria-hidden="true">{{ $gEmoji }}</span>
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $gName }}</h3>
                            <span class="text-xs text-slate-400">{{ $rows->count() }}</span>
                            <button type="button" wire:click="newExtra('{{ $g }}')" class="ml-auto text-xs font-semibold text-rose-600 dark:text-rose-300 hover:underline"><i class="bi bi-plus-lg"></i> Adicionar</button>
                        </div>
                        <div class="grid gap-2 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            @foreach($rows as $e)
                                <div wire:key="ke-{{ $e->id }}" class="min-w-0 flex items-center gap-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70 p-2.5 {{ $e->active ? '' : 'opacity-60' }}">
                                    <span class="shrink-0 w-12 h-12 rounded-xl overflow-hidden flex items-center justify-center bg-rose-50 dark:bg-rose-500/10 text-2xl">
                                        @if($e->image)<img src="{{ $K::imageUrl($e->image) }}" alt="" class="w-full h-full object-cover">@else{{ $gEmoji }}@endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <b class="block text-sm text-slate-800 dark:text-slate-100 truncate">{{ $e->name }}</b>
                                        <small class="block text-xs text-slate-500 dark:text-slate-400">{{ $money($e->price) }} · até {{ $e->max_qty }} por kit{{ $e->active ? '' : ' · desligado' }}</small>
                                    </span>
                                    <span class="shrink-0 flex items-center gap-0.5">
                                        <button type="button" wire:click="toggleExtra({{ $e->id }})" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" title="{{ $e->active ? 'Desligar' : 'Ligar' }}"><i class="bi {{ $e->active ? 'bi-toggle-on text-emerald-500' : 'bi-toggle-off text-slate-400' }} text-lg"></i></button>
                                        <button type="button" wire:click="editExtra({{ $e->id }})" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-indigo-500" title="Editar"><i class="bi bi-pencil"></i></button>
                                        <button type="button" x-data @click="fmConfirm({ title: @js('Excluir ' . $e->name . '?'), confirmText: 'Excluir' }).then(ok => ok && $wire.deleteExtra({{ $e->id }}))" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-rose-500" title="Excluir"><i class="bi bi-trash3"></i></button>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    @endif

    <p class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-info-circle"></i> O pedido do kit chega em Pedidos do portal com os produtos, os enfeites e a mensagem. Os produtos saem do estoque quando você confirma a venda; os enfeites não são controlados no estoque.</p>

    {{-- Tipo de kit --}}
    @if($typeForm)
        <div class="fixed inset-0 z-[90] flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="$set('typeForm', false)">
            <div class="absolute inset-0" wire:click="$set('typeForm', false)"></div>
            <form wire:submit="saveType" class="relative w-full sm:max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl bg-white dark:bg-slate-900 shadow-2xl p-5 sm:p-6 space-y-4" data-testid="kit-type-form">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $typeId ? 'Editar tipo de kit' : 'Novo tipo de kit' }}</h2>
                    <button type="button" wire:click="$set('typeForm', false)" class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>

                <div>
                    <span class="{{ $label }}">Formato</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach($K::STYLES as $sk => [$sn, $se, $si, $s1, $s2])
                            <button type="button" wire:click="$set('t_style', '{{ $sk }}')"
                                    class="relative rounded-xl p-2.5 text-left text-white overflow-hidden transition {{ $t_style === $sk ? 'ring-2 ring-offset-2 ring-rose-500 dark:ring-offset-slate-900' : 'opacity-80 hover:opacity-100' }}"
                                    style="background:linear-gradient(135deg, {{ $s1 }}, {{ $s2 }})">
                                <span class="block text-2xl leading-none">{{ $se }}</span>
                                <span class="block text-[11px] font-bold mt-1.5 leading-tight">{{ $sn }}</span>
                                @if($t_style === $sk)<i class="bi bi-check-circle-fill absolute right-2 top-2"></i>@endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid sm:grid-cols-[minmax(0,1fr)_170px] gap-3">
                    <div class="space-y-3 min-w-0">
                        <div>
                            <label class="{{ $label }}" for="kt-name">Nome que o cliente vê</label>
                            <input id="kt-name" type="text" wire:model="t_name" maxlength="80" placeholder="Ex.: Cesta romântica com balão" class="{{ $input }}">
                            @error('t_name')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="{{ $label }}" for="kt-desc">Descrição <span class="font-normal text-slate-400">(opcional)</span></label>
                            <textarea id="kt-desc" wire:model="t_description" rows="2" maxlength="400" placeholder="O que vem na base: cesta, papel de seda, laço…" class="{{ $input }}"></textarea>
                        </div>
                    </div>
                    <div>
                        <span class="{{ $label }}">Foto <span class="font-normal text-slate-400">(opcional)</span></span>
                        @php $pv = $t_upload ? $t_upload->temporaryUrl() : $K::imageUrl($t_image); [$x1, $x2] = array_slice($K::style($t_style), 3, 2); @endphp
                        <label class="relative block aspect-[4/3] rounded-xl overflow-hidden cursor-pointer border border-slate-200 dark:border-slate-700" style="background:linear-gradient(135deg, {{ $x1 }}, {{ $x2 }})">
                            @if($pv)
                                <img src="{{ $pv }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                            @else
                                <span class="absolute inset-0 flex flex-col items-center justify-center text-white text-xs font-semibold gap-1"><span class="text-3xl">{{ $K::style($t_style)[1] }}</span><span><i class="bi bi-camera"></i> Pôr foto</span></span>
                            @endif
                            <input type="file" wire:model="t_upload" accept="image/*" class="sr-only">
                            <span wire:loading wire:target="t_upload" class="absolute inset-0 bg-white/70 flex items-center justify-center text-xs font-semibold text-slate-700">Enviando…</span>
                        </label>
                        @if($pv)<button type="button" wire:click="removeTypeImage" class="mt-1 text-xs font-semibold text-rose-600">Tirar foto</button>@endif
                        @error('t_upload')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="{{ $label }}" for="kt-price">Preço da base</label>
                        <input id="kt-price" type="text" inputmode="decimal" wire:model="t_base_price" placeholder="0,00" class="{{ $input }}">
                        @error('t_base_price')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="kt-min">Mín. produtos</label>
                        <input id="kt-min" type="number" min="1" max="50" wire:model="t_min" class="{{ $input }}">
                        @error('t_min')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="kt-max">Máx. produtos</label>
                        <input id="kt-max" type="number" min="1" max="50" wire:model="t_max" class="{{ $input }}">
                        @error('t_max')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                </div>
                <p class="-mt-2 text-xs text-slate-500 dark:text-slate-400">A base é a cesta ou a caixa com a montagem. O cliente paga a base + os produtos + os enfeites.</p>

                @if($cats->isNotEmpty())
                    <div x-data="{ open: {{ $t_cats ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open" class="text-sm font-semibold text-slate-700 dark:text-slate-200"><i class="bi" :class="open ? 'bi-chevron-down' : 'bi-chevron-right'"></i> Só produtos de algumas categorias <span class="font-normal text-slate-400">(opcional)</span></button>
                        <div x-show="open" x-cloak class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($cats as $c)
                                <label class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold cursor-pointer {{ in_array((string) $c->id_category, $t_cats, true) ? 'border-rose-400 bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}">
                                    <input type="checkbox" wire:model.live="t_cats" value="{{ $c->id_category }}" class="sr-only">{{ $c->name }}
                                </label>
                            @endforeach
                            <p class="w-full text-xs text-slate-500">Sem nenhuma marcada, o cliente escolhe entre todos os produtos com estoque.</p>
                        </div>
                    </div>
                @endif

                {{-- Opções que o cliente escolhe --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-3 space-y-3" data-testid="kit-options">
                    <div class="flex items-start gap-2">
                        <i class="bi bi-palette2 text-fuchsia-500 text-lg"></i>
                        <div class="min-w-0 flex-1">
                            <b class="block text-sm text-slate-800 dark:text-slate-100">Opções que o cliente escolhe</b>
                            <small class="block text-xs text-slate-500 dark:text-slate-400">Ex.: cor do laço, tamanho, papel. Cada opção pode ter um valor a mais.</small>
                        </div>
                    </div>
                    @foreach($t_options as $oi => $opt)
                        <div wire:key="opt-{{ $oi }}" class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-2.5 space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="t_options.{{ $oi }}.name" maxlength="40" placeholder="Nome da opção (ex.: Cor do laço)" class="{{ $input }} !py-2 font-semibold">
                                <label class="shrink-0 inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500" title="O cliente tem que escolher"><input type="checkbox" wire:model="t_options.{{ $oi }}.required" class="rounded text-rose-500">Obrigatória</label>
                                <button type="button" wire:click="removeOption({{ $oi }})" class="shrink-0 w-8 h-8 rounded-lg hover:bg-white dark:hover:bg-slate-700 text-rose-500" title="Tirar opção"><i class="bi bi-trash3"></i></button>
                            </div>
                            <div class="grid gap-1.5">
                                @foreach($opt['choices'] ?? [] as $ci => $ch)
                                    <div wire:key="ch-{{ $oi }}-{{ $ci }}" class="flex items-center gap-1.5">
                                        <input type="color" wire:model.blur="t_options.{{ $oi }}.choices.{{ $ci }}.color" value="{{ $ch['color'] ?? '#ffffff' }}" class="shrink-0 w-8 h-8 rounded-lg border-0 bg-transparent p-0 cursor-pointer {{ empty($ch['color']) ? 'opacity-40' : '' }}" title="Cor (opcional)">
                                        <input type="text" wire:model="t_options.{{ $oi }}.choices.{{ $ci }}.label" maxlength="40" placeholder="Escolha (ex.: Rosa)" class="{{ $input }} !py-1.5 min-w-0" style="flex:1 1 0%">
                                        <span class="shrink-0 text-xs text-slate-400">+R$</span>
                                        <input type="text" inputmode="decimal" wire:model="t_options.{{ $oi }}.choices.{{ $ci }}.price" class="{{ $input }} !py-1.5 shrink-0" style="width:5rem;flex:0 0 5rem" placeholder="0">
                                        <button type="button" wire:click="removeChoice({{ $oi }}, {{ $ci }})" class="shrink-0 w-7 h-7 rounded-lg text-slate-400 hover:text-rose-500" title="Tirar"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="addChoice({{ $oi }})" class="text-xs font-semibold text-rose-600 dark:text-rose-300"><i class="bi bi-plus-lg"></i> Adicionar escolha</button>
                        </div>
                    @endforeach
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($K::OPTION_IDEAS as $ik => [$iname])
                            @continue(collect($t_options)->pluck('name')->contains($iname))
                            <button type="button" wire:click="addOption('{{ $ik }}')" class="rounded-full border border-dashed border-slate-300 dark:border-slate-600 px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:border-rose-400"><i class="bi bi-plus"></i> {{ $iname }}</button>
                        @endforeach
                        <button type="button" wire:click="addOption" class="rounded-full border border-dashed border-rose-300 px-3 py-1 text-xs font-semibold text-rose-600 dark:text-rose-300"><i class="bi bi-plus"></i> Outra opção</button>
                    </div>
                </div>

                {{-- O que mais o cliente manda --}}
                <div class="grid sm:grid-cols-2 gap-2">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3">
                        <i class="bi bi-images text-fuchsia-500 text-lg"></i>
                        <span class="min-w-0 flex-1 text-sm text-slate-700 dark:text-slate-200"><b>Fotos do cliente</b><small class="block text-xs text-slate-500">Para polaroid, caneca ou porta-retrato.</small>
                            <select wire:model="t_photos" class="{{ $input }} !py-1.5 mt-1.5">
                                <option value="0">Não pedir fotos</option>
                                @foreach([1, 2, 3, 4, 5, 6] as $n)<option value="{{ $n }}">Até {{ $n }} {{ $n == 1 ? 'foto' : 'fotos' }}</option>@endforeach
                            </select>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                        <input type="checkbox" wire:model="t_date" class="mt-0.5 rounded text-rose-500 focus:ring-rose-400">
                        <span class="text-sm text-slate-700 dark:text-slate-200"><b>Data de entrega</b><small class="block text-xs text-slate-500">O cliente diz para quando quer o kit (aniversário, data especial).</small></span>
                    </label>
                </div>

                <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                    <input type="checkbox" wire:model="t_active" class="mt-0.5 rounded text-rose-500 focus:ring-rose-400">
                    <span class="text-sm text-slate-700 dark:text-slate-200"><b>Ligado</b><small class="block text-xs text-slate-500">Aparece no "Monte seu kit" do catálogo.</small></span>
                </label>

                <div class="flex gap-2 pt-1">
                    <button type="button" wire:click="$set('typeForm', false)" class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</button>
                    <button type="submit" class="flex-1 {{ $primary }} justify-center !py-2.5" data-testid="kit-type-save"><span wire:loading.remove wire:target="saveType">Salvar kit</span><span wire:loading wire:target="saveType">Salvando…</span></button>
                </div>
            </form>
        </div>
    @endif

    {{-- Enfeite --}}
    @if($extraForm)
        <div class="fixed inset-0 z-[90] flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="$set('extraForm', false)">
            <div class="absolute inset-0" wire:click="$set('extraForm', false)"></div>
            <form wire:submit="saveExtra" class="relative w-full sm:max-w-lg max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl bg-white dark:bg-slate-900 shadow-2xl p-5 sm:p-6 space-y-4" data-testid="kit-extra-form">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $extraId ? 'Editar enfeite' : 'Novo enfeite' }}</h2>
                    <button type="button" wire:click="$set('extraForm', false)" class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>
                <div>
                    <span class="{{ $label }}">Tipo</span>
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach($K::GROUPS as $g => [$gn, $ge])
                            <button type="button" wire:click="$set('e_group', '{{ $g }}')" class="rounded-xl border px-1 py-2 text-center text-[11px] font-semibold leading-tight {{ $e_group === $g ? 'border-rose-400 bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}">
                                <span class="block text-xl">{{ $ge }}</span>{{ $gn }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-[88px_minmax(0,1fr)] gap-3 items-start">
                    @php $epv = $e_upload ? $e_upload->temporaryUrl() : $K::imageUrl($e_image); @endphp
                    <div>
                        <label class="relative block w-[88px] h-[88px] rounded-xl overflow-hidden cursor-pointer bg-rose-50 dark:bg-rose-500/10 border border-slate-200 dark:border-slate-700">
                            @if($epv)<img src="{{ $epv }}" alt="" class="absolute inset-0 w-full h-full object-cover">@else<span class="absolute inset-0 flex flex-col items-center justify-center text-[11px] font-semibold text-slate-500"><span class="text-3xl">{{ $K::GROUPS[$e_group][1] ?? '✨' }}</span><i class="bi bi-camera"></i></span>@endif
                            <input type="file" wire:model="e_upload" accept="image/*" class="sr-only">
                            <span wire:loading wire:target="e_upload" class="absolute inset-0 bg-white/70 flex items-center justify-center text-[11px] font-semibold text-slate-700">Enviando…</span>
                        </label>
                        @if($epv)<button type="button" wire:click="removeExtraImage" class="mt-1 text-[11px] font-semibold text-rose-600">Tirar foto</button>@endif
                    </div>
                    <div class="space-y-3 min-w-0">
                        <div>
                            <label class="{{ $label }}" for="ke-name">Nome</label>
                            <input id="ke-name" type="text" wire:model="e_name" maxlength="80" placeholder="Ex.: Balão coração metalizado" class="{{ $input }}">
                            @error('e_name')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $label }}" for="ke-price">Preço</label>
                                <input id="ke-price" type="text" inputmode="decimal" wire:model="e_price" placeholder="0,00" class="{{ $input }}">
                                @error('e_price')<p class="{{ $err }}">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="ke-max">Até quantos por kit</label>
                                <input id="ke-max" type="number" min="1" max="99" wire:model="e_max" class="{{ $input }}">
                                @error('e_max')<p class="{{ $err }}">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                @error('e_upload')<p class="{{ $err }}">{{ $message }}</p>@enderror
                <div>
                    <label class="{{ $label }}" for="ke-desc">Detalhe <span class="font-normal text-slate-400">(opcional)</span></label>
                    <input id="ke-desc" type="text" wire:model="e_description" maxlength="200" placeholder="Ex.: 45 cm, cores sortidas" class="{{ $input }}">
                </div>
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                    <input type="checkbox" wire:model="e_active" class="mt-0.5 rounded text-rose-500 focus:ring-rose-400">
                    <span class="text-sm text-slate-700 dark:text-slate-200"><b>Ligado</b><small class="block text-xs text-slate-500">Desligado, some da montagem (quando acabar, por exemplo).</small></span>
                </label>
                <div class="flex gap-2 pt-1">
                    <button type="button" wire:click="$set('extraForm', false)" class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">Cancelar</button>
                    <button type="submit" class="flex-1 {{ $primary }} justify-center !py-2.5" data-testid="kit-extra-save"><span wire:loading.remove wire:target="saveExtra">Salvar</span><span wire:loading wire:target="saveExtra">Salvando…</span></button>
                </div>
            </form>
        </div>
    @endif
    </x-portal-settings-nav>
</div>
