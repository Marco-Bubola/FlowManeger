{{-- Formulário único de categoria (Nova e Editar usam o mesmo). Espera as propriedades públicas
     de CreateCategory/EditCategory e $banks, $clients, $categories. --}}
@php
    $isProduct = $type === 'product';
    $iconGroups = [
        'Produtos' => [
            'fas fa-box' => 'Caixa', 'fas fa-tag' => 'Etiqueta', 'fas fa-tshirt' => 'Roupas', 'fas fa-shoe-prints' => 'Calçados',
            'fas fa-gem' => 'Joias', 'fas fa-spa' => 'Beleza', 'fas fa-pump-soap' => 'Higiene', 'fas fa-mobile-alt' => 'Celular',
            'fas fa-laptop' => 'Eletrônicos', 'fas fa-headphones' => 'Áudio', 'fas fa-couch' => 'Casa', 'fas fa-blender' => 'Cozinha',
            'fas fa-baby' => 'Bebê', 'fas fa-paw' => 'Pet', 'fas fa-gift' => 'Presentes', 'fas fa-book' => 'Livros',
            'fas fa-futbol' => 'Esporte', 'fas fa-tools' => 'Ferramentas', 'fas fa-cookie-bite' => 'Alimentos', 'fas fa-wine-bottle' => 'Bebidas',
        ],
        'Finanças' => [
            'fas fa-dollar-sign' => 'Dinheiro', 'fas fa-wallet' => 'Carteira', 'fas fa-credit-card' => 'Cartão', 'fas fa-coins' => 'Moedas',
            'fas fa-piggy-bank' => 'Poupança', 'fas fa-chart-line' => 'Investimento', 'fas fa-hand-holding-usd' => 'Recebimento', 'fas fa-file-invoice' => 'Contas',
            'fas fa-exchange-alt' => 'Transferência', 'fas fa-university' => 'Banco', 'fas fa-percent' => 'Juros', 'fas fa-receipt' => 'Recibo',
        ],
        'Dia a dia' => [
            'fas fa-shopping-basket' => 'Mercado', 'fas fa-utensils' => 'Restaurante', 'fas fa-car' => 'Carro', 'fas fa-gas-pump' => 'Combustível',
            'fas fa-bus' => 'Transporte', 'fas fa-home' => 'Moradia', 'fas fa-bolt' => 'Energia', 'fas fa-tint' => 'Água',
            'fas fa-wifi' => 'Internet', 'fas fa-heartbeat' => 'Saúde', 'fas fa-pills' => 'Farmácia', 'fas fa-dumbbell' => 'Academia',
            'fas fa-graduation-cap' => 'Educação', 'fas fa-plane' => 'Viagem', 'fas fa-gamepad' => 'Lazer', 'fas fa-film' => 'Streaming',
            'fas fa-briefcase' => 'Trabalho', 'fas fa-users' => 'Família',
        ],
        'Marcas e imagens' => [
            'icons8-nubank' => 'Nubank', 'icons8-inter' => 'Inter', 'icons8-xp' => 'XP', 'icons8-pix' => 'PIX', 'icons8-cartao' => 'Cartão',
            'icons8-supermercado' => 'Supermercado', 'icons8-restaurante' => 'Restaurante', 'icons8-posto' => 'Posto', 'icons8-farmacia' => 'Farmácia',
            'icons8-academia' => 'Academia', 'icons8-beleza' => 'Beleza', 'icons8-perfume' => 'Perfume', 'icons8-viagem' => 'Viagem',
            'icons8-steaming' => 'Streaming', 'icons8-pagamento' => 'Pagamento', 'icons8-transferencia' => 'Transferência', 'icons8-rendimento' => 'Rendimento',
            'icons8-online' => 'Online', 'icons8-box' => 'Caixa',
        ],
    ];
    $swatches = ['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#64748b'];
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30';
    $label = 'mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-300';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm';
@endphp

<form id="category-form" wire:submit.prevent="save" class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    {{-- Coluna principal --}}
    <div class="xl:col-span-2 space-y-5">
        {{-- Tipo --}}
        <section class="{{ $card }} p-5">
            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"><i class="fas fa-layer-group text-indigo-500"></i>Para que serve esta categoria?</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach(['product' => ['Produtos', 'Organiza os produtos do estoque', 'fas fa-box'], 'transaction' => ['Transações', 'Classifica receitas e despesas', 'fas fa-exchange-alt']] as $value => [$title, $hint, $ico])
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="type" value="{{ $value }}" class="peer sr-only">
                        <div class="flex items-center gap-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 p-3 transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/10 hover:border-indigo-300">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-300"><i class="{{ $ico }}"></i></span>
                            <span>
                                <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ $title }}</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</span>
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>
            @unless($isProduct)
                <div class="mt-4">
                    <span class="{{ $label }}">Tipo de lançamento</span>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['gasto' => ['Despesa', 'fas fa-arrow-down', 'peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-500/10'], 'receita' => ['Receita', 'fas fa-arrow-up', 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-500/10'], 'ambos' => ['As duas', 'fas fa-exchange-alt', 'peer-checked:border-sky-500 peer-checked:bg-sky-50 dark:peer-checked:bg-sky-500/10']] as $value => [$text, $ico, $checked])
                            <label class="cursor-pointer">
                                <input type="radio" wire:model="tipo" value="{{ $value }}" class="peer sr-only">
                                <div class="flex items-center justify-center gap-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 transition {{ $checked }}">
                                    <i class="{{ $ico }} text-xs"></i>{{ $text }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('tipo') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endunless
        </section>

        {{-- Informações --}}
        <section class="{{ $card }} p-5">
            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"><i class="fas fa-info-circle text-indigo-500"></i>Informações</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="{{ $label }}" for="cat-name">Nome *</label>
                    <input id="cat-name" type="text" wire:model.live.debounce.300ms="name" class="{{ $input }}" placeholder="Ex.: Mercado, Roupas, Salário">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="cat-desc">Descrição curta</label>
                    <input id="cat-desc" type="text" wire:model.live.debounce.300ms="desc_category" maxlength="100" class="{{ $input }}" placeholder="Aparece embaixo do nome">
                </div>
                <div>
                    <span class="{{ $label }}">Situação</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([1 => 'Ativa', 0 => 'Inativa'] as $value => $text)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model="is_active" value="{{ $value }}" class="peer sr-only">
                                <div class="rounded-xl border-2 border-slate-200 dark:border-slate-700 px-3 py-2 text-center text-sm font-semibold text-slate-700 dark:text-slate-200 transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/10">{{ $text }}</div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Aparência --}}
        <section class="{{ $card }} p-5" x-data="{ q: '' }">
            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"><i class="fas fa-palette text-indigo-500"></i>Aparência</h3>
            <span class="{{ $label }}">Cor</span>
            <div class="flex flex-wrap items-center gap-2">
                @foreach($swatches as $swatch)
                    <button type="button" wire:click="$set('hexcolor_category', '{{ $swatch }}')" title="{{ $swatch }}"
                        class="h-8 w-8 rounded-full shadow-sm ring-offset-2 dark:ring-offset-slate-900 transition hover:scale-110 {{ strtolower($hexcolor_category) === $swatch ? 'ring-2 ring-slate-900 dark:ring-white' : '' }}"
                        style="background: {{ $swatch }}"></button>
                @endforeach
                <label class="relative inline-flex h-8 cursor-pointer items-center gap-2 rounded-full border border-slate-200 dark:border-slate-700 pl-1 pr-3 text-xs font-semibold text-slate-600 dark:text-slate-300" title="Escolher outra cor">
                    <input type="color" wire:model.live="hexcolor_category" class="h-6 w-6 cursor-pointer rounded-full border-0 bg-transparent p-0">
                    <span class="font-mono">{{ $hexcolor_category }}</span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <span class="{{ $label }} mb-0">Ícone</span>
                <input type="text" x-model="q" placeholder="Buscar ícone..." class="w-44 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200">
            </div>
            <div class="mt-2 max-h-72 overflow-y-auto pr-1 space-y-3">
                @foreach($iconGroups as $group => $icons)
                    <div>
                        <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $group }}</p>
                        <div class="grid grid-cols-6 sm:grid-cols-8 lg:grid-cols-10 gap-1.5">
                            @foreach($icons as $iconClass => $iconLabel)
                                <button type="button" wire:click="$set('icone', '{{ $iconClass }}')" title="{{ $iconLabel }}"
                                    x-show="!q || '{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($iconLabel)) }}'.includes(q.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''))"
                                    class="flex aspect-square items-center justify-center rounded-xl border transition {{ $icone === $iconClass ? 'border-transparent text-white shadow-md' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-indigo-300 hover:text-indigo-600' }}"
                                    @if($icone === $iconClass) style="background: {{ $hexcolor_category }}" @endif>
                                    <x-category-icon :icon="$iconClass" size="text-base" />
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Coluna lateral --}}
    <div class="space-y-5">
        {{-- Prévia --}}
        <section class="{{ $card }} overflow-hidden xl:sticky xl:top-4">
            <div class="h-1.5" style="background: linear-gradient(90deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}99)"></div>
            <div class="p-5">
                <p class="mb-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Prévia</p>
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white shadow-md" style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}cc)">
                        <x-category-icon :icon="$icone" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $name ?: 'Nome da categoria' }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $desc_category ?: ($isProduct ? 'Categoria de produtos' : 'Categoria de lançamentos') }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Mais opções --}}
        <section class="{{ $card }} p-5" x-data="{ open: {{ ($parent_id || $limite_orcamento || $tags || $descricao_detalhada || $description || $id_bank || $id_clients) ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between text-sm font-bold text-slate-900 dark:text-white">
                <span class="flex items-center gap-2"><i class="fas fa-sliders-h text-indigo-500"></i>Mais opções</span>
                <i class="fas fa-chevron-down text-xs text-slate-400 transition" :class="open && 'rotate-180'"></i>
            </button>
            <div x-show="open" x-cloak class="mt-4 space-y-4">
                <div>
                    <label class="{{ $label }}" for="cat-parent">Categoria pai</label>
                    <select id="cat-parent" wire:model="parent_id" class="{{ $input }}">
                        <option value="">Nenhuma (categoria principal)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id_category }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                @unless($isProduct)
                    <div>
                        <label class="{{ $label }}">Limite de gasto por mês (R$)</label>
                        <x-money-input bare :model="'limite_orcamento'" :value="$limite_orcamento ?? 0" :live="false" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}" for="cat-bank">Banco</label>
                            <select id="cat-bank" wire:model="id_bank" class="{{ $input }}">
                                <option value="">Nenhum</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id_bank }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}" for="cat-client">Cliente</label>
                            <select id="cat-client" wire:model="id_clients" class="{{ $input }}">
                                <option value="">Nenhum</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endunless
                <div>
                    <label class="{{ $label }}" for="cat-tags">Tags (separadas por vírgula)</label>
                    <input id="cat-tags" type="text" wire:model="tags" class="{{ $input }}" placeholder="pix, mercado, fixo">
                </div>
                <div>
                    <label class="{{ $label }}" for="cat-detail">Descrição detalhada</label>
                    <textarea id="cat-detail" wire:model="descricao_detalhada" rows="3" class="{{ $input }}"></textarea>
                </div>
                <div>
                    <label class="{{ $label }}" for="cat-notes">Observações</label>
                    <textarea id="cat-notes" wire:model="description" rows="2" class="{{ $input }}"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" wire:model="compartilhavel" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Compartilhável com outros usuários
                </label>
            </div>
        </section>
    </div>
</form>
