{{-- Amostra do menu de variações aberto, com as cores escolhidas. --}}
@php [$tn, $c1, $c2] = \App\Services\Portal\ProductPageService::VARIANT_THEMES[$theme] ?? \App\Services\Portal\ProductPageService::VARIANT_THEMES['aurora']; @endphp
@include('portal.partials.variants-dropdown-styles')
<div class="rounded-2xl bg-slate-100 dark:bg-slate-800/60 p-4" data-testid="variants-demo">
    <small class="block text-[11px] font-semibold text-slate-500 mb-2"><i class="bi bi-eye"></i> Assim o cliente vê (aberto)</small>
    <div class="pp-vd open" style="--c1: {{ $c1 }}; --c2: {{ $c2 }}; margin: 0 0 272px; pointer-events: none">
        <div class="pp-vd-btn">
            <span class="pp-vd-av"><i class="bi bi-layers"></i></span>
            <span class="pp-vd-txt"><small>Tamanho</small><b>90 cm</b></span>
            <span class="pp-vd-chev"><i class="bi bi-chevron-down"></i></span>
        </div>
        <div class="pp-vd-panel">
            @foreach([['60 cm', 'R$ 890,00', false, false], ['90 cm', 'R$ 1.290,00', true, false], ['120 cm', 'R$ 1.590,00', false, false], ['150 cm', 'Esgotado', false, true]] as [$n, $pr, $on, $off])
                <div class="pp-vd-item {{ $on ? 'on' : '' }} {{ $off ? 'off' : '' }}"><span class="pp-vd-ic"><i class="bi bi-circle-fill"></i></span><span class="pp-vd-name">{{ $n }}</span><span class="pp-vd-price">{{ $pr }}</span><i class="bi bi-check-lg pp-vd-ok"></i></div>
            @endforeach
        </div>
    </div>
</div>
