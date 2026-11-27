{{-- Tabela do grupo: muda custo, venda, estoque e encomenda de várias variações de uma vez --}}
@php
    $gtFamily = collect([$product])->merge($variants)->sortBy(fn ($p) => [$p->is_variation_parent ? 0 : 1, (int) $p->variation_sort, $p->id])->values();
    $gtTokens = $this->groupTokens($gtFamily);
    $gtMarked = array_map('intval', $fvMarked);
    $gtMoney = fn ($v) => (float) str_replace(['.', ','], ['', '.'], (string) $v);
@endphp
<div class="gt" id="tabela-grupo" data-testid="group-table" x-data="{ open: @js($gtOpen) }">
    <button type="button" class="gt-h" @click="open = !open">
        <span class="gt-h-ico"><i class="bi bi-table"></i></span>
        <span class="min-w-0 flex-1 text-left">
            <b>Preço e estoque do grupo</b>
            <small>Mude várias de uma vez: marque por tamanho ou cor e aplique.</small>
        </span>
        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
    </button>
    <div x-show="open" x-collapse x-cloak>
        <div class="gt-tools">
            <div class="gt-marks">
                <span class="gt-lbl">Marcar:</span>
                <button type="button" wire:click="fvMarkAll(true)" class="gt-chip">Todas</button>
                @foreach($gtTokens as $tk)
                    <button type="button" wire:click="fvMarkToken(@js($tk))" class="gt-chip is-tk" wire:key="gtk-{{ md5($tk) }}">{{ $tk }}</button>
                @endforeach
                <button type="button" wire:click="fvMarkAll(false)" class="gt-chip is-off {{ $gtMarked ? '' : 'hidden' }}"><i class="bi bi-x"></i>Nenhuma</button>
            </div>
            @if($gtMarked)
                <div class="gt-bulk" data-testid="group-bulk">
                    <b>{{ count($gtMarked) }} {{ count($gtMarked) === 1 ? 'marcada' : 'marcadas' }}</b>
                    <label><span>Custo</span><input type="text" inputmode="decimal" wire:model="fvBulkCost" class="ep-input" placeholder="R$"></label>
                    <label><span>Venda</span><input type="text" inputmode="decimal" wire:model="fvBulkSale" class="ep-input" placeholder="R$"></label>
                    <label><span>Somar na venda</span><input type="text" inputmode="decimal" wire:model="fvBulkAdd" class="ep-input" placeholder="+30 ou -10"></label>
                    <label><span>Estoque</span><input type="number" min="0" wire:model="fvBulkStock" class="ep-input" placeholder="un."></label>
                    <div class="gt-bulk-b">
                        <button type="button" wire:click="fvApplyBulk" class="vx-btn is-primary" data-testid="group-apply"><i class="bi bi-check2-all"></i>Aplicar</button>
                        <button type="button" wire:click="fvApplyBulk('on')" class="vx-btn gt-soft" title="Marcar como sob encomenda"><i class="bi bi-hammer"></i>Encomenda</button>
                        <button type="button" wire:click="fvApplyBulk('off')" class="vx-btn gt-soft" title="Tirar sob encomenda"><i class="bi bi-box"></i>Pronta entrega</button>
                    </div>
                </div>
            @endif
        </div>

        <div class="gt-list">
            <div class="gt-row gt-row-h"><span></span><span>Variação</span><span>Custo</span><span>Venda</span><span>Estoque</span><span>Encomenda</span><span></span></div>
            @foreach($gtFamily as $vp)
                @continue(! isset($fvRows[$vp->id]))
                @php
                    $r = $fvRows[$vp->id];
                    $on = in_array($vp->id, $gtMarked, true);
                    $low = $gtMoney($r['s']) > 0 && $gtMoney($r['s']) < $gtMoney($r['c']) * 1.05;
                @endphp
                <div class="gt-row {{ $on ? 'is-on' : '' }} {{ $vp->id === $product->id ? 'is-self' : '' }}" wire:key="gtr-{{ $vp->id }}">
                    <button type="button" wire:click="fvToggleRow({{ $vp->id }})" class="gt-ck" aria-label="Marcar"><i class="bi {{ $on ? 'bi-check-square-fill' : 'bi-square' }}"></i></button>
                    <span class="gt-name">
                        <img src="{{ asset('storage/products/' . ($vp->image ?: 'product-placeholder.png')) }}" alt="" loading="lazy">
                        <span class="min-w-0">
                            <b title="{{ $vp->variation_value ?: $vp->name }}">{{ $vp->variation_value ?: ($vp->is_variation_parent ? 'Principal' : $vp->name) }}</b>
                            <small>{{ $vp->product_code }}{{ $vp->id === $product->id ? ' · este' : '' }}</small>
                        </span>
                    </span>
                    <label class="gt-f"><span>Custo</span><input type="text" inputmode="decimal" wire:model.blur="fvRows.{{ $vp->id }}.c" class="ep-input"></label>
                    <label class="gt-f"><span>Venda</span><input type="text" inputmode="decimal" wire:model.blur="fvRows.{{ $vp->id }}.s" class="ep-input {{ $low ? 'is-err' : '' }}" title="{{ $low ? 'Venda perto ou abaixo do custo' : '' }}"></label>
                    <label class="gt-f"><span>Estoque</span><input type="number" min="0" wire:model.blur="fvRows.{{ $vp->id }}.q" class="ep-input"></label>
                    <button type="button" wire:click="$toggle('fvRows.{{ $vp->id }}.m')" class="gt-mto {{ $r['m'] ? 'on' : '' }}" title="Sob encomenda"><i class="bi bi-hammer"></i><span>{{ $r['m'] ? 'Sim' : 'Não' }}</span></button>
                    @if($vp->id !== $product->id)
                        <a href="{{ route('products.edit', $vp) }}" class="gt-open" title="Abrir"><i class="bi bi-pencil-square"></i></a>
                    @else
                        <span class="gt-open"></span>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="gt-foot">
            <button type="button" wire:click="saveGroup" wire:loading.attr="disabled" wire:target="saveGroup" class="vx-btn is-primary" data-testid="group-save">
                <i class="bi bi-check2-circle" wire:loading.remove wire:target="saveGroup"></i><i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="saveGroup"></i>Salvar o grupo
            </button>
            <span class="fv-hint">Grava custo, venda, estoque e encomenda de todas as linhas.</span>
        </div>
    </div>
</div>
