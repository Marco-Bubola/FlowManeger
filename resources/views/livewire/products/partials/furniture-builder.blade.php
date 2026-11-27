{{-- Loja de móveis: monta as variações tamanho × cor da ferragem × estofado × kit, como o robô da Stylo --}}
@php
    $fvFrameItems = $this->fvItems('ferragem');
    $fvFabricItems = $this->fvItems('estofado');
    $fvByMaterial = $fvFabricItems->groupBy(fn ($it) => $it->material ?: 'Estofado');
    $fvCombos = $this->fvCombos();
    $fvInGroup = $variants->count() + 1;
    $fvTaken = collect([$product])->merge($variants)->pluck('variation_value')->filter()->map(fn ($v) => mb_strtolower($v))->all();
    $fvNew = collect($fvCombos)->reject(fn ($c) => in_array(mb_strtolower($c['v']), $fvTaken, true))->count();
    $fvTooMany = $fvNew + count($fvTaken) > \App\Livewire\Products\EditProduct::FV_MAX;
    $fvM = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $fvFrameSel = array_map('intval', $fvFrames);
    $fvFabricSel = array_map('intval', $fvFabrics);
@endphp
<div class="fv" id="montar-variacoes" data-testid="furniture-builder">
    <div class="fv-head">
        <span class="fv-head-ico"><i class="bi bi-magic"></i></span>
        <div class="min-w-0 flex-1">
            <p class="fv-t">Montar variações do móvel</p>
            <p class="fv-s">Marque os tamanhos, as cores e os estofados. Cada combinação vira uma variação com estoque próprio, ligada a este produto.</p>
        </div>
        @if($fvSizes || $fvFrames || $fvFabrics || trim($fvFrameText) !== '')
            <button type="button" wire:click="fvClear" class="fv-link"><i class="bi bi-eraser"></i>Limpar</button>
        @endif
    </div>

    <div class="fv-steps">
        {{-- 1. Tamanhos --}}
        <section class="fv-step">
            <header><span class="fv-n">1</span><b>Tamanhos</b><small>Cada tamanho pode ter seu custo e sua venda</small></header>
            <div class="fv-add" x-data>
                <input type="text" wire:model="fvSizeInput" wire:keydown.enter.prevent="fvAddSize" class="ep-input" placeholder="Ex: 80 cm, 100 cm, 120 cm" data-testid="fv-size-input">
                <button type="button" wire:click="fvAddSize" class="vx-btn is-primary fv-go"><i class="bi bi-plus-lg"></i>Pôr</button>
            </div>
            @if($fvSizes)
                <div class="fv-sizes">
                    <div class="fv-sizes-h"><span>Tamanho</span><span>Custo</span><span>Venda</span><span></span></div>
                    @foreach($fvSizes as $i => $sz)
                        <div class="fv-size" wire:key="fvsz-{{ $i }}-{{ $sz['v'] }}">
                            <span class="fv-size-v"><i class="bi bi-rulers"></i>{{ $sz['v'] }}</span>
                            <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="fvSizes.{{ $i }}.c" class="ep-input" placeholder="Custo {{ number_format((float) $product->price, 2, ',', '.') }}" aria-label="Custo {{ $sz['v'] }}">
                            <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="fvSizes.{{ $i }}.s" class="ep-input" placeholder="Venda {{ number_format((float) $product->price_sale, 2, ',', '.') }}" aria-label="Venda {{ $sz['v'] }}">
                            <button type="button" wire:click="fvRemoveSize({{ $i }})" class="ep-x" title="Tirar"><i class="bi bi-x-lg"></i></button>
                        </div>
                    @endforeach
                    <p class="fv-hint">Vazio usa o custo e a venda deste produto.</p>
                </div>
            @endif
        </section>

        {{-- 2. Cor da ferragem --}}
        <section class="fv-step">
            <header><span class="fv-n">2</span><b>Cor da ferragem</b><small>Pintura do ferro (ou da madeira)</small></header>
            @if($fvFrameItems->isNotEmpty())
                <div class="fv-dots">
                    @foreach($fvFrameItems as $it)
                        @php $on = in_array($it->id, $fvFrameSel, true); @endphp
                        <button type="button" wire:click="fvToggle('fvFrames', {{ $it->id }})" class="fv-dot {{ $on ? 'on' : '' }}" wire:key="fvfr-{{ $it->id }}" title="{{ $it->label() }}">
                            <span class="sw" style="{{ $it->image ? "background-image:url('" . $it->imageUrl() . "');background-size:cover" : 'background:' . ($it->hex ?: '#e2e8f0') }}"><i class="bi {{ $on ? 'bi-check-lg' : '' }}"></i></span>
                            <span class="nm">{{ $it->name }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
            <input type="text" wire:model.live.debounce.500ms="fvFrameText" class="ep-input mt-2" placeholder="{{ $fvFrameItems->isEmpty() ? 'Ex: Preto, Branco, Dourado' : 'Outras cores (opcional): Bronze, Cobre' }}">
        </section>

        {{-- 3. Estofado --}}
        <section class="fv-step fv-step-wide">
            <header><span class="fv-n">3</span><b>Estofado</b><small>Do catálogo Estofados e cores</small></header>
            @if($fvFabricItems->isNotEmpty())
                <div class="fv-mode" role="radiogroup">
                    <button type="button" wire:click="$set('fvFabricMode', 'variation')" class="{{ $fvFabricMode === 'variation' ? 'on' : '' }}" data-testid="fv-fabric-variation">
                        <i class="bi bi-diagram-3"></i><span><b>Vira variação</b><small>Cada cor com estoque próprio</small></span>
                    </button>
                    <button type="button" wire:click="$set('fvFabricMode', 'choice')" class="{{ $fvFabricMode === 'choice' ? 'on' : '' }}" data-testid="fv-fabric-choice">
                        <i class="bi bi-hand-index-thumb"></i><span><b>Cliente escolhe no portal</b><small>Sob encomenda, sem criar variações</small></span>
                    </button>
                </div>
                @foreach($fvByMaterial as $material => $items)
                    @php $allOn = $items->every(fn ($it) => in_array($it->id, $fvFabricSel, true)); @endphp
                    <div class="fv-mat" wire:key="fvmat-{{ \Illuminate\Support\Str::slug($material) }}">
                        <div class="fv-mat-h">
                            <b>{{ $material }}</b>
                            <small>{{ $items->count() }} {{ $items->count() === 1 ? 'cor' : 'cores' }}{{ $items->max('extra_price') > 0 ? ' · +' . $fvM($items->max('extra_price')) : '' }}</small>
                            <button type="button" wire:click="fvToggleMaterial(@js($material))" class="fv-link">{{ $allOn ? 'Desmarcar' : 'Marcar' }} todos</button>
                        </div>
                        <div class="fv-dots">
                            @foreach($items as $it)
                                @php $on = in_array($it->id, $fvFabricSel, true); @endphp
                                <button type="button" wire:click="fvToggle('fvFabrics', {{ $it->id }})" class="fv-dot {{ $on ? 'on' : '' }}" wire:key="fvfb-{{ $it->id }}" title="{{ $it->label() }}{{ $it->code ? ' · ' . $it->code : '' }}" data-testid="fv-fabric">
                                    <span class="sw" style="{{ $it->image ? "background-image:url('" . $it->imageUrl() . "');background-size:cover" : 'background:' . ($it->hex ?: '#e2e8f0') }}"><i class="bi {{ $on ? 'bi-check-lg' : '' }}"></i></span>
                                    <span class="nm">{{ $it->name }}<em>{{ $it->code }}</em></span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @if($fvFabricMode === 'variation' && $fvFabricItems->max('extra_price') > 0)
                    <label class="fv-check"><input type="checkbox" wire:model.live="fvFabricExtra" class="w-4 h-4 accent-indigo-600"> Somar o valor a mais do tecido na venda</label>
                @endif
            @else
                <div class="fv-empty">
                    <i class="bi bi-palette"></i>
                    <span>Cadastre seus corinos e camurças uma vez (com a bolinha da cor) e eles aparecem aqui para todos os produtos.</span>
                    <a href="{{ route('orcamentos.choices') }}" target="_blank" class="vx-btn is-primary"><i class="bi bi-plus-lg"></i>Criar catálogo</a>
                </div>
            @endif
        </section>

        {{-- 4. Estoque, encomenda e kits --}}
        <section class="fv-step fv-step-wide">
            <header><span class="fv-n">4</span><b>Estoque e kits</b><small>Para todas as que vão ser criadas</small></header>
            <div class="fv-opts">
                <label class="fv-toggle {{ $fvMadeToOrder ? 'on' : '' }}">
                    <input type="checkbox" wire:model.live="fvMadeToOrder" class="sr-only">
                    <span class="fv-sw"><span></span></span>
                    <span><b>Sob encomenda</b><small>Estoque 0, vende mesmo assim</small></span>
                </label>
                @unless($fvMadeToOrder)
                    <div>
                        <label class="ep-label"><span>Estoque de cada</span></label>
                        <input type="number" min="0" wire:model.blur="fvStock" class="ep-input">
                    </div>
                @endunless
                <div>
                    <label class="ep-label"><span>Kits (cadeiras)</span></label>
                    <input type="text" wire:model.live.debounce.500ms="fvKits" class="ep-input" placeholder="Ex: 2, 4">
                </div>
                @if(trim($fvKits) !== '')
                    <div>
                        <label class="ep-label"><span>Desconto no kit %</span></label>
                        <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="fvKitDiscount" class="ep-input" placeholder="Ex: 5">
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- Prévia --}}
    @if($fvCombos)
        <div wire:key="fv-prev" class="fv-prev {{ $fvTooMany ? 'is-bad' : '' }}">
            <div class="fv-prev-h">
                <b>{{ count($fvCombos) }} {{ count($fvCombos) === 1 ? 'combinação' : 'combinações' }}</b>
                @if($fvNew < count($fvCombos))<small>{{ count($fvCombos) - $fvNew }} já {{ count($fvCombos) - $fvNew === 1 ? 'existe' : 'existem' }} no grupo</small>@endif
                @if($fvTooMany)
                    <small class="bad"><i class="bi bi-exclamation-triangle"></i>Passa de {{ \App\Livewire\Products\EditProduct::FV_MAX }} no grupo. {{ $fvFabricMode === 'variation' && $fvFabrics ? 'Deixe o estofado como escolha no portal.' : 'Marque menos.' }}</small>
                @endif
            </div>
            <div class="fv-prev-list">
                @foreach(array_slice($fvCombos, 0, 60) as $c)
                    @php $dup = in_array(mb_strtolower($c['v']), $fvTaken, true); @endphp
                    <div class="fv-pc {{ $dup ? 'is-dup' : '' }}" wire:key="fvpc-{{ md5($c['v']) }}" title="{{ $c['v'] }}">
                        <span class="fv-pc-dots">{!! $c['dots'] ? collect($c['dots'])->map(fn ($d) => '<span style="' . e($d) . '"></span>')->implode('') : '<i class="bi bi-rulers"></i>' !!}</span>
                        <span class="fv-pc-n">{{ $c['v'] }}</span>
                        <span class="fv-pc-p {{ $c['s'] > 0 && $c['s'] < $c['c'] * 1.05 ? 'is-low' : '' }}">{{ $fvM($c['s']) }}</span>
                    </div>
                @endforeach
            </div>
            @if(count($fvCombos) > 60)<p class="fv-more">+{{ count($fvCombos) - 60 }} combinações</p>@endif
        </div>
    @endif
    @error('fvSizes')<p class="ep-err" wire:key="fv-err">{{ $message }}</p>@enderror

    <div class="fv-foot">
        @php $fvChoiceOnly = ! $fvCombos && $fvFabricMode === 'choice' && $fvFabrics; @endphp
        <button type="button" wire:click="fvCreate" wire:loading.attr="disabled" wire:target="fvCreate" class="vx-btn is-primary fv-go" @disabled((! $fvNew && ! $fvChoiceOnly && ! ($fvFabricMode === 'choice' && $fvFabrics)) || $fvTooMany) data-testid="fv-create">
            <i class="bi bi-magic" wire:loading.remove wire:target="fvCreate"></i><i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="fvCreate"></i>
            @if($fvNew)
                Criar {{ $fvNew }} {{ $fvNew === 1 ? 'variação' : 'variações' }}{{ $fvFabricMode === 'choice' && $fvFabrics ? ' e ligar o estofado' : '' }}
            @elseif($fvFabricMode === 'choice' && $fvFabrics)
                Ligar o estofado no portal
            @else
                Criar variações
            @endif
        </button>
        <span class="fv-hint">
            @if($fvFabricMode === 'choice' && $fvFabrics)
                {{ count($fvFabricSel) }} {{ count($fvFabricSel) === 1 ? 'estofado aparece' : 'estofados aparecem' }} para o cliente escolher na página do produto.
            @else
                Descrição, ficha, embalagem e foto vêm deste produto. Kit multiplica custo, venda e peso.
            @endif
        </span>
    </div>
</div>
