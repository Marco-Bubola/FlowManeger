<div class="qt-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header title="Ajustes dos orçamentos" subtitle="Margem, validade, acréscimos, cores que o portal oferece e automações" icon="bi-sliders"
                       section="orcamentos" tabs-section="orcamentos" tabs-active="ajustes">
            <x-slot:actions>
                <button type="button" wire:click="save" class="crm-btn crm-btn-primary"><i class="bi bi-check2"></i><span class="hidden sm:inline">Salvar</span></button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid gap-4 xl:grid-cols-2 items-start">
            <section class="crs-card">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-percent"></i></span>
                    <div class="min-w-0 flex-1"><h2 class="crs-title">Preço e validade</h2><p class="crs-sub">Usados em todo orçamento novo</p></div>
                </div>
                <div class="qt-sec">
                    <div class="qt-2">
                        <label class="crm-label">Margem de lucro (%)
                            <input type="number" step="1" min="0" max="90" wire:model.live.debounce.400ms="margin" class="crm-input">
                            <span class="qt-muted">Custo R$ 100,00 → preço sugerido {{ \App\Services\Quotes\QuoteService::money($example) }}</span>
                            @error('margin') <span class="crm-err">{{ $message }}</span> @enderror
                        </label>
                        <label class="crm-label">Avisar quando o preço ficar abaixo do custo mais (%)
                            <input type="number" step="1" min="0" max="100" wire:model="min_markup" class="crm-input">
                        </label>
                        <label class="crm-label">Validade do orçamento (dias)
                            <input type="number" min="1" max="180" wire:model="validity_days" class="crm-input">
                            @error('validity_days') <span class="crm-err">{{ $message }}</span> @enderror
                        </label>
                        <label class="crm-label">O portal promete responder em (dias úteis)
                            <input type="number" min="1" max="30" wire:model="reply_days" class="crm-input">
                        </label>
                    </div>
                    <label class="crm-label">Texto no fim do orçamento (garantia, condições)
                        <textarea wire:model="footer" rows="3" class="crm-input" placeholder="Ex.: Garantia de 1 ano na estrutura. Entrega grátis em Ribeirão."></textarea>
                    </label>
                </div>
            </section>

            <section class="crs-card">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-plus-slash-minus"></i></span>
                    <div class="min-w-0 flex-1"><h2 class="crs-title">Acréscimos prontos</h2><p class="crs-sub">Somam no preço do item com um toque</p></div>
                    <button type="button" wire:click="addExtra" class="crs-link"><i class="bi bi-plus-lg"></i>Acréscimo</button>
                </div>
                <div class="qt-sec !gap-2">
                    @forelse($extras as $i => $e)
                        <div class="flex items-center gap-2" wire:key="ex-{{ $i }}">
                            <input type="text" wire:model="extras.{{ $i }}.n" class="crm-input flex-1" placeholder="Ex.: Assento estofado grosso" maxlength="80">
                            <div class="relative w-28 shrink-0">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">+R$</span>
                                <input type="number" step="0.01" min="0" wire:model="extras.{{ $i }}.v" class="crm-input !pl-10 text-right">
                            </div>
                            <button type="button" wire:click="removeExtra({{ $i }})" class="crm-icon-btn" title="Tirar"><i class="bi bi-trash3"></i></button>
                        </div>
                    @empty
                        <p class="qt-muted">Nenhum acréscimo. Toque em “Acréscimo” para criar.</p>
                    @endforelse
                </div>
            </section>

            <section class="crs-card">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-magic"></i></span>
                    <div class="min-w-0 flex-1"><h2 class="crs-title">Portal e automações</h2><p class="crs-sub">O que o app faz sozinho</p></div>
                </div>
                <div class="qt-sec">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="portal_requests" class="mt-1 h-4 w-4 rounded text-indigo-600">
                        <span><b class="text-sm text-slate-800 dark:text-slate-100">Aceitar pedidos de orçamento pelo portal</b><br><span class="qt-muted">Mostra “Pedir orçamento” e “Quero em outra cor” no catálogo.</span></span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="remind_client" class="mt-1 h-4 w-4 rounded text-indigo-600">
                        <span><b class="text-sm text-slate-800 dark:text-slate-100">Lembrar o cliente 2 dias antes de vencer</b><br><span class="qt-muted">Cria uma tarefa com a mensagem do WhatsApp pronta para você mandar.</span></span>
                    </label>
                    <div class="qt-alert is-info"><i class="bi bi-info-circle mt-0.5"></i>
                        <span>Sempre ligado: aviso no sino quando chega pedido ou o cliente aprova, lembrete de pedido sem resposta, de orçamento não visto em 3 dias, e orçamento vencido muda sozinho para “Vencido”.</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="qt-muted">Link do pedido de orçamento:</span>
                        <a href="{{ $portalUrl }}" target="_blank" class="crs-link"><i class="bi bi-box-arrow-up-right"></i>Abrir no portal</a>
                        <button type="button" x-data @click="navigator.clipboard.writeText(@js($portalUrl)); $dispatch('notify', { type: 'success', message: 'Link copiado.' })" class="crs-link"><i class="bi bi-copy"></i>Copiar</button>
                    </div>
                </div>
            </section>

            <section class="crs-card">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-pink-500 to-rose-500"><i class="bi bi-palette"></i></span>
                    <div class="min-w-0 flex-1"><h2 class="crs-title">Cores que o cliente pode escolher</h2><p class="crs-sub">Aparecem no “Quero em outra cor” do portal</p></div>
                    <button type="button" wire:click="resetColors" wire:confirm="Voltar para as cores do robô? As suas mudanças nas cores se perdem." class="crs-link"><i class="bi bi-arrow-counterclockwise"></i><span class="hidden sm:inline">Do robô</span></button>
                </div>
                <div class="qt-sec">
                    @foreach($colors as $g => $group)
                        <div wire:key="cg-{{ $g }}-{{ $group['key'] }}" class="flex flex-col gap-2">
                            <button type="button" wire:click="$set('colorGroup', {{ $colorGroup === $g ? 'null' : $g }})" class="flex items-center gap-2 text-left">
                                <b class="text-sm text-slate-800 dark:text-slate-100 flex-1">{{ $group['name'] }} <span class="crs-count">{{ count($group['items']) }}</span></b>
                                <span class="flex -space-x-1">@foreach(array_slice($group['items'], 0, 8) as $c)<span class="w-4 h-4 rounded-full border border-white dark:border-slate-800" style="background: {{ $c[1] }}"></span>@endforeach</span>
                                <i class="bi {{ $colorGroup === $g ? 'bi-chevron-up' : 'bi-chevron-down' }} text-slate-400"></i>
                            </button>
                            @if($colorGroup === $g)
                                <div class="crm-chips">
                                    @foreach($group['items'] as $i => $c)
                                        <span class="crm-chip" wire:key="c-{{ $g }}-{{ $i }}"><span class="w-3 h-3 rounded-full border border-black/10" style="background: {{ $c[1] }}"></span>{{ $c[0] }}
                                            <button type="button" wire:click="removeColor({{ $g }}, {{ $i }})" class="ml-0.5 text-slate-400 hover:text-rose-500" title="Tirar"><i class="bi bi-x"></i></button></span>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="color" wire:model="newColorHex" class="h-10 w-12 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent p-1 shrink-0" title="Cor">
                                    <input type="text" wire:model="newColorName" wire:keydown.enter.prevent="addColor({{ $g }})" class="crm-input flex-1" placeholder="Nome da cor nova" maxlength="40">
                                    <button type="button" wire:click="addColor({{ $g }})" class="crm-btn"><i class="bi bi-plus-lg"></i></button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    <div class="flex items-center gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                        <input type="text" wire:model="newGroup" wire:keydown.enter.prevent="addGroup" class="crm-input flex-1" placeholder="Novo grupo (ex.: Cor do vidro)" maxlength="60">
                        <button type="button" wire:click="addGroup" class="crm-btn"><i class="bi bi-plus-lg"></i>Grupo</button>
                    </div>
                </div>
            </section>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="button" wire:click="save" class="crm-btn crm-btn-primary w-full sm:w-auto justify-center"><i class="bi bi-check2"></i>Salvar ajustes</button>
        </div>
    </div>
    <x-quotes-css />
</div>
