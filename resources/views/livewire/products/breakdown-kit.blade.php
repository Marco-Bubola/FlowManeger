<div class="product-form-page breakdown-page w-full px-4 sm:px-6 lg:px-8 pt-4 pb-16">
    @php
        $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $num = function ($v) {
            if (is_numeric($v)) return (float) $v;
            $v = str_replace([' ', 'R$'], '', (string) $v);
            if (str_contains($v, ',')) $v = str_replace(['.', ','], ['', '.'], $v);
            return is_numeric($v) ? (float) $v : 0.0;
        };
        $kitStock = (int) $kit->stock_quantity;
        $kitCostTotal = (float) $kit->price * max(1, $units);
        $itemsCost = 0.0;
        foreach ($items as $it) { $itemsCost += $num($it['price']) * max(1, (int) $it['qty']) * max(1, $units); }
        $diff = round($itemsCost - $kitCostTotal, 2);
    @endphp

    <x-product-page-header title="Desmembrar kit" :product="$kit" icon="bi-scissors"
        subtitle="Separe o kit nos produtos que vêm dentro dele. Cada item fica com o seu código e o seu estoque.">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300 max-w-[16rem] truncate"><i class="bi bi-box-seam"></i>{{ ucwords(mb_strtolower($kit->name)) }}</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700/60 dark:text-slate-200"><i class="bi bi-upc"></i>{{ $kit->product_code }}</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $kitStock > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' }}"><i class="bi bi-stack"></i>{{ $kitStock }} em estoque</span>
        </x-slot:meta>
        @if(! $result)
            <x-slot:actions>
                <a href="{{ route('products.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-white/85 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70 px-3.5 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-800 shadow-sm transition">
                    <i class="bi bi-x-lg"></i>Cancelar
                </a>
                <button data-mobile-save="Desmembrar" type="button" wire:click="confirm" wire:loading.attr="disabled" wire:target="confirm" @disabled($maxUnits < 1)
                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="confirm"><i class="bi bi-scissors mr-1"></i>Desmembrar</span>
                    <span wire:loading wire:target="confirm"><i class="bi bi-arrow-repeat animate-spin mr-1"></i>Desmembrando...</span>
                </button>
            </x-slot:actions>
        @endif
    </x-product-page-header>

    @include('livewire.products.partials.ep-styles')
    <style>
        .bk-item { border-radius: 1.1rem; border: 1px solid rgba(148,163,184,.3); background: rgba(248,250,252,.7); }
        .dark .bk-item { background: rgba(30,41,59,.45); border-color: rgba(71,85,105,.55); }
        .bk-item.has-err { border-color: rgba(244,63,94,.6); box-shadow: 0 0 0 3px rgba(244,63,94,.1); }
        .bk-item-h { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; padding: .7rem .8rem; }
        .bk-n { width: 1.8rem; height: 1.8rem; flex-shrink: 0; border-radius: .6rem; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 900; color: #fff; background: linear-gradient(135deg, #6366f1, #a855f7); }
        .bk-seg { display: inline-flex; padding: .2rem; border-radius: .8rem; background: rgba(148,163,184,.15); }
        .bk-seg button { padding: .35rem .65rem; border-radius: .6rem; font-size: .76rem; font-weight: 700; color: #64748b; white-space: nowrap; }
        .dark .bk-seg button { color: #94a3b8; }
        .bk-seg button.on { color: #fff; background: linear-gradient(135deg, #6366f1, #a855f7); box-shadow: 0 3px 10px rgba(99,102,241,.3); }
        .bk-step { display: inline-flex; align-items: center; border-radius: .8rem; border: 1px solid rgba(148,163,184,.45); background: #fff; overflow: hidden; }
        .dark .bk-step { background: rgba(15,23,42,.8); border-color: rgba(71,85,105,.7); }
        .bk-step button { width: 2.1rem; height: 2.1rem; display: flex; align-items: center; justify-content: center; color: #4f46e5; font-weight: 900; }
        .dark .bk-step button { color: #a5b4fc; }
        .bk-step button:disabled { opacity: .35; }
        .bk-step input { width: 3rem; text-align: center; font-weight: 800; border: 0; background: transparent; padding: 0; color: #0f172a; }
        .dark .bk-step input { color: #f1f5f9; }
        .bk-step input:focus { outline: none; box-shadow: none; }
        .bk-step.big button { width: 2.8rem; height: 2.8rem; font-size: 1.1rem; }
        .bk-step.big input { width: 4rem; font-size: 1.25rem; }
        .bk-body { padding: 0 .8rem .85rem; display: grid; gap: .75rem; }
        .bk-pick { display: flex; align-items: center; gap: .65rem; padding: .6rem; border-radius: .9rem; background: #fff; border: 1px solid rgba(99,102,241,.3); }
        .dark .bk-pick { background: rgba(15,23,42,.7); }
        .bk-res { border-radius: .9rem; border: 1px solid rgba(148,163,184,.35); background: #fff; overflow: hidden; }
        .dark .bk-res { background: rgba(15,23,42,.9); border-color: rgba(71,85,105,.6); }
        .bk-res button { width: 100%; display: flex; align-items: center; gap: .6rem; padding: .5rem .65rem; text-align: left; }
        .bk-res button:hover { background: rgba(99,102,241,.08); }
        .bk-res button + button { border-top: 1px solid rgba(148,163,184,.2); }
        .bk-thumb { width: 2.6rem; height: 2.6rem; flex-shrink: 0; border-radius: .7rem; object-fit: cover; background: #f1f5f9; }
        .bk-sum-row { display: flex; justify-content: space-between; gap: .75rem; font-size: .85rem; padding: .3rem 0; color: #475569; }
        .dark .bk-sum-row { color: #cbd5e1; }
        .bk-sum-row b { color: #0f172a; }
        .dark .bk-sum-row b { color: #f1f5f9; }
        .bk-photo { width: 4.5rem; height: 4.5rem; border-radius: .9rem; object-fit: cover; border: 1px solid rgba(148,163,184,.35); background: #f8fafc; }
        .bk-check { display: flex; align-items: center; gap: .5rem; font-size: .82rem; font-weight: 600; color: #475569; }
        .dark .bk-check { color: #cbd5e1; }
    </style>

    @if($result)
        {{-- ===== Pronto ===== --}}
        <section class="ep-card max-w-4xl mx-auto" data-testid="breakdown-done">
            <div class="ep-card-b text-center pt-8">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/30">
                    <i class="bi bi-check2-all text-3xl text-white"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $result['units'] }} {{ $result['units'] === 1 ? 'kit desmembrado' : 'kits desmembrados' }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">O kit ficou com <b>{{ $result['kitStock'] }}</b> em estoque e os itens abaixo ganharam estoque.</p>
            </div>
            <div class="px-4 pb-4 grid gap-2">
                @foreach($result['items'] as $r)
                    <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/70 p-3" wire:key="done-{{ $r['id'] }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ ucwords(mb_strtolower($r['name'])) }}</span>
                                @if($r['new'])<span class="rounded-full px-2 py-0.5 text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">NOVO</span>@endif
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-upc"></i> {{ $r['code'] }} · estoque {{ $r['before'] }} <i class="bi bi-arrow-right"></i> <b class="text-emerald-600 dark:text-emerald-400">{{ $r['after'] }}</b> (+{{ $r['added'] }})</div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="$dispatch('open-product-share', { productId: {{ $r['id'] }} })" class="ep-btn btn-wa-product !py-2 !px-3" title="Enviar no WhatsApp"><i class="bi bi-whatsapp"></i><span class="hidden sm:inline">WhatsApp</span></button>
                            <a href="{{ route('products.edit', $r['id']) }}" class="ep-btn ep-btn-soft !py-2 !px-3" title="Editar"><i class="bi bi-pencil-square"></i><span class="hidden sm:inline">Editar</span></a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-col sm:flex-row gap-2 justify-center px-4 pb-6">
                <a href="{{ route('products.index') }}" class="ep-btn ep-btn-ghost"><i class="bi bi-grid"></i>Voltar aos produtos</a>
                @if($result['kitStock'] > 0)
                    <button type="button" wire:click="again" class="ep-btn ep-btn-primary"><i class="bi bi-scissors"></i>Desmembrar mais deste kit</button>
                @endif
            </div>
        </section>
    @elseif($kit->isKit())
        <section class="ep-card max-w-3xl mx-auto">
            <div class="ep-card-b text-center py-10">
                <i class="bi bi-boxes text-4xl text-indigo-400"></i>
                <h3 class="mt-3 font-semibold text-slate-800 dark:text-slate-100">Este é um kit montado no app</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ele não tem estoque próprio: cada venda já tira dos itens. Para mudar os itens, edite o kit.</p>
                <a href="{{ route('products.kit.edit', $kit->id) }}" class="ep-btn ep-btn-primary mt-4"><i class="bi bi-pencil-square"></i>Editar kit</a>
            </div>
        </section>
    @else
        <div class="ep-grid">
            <div class="ep-col">
                {{-- Kit --}}
                <section class="ep-card">
                    <div class="ep-card-h">
                        <span class="ep-ico"><i class="bi bi-box2-heart"></i></span>
                        <div><h3>Quantos kits abrir</h3><p>Sai do estoque do kit e entra no estoque de cada item</p></div>
                    </div>
                    <div class="ep-card-b">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <x-product-thumb :product="$kit" size="w-16 h-16" rounded="rounded-2xl" />
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-800 dark:text-slate-100 leading-tight line-clamp-2">{{ ucwords(mb_strtolower($kit->name)) }}</div>
                                    <div class="mt-1 flex flex-wrap gap-1.5 text-[11px] font-semibold">
                                        <span class="ep-pill !py-0.5 !text-[11px]"><i class="bi bi-cash"></i>Custo {{ $money($kit->price) }}</span>
                                        <span class="ep-pill !py-0.5 !text-[11px]"><i class="bi bi-tag"></i>Venda {{ $money($kit->price_sale) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-start sm:items-end gap-1.5">
                                @if($maxUnits < 1)
                                    <span class="ep-pill bad"><i class="bi bi-exclamation-circle"></i>Kit sem estoque</span>
                                @else
                                    <div class="flex items-center gap-2">
                                        <div class="bk-step big">
                                            <button type="button" wire:click="stepUnits(-1)" @disabled($units <= 1) aria-label="Menos"><i class="bi bi-dash-lg"></i></button>
                                            <input type="number" min="1" max="{{ $maxUnits }}" wire:model.live.debounce.400ms="units" data-testid="bk-units">
                                            <button type="button" wire:click="stepUnits(1)" @disabled($units >= $maxUnits) aria-label="Mais"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                        @if($maxUnits > 1)
                                            <button type="button" wire:click="$set('units', {{ $maxUnits }})" class="ep-btn ep-btn-soft !py-2 !px-3 !text-xs">Todos ({{ $maxUnits }})</button>
                                        @endif
                                    </div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">de {{ $maxUnits }} em estoque</span>
                                @endif
                                @error('units')<p class="ep-err">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Itens --}}
                <section class="ep-card">
                    <div class="ep-card-h">
                        <span class="ep-ico pink"><i class="bi bi-diagram-2"></i></span>
                        <div class="flex-1"><h3>O que vem dentro do kit</h3><p>Cada produto separado, com o seu código e quantos vêm em 1 kit</p></div>
                    </div>
                    <div class="ep-card-b grid gap-3">
                        @if($lastRecipeAt)
                            <div class="flex items-start gap-2 rounded-xl px-3 py-2 text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <i class="bi bi-clock-history mt-0.5"></i><span>Itens preenchidos com o que você usou em {{ $lastRecipeAt }}. Confira e ajuste se o kit mudou.</span>
                            </div>
                        @endif

                        @foreach($items as $i => $it)
                            @php
                                $k = $it['key'];
                                $hasErr = collect($errors->keys())->contains(fn ($e) => str_starts_with($e, "items.$i.") || $e === "photos.$k");
                                $cost = $num($it['price']);
                                $sale = $num($it['price_sale']);
                                $profit = $sale - $cost;
                                $margin = $sale > 0 ? round($profit / $sale * 100) : 0;
                                $adds = max(1, (int) $it['qty']) * max(1, $units);
                            @endphp
                            <div class="bk-item {{ $hasErr ? 'has-err' : '' }}" wire:key="bk-{{ $k }}" data-testid="bk-item">
                                <div class="bk-item-h">
                                    <span class="bk-n">{{ $i + 1 }}</span>
                                    <div class="bk-seg">
                                        <button type="button" wire:click="setMode('{{ $k }}', 'new')" class="{{ $it['mode'] === 'new' ? 'on' : '' }}"><i class="bi bi-plus-circle"></i> Produto novo</button>
                                        <button type="button" wire:click="setMode('{{ $k }}', 'existing')" class="{{ $it['mode'] === 'existing' ? 'on' : '' }}"><i class="bi bi-search"></i> Já tenho</button>
                                    </div>
                                    <div class="ml-auto flex items-center gap-1.5">
                                        <span class="text-[11px] font-bold uppercase tracking-wide text-slate-500 hidden sm:inline">Por kit</span>
                                        <div class="bk-step">
                                            <button type="button" wire:click="$set('items.{{ $i }}.qty', {{ max(1, (int) $it['qty'] - 1) }})" @disabled((int) $it['qty'] <= 1) aria-label="Menos"><i class="bi bi-dash"></i></button>
                                            <input type="number" min="1" max="999" wire:model.live.debounce.400ms="items.{{ $i }}.qty" aria-label="Quantidade por kit">
                                            <button type="button" wire:click="$set('items.{{ $i }}.qty', {{ (int) $it['qty'] + 1 }})" aria-label="Mais"><i class="bi bi-plus"></i></button>
                                        </div>
                                        @if($it['mode'] === 'new')
                                            <button type="button" wire:click="toggleOpen('{{ $k }}')" class="ep-x !text-slate-500 !bg-slate-500/10" title="{{ $it['open'] ? 'Recolher' : 'Abrir' }}"><i class="bi {{ $it['open'] ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i></button>
                                        @endif
                                        <button type="button" wire:click="removeItem('{{ $k }}')" class="ep-x" title="Tirar item"><i class="bi bi-trash3"></i></button>
                                    </div>
                                    @error("items.$i.qty")<p class="ep-err w-full">{{ $message }}</p>@enderror
                                </div>

                                <div class="bk-body">
                                    @if($it['mode'] === 'existing')
                                        @if($it['product_id'])
                                            <div class="bk-pick">
                                                @if(!empty($it['image_url']))<img src="{{ $it['image_url'] }}" class="bk-thumb" alt="" onerror="this.style.visibility='hidden'">@endif
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-semibold text-sm text-slate-800 dark:text-slate-100 truncate">{{ ucwords(mb_strtolower($it['label'])) }}</div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-upc"></i> {{ $it['code_label'] }} · custo {{ $money($num($it['price'])) }} · estoque {{ $it['stock'] }} <i class="bi bi-arrow-right"></i> <b class="text-emerald-600 dark:text-emerald-400">{{ $it['stock'] + $adds }}</b></div>
                                                </div>
                                                <button type="button" wire:click="clearPick('{{ $k }}')" class="ep-btn ep-btn-soft !py-1.5 !px-2.5 !text-xs">Trocar</button>
                                            </div>
                                        @else
                                            <div>
                                                <div class="relative">
                                                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                    <input type="text" wire:model.live.debounce.300ms="items.{{ $i }}.search" class="ep-input !pl-9 {{ $errors->has("items.$i.search") ? 'is-err' : '' }}" placeholder="Buscar pelo nome, código ou código de barras..." data-testid="bk-search">
                                                </div>
                                                @error("items.$i.search")<p class="ep-err">{{ $message }}</p>@enderror
                                                @if(isset($search[$k]))
                                                    <div class="bk-res mt-2">
                                                        @forelse($search[$k] as $p)
                                                            <button type="button" wire:click="pickProduct('{{ $k }}', {{ $p->id }})" wire:key="r-{{ $k }}-{{ $p->id }}">
                                                                <x-product-thumb :product="$p" size="w-10 h-10" />
                                                                <span class="min-w-0 flex-1">
                                                                    <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ ucwords(mb_strtolower($p->name)) }}{{ $p->parent_id && $p->variation_value ? ' · ' . $p->variation_value : '' }}</span>
                                                                    <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $p->product_code }} · {{ (int) $p->stock_quantity }} em estoque · {{ $money($p->price_sale) }}</span>
                                                                </span>
                                                                <i class="bi bi-plus-circle text-indigo-500"></i>
                                                            </button>
                                                        @empty
                                                            <div class="px-3 py-3 text-sm text-slate-500 dark:text-slate-400">Nenhum produto encontrado. <button type="button" wire:click="setMode('{{ $k }}', 'new')" class="font-semibold text-indigo-600 dark:text-indigo-300 !w-auto !inline !p-0">Cadastrar como produto novo</button></div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @elseif(! $it['open'])
                                        <button type="button" wire:click="toggleOpen('{{ $k }}')" class="text-left text-sm text-slate-600 dark:text-slate-300">
                                            <b>{{ $it['name'] ?: 'Sem nome' }}</b> · {{ $it['product_code'] ?: 'sem código' }} · {{ $sale > 0 ? $money($sale) : 'sem preço' }} <span class="text-indigo-600 dark:text-indigo-300 font-semibold">· editar</span>
                                        </button>
                                    @else
                                        <div class="ep-row c3">
                                            <div>
                                                <label class="ep-label"><span>Nome <span class="req">*</span></span></label>
                                                <input type="text" wire:model.blur="items.{{ $i }}.name" class="ep-input {{ $errors->has("items.$i.name") ? 'is-err' : '' }}" placeholder="Ex: Body splash Instance 200ml" data-testid="bk-name">
                                                @error("items.$i.name")<p class="ep-err">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label class="ep-label"><span>Código <span class="req">*</span></span></label>
                                                <input type="text" wire:model.blur="items.{{ $i }}.product_code" class="ep-input {{ $errors->has("items.$i.product_code") ? 'is-err' : '' }}" placeholder="Código do item" data-testid="bk-code">
                                                @error("items.$i.product_code")<p class="ep-err">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label class="ep-label"><span>Categoria <span class="req">*</span></span></label>
                                                <select wire:model="items.{{ $i }}.category_id" class="ep-input {{ $errors->has("items.$i.category_id") ? 'is-err' : '' }}">
                                                    <option value="">Escolha...</option>
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat->id_category }}">{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error("items.$i.category_id")<p class="ep-err">{{ $message }}</p>@enderror
                                            </div>
                                        </div>

                                        <div class="ep-row c4">
                                            <div>
                                                <label class="ep-label"><span>Custo (cada) <span class="req">*</span></span></label>
                                                <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="items.{{ $i }}.price" class="ep-input {{ $errors->has("items.$i.price") ? 'is-err' : '' }}" placeholder="0,00" data-testid="bk-cost">
                                                @error("items.$i.price")<p class="ep-err">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label class="ep-label"><span>Preço de venda <span class="req">*</span></span></label>
                                                <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="items.{{ $i }}.price_sale" class="ep-input {{ $errors->has("items.$i.price_sale") ? 'is-err' : '' }}" placeholder="0,00" data-testid="bk-sale">
                                                @error("items.$i.price_sale")<p class="ep-err">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label class="ep-label"><span>Preço de tabela</span></label>
                                                <input type="text" inputmode="decimal" wire:model.blur="items.{{ $i }}.price_original" class="ep-input" placeholder="Riscado na promoção">
                                            </div>
                                            <div>
                                                <label class="ep-label"><span>Código de barras</span></label>
                                                <input type="text" inputmode="numeric" wire:model.blur="items.{{ $i }}.barcode" class="ep-input {{ $errors->has("items.$i.barcode") ? 'is-err' : '' }}" placeholder="EAN (opcional)">
                                                @error("items.$i.barcode")<p class="ep-err">{{ $message }}</p>@enderror
                                            </div>
                                        </div>

                                        <div class="ep-row c4">
                                            <div>
                                                <label class="ep-label"><span>Marca</span></label>
                                                <input type="text" wire:model.blur="items.{{ $i }}.brand" class="ep-input" placeholder="Ex: Eudora">
                                            </div>
                                            <div>
                                                <label class="ep-label"><span>Modelo</span></label>
                                                <input type="text" wire:model.blur="items.{{ $i }}.model" class="ep-input" placeholder="Opcional">
                                            </div>
                                            <div>
                                                <label class="ep-label"><span>Condição</span></label>
                                                <select wire:model="items.{{ $i }}.condition" class="ep-input">
                                                    <option value="new">Novo</option>
                                                    <option value="used">Usado</option>
                                                    <option value="refurbished">Recondicionado</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="ep-label"><span>Garantia (meses)</span></label>
                                                <input type="number" min="0" max="120" wire:model.blur="items.{{ $i }}.warranty_months" class="ep-input">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="ep-label"><span>Descrição</span></label>
                                            <textarea rows="2" wire:model.blur="items.{{ $i }}.description" class="ep-input" placeholder="Opcional: aparece no catálogo e no Mercado Livre"></textarea>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3">
                                            @php
                                                $preview = null;
                                                if (isset($photos[$k])) { try { $preview = $photos[$k]->temporaryUrl(); } catch (\Throwable $e) { $preview = null; } }
                                            @endphp
                                            @if($preview)
                                                <img src="{{ $preview }}" class="bk-photo" alt="">
                                            @elseif($it['use_kit_photo'] && $kit->image && $kit->image !== 'product-placeholder.png')
                                                <img src="{{ asset('storage/products/' . $kit->image) }}" class="bk-photo" alt="">
                                            @else
                                                <span class="bk-photo flex items-center justify-center text-slate-400"><i class="bi bi-image text-xl"></i></span>
                                            @endif
                                            <div class="grid gap-1.5">
                                                <label class="ep-btn ep-btn-soft !py-2 cursor-pointer">
                                                    <i class="bi bi-camera"></i><span wire:loading.remove wire:target="photos.{{ $k }}">{{ $preview ? 'Trocar foto' : 'Foto do item' }}</span><span wire:loading wire:target="photos.{{ $k }}">Enviando...</span>
                                                    <input type="file" accept="image/*" wire:model="photos.{{ $k }}" class="hidden">
                                                </label>
                                                @if($kit->image && $kit->image !== 'product-placeholder.png' && ! $preview)
                                                    <label class="bk-check"><input type="checkbox" wire:model.live="items.{{ $i }}.use_kit_photo" class="rounded border-slate-300 text-indigo-600"> Usar a foto do kit</label>
                                                @endif
                                            </div>
                                            @error("photos.$k")<p class="ep-err w-full">{{ $message }}</p>@enderror
                                        </div>

                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="ep-pill"><i class="bi bi-stack"></i>Entra com {{ $adds }} em estoque</span>
                                            @if($sale > 0)
                                                <span class="ep-pill {{ $profit > 0 ? 'good' : 'bad' }}"><i class="bi bi-graph-up-arrow"></i>Lucro {{ $money($profit) }} ({{ $margin }}%)</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button type="button" wire:click="addItem('new')" class="ep-btn ep-btn-soft !py-3" data-testid="bk-add-new"><i class="bi bi-plus-circle"></i>Adicionar produto novo</button>
                            <button type="button" wire:click="addItem('existing')" class="ep-btn ep-btn-ghost !py-3"><i class="bi bi-search"></i>Adicionar produto que já tenho</button>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Resumo --}}
            <aside class="ep-side ep-col">
                <section class="ep-card">
                    <div class="ep-card-h">
                        <span class="ep-ico green"><i class="bi bi-clipboard-check"></i></span>
                        <div><h3>Resumo</h3><p>Confira antes de desmembrar</p></div>
                    </div>
                    <div class="ep-card-b">
                        <div class="bk-sum-row"><span>Kits abertos</span><b>{{ $units }}</b></div>
                        <div class="bk-sum-row"><span>Estoque do kit</span><b>{{ $kitStock }} <i class="bi bi-arrow-right text-xs"></i> {{ max(0, $kitStock - $units) }}</b></div>
                        <div class="my-2 border-t border-slate-200/70 dark:border-slate-700/60"></div>
                        @foreach($items as $it)
                            @php $nm = $it['mode'] === 'existing' ? ($it['label'] ?: 'Escolha o produto') : ($it['name'] ?: 'Produto novo ' . ($loop->iteration)); @endphp
                            <div class="bk-sum-row"><span class="truncate">{{ ucwords(mb_strtolower($nm)) }}</span><b class="text-emerald-600 dark:text-emerald-400 shrink-0">+{{ max(1, (int) $it['qty']) * max(1, $units) }}</b></div>
                        @endforeach
                        <div class="my-2 border-t border-slate-200/70 dark:border-slate-700/60"></div>
                        <div class="bk-sum-row"><span>Custo dos kits</span><b>{{ $money($kitCostTotal) }}</b></div>
                        <div class="bk-sum-row"><span>Custo somado dos itens</span><b>{{ $money($itemsCost) }}</b></div>
                        @if(abs($diff) >= 0.01)
                            <div class="mt-1 rounded-xl px-3 py-2 text-xs font-semibold {{ $diff > 0 ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' }}">
                                <i class="bi bi-info-circle"></i> Os itens somam {{ $money(abs($diff)) }} {{ $diff > 0 ? 'a mais' : 'a menos' }} que o kit. Use "Dividir o custo do kit" para bater.
                            </div>
                        @else
                            <div class="mt-1 rounded-xl px-3 py-2 text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-check2-circle"></i> O custo dos itens bate com o do kit.</div>
                        @endif
                        <button type="button" wire:click="splitCost" class="ep-btn ep-btn-soft w-full mt-3" data-testid="bk-split-cost"><i class="bi bi-pie-chart"></i>Dividir o custo do kit</button>
                        <p class="ep-hint mt-2"><i class="bi bi-lightbulb"></i><span>Divide o custo do kit entre os itens novos, proporcional ao preço de venda de cada um.</span></p>

                        @if($maxUnits > 0 && $units >= $maxUnits)
                            <label class="bk-check mt-3"><input type="checkbox" wire:model="archiveKit" class="rounded border-slate-300 text-indigo-600"> Inativar o kit quando zerar o estoque</label>
                        @endif

                        <button type="button" wire:click="confirm" wire:loading.attr="disabled" wire:target="confirm" @disabled($maxUnits < 1)
                            class="ep-btn ep-btn-primary w-full mt-4 !py-3" data-testid="bk-confirm">
                            <span wire:loading.remove wire:target="confirm"><i class="bi bi-scissors"></i> Desmembrar {{ $units }} {{ $units === 1 ? 'kit' : 'kits' }}</span>
                            <span wire:loading wire:target="confirm"><i class="bi bi-arrow-repeat animate-spin"></i> Desmembrando...</span>
                        </button>
                        @if($errors->any())
                            <p class="ep-err mt-2 text-center"><i class="bi bi-exclamation-circle"></i> Confira os campos marcados em vermelho.</p>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    @endif

    <livewire:products.product-share />
</div>
