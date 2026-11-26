<div class="qt-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header title="Materiais e horas" subtitle="Preço de cada material e da hora de trabalho: é com eles que o orçamento monta o custo" icon="bi-calculator"
                       section="orcamentos" tabs-section="orcamentos" tabs-active="materiais">
            <x-slot:actions>
                <button type="button" wire:click="openForm" class="crm-btn crm-btn-primary" title="Novo item">
                    <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Novo item</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @if($estimated)
            <div class="qt-alert is-warn mb-4">
                <i class="bi bi-lightbulb mt-0.5"></i>
                <span><b>{{ $estimated }} de {{ $total }}</b> preços ainda são a estimativa que veio com a tabela do robô (em laranja). Quando você muda um preço, ele fica como seu. O robô usa esta mesma tabela.</span>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" class="crm-input !pl-9" placeholder="Buscar material (ex.: metalon 20, espuma, hora)">
            </div>
            <button type="button" wire:click="restoreDefaults" wire:confirm="Trazer de volta os itens da tabela do robô que você apagou? Seus preços não mudam." class="crm-btn justify-center">
                <i class="bi bi-arrow-counterclockwise"></i>Itens do robô
            </button>
        </div>

        <div class="qt-tabs mb-4">
            <button type="button" wire:click="setCategory('')" class="qt-tab {{ $category === '' ? 'is-on' : '' }}">Tudo <b>{{ $total }}</b></button>
            @foreach($cats as $cat => $n)
                <button type="button" wire:click="setCategory(@js($cat))" class="qt-tab {{ $category === $cat ? 'is-on' : '' }}">{{ $cat }} <b>{{ $n }}</b></button>
            @endforeach
        </div>

        @if($showForm)
            <section class="crs-card mb-4" x-data x-init="$nextTick(() => $el.querySelector('input')?.focus())">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-plus-lg"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">Novo item na tabela</h2>
                        <p class="crs-sub">Material, peça comprada pronta ou hora de trabalho</p>
                    </div>
                    <button type="button" wire:click="$set('showForm', false)" class="crm-icon-btn" title="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>
                <form wire:submit="add" class="qt-sec">
                    <div class="qt-2">
                        <label class="crm-label">Nome
                            <input type="text" wire:model="newName" class="crm-input" placeholder="Ex.: Metalon 20×30 parede 1,20 mm" maxlength="160">
                            @error('newName') <span class="crm-err">{{ $message }}</span> @enderror
                        </label>
                        <label class="crm-label">Grupo
                            <input type="text" wire:model="newCategory" list="qt-cats" class="crm-input" placeholder="Escolha ou escreva um grupo novo" maxlength="80">
                            <datalist id="qt-cats">@foreach($cats as $cat => $n)<option value="{{ $cat }}">@endforeach</datalist>
                            @error('newCategory') <span class="crm-err">{{ $message }}</span> @enderror
                        </label>
                    </div>
                    <div class="qt-3">
                        <label class="crm-label">Unidade
                            <select wire:model="newUnit" class="crm-input">@foreach($units as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach</select>
                        </label>
                        <label class="crm-label">Preço por unidade (R$)
                            <input type="text" inputmode="decimal" wire:model="newPrice" class="crm-input" placeholder="0,00">
                        </label>
                        <label class="crm-label col-span-2 md:col-span-1">Observação
                            <input type="text" wire:model="newNote" class="crm-input" placeholder="Ex.: barra de 6 m ≈ R$ 45" maxlength="200">
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="crm-btn crm-btn-primary flex-1 sm:flex-none justify-center"><i class="bi bi-check2"></i>Colocar na tabela</button>
                        <button type="button" wire:click="$set('showForm', false)" class="crm-btn">Cancelar</button>
                    </div>
                </form>
            </section>
        @endif

        <div class="flex flex-col gap-4">
            @forelse($groups as $cat => $items)
                <section class="crs-card" wire:key="cat-{{ md5($cat) }}">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br {{ str_contains(mb_strtolower($cat), 'mão de obra') ? 'from-amber-500 to-orange-500' : 'from-sky-500 to-indigo-600' }}">
                            <i class="bi {{ str_contains(mb_strtolower($cat), 'mão de obra') ? 'bi-person-gear' : (str_contains(mb_strtolower($cat), 'tecido') || str_contains(mb_strtolower($cat), 'espuma') ? 'bi-palette' : (str_contains(mb_strtolower($cat), 'madeira') ? 'bi-tree' : 'bi-box')) }}"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="crs-title">{{ $cat }} <span class="crs-count">{{ $items->count() }}</span></h2>
                        </div>
                        <button type="button" wire:click="openForm(@js($cat))" class="crs-link"><i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Item</span></button>
                    </div>
                    <div class="qt-mat-row qt-head-row !py-1.5">
                        <span>Item</span><span class="qt-mat-note">Observação</span><span>Unidade</span><span class="text-right pr-2">R$ por unid.</span><span></span>
                    </div>
                    <div class="crs-list">
                        @foreach($items as $m)
                            <div class="qt-mat-row" wire:key="mat-{{ $m->id }}">
                                <input type="text" value="{{ $m->name }}" wire:change="updateField({{ $m->id }}, 'name', $event.target.value)" class="qt-cell font-semibold" maxlength="160" aria-label="Nome">
                                <input type="text" value="{{ $m->note }}" wire:change="updateField({{ $m->id }}, 'note', $event.target.value)" class="qt-cell qt-mat-note text-slate-500" placeholder="—" maxlength="200" aria-label="Observação">
                                <select wire:change="updateField({{ $m->id }}, 'unit', $event.target.value)" class="qt-cell" aria-label="Unidade">
                                    @foreach(array_unique(array_merge($units, [$m->unit])) as $u)<option value="{{ $u }}" @selected($u === $m->unit)>{{ $u }}</option>@endforeach
                                </select>
                                <input type="text" inputmode="decimal" value="{{ number_format($m->price, 2, ',', '.') }}" wire:change="updatePrice({{ $m->id }}, $event.target.value)"
                                       class="qt-cell is-money {{ $m->estimated ? 'is-est' : '' }}" title="{{ $m->estimated ? 'Estimativa: confira e mude se precisar' : 'Seu preço' }}" aria-label="Preço">
                                <button type="button" wire:click="delete({{ $m->id }})" wire:confirm="Tirar {{ $m->name }} da tabela?" class="crm-icon-btn" title="Tirar da tabela"><i class="bi bi-trash3"></i></button>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="crs-card"><div class="crs-empty"><i class="bi bi-search"></i>Nada encontrado com “{{ $search }}”.</div></div>
            @endforelse
        </div>
    </div>
    <x-quotes-css />
</div>
