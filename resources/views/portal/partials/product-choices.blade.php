{{-- Listas de escolha do produto (cor da ferragem, estofado...) do catálogo de estofados e cores. Sob encomenda. --}}
@if(! empty($choices))
<div class="pp-ch" id="escolhas" style="--c1: {{ $variants['c1'] }}; --c2: {{ $variants['c2'] }}" data-testid="choices">
    <template x-for="l in ch" :key="l.id">
        <div class="pp-ch-list" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.stop="open = false"
             :class="{ open, miss: chErr && !sel[l.id] }" data-testid="choice-list">
            <span class="pp-ch-label" x-text="l.label"></span>
            <button type="button" class="pp-ch-btn" @click="open = !open" :aria-expanded="open" aria-haspopup="listbox" data-testid="choice-open">
                <span class="pp-ch-sw" :style="chSw(chPicked(l))"><i class="fas fa-palette" x-show="!chPicked(l)"></i></span>
                <span class="pp-ch-val">
                    <b x-text="chPicked(l) ? chPicked(l).label : 'Escolha ' + l.label.toLowerCase()"></b>
                    <small x-show="chPicked(l) && chPicked(l).x > 0" x-text="'+ ' + money(chPicked(l)?.x || 0)"></small>
                    <small x-show="!chPicked(l)" x-text="l.options.length + (l.options.length === 1 ? ' opção' : ' opções')"></small>
                </span>
                <i class="fas fa-chevron-down pp-ch-chev"></i>
            </button>
            <div class="pp-ch-panel" role="listbox" :aria-label="l.label" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                <template x-for="(o, k) in l.options" :key="o.id">
                    <div>
                        <p class="pp-ch-group" x-show="l.grouped && (k === 0 || l.options[k - 1].m !== o.m)">
                            <span x-text="o.m || 'Outros'"></span>
                            <em x-show="o.x > 0" x-text="'+ ' + money(o.x)"></em>
                        </p>
                        <button type="button" class="pp-ch-opt" role="option" :aria-selected="sel[l.id] === o.id" :class="{ on: sel[l.id] === o.id }"
                                @click="sel[l.id] = o.id; open = false" data-testid="choice-option">
                            <span class="pp-ch-sw" :style="chSw(o)"></span>
                            <span class="pp-ch-name" x-text="l.grouped ? o.n : o.label"></span>
                            <span class="pp-ch-x" x-text="o.x > 0 ? '+ ' + money(o.x) : ''"></span>
                            <i class="fas fa-check pp-ch-ok"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>
    <p class="pp-ch-tot" x-show="chExtra() > 0" x-cloak data-testid="choices-total">
        Com as escolhas: <b x-text="money(p.price + chExtra())"></b> <small x-text="'(+ ' + money(chExtra()) + ')'"></small>
    </p>
    <p class="pp-ch-hint" x-show="chErr && chMissing().length" x-cloak data-testid="choices-missing">
        <i class="fas fa-hand-pointer"></i> <span x-text="'Escolha ' + chMissing().map(l => l.label.toLowerCase()).join(' e ') + ' para continuar'"></span>
    </p>
    <div class="pp-ch-mix" x-show="chOther()" x-cloak data-testid="choices-other">
        <i class="fas fa-triangle-exclamation"></i>
        <span>No carrinho esta peça está com <b x-text="chOther() ? $store.cart.optsText(chOther()) : ''"></b>. A loja recomenda tudo da mesma cor.
            <button type="button" @click="chCopy(chOther())">Usar as mesmas</button></span>
    </div>
    <p class="pp-ch-note"><i class="fas fa-clock"></i> Feito sob encomenda com as cores que você escolher.</p>
</div>
@once
<style>
    .pp-ch { margin-top: 16px; display: flex; flex-direction: column; gap: 12px; }
    .pp-ch-list { position: relative; z-index: 5; }
    .pp-ch-list.open { z-index: 40; }
    .pp-ch-label { display: block; font-size: 13px; font-weight: 700; color: var(--ml-text); margin: 0 0 6px 2px; }
    .pp-ch-btn { width: 100%; display: flex; align-items: center; gap: 12px; min-height: 58px; padding: 7px 14px 7px 8px; border-radius: 16px; cursor: pointer; text-align: left;
        border: 1.5px solid var(--ml-line); background: var(--ml-card, #fff); color: var(--ml-text); transition: border-color .15s, box-shadow .15s; }
    .pp-ch-btn:hover { border-color: color-mix(in srgb, var(--c1) 55%, var(--ml-line)); }
    .pp-ch-list.open .pp-ch-btn { border-color: var(--c1); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c1) 18%, transparent); }
    .pp-ch-list.miss .pp-ch-btn { border-color: #e11d48; box-shadow: 0 0 0 3px rgba(225,29,72,.15); animation: pp-ch-shake .35s; }
    @keyframes pp-ch-shake { 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }
    .pp-ch-sw { flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--c1);
        background: color-mix(in srgb, var(--c1) 12%, #fff); background-size: cover; background-position: center; box-shadow: inset 0 0 0 1px rgba(0,0,0,.12), 0 1px 4px rgba(0,0,0,.12); }
    .pp-ch-val { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.25; }
    .pp-ch-val b { font-size: 15px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pp-ch-val small { font-size: 12.5px; color: var(--ml-muted); font-weight: 600; }
    .pp-ch-chev { flex-shrink: 0; color: var(--ml-muted); transition: transform .2s; }
    .pp-ch-list.open .pp-ch-chev { transform: rotate(180deg); color: var(--c1); }
    .pp-ch-panel { position: absolute; left: 0; right: 0; top: calc(100% + 6px); padding: 6px; border-radius: 16px; background: var(--ml-card, #fff); border: 1px solid var(--ml-line);
        box-shadow: 0 22px 44px -16px rgba(0,0,0,.35); max-height: min(58vh, 420px); overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; }
    .pp-ch-group { display: flex; align-items: baseline; gap: 6px; margin: 8px 10px 4px; font-size: 11.5px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--ml-muted); }
    .pp-ch-group em { font-style: normal; text-transform: none; letter-spacing: 0; color: var(--c1); }
    .pp-ch-opt { width: 100%; display: flex; align-items: center; gap: 12px; min-height: 50px; padding: 5px 12px 5px 6px; border: 0; border-radius: 12px; background: transparent; color: var(--ml-text); cursor: pointer; text-align: left; font-size: 15px; }
    .pp-ch-opt .pp-ch-sw { width: 36px; height: 36px; }
    .pp-ch-opt:hover { background: var(--ml-chip); }
    .pp-ch-opt.on { background: color-mix(in srgb, var(--c1) 10%, transparent); font-weight: 700; }
    .pp-ch-name { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pp-ch-x { flex-shrink: 0; font-size: 13px; font-weight: 700; color: var(--ml-muted); }
    .pp-ch-ok { width: 18px; color: var(--c1); opacity: 0; }
    .pp-ch-opt.on .pp-ch-ok { opacity: 1; }
    .pp-ch-tot { margin: 0 2px; font-size: 14px; color: var(--ml-text); }
    .pp-ch-tot small { color: var(--ml-muted); }
    .pp-ch-hint { margin: 0 2px; font-size: 13px; font-weight: 700; color: #e11d48; }
    .pp-ch-mix { display: flex; gap: 8px; align-items: flex-start; padding: 10px 12px; border-radius: 12px; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-size: 13px; line-height: 1.4; }
    .pp-ch-mix i { margin-top: 3px; }
    .pp-ch-mix button { border: 0; background: none; padding: 0; margin-left: 4px; color: #b45309; font-weight: 800; text-decoration: underline; cursor: pointer; }
    .ml-dark .pp-ch-mix { background: rgba(120,53,15,.25); color: #fde68a; border-color: rgba(253,230,138,.3); }
    .pp-ch-note { margin: 0 2px; font-size: 12.5px; color: var(--ml-muted); }
</style>
@endonce
@endif
