<div class="qt-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header title="Estofados e cores" subtitle="Catálogo de tecidos e cores que o cliente escolhe na página do produto. O robô usa o mesmo catálogo." icon="bi-palette"
                       section="orcamentos" tabs-section="orcamentos" tabs-active="escolhas">
            <x-slot:actions>
                <button type="button" wire:click="openNewList('estofado')" class="crm-btn crm-btn-primary" title="Nova lista" data-testid="new-list">
                    <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Nova lista</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @if($showNewList)
            <section class="crs-card mb-4" x-data x-init="$nextTick(() => $el.querySelector('input[type=text]')?.focus())">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-plus-lg"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">Nova lista</h2>
                        <p class="crs-sub">Cada lista vira uma escolha na página do produto</p>
                    </div>
                    <button type="button" wire:click="$set('showNewList', false)" class="crm-icon-btn" title="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>
                <form wire:submit="createList" class="qt-sec">
                    <div class="ch-kinds">
                        @foreach($kinds as $k => [$label, $icon, $hint])
                            <label class="ch-kind {{ $listKind === $k ? 'on' : '' }}">
                                <input type="radio" wire:model.live="listKind" value="{{ $k }}" class="sr-only"
                                       x-on:change="$wire.set('listName', @js($k === 'ferragem' ? 'Cores de ferragem' : ($k === 'estofado' ? 'Estofados' : '')))">
                                <i class="bi {{ $icon }}"></i><span><b>{{ $label }}</b><small>{{ $hint }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    <label class="crm-label">Nome da lista
                        <input type="text" wire:model="listName" class="crm-input" placeholder="Ex.: Estofados, Cores de ferragem, Madeira" maxlength="80" data-testid="list-name">
                        @error('listName') <span class="crm-err">{{ $message }}</span> @enderror
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="crm-btn crm-btn-primary flex-1 sm:flex-none justify-center" data-testid="create-list"><i class="bi bi-check2"></i>Criar lista</button>
                        <button type="button" wire:click="$set('showNewList', false)" class="crm-btn">Cancelar</button>
                    </div>
                </form>
            </section>
        @endif

        @if($catalogs->isEmpty() && ! $showNewList)
            <section class="crs-card">
                <div class="ch-empty">
                    <span class="ch-empty-ic"><i class="bi bi-palette"></i></span>
                    <h2>Monte seu catálogo de estofados e cores</h2>
                    <p>Cadastre os tecidos (corino, corvin, suede...) com as cores de cada um, e as cores da ferragem. Depois, no cadastro do produto, ligue as listas que ele usa: o cliente escolhe na página do produto.</p>
                    <div class="ch-empty-btns">
                        <button type="button" wire:click="startWithCorino" class="crm-btn crm-btn-primary justify-center" data-testid="start-corino"><i class="bi bi-palette"></i>Começar com os corinos do mostruário (18 cores)</button>
                        <button type="button" wire:click="openNewList('estofado')" class="crm-btn justify-center" data-testid="start-estofado"><i class="bi bi-plus-lg"></i>Criar Estofados vazio</button>
                        <button type="button" wire:click="openNewList('ferragem')" class="crm-btn justify-center" data-testid="start-ferragem"><i class="bi bi-paint-bucket"></i>Criar Cores de ferragem</button>
                    </div>
                </div>
            </section>
        @endif

        @if($catalogs->isNotEmpty())
            <div class="qt-tabs mb-4">
                @foreach($catalogs as $c)
                    <button type="button" wire:click="selectCatalog({{ $c->id }})" class="qt-tab {{ $current?->id === $c->id ? 'is-on' : '' }}" wire:key="tab-{{ $c->id }}">
                        <i class="bi {{ $kinds[$c->kind][1] ?? 'bi-ui-checks-grid' }}"></i>{{ $c->name }} <b>{{ $c->items->count() }}</b>
                    </button>
                @endforeach
                <button type="button" wire:click="openNewList('{{ $catalogs->contains('kind', 'ferragem') ? 'outro' : 'ferragem' }}')" class="qt-tab"><i class="bi bi-plus-lg"></i>Nova lista</button>
            </div>
        @endif

        @if($current)
            <section class="crs-card mb-4" wire:key="cat-{{ $current->id }}">
                <div class="crs-head flex-wrap">
                    <span class="crs-head-icon bg-gradient-to-br {{ $current->kind === 'ferragem' ? 'from-slate-600 to-slate-900' : ($current->kind === 'estofado' ? 'from-amber-500 to-rose-500' : 'from-sky-500 to-indigo-600') }}">
                        <i class="bi {{ $kinds[$current->kind][1] ?? 'bi-ui-checks-grid' }}"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <input type="text" value="{{ $current->name }}" wire:change="renameList($event.target.value)" class="qt-cell ch-list-name" maxlength="80" aria-label="Nome da lista">
                        <p class="crs-sub px-2">
                            {{ $current->items->count() }} {{ $current->items->count() === 1 ? 'item' : 'itens' }} ·
                            {{ $usedBy ? "usada em $usedBy " . ($usedBy === 1 ? 'produto' : 'produtos') : 'nenhum produto usa ainda (ligue no cadastro do produto)' }}
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" wire:click="openForm" class="crm-btn crm-btn-primary" data-testid="add-item"><i class="bi bi-plus-lg"></i><span>{{ $current->kind === 'estofado' ? 'Tecido ou cor' : 'Cor' }}</span></button>
                        <button type="button" wire:click="deleteList" wire:confirm="Apagar a lista {{ $current->name }} com todos os itens? Os produtos que usam ela deixam de mostrar essa escolha." class="crm-icon-btn" title="Apagar lista"><i class="bi bi-trash3"></i></button>
                    </div>
                </div>

                @if($showForm)
                    <form wire:submit="add" class="qt-sec ch-form" x-data x-init="$nextTick(() => $el.querySelector('[data-first]')?.focus())">
                        <div class="qt-2">
                            @if($current->kind !== 'ferragem')
                                <label class="crm-label">{{ $current->kind === 'estofado' ? 'Tecido' : 'Grupo' }} <span class="font-normal text-slate-400">(opcional)</span>
                                    <input type="text" wire:model="newMaterial" list="ch-mats" class="crm-input" placeholder="{{ $current->kind === 'estofado' ? 'Ex.: Corino, Corvin, Suede, Linho' : 'Ex.: Madeira' }}" maxlength="80" data-first data-testid="new-material">
                                    <datalist id="ch-mats">@foreach($materials as $m)<option value="{{ $m }}">@endforeach</datalist>
                                </label>
                            @endif
                            <label class="crm-label">{{ $current->kind === 'estofado' ? 'Cores deste tecido' : 'Cores' }}
                                <input type="text" wire:model="newNames" class="crm-input" placeholder="Ex.: Preto 2792, Marrom 4262, Caramelo" maxlength="600" @if($current->kind === 'ferragem') data-first @endif data-testid="new-names">
                                <span class="text-[11px] font-normal text-slate-500">Separe por vírgula para colocar várias de uma vez. Número no fim vira o código.</span>
                                @error('newNames') <span class="crm-err">{{ $message }}</span> @enderror
                            </label>
                        </div>
                        <div class="qt-3">
                            <label class="crm-label">Valor a mais no produto (R$)
                                <input type="text" inputmode="decimal" wire:model="newExtra" class="crm-input" placeholder="0,00 = mesmo preço" data-testid="new-extra">
                            </label>
                            <label class="crm-label">Amostra da cor <span class="font-normal text-slate-400">(se for uma só)</span>
                                <span class="flex items-center gap-2">
                                    <input type="color" wire:model="newHex" class="ch-color" aria-label="Amostra">
                                    <span class="text-[11px] font-normal text-slate-500">Sem escolher, o app acerta pela cor escrita</span>
                                </span>
                                @error('newHex') <span class="crm-err">{{ $message }}</span> @enderror
                            </label>
                            <label class="crm-label col-span-2 md:col-span-1">Foto do tecido <span class="font-normal text-slate-400">(opcional)</span>
                                <input type="file" wire:model="newPhoto" accept="image/*" class="crm-input !py-1.5 text-xs">
                                @error('newPhoto') <span class="crm-err">{{ $message }}</span> @enderror
                            </label>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="crm-btn crm-btn-primary flex-1 sm:flex-none justify-center" wire:loading.attr="disabled" data-testid="save-item"><i class="bi bi-check2"></i>Colocar na lista</button>
                            <button type="button" wire:click="$set('showForm', false)" class="crm-btn">Cancelar</button>
                        </div>
                    </form>
                @endif

                @if($current->kind === 'estofado' && ! $current->items->contains('material', 'Corino') && ! $showForm)
                    <div class="qt-sec"><button type="button" wire:click="startWithCorino" class="crm-btn justify-center"><i class="bi bi-palette"></i>Colocar os corinos do mostruário (18 cores)</button></div>
                @endif
                @if($current->items->isEmpty() && ! $showForm)
                    <div class="crs-empty"><i class="bi bi-palette"></i>Lista vazia. Toque em “{{ $current->kind === 'estofado' ? 'Tecido ou cor' : 'Cor' }}” para colocar os itens.</div>
                @endif

                <input type="file" accept="image/*" class="hidden" x-ref="photo" wire:model="photo" id="ch-photo-input">
                @foreach($groups as $mat => $items)
                    <div class="ch-group" wire:key="grp-{{ md5($mat) }}">
                        <div class="ch-group-h">
                            @if($current->kind !== 'ferragem' || $mat !== '')
                                <input type="text" value="{{ $mat }}" wire:change="renameMaterial(@js($mat), $event.target.value)" class="qt-cell ch-mat" placeholder="Sem tecido" maxlength="80" aria-label="Tecido">
                            @else
                                <span class="ch-mat-label">Cores</span>
                            @endif
                            <label class="ch-mat-extra" title="Vale para todas as cores deste {{ $current->kind === 'estofado' ? 'tecido' : 'grupo' }}">
                                <span>+ R$</span>
                                <input type="text" inputmode="decimal" value="{{ ($x = (float) $items->max('extra_price')) > 0 && $items->pluck('extra_price')->unique()->count() === 1 ? number_format($x, 2, ',', '.') : '' }}"
                                       wire:change="updateMaterialExtra(@js($mat), $event.target.value)" placeholder="{{ $items->pluck('extra_price')->unique()->count() > 1 ? 'vários' : '0,00' }}" class="qt-cell is-money" aria-label="Valor a mais">
                            </label>
                            <button type="button" wire:click="openForm(@js($mat))" class="crs-link"><i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Cor</span></button>
                        </div>
                        <div class="ch-grid">
                            @foreach($items as $it)
                                <div class="ch-item {{ $it->active ? '' : 'off' }}" wire:key="it-{{ $it->id }}" data-testid="choice-item">
                                    <div class="ch-sw-wrap">
                                        @if($it->image)
                                            <button type="button" class="ch-sw" style="background-image:url('{{ $it->imageUrl() }}')" title="Trocar foto"
                                                    wire:click="pickPhoto({{ $it->id }})" x-on:click="$nextTick(() => document.getElementById('ch-photo-input').click())"></button>
                                        @else
                                            <label class="ch-sw" style="background: {{ $it->hex ?: 'repeating-linear-gradient(45deg,#e2e8f0 0 6px,#f8fafc 6px 12px)' }}" title="Trocar a cor da amostra">
                                                <input type="color" value="{{ $it->hex ?: '#cccccc' }}" wire:change="updateField({{ $it->id }}, 'hex', $event.target.value)" class="sr-only">
                                            </label>
                                        @endif
                                    </div>
                                    <div class="ch-item-b">
                                        <input type="text" value="{{ $it->name }}" wire:change="updateField({{ $it->id }}, 'name', $event.target.value)" class="qt-cell font-semibold" maxlength="80" aria-label="Cor">
                                        <span class="ch-meta">
                                        <label class="ch-x"><span>cód.</span>
                                            <input type="text" value="{{ $it->code }}" placeholder="—" wire:change="updateField({{ $it->id }}, 'code', $event.target.value)" class="qt-cell ch-code" maxlength="30" aria-label="Código">
                                        </label>
                                        <label class="ch-x"><span>+ R$</span>
                                            <input type="text" inputmode="decimal" value="{{ $it->extra_price > 0 ? number_format($it->extra_price, 2, ',', '.') : '' }}" placeholder="0,00"
                                                   wire:change="updateExtra({{ $it->id }}, $event.target.value)" class="qt-cell is-money" aria-label="Valor a mais">
                                        </label>
                                        </span>
                                    </div>
                                    <div class="ch-item-a">
                                        <button type="button" wire:click="toggle({{ $it->id }})" class="ch-pill {{ $it->active ? 'ok' : '' }}" title="{{ $it->active ? 'Aparece no portal' : 'Em falta: não aparece no portal' }}">
                                            {{ $it->active ? 'Tem' : 'Em falta' }}
                                        </button>
                                        @if($it->image)
                                            <button type="button" wire:click="removePhoto({{ $it->id }})" class="crm-icon-btn" title="Tirar foto"><i class="bi bi-image-alt"></i></button>
                                        @else
                                            <button type="button" wire:click="pickPhoto({{ $it->id }})" x-on:click="$nextTick(() => document.getElementById('ch-photo-input').click())" class="crm-icon-btn" title="Pôr foto do tecido"><i class="bi bi-camera"></i></button>
                                        @endif
                                        <button type="button" wire:click="delete({{ $it->id }})" wire:confirm="Tirar {{ $it->label() }} da lista?" class="crm-icon-btn" title="Tirar"><i class="bi bi-trash3"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </section>

            <div class="qt-alert is-info">
                <i class="bi bi-info-circle mt-0.5"></i>
                <span>Para o cliente escolher, abra o produto em <b>Produtos › Editar</b> e ligue esta lista em <b>Escolhas no portal</b>. As escolhas são feitas sob encomenda: não têm estoque próprio. O valor a mais soma no preço do produto.</span>
            </div>
        @endif
    </div>
    <x-quotes-css />
    <style>
        .ch-empty { padding: 2rem 1.25rem; text-align: center; max-width: 34rem; margin: 0 auto; }
        .ch-empty-ic { display: inline-flex; width: 3.5rem; height: 3.5rem; border-radius: 1rem; align-items: center; justify-content: center; font-size: 1.6rem; color: #fff; background: linear-gradient(135deg, #f59e0b, #e11d48); margin-bottom: .75rem; }
        .ch-empty h2 { font-size: 1.05rem; font-weight: 800; color: rgb(15 23 42); }
        .dark .ch-empty h2 { color: #fff; }
        .ch-empty p { font-size: .85rem; color: rgb(100 116 139); margin: .4rem 0 1rem; }
        .ch-empty-btns { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; }
        .ch-kinds { display: grid; gap: .5rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 640px) { .ch-kinds { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ch-kind { display: flex; gap: .6rem; align-items: flex-start; padding: .7rem .8rem; border-radius: .9rem; border: 1.5px solid rgb(226 232 240); cursor: pointer; }
        .ch-kind i { font-size: 1.2rem; color: rgb(99 102 241); }
        .ch-kind b { display: block; font-size: .85rem; color: rgb(15 23 42); }
        .ch-kind small { display: block; font-size: .72rem; color: rgb(100 116 139); }
        .ch-kind.on { border-color: rgb(99 102 241); background: rgb(238 242 255); }
        .dark .ch-kind { border-color: rgb(51 65 85); }
        .dark .ch-kind b { color: #fff; }
        .dark .ch-kind.on { background: rgb(49 46 129 / .35); }
        .ch-list-name { font-size: 1rem; font-weight: 800; }
        .ch-form { border-bottom: 1px solid rgb(241 245 249); background: rgb(248 250 252); }
        .dark .ch-form { background: rgb(2 6 23 / .4); border-color: rgb(30 41 59); }
        .ch-color { width: 2.6rem; height: 2.3rem; padding: .15rem; border-radius: .6rem; border: 1px solid rgb(226 232 240); background: #fff; cursor: pointer; }
        .ch-group { padding: .6rem .8rem .9rem; border-top: 1px solid rgb(241 245 249); }
        .dark .ch-group { border-color: rgb(30 41 59); }
        .ch-group-h { display: flex; align-items: center; gap: .5rem; margin-bottom: .55rem; }
        .ch-mat { font-size: .95rem; font-weight: 800; max-width: 16rem; min-width: 0; flex: 1; }
        .ch-mat-label { font-size: .95rem; font-weight: 800; padding: 0 .5rem; color: rgb(15 23 42); }
        .dark .ch-mat-label { color: #fff; }
        .ch-mat-extra { display: inline-flex; align-items: center; gap: .15rem; margin-left: auto; white-space: nowrap; flex-shrink: 0; font-size: .75rem; font-weight: 700; color: rgb(100 116 139); border: 1px solid rgb(226 232 240); border-radius: .6rem; padding-left: .5rem; }
        .ch-mat-extra input { width: 5.5rem; }
        .dark .ch-mat-extra { border-color: rgb(51 65 85); }
        .ch-grid { display: grid; gap: .5rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 640px) { .ch-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .ch-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 1800px) { .ch-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        .ch-item { display: flex; align-items: center; gap: .55rem; padding: .45rem .5rem; border-radius: .9rem; border: 1px solid rgb(226 232 240); background: #fff; min-width: 0; }
        .dark .ch-item { border-color: rgb(51 65 85); background: rgb(15 23 42); }
        .ch-item.off { opacity: .55; }
        .ch-sw { display: block; width: 2.6rem; height: 2.6rem; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px rgb(203 213 225), 0 2px 6px rgb(15 23 42 / .15); background-size: cover; background-position: center; cursor: pointer; flex-shrink: 0; }
        .ch-item-b { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .ch-x { display: inline-flex; align-items: center; gap: .1rem; white-space: nowrap; font-size: .7rem; font-weight: 700; color: rgb(100 116 139); padding-left: .5rem; }
        .ch-x input.ch-code { width: 4.2rem; }
        .ch-x input { width: 5rem; text-align: left !important; padding-top: .1rem; padding-bottom: .1rem; font-size: .78rem; }
        .ch-meta { display: flex; flex-wrap: wrap; align-items: center; column-gap: .25rem; min-width: 0; }
        .ch-item-a { display: flex; align-items: center; gap: .15rem; flex-shrink: 0; }
        .ch-item-a .crm-icon-btn { width: 2rem; height: 2rem; }
        .ch-pill { font-size: .68rem; font-weight: 800; padding: .25rem .55rem; border-radius: 999px; background: rgb(254 226 226); color: rgb(159 18 57); white-space: nowrap; }
        .ch-pill.ok { background: rgb(220 252 231); color: rgb(21 128 61); }
        .dark .ch-pill { background: rgb(127 29 29 / .4); color: rgb(254 202 202); }
        .dark .ch-pill.ok { background: rgb(20 83 45 / .5); color: rgb(187 247 208); }
    </style>
</div>
