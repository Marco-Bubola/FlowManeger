<div class="print-capture-page w-full space-y-4">
    <x-cashbook-page-header title="Lançar por print" subtitle="Mande o print da notificação do banco e ele vai para a fatura do cartão certo ou para o livro caixa" icon="bi-phone" active="print">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 dark:bg-indigo-500/15 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300"><i class="bi bi-check2-circle"></i>{{ $monthCount }} lançados no mês</span>
            @if($waReady)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300"><i class="bi bi-whatsapp"></i>WhatsApp ligado</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            @if(Route::has('settings.whatsapp'))
            <a href="{{ route('settings.whatsapp') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-whatsapp text-emerald-500"></i>WhatsApp</a>
            @endif
            @if(Route::has('settings.bank-connections'))
            <a href="{{ route('settings.bank-connections') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-bank text-indigo-500"></i>Bancos conectados</a>
            @endif
        </x-slot:actions>
    </x-cashbook-page-header>

    @if($message)
        <div class="pc-msg {{ $messageOk ? 'ok' : 'bad' }}" wire:key="msg-{{ md5($message) }}">
            <i class="bi {{ $messageOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
            <span>{{ $message }}</span>
            <button type="button" wire:click="$set('message', null)" class="ml-auto" title="Fechar"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <div class="pc-top">
        {{-- Prints --}}
        <div class="pc-card"
             x-data="{ over: false, busy: false }"
             x-on:livewire-upload-start="busy = true" x-on:livewire-upload-finish="busy = false" x-on:livewire-upload-error="busy = false"
             x-on:paste.window="const f=[...($event.clipboardData?.files||[])].filter(x=>x.type.startsWith('image/')); if(f.length){ $event.preventDefault(); busy=true; $wire.uploadMultiple('prints', f, ()=>busy=false, ()=>busy=false) }">
            <div class="pc-card-head">
                <div class="pc-ic" style="background:rgba(99,102,241,.12);color:#6366f1"><i class="bi bi-image"></i></div>
                <div>
                    <p class="pc-title">Prints das notificações</p>
                    <p class="pc-desc">Compra no cartão, Pix recebido, pagamento... pode mandar vários de uma vez</p>
                </div>
            </div>
            <label class="pc-drop" :class="over && 'over'"
                   x-on:dragover.prevent="over=true" x-on:dragleave.prevent="over=false"
                   x-on:drop.prevent="over=false; const f=[...$event.dataTransfer.files].filter(x=>x.type.startsWith('image/')); if(f.length){ busy=true; $wire.uploadMultiple('prints', f, ()=>busy=false, ()=>busy=false) }">
                <input type="file" wire:model="prints" multiple accept="image/*" class="sr-only" data-testid="print-input">
                <template x-if="!busy">
                    <div class="pc-drop-inner">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <b>Escolher prints</b>
                        <span class="pc-hide-touch">ou arraste aqui, ou cole com Ctrl+V</span>
                        <span class="pc-show-touch">da galeria ou da câmera</span>
                    </div>
                </template>
                <template x-if="busy">
                    <div class="pc-drop-inner"><span class="pc-spin"></span><b>Lendo os prints...</b></div>
                </template>
                <div wire:loading.flex wire:target="prints,processPrints" class="pc-drop-cover"><span class="pc-spin"></span><b>Lendo os prints...</b></div>
            </label>
            @error('prints') <p class="pc-err">{{ $message }}</p> @enderror
            @error('prints.*') <p class="pc-err">{{ $message }}</p> @enderror
            @unless($aiReady)
                <p class="pc-warn"><i class="bi bi-exclamation-triangle"></i> A leitura de imagem precisa da chave do Gemini no servidor. Enquanto isso, cole o texto da notificação no outro quadro.</p>
            @endunless
        </div>

        {{-- Texto --}}
        <div class="pc-card">
            <div class="pc-card-head">
                <div class="pc-ic" style="background:rgba(16,185,129,.12);color:#10b981"><i class="bi bi-chat-left-text"></i></div>
                <div>
                    <p class="pc-title">Ou cole o texto da notificação</p>
                    <p class="pc-desc">Uma notificação por bloco, separadas por uma linha em branco</p>
                </div>
            </div>
            <form wire:submit="processText" class="flex flex-col gap-2 flex-1">
                <textarea wire:model="pasteText" rows="4" class="pc-input pc-textarea" placeholder="Ex.: Compra de R$ 45,90 APROVADA em IFOOD para o cartão com final 1234"></textarea>
                @error('pasteText') <p class="pc-err">{{ $message }}</p> @enderror
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="submit" class="pc-btn pc-btn-primary" wire:loading.attr="disabled" wire:target="processText">
                        <i class="bi bi-magic"></i> <span wire:loading.remove wire:target="processText">Lançar</span><span wire:loading wire:target="processText">Lendo...</span>
                    </button>
                    <label class="pc-check">
                        <input type="checkbox" wire:model.live="autoSave">
                        <span>Gravar direto quando entender tudo</span>
                    </label>
                </div>
            </form>
        </div>
    </div>

    {{-- Para conferir --}}
    @if($review->isNotEmpty())
        <div class="pc-section-head">
            <h2><i class="bi bi-hourglass-split text-amber-500"></i> Para conferir <span class="pc-count">{{ $review->count() }}</span></h2>
            @if($review->where('status', 'review')->count() > 1)
                <button type="button" wire:click="saveAll" class="pc-btn pc-btn-primary pc-btn-sm"><i class="bi bi-check2-all"></i> Gravar todos</button>
            @endif
        </div>
        <div class="pc-grid">
            @foreach($review as $c)
                @php $f = $form[$c->id] ?? []; $isCard = ($f['kind'] ?? '') === 'card'; @endphp
                <div class="pc-card pc-item" wire:key="rev-{{ $c->id }}">
                    <div class="pc-item-top">
                        @if($c->image_path)
                            <a href="{{ route('finance.print-capture.image', $c) }}" target="_blank" class="pc-thumb" title="Ver o print"><img src="{{ route('finance.print-capture.image', $c) }}" alt="" loading="lazy"></a>
                        @else
                            <div class="pc-thumb pc-thumb-text"><i class="bi bi-chat-left-text"></i></div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="pc-item-src">
                                <i class="bi {{ $c->source === 'whatsapp' ? 'bi-whatsapp text-emerald-500' : 'bi-phone' }}"></i>
                                {{ $c->source === 'whatsapp' ? 'WhatsApp' : 'Enviado aqui' }} · {{ $c->created_at->format('d/m H:i') }}
                                @if($c->institution) · <b>{{ $c->institution }}</b> @endif
                            </p>
                            @if($c->status === 'failed')
                                <p class="pc-err mt-1">{{ $c->error }}</p>
                            @elseif($c->error)
                                <p class="pc-warn mt-1"><i class="bi bi-files"></i> {{ $c->error }}</p>
                            @elseif($c->raw_text)
                                <p class="pc-raw">{{ \Illuminate\Support\Str::limit($c->raw_text, 140) }}</p>
                            @endif
                        </div>
                    </div>

                    @if($c->status === 'failed')
                        <div class="flex gap-2 justify-end">
                            <button type="button" wire:click="discard({{ $c->id }})" class="pc-btn pc-btn-ghost pc-btn-sm"><i class="bi bi-trash"></i> Tirar da lista</button>
                        </div>
                    @else
                        <div class="pc-kinds">
                            @foreach(['card' => ['Cartão de crédito', 'bi-credit-card'], 'expense' => ['Saída', 'bi-arrow-up-right'], 'income' => ['Entrada', 'bi-arrow-down-left']] as $k => [$kl, $ki])
                                <button type="button" wire:click="$set('form.{{ $c->id }}.kind', '{{ $k }}')" class="pc-kind {{ ($f['kind'] ?? '') === $k ? 'on k-' . $k : '' }}"><i class="bi {{ $ki }}"></i>{{ $kl }}</button>
                            @endforeach
                        </div>
                        @error("form.$c->id.kind") <p class="pc-err">{{ $message }}</p> @enderror

                        <div class="pc-fields">
                            <div class="pc-f-full">
                                @if($isCard)
                                    <label class="pc-label">Cartão</label>
                                    <select wire:model="form.{{ $c->id }}.bank_id" class="pc-input">
                                        <option value="">Escolha o cartão</option>
                                        @foreach($banks as $b)<option value="{{ $b->id_bank }}">{{ $b->name }}</option>@endforeach
                                    </select>
                                    @error("form.$c->id.bank_id") <p class="pc-err">{{ $message }}</p> @enderror
                                @else
                                    <label class="pc-label">Conta (opcional)</label>
                                    <select wire:model="form.{{ $c->id }}.account_id" class="pc-input">
                                        <option value="">Sem conta</option>
                                        @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
                                    </select>
                                @endif
                            </div>
                            <div>
                                <label class="pc-label">Valor{{ $isCard ? ' total' : '' }} (R$)</label>
                                <input type="text" inputmode="decimal" wire:model="form.{{ $c->id }}.amount" class="pc-input" placeholder="0,00">
                                @error("form.$c->id.amount") <p class="pc-err">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="pc-label">Data</label>
                                <input type="date" wire:model="form.{{ $c->id }}.date" class="pc-input">
                                @error("form.$c->id.date") <p class="pc-err">{{ $message }}</p> @enderror
                            </div>
                            <div class="pc-f-full">
                                <label class="pc-label">Descrição</label>
                                <input type="text" wire:model="form.{{ $c->id }}.description" class="pc-input" maxlength="120" placeholder="Loja ou pessoa">
                            </div>
                            <div class="{{ $isCard ? '' : 'pc-f-full' }}">
                                <label class="pc-label">Categoria</label>
                                <select wire:model="form.{{ $c->id }}.category_id" class="pc-input">
                                    <option value="">Escolha</option>
                                    @foreach($categories as $cat)<option value="{{ $cat->id_category }}">{{ $cat->name }}</option>@endforeach
                                </select>
                                @error("form.$c->id.category_id") <p class="pc-err">{{ $message }}</p> @enderror
                            </div>
                            @if($isCard)
                                <div>
                                    <label class="pc-label">Parcelas</label>
                                    <select wire:model="form.{{ $c->id }}.installments" class="pc-input">
                                        @for($i = 1; $i <= 24; $i++)<option value="{{ $i }}">{{ $i === 1 ? 'À vista' : $i . 'x' }}</option>@endfor
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="pc-item-actions">
                            <button type="button" wire:click="discard({{ $c->id }})" class="pc-btn pc-btn-ghost pc-btn-sm"><i class="bi bi-x-lg"></i> Descartar</button>
                            <button type="button" wire:click="saveItem({{ $c->id }})" class="pc-btn pc-btn-primary pc-btn-sm" data-testid="save-{{ $c->id }}"><i class="bi bi-check2"></i> Gravar</button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Lançados --}}
    <div class="pc-section-head">
        <h2><i class="bi bi-clock-history text-indigo-500"></i> Lançados por print</h2>
    </div>
    @if($recent->isEmpty())
        <div class="pc-empty">
            <i class="bi bi-phone"></i>
            <p class="font-semibold">Nada lançado por print ainda.</p>
            <p>Tire print da notificação do banco e mande aqui ou no WhatsApp do app.</p>
        </div>
    @else
        <div class="pc-list">
            @foreach($recent as $c)
                <div class="pc-row {{ $c->status === 'undone' ? 'is-undone' : '' }}" wire:key="rec-{{ $c->id }}">
                    <div class="pc-row-ic k-{{ $c->kind }}"><i class="bi {{ $c->kind === 'card' ? 'bi-credit-card' : ($c->kind === 'income' ? 'bi-arrow-down-left' : 'bi-arrow-up-right') }}"></i></div>
                    <div class="min-w-0 flex-1">
                        <p class="pc-row-title">{{ $c->description ?: $c->kindLabel() }}</p>
                        <p class="pc-row-sub">
                            {{ $c->date?->format('d/m') }} · {{ $c->destinationLabel() }}
                            @if($c->installments) · {{ explode(' de ', $c->installments)[1] }}x @endif
                            · <i class="bi {{ $c->source === 'whatsapp' ? 'bi-whatsapp' : 'bi-phone' }}"></i>
                            @if($c->linked_existing) · <span class="text-sky-600 dark:text-sky-400">já estava lançado</span> @endif
                            @if($c->status === 'undone') · <span class="text-rose-500">desfeito</span> @endif
                        </p>
                    </div>
                    <p class="pc-row-val k-{{ $c->kind }}">{{ $c->kind === 'income' ? '+' : '−' }} R$ {{ number_format((float) $c->amount, 2, ',', '.') }}</p>
                    <div class="pc-row-actions">
                        @if($c->status === 'saved')
                            @if($c->kind === 'card' && $c->bank_id)
                                <a href="{{ route('invoices.index', ['bankId' => $c->bank_id]) }}" wire:navigate class="pc-icon-btn" title="Abrir a fatura"><i class="bi bi-box-arrow-up-right"></i></a>
                            @elseif($c->kind !== 'card')
                                <a href="{{ route('cashbook.index') }}" wire:navigate class="pc-icon-btn" title="Abrir o livro caixa"><i class="bi bi-box-arrow-up-right"></i></a>
                            @endif
                            <button type="button" wire:click="undo({{ $c->id }})" wire:confirm="{{ $c->linked_existing ? 'Desligar este print do lançamento? O lançamento que já existia continua.' : 'Desfazer? O lançamento sai da ' . ($c->kind === 'card' ? 'fatura.' : 'lista do livro caixa.') }}" class="pc-icon-btn danger" title="Desfazer"><i class="bi bi-arrow-counterclockwise"></i></button>
                        @else
                            <button type="button" wire:click="reopen({{ $c->id }})" class="pc-icon-btn" title="Conferir de novo"><i class="bi bi-pencil"></i></button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        .print-capture-page{--pc-bd:rgba(148,163,184,.28)}
        .print-capture-page .pc-top{display:grid;grid-template-columns:1fr;gap:1rem}
        @media(min-width:1024px){.print-capture-page .pc-top{grid-template-columns:1fr 1fr}}
        .print-capture-page .pc-card{display:flex;flex-direction:column;gap:.85rem;border:1px solid var(--pc-bd);border-radius:1.25rem;background:#fff;padding:1rem;box-shadow:0 1px 2px rgba(15,23,42,.05)}
        .dark .print-capture-page .pc-card{background:rgba(15,23,42,.8);border-color:rgba(51,65,85,.7)}
        .print-capture-page .pc-card-head{display:flex;gap:.75rem;align-items:center}
        .print-capture-page .pc-ic{width:2.5rem;height:2.5rem;border-radius:.8rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
        .print-capture-page .pc-title{font-weight:700;font-size:.95rem;color:#1e293b}
        .dark .print-capture-page .pc-title{color:#f1f5f9}
        .print-capture-page .pc-desc{font-size:.78rem;color:#64748b}
        .print-capture-page .pc-drop{position:relative;display:flex;align-items:center;justify-content:center;min-height:9.5rem;border:2px dashed rgba(99,102,241,.35);border-radius:1rem;background:rgba(99,102,241,.04);cursor:pointer;transition:.15s;flex:1}
        .print-capture-page .pc-drop.over,.print-capture-page .pc-drop:hover{border-color:#6366f1;background:rgba(99,102,241,.09)}
        .print-capture-page .pc-drop-inner{display:flex;flex-direction:column;align-items:center;gap:.25rem;text-align:center;font-size:.8rem;color:#64748b;padding:1rem}
        .print-capture-page .pc-drop-inner i{font-size:2rem;color:#6366f1}
        .print-capture-page .pc-drop-inner b{font-size:.95rem;color:#4338ca}
        .dark .print-capture-page .pc-drop-inner b{color:#a5b4fc}
        .print-capture-page .pc-drop-cover{position:absolute;inset:0;flex-direction:column;align-items:center;gap:.35rem;font-size:.85rem;color:#4338ca;justify-content:center;background:rgba(255,255,255,.92);border-radius:1rem}
        .dark .print-capture-page .pc-drop-cover{background:rgba(15,23,42,.92)}
        .print-capture-page .pc-show-touch{display:none}
        @media(hover:none),(pointer:coarse){.print-capture-page .pc-show-touch{display:inline}.print-capture-page .pc-hide-touch{display:none}}
        .print-capture-page .pc-spin{width:1.6rem;height:1.6rem;border-radius:50%;border:3px solid rgba(99,102,241,.25);border-top-color:#6366f1;animation:pcspin .8s linear infinite}
        @keyframes pcspin{to{transform:rotate(360deg)}}
        .print-capture-page .pc-input{width:100%;border:1.5px solid var(--pc-bd);border-radius:.75rem;padding:.55rem .7rem;font-size:.9rem;background:#fff;color:#0f172a;min-height:2.6rem}
        .dark .print-capture-page .pc-input{background:#0f172a;color:#e2e8f0;border-color:#334155}
        .print-capture-page .pc-input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15)}
        .print-capture-page .pc-textarea{flex:1;min-height:6.5rem;resize:vertical}
        .print-capture-page .pc-label{display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:.2rem}
        .print-capture-page .pc-err{color:#ef4444;font-size:.74rem}
        .print-capture-page .pc-warn{font-size:.75rem;color:#b45309;background:rgba(245,158,11,.1);border-radius:.6rem;padding:.45rem .6rem}
        .print-capture-page .pc-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;border-radius:.75rem;padding:.6rem 1rem;font-size:.86rem;font-weight:700;cursor:pointer;border:0;min-height:2.6rem}
        .print-capture-page .pc-btn-sm{padding:.45rem .8rem;font-size:.8rem;min-height:2.3rem}
        .print-capture-page .pc-btn-primary{color:#fff;background:linear-gradient(90deg,#4f46e5,#7c3aed);box-shadow:0 4px 12px rgba(79,70,229,.25)}
        .print-capture-page .pc-btn-primary:disabled{opacity:.6}
        .print-capture-page .pc-btn-ghost{background:transparent;color:#64748b;border:1.5px solid var(--pc-bd)}
        .print-capture-page .pc-check{display:inline-flex;align-items:center;gap:.4rem;font-size:.8rem;color:#475569;cursor:pointer}
        .dark .print-capture-page .pc-check{color:#cbd5e1}
        .print-capture-page .pc-check input{width:1.05rem;height:1.05rem;accent-color:#6366f1}
        .print-capture-page .pc-msg{display:flex;align-items:flex-start;gap:.5rem;border-radius:1rem;padding:.75rem .9rem;font-size:.86rem;font-weight:600}
        .print-capture-page .pc-msg.ok{background:rgba(16,185,129,.1);color:#047857}
        .print-capture-page .pc-msg.bad{background:rgba(239,68,68,.1);color:#b91c1c}
        .dark .print-capture-page .pc-msg.ok{color:#6ee7b7}.dark .print-capture-page .pc-msg.bad{color:#fca5a5}
        .print-capture-page .pc-section-head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:.5rem}
        .print-capture-page .pc-section-head h2{display:flex;align-items:center;gap:.45rem;font-weight:800;font-size:1rem;color:#1e293b}
        .dark .print-capture-page .pc-section-head h2{color:#f1f5f9}
        .print-capture-page .pc-count{font-size:.72rem;background:rgba(245,158,11,.15);color:#b45309;border-radius:999px;padding:.1rem .5rem}
        .print-capture-page .pc-grid{display:grid;grid-template-columns:1fr;gap:1rem;align-items:start}
        @media(min-width:768px){.print-capture-page .pc-grid{grid-template-columns:repeat(2,1fr)}}
        @media(min-width:1536px){.print-capture-page .pc-grid{grid-template-columns:repeat(3,1fr)}}
        @media(min-width:2200px){.print-capture-page .pc-grid{grid-template-columns:repeat(4,1fr)}}
        .print-capture-page .pc-item-top{display:flex;gap:.75rem;align-items:flex-start}
        .print-capture-page .pc-thumb{width:3.5rem;height:4.5rem;border-radius:.6rem;overflow:hidden;flex-shrink:0;background:#f1f5f9;border:1px solid var(--pc-bd);display:flex;align-items:center;justify-content:center;color:#94a3b8}
        .dark .print-capture-page .pc-thumb{background:#1e293b}
        .print-capture-page .pc-thumb img{width:100%;height:100%;object-fit:cover;object-position:top}
        .print-capture-page .pc-item-src{font-size:.75rem;color:#64748b}
        .print-capture-page .pc-raw{font-size:.78rem;color:#475569;margin-top:.2rem;line-height:1.35}
        .dark .print-capture-page .pc-raw{color:#94a3b8}
        .print-capture-page .pc-kinds{display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem}
        .print-capture-page .pc-kind{display:flex;flex-direction:column;align-items:center;gap:.15rem;border:1.5px solid var(--pc-bd);border-radius:.75rem;padding:.45rem .25rem;font-size:.72rem;font-weight:700;color:#64748b;background:transparent;cursor:pointer;min-height:2.9rem}
        .print-capture-page .pc-kind i{font-size:1rem}
        .print-capture-page .pc-kind.on.k-card{border-color:#6366f1;background:rgba(99,102,241,.1);color:#4f46e5}
        .print-capture-page .pc-kind.on.k-expense{border-color:#f43f5e;background:rgba(244,63,94,.08);color:#e11d48}
        .print-capture-page .pc-kind.on.k-income{border-color:#10b981;background:rgba(16,185,129,.1);color:#059669}
        .print-capture-page .pc-fields{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
        .print-capture-page .pc-f-full{grid-column:1/-1}
        .print-capture-page .pc-item-actions{display:flex;justify-content:flex-end;gap:.5rem;margin-top:auto}
        .print-capture-page .pc-list{display:flex;flex-direction:column;border:1px solid var(--pc-bd);border-radius:1.25rem;background:#fff;overflow:hidden}
        .dark .print-capture-page .pc-list{background:rgba(15,23,42,.8);border-color:rgba(51,65,85,.7)}
        .print-capture-page .pc-row{display:flex;align-items:center;gap:.75rem;padding:.7rem 1rem;border-top:1px solid var(--pc-bd)}
        .print-capture-page .pc-row:first-child{border-top:0}
        .print-capture-page .pc-row.is-undone{opacity:.55}
        .print-capture-page .pc-row-ic{width:2.25rem;height:2.25rem;border-radius:.7rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(244,63,94,.1);color:#e11d48}
        .print-capture-page .pc-row-ic.k-card{background:rgba(99,102,241,.12);color:#4f46e5}
        .print-capture-page .pc-row-ic.k-income{background:rgba(16,185,129,.12);color:#059669}
        .print-capture-page .pc-row-title{font-weight:700;font-size:.88rem;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .dark .print-capture-page .pc-row-title{color:#f1f5f9}
        .print-capture-page .pc-row-sub{font-size:.73rem;color:#64748b}
        .print-capture-page .pc-row-val{font-weight:800;font-size:.9rem;white-space:nowrap;color:#e11d48}
        .print-capture-page .pc-row-val.k-income{color:#059669}
        .print-capture-page .pc-row-val.k-card{color:#4f46e5}
        .print-capture-page .pc-row-actions{display:flex;gap:.3rem}
        .print-capture-page .pc-icon-btn{width:2.3rem;height:2.3rem;border-radius:.65rem;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid var(--pc-bd);color:#64748b;background:transparent}
        .print-capture-page .pc-icon-btn.danger{color:#e11d48}
        .print-capture-page .pc-empty{text-align:center;border:1.5px dashed var(--pc-bd);border-radius:1.25rem;padding:2rem 1rem;font-size:.85rem;color:#64748b}
        .print-capture-page .pc-empty i{font-size:2rem;color:#94a3b8}
        @media(max-width:640px){
            .print-capture-page .pc-row{flex-wrap:wrap;padding:.7rem .8rem}
            .print-capture-page .pc-row > .min-w-0{flex-basis:calc(100% - 3rem)}
            .print-capture-page .pc-row-val{margin-left:3rem;flex:1}
            .print-capture-page .pc-drop{min-height:8rem}
        }
    </style>
</div>
