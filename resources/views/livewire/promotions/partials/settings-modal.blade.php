{{-- Configurações das promoções: abas Preços, Mensagem e Automático --}}
@php
    $f = $settingsForm;
    $num = fn ($v) => (float) str_replace(',', '.', str_replace('.', '', (string) $v));
    $margin = $num($f['min_margin_percent'] ?? 0);
    $sample = [
        '{nome}' => 'Dream Des Col 200ml', '{de}' => 'R$ 119,90', '{por}' => 'R$ 89,90', '{desconto}' => '25%',
        '{economia}' => 'R$ 30,00', '{validade}' => 'Válido até ' . now()->addDays(7)->format('d/m') . ' ou enquanto durar o estoque (3 un.)',
        '{estoque}' => '3', '{cliente}' => 'Maria', '{link}' => 'seusite.com/catalogo',
    ];
    $preview = e(strtr((string) ($f['message_template'] ?? ''), $sample));
    $preview = preg_replace(['/\*([^*\n]+)\*/', '/~([^~\n]+)~/', '/_([^_\n]+)_/'], ['<b>$1</b>', '<s>$1</s>', '<i>$1</i>'], $preview);
    if (($f['greet_client'] ?? true) && !str_contains((string) ($f['message_template'] ?? ''), '{cliente}')) {
        $preview = '<span class="opacity-70">Oi, Maria! 💜</span>' . "\n" . $preview;
    }
    $footerText = trim((string) ($f['footer'] ?? ''));
    $currentTemplate = trim((string) ($f['message_template'] ?? ''));
@endphp
<div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm p-0 sm:p-4"
     x-data="{ tab: 'precos' }" wire:keydown.escape="$set('showSettingsModal', false)">
    <div class="w-full sm:max-w-2xl max-h-[95vh] flex flex-col bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden">

        {{-- Cabeçalho --}}
        <div class="px-5 pt-4 pb-3 bg-gradient-to-r from-rose-500/10 via-pink-500/10 to-orange-500/10 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-500 via-pink-500 to-orange-500 flex items-center justify-center shadow-lg shadow-rose-500/30 shrink-0">
                    <i class="bi bi-sliders text-white text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white leading-tight">Configurações</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Como as promoções são calculadas e enviadas</p>
                </div>
                <button type="button" wire:click="$set('showSettingsModal', false)" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-500/10"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-1 p-1 rounded-2xl bg-white/70 dark:bg-slate-900/60">
                @foreach(['precos' => ['Preços', 'bi-cash-coin'], 'mensagem' => ['Mensagem', 'bi-chat-heart'], 'auto' => ['Automático', 'bi-magic']] as $key => [$label, $icon])
                    <button type="button" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-gradient-to-r from-rose-500 to-orange-500 text-white shadow' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800'"
                            class="py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-1.5">
                        <i class="bi {{ $icon }}"></i> {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-5">

            {{-- ===== PREÇOS ===== --}}
            <div x-show="tab === 'precos'" class="space-y-3">
                <div class="set-card">
                    <div class="set-head"><span class="set-icon bg-emerald-500"><i class="bi bi-shield-check"></i></span>
                        <div><h4>Lucro mínimo</h4><p>A promoção nunca fica abaixo do custo + esta margem.</p></div></div>
                    <div class="set-row">
                        @foreach([5, 10, 15, 20, 30] as $v)
                            <button type="button" wire:click="$set('settingsForm.min_margin_percent', '{{ $v }},00')" class="promo-chip {{ abs($margin - $v) < 0.001 ? 'promo-chip-active' : '' }}">{{ $v }}%</button>
                        @endforeach
                        <label class="set-input"><input type="text" inputmode="decimal" wire:model.live.debounce.400ms="settingsForm.min_margin_percent"><span>%</span></label>
                    </div>
                    <p class="set-example"><i class="bi bi-info-circle"></i> Produto que custa R$ 100,00 sai por no mínimo <b>R$ {{ number_format(100 * (1 + $margin / 100), 2, ',', '.') }}</b>.</p>
                    @error('settingsForm.min_margin_percent') <p class="set-error">{{ $message }}</p> @enderror
                </div>

                <div class="set-card">
                    <div class="set-head"><span class="set-icon bg-rose-500"><i class="bi bi-percent"></i></span>
                        <div><h4>Desconto padrão</h4><p>Usado ao escolher produto sem preço de tabela.</p></div></div>
                    <div class="set-row">
                        @foreach([5, 10, 15, 20, 25, 30] as $v)
                            <button type="button" wire:click="$set('settingsForm.default_discount', '{{ $v }},00')" class="promo-chip {{ abs($num($f['default_discount'] ?? 0) - $v) < 0.001 ? 'promo-chip-active' : '' }}">{{ $v }}%</button>
                        @endforeach
                        <label class="set-input"><input type="text" inputmode="decimal" wire:model.live.debounce.400ms="settingsForm.default_discount"><span>%</span></label>
                    </div>
                    @error('settingsForm.default_discount') <p class="set-error">{{ $message }}</p> @enderror
                </div>

                <div class="set-card">
                    <div class="set-head"><span class="set-icon bg-violet-500"><i class="bi bi-123"></i></span>
                        <div><h4>Final do preço</h4><p>Arredonda o preço da promoção para baixo, sem passar do mínimo.</p></div></div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach(\App\Models\PromotionSetting::PRICE_ENDINGS as $key => [$label, $example])
                            <button type="button" wire:click="$set('settingsForm.price_ending', '{{ $key }}')"
                                    class="set-option {{ ($f['price_ending'] ?? 'none') === $key ? 'set-option-on' : '' }}">
                                <span class="text-[11px] font-semibold">{{ $label }}</span>
                                <span class="text-sm font-black">{{ $example }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="set-card">
                        <div class="set-head"><span class="set-icon bg-sky-500"><i class="bi bi-calendar-event"></i></span>
                            <div><h4>Validade padrão</h4><p>Dias até a promoção acabar.</p></div></div>
                        <div class="set-row">
                            @foreach([3, 7, 15, 30] as $v)
                                <button type="button" wire:click="$set('settingsForm.default_days', {{ $v }})" class="promo-chip {{ (int) ($f['default_days'] ?? 0) === $v ? 'promo-chip-active' : '' }}">{{ $v }}d</button>
                            @endforeach
                            <button type="button" wire:click="$set('settingsForm.default_days', '')" class="promo-chip {{ ($f['default_days'] ?? '') === '' || $f['default_days'] === null ? 'promo-chip-active' : '' }}" title="Até acabar o estoque">∞</button>
                        </div>
                        @error('settingsForm.default_days') <p class="set-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="set-card">
                        <div class="set-head"><span class="set-icon bg-amber-500"><i class="bi bi-lightbulb"></i></span>
                            <div><h4>Sugestões</h4><p>Sugerir produtos com desconto de tabela a partir de:</p></div></div>
                        <label class="set-input w-28"><input type="text" inputmode="decimal" wire:model="settingsForm.suggest_min_discount"><span>%</span></label>
                        @error('settingsForm.suggest_min_discount') <p class="set-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ===== MENSAGEM ===== --}}
            <div x-show="tab === 'mensagem'" x-cloak class="space-y-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Escolha um modelo</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach(\App\Models\PromotionSetting::TEMPLATES as $key => [$label, $icon, $text])
                            <button type="button" wire:click="useTemplate('{{ $key }}')"
                                    class="set-option {{ $currentTemplate === trim($text) ? 'set-option-on' : '' }}">
                                <i class="bi {{ $icon }} text-lg"></i>
                                <span class="text-[11px] font-semibold leading-tight">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-3">
                    <div x-data="{
                            insert(v) {
                                const el = this.$refs.tpl; const a = el.selectionStart ?? el.value.length; const b = el.selectionEnd ?? a;
                                el.value = el.value.slice(0, a) + v + el.value.slice(b);
                                el.focus(); el.selectionStart = el.selectionEnd = a + v.length;
                                el.dispatchEvent(new Event('input'));
                            }
                         }">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200"><i class="bi bi-pencil-square text-rose-500"></i> Texto</span>
                            <button type="button" wire:click="resetTemplate" class="text-[11px] text-rose-600 font-semibold hover:underline"><i class="bi bi-arrow-counterclockwise"></i> Padrão</button>
                        </div>
                        <textarea x-ref="tpl" wire:model.live.debounce.500ms="settingsForm.message_template" rows="8" class="set-textarea font-mono"></textarea>
                        @error('settingsForm.message_template') <p class="set-error">{{ $message }}</p> @enderror
                        <div class="mt-1.5 flex flex-wrap gap-1">
                            @foreach(\App\Models\PromotionSetting::VARIABLES as $var => $label)
                                <button type="button" @click="insert('{{ $var }}')" class="set-var" title="{{ $label }}">{{ $label }}</button>
                            @endforeach
                        </div>
                        <p class="text-[10.5px] text-slate-400 mt-1.5">Toque num item para colocar no texto. *texto* = negrito, ~texto~ = riscado.</p>
                    </div>

                    <div>
                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1"><i class="bi bi-whatsapp text-green-500"></i> Como fica</div>
                        <div class="set-wa">
                            <div class="set-wa-bubble">{!! nl2br($preview) !!}@if($footerText)<br><br>{!! nl2br(e($footerText)) !!}@endif @if($f['footer_catalog_link'] ?? false)<br><span class="text-sky-600">Veja mais no catálogo: seusite.com/catalogo</span>@endif</div>
                        </div>
                    </div>
                </div>

                <div class="set-card">
                    <div class="set-head"><span class="set-icon bg-slate-500"><i class="bi bi-pen"></i></span>
                        <div><h4>Assinatura</h4><p>Vai no fim de toda mensagem.</p></div></div>
                    <textarea wire:model.live.debounce.500ms="settingsForm.footer" rows="2" placeholder="Ex.: Ana · Pix, cartão ou parcelado" class="set-textarea"></textarea>
                    <label class="set-toggle mt-2">
                        <input type="checkbox" wire:model.live="settingsForm.footer_catalog_link" class="sr-only peer">
                        <span class="set-switch"></span>
                        <span><i class="bi bi-link-45deg"></i> Incluir o link do meu catálogo</span>
                    </label>
                </div>
            </div>

            {{-- ===== AUTOMÁTICO ===== --}}
            <div x-show="tab === 'auto'" x-cloak class="space-y-3">
                <label class="set-card set-toggle-card">
                    <span class="set-icon bg-orange-500"><i class="bi bi-box-seam"></i></span>
                    <span class="flex-1"><b>Encerrar quando o estoque zerar</b><small>A promoção sai do ar sozinha quando o produto acaba.</small></span>
                    <input type="checkbox" wire:model.live="settingsForm.auto_end_out_of_stock" class="sr-only peer">
                    <span class="set-switch"></span>
                </label>
                <label class="set-card set-toggle-card">
                    <span class="set-icon bg-pink-500"><i class="bi bi-emoji-smile"></i></span>
                    <span class="flex-1"><b>Cumprimentar o cliente</b><small>Ao enviar para um cliente, começa com "Oi, Maria! 💜".</small></span>
                    <input type="checkbox" wire:model.live="settingsForm.greet_client" class="sr-only peer">
                    <span class="set-switch"></span>
                </label>
                <div class="set-card set-toggle-card">
                    <span class="set-icon bg-amber-500"><i class="bi bi-file-earmark-pdf"></i></span>
                    <span class="flex-1"><b>Buscar preço de tabela</b><small>Relê os PDFs dos uploads antigos e preenche o "de" dos produtos.</small></span>
                    <button type="button" wire:click="backfillOriginalPrices" wire:loading.attr="disabled" wire:target="backfillOriginalPrices" class="px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold whitespace-nowrap">
                        <span wire:loading.remove wire:target="backfillOriginalPrices"><i class="bi bi-search"></i> Buscar</span>
                        <span wire:loading wire:target="backfillOriginalPrices">Lendo…</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex gap-2 px-5 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800">
            <button type="button" wire:click="$set('showSettingsModal', false)" class="flex-1 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold">Cancelar</button>
            <button type="button" wire:click="saveSettings" wire:loading.attr="disabled" wire:target="saveSettings" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-rose-500 via-pink-500 to-orange-500 text-white font-bold shadow-lg shadow-rose-500/30"><i class="bi bi-check2-circle"></i> Salvar</button>
        </div>
    </div>
</div>

<style>
    .set-card { border-radius: 1rem; padding: .85rem; background: rgba(248,250,252,.9); border: 1px solid rgba(226,232,240,.9); }
    .dark .set-card { background: rgba(15,23,42,.5); border-color: rgba(51,65,85,.8); }
    .set-head { display: flex; gap: .65rem; align-items: flex-start; margin-bottom: .6rem; }
    .set-head h4 { font-size: .88rem; font-weight: 800; color: rgb(30 41 59); line-height: 1.2; }
    .set-head p { font-size: .72rem; color: rgb(100 116 139); margin-top: .1rem; }
    .dark .set-head h4 { color: #fff; }
    .set-icon { width: 2.1rem; height: 2.1rem; flex-shrink: 0; border-radius: .7rem; display: inline-flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,.12); }
    .set-row { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
    .set-input { display: inline-flex; align-items: center; border-radius: .75rem; border: 1px solid rgba(203,213,225,.9); background: #fff; overflow: hidden; }
    .set-input input { width: 4.2rem; padding: .4rem .5rem; border: 0; background: transparent; font-size: .85rem; font-weight: 700; text-align: right; color: rgb(30 41 59); }
    .set-input input:focus { outline: none; box-shadow: none; }
    .set-input span { padding: 0 .55rem 0 0; font-size: .8rem; color: rgb(148 163 184); font-weight: 700; }
    .dark .set-input { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .dark .set-input input { color: rgb(226 232 240); }
    .set-example { margin-top: .55rem; font-size: .72rem; color: rgb(5 150 105); }
    .set-error { margin-top: .35rem; font-size: .72rem; font-weight: 600; color: rgb(220 38 38); }
    .set-option { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .2rem; padding: .6rem .4rem; border-radius: .9rem; text-align: center;
        background: #fff; border: 1.5px solid rgba(226,232,240,.95); color: rgb(71 85 105); transition: all .15s; min-height: 3.6rem; }
    .set-option:hover { border-color: rgba(244,63,94,.5); color: rgb(225 29 72); }
    .set-option-on { border-color: transparent !important; color: #fff !important; background: linear-gradient(135deg, #f43f5e, #ec4899, #f97316) !important; box-shadow: 0 6px 16px rgba(244,63,94,.3); }
    .dark .set-option { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(203 213 225); }
    .set-textarea { width: 100%; padding: .6rem .7rem; border-radius: .9rem; font-size: .8rem; border: 1px solid rgba(203,213,225,.9); background: #fff; color: rgb(30 41 59); }
    .set-textarea:focus { outline: none; border-color: #f43f5e; box-shadow: 0 0 0 3px rgba(244,63,94,.15); }
    .dark .set-textarea { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(226 232 240); }
    .set-var { padding: .2rem .55rem; border-radius: 999px; font-size: .68rem; font-weight: 600; background: rgba(244,63,94,.1); color: rgb(190 18 60); border: 1px solid rgba(244,63,94,.2); }
    .set-var:hover { background: rgba(244,63,94,.2); }
    .dark .set-var { color: rgb(253 164 175); }
    .set-wa { border-radius: 1rem; padding: .8rem; min-height: 12rem; background: #efeae2 radial-gradient(rgba(0,0,0,.04) 1px, transparent 1px) 0 0 / 14px 14px; }
    .dark .set-wa { background-color: #0b141a; }
    .set-wa-bubble { position: relative; max-width: 95%; margin-left: auto; padding: .55rem .7rem; border-radius: .8rem .2rem .8rem .8rem; background: #d9fdd3; color: #111b21; font-size: .8rem; line-height: 1.35; box-shadow: 0 1px 1px rgba(0,0,0,.1); word-break: break-word; }
    .dark .set-wa-bubble { background: #005c4b; color: #e9edef; }
    .set-toggle { display: flex; align-items: center; gap: .6rem; font-size: .8rem; color: rgb(71 85 105); cursor: pointer; }
    .dark .set-toggle { color: rgb(203 213 225); }
    .set-toggle-card { display: flex; align-items: center; gap: .75rem; cursor: pointer; }
    .set-toggle-card b { display: block; font-size: .86rem; color: rgb(30 41 59); }
    .set-toggle-card small { display: block; font-size: .72rem; color: rgb(100 116 139); margin-top: .1rem; }
    .dark .set-toggle-card b { color: #fff; }
    .set-switch { position: relative; width: 2.6rem; height: 1.5rem; flex-shrink: 0; border-radius: 999px; background: rgb(203 213 225); transition: background .2s; }
    .set-switch::after { content: ''; position: absolute; top: .18rem; left: .18rem; width: 1.14rem; height: 1.14rem; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
    .peer:checked + .set-switch { background: linear-gradient(135deg, #f43f5e, #f97316); }
    .peer:checked + .set-switch::after { transform: translateX(1.1rem); }
    .dark .set-switch { background: rgb(71 85 105); }
</style>
