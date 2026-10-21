<div class="import-ficha-page">
    <x-product-page-header title="Importar ficha" icon="bi-file-earmark-richtext" active="ficha"
        subtitle="Um produto completo a partir da ficha feita pela IA, com variações e fotos">
        @if($plan)
        <x-slot:meta>
            @php $okRows = collect($plan['rows'])->where('status', 'ok')->count(); @endphp
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-check2-circle"></i>{{ $okRows }} {{ $okRows === 1 ? 'pronto' : 'prontos' }}</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="bi bi-images"></i>{{ count($photos) }} {{ count($photos) === 1 ? 'foto' : 'fotos' }}</span>
        </x-slot:meta>
        @endif
    </x-product-page-header>

    @include('livewire.products.partials.ep-styles')

    @if($result)
    <section class="ep-card if-done mb-4" data-testid="ficha-result">
        <div class="ep-card-h">
            <span class="ep-ico green"><i class="bi bi-check2-all"></i></span>
            <div class="flex-1 min-w-0"><h3>{{ $result['name'] }} cadastrado</h3>
                <p>{{ count($result['items']) }} {{ count($result['items']) === 1 ? 'produto criado' : 'produtos criados' }}@if($result['skipped']) · {{ $result['skipped'] }} já existiam @endif</p></div>
            <button type="button" wire:click="clear" class="ep-btn ep-btn-soft shrink-0" style="padding:.45rem .8rem;font-size:.8rem"><i class="bi bi-plus-lg"></i>Outra ficha</button>
        </div>
        <div class="if-list">
            @foreach($result['items'] as $it)
                <a href="{{ route('products.show', ['productCode' => $it['code'], 'id' => $it['id']]) }}" class="if-row" data-product-id="{{ $it['id'] }}" data-product-code="{{ $it['code'] }}">
                    <span class="if-tag">{{ $it['action'] }}</span>
                    <span class="min-w-0 flex-1"><b class="if-name">{{ $it['name'] }}</b><small>{{ $it['code'] }}</small></span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            @endforeach
        </div>
        @if($result['ml_title'] || $result['ml_link'])
        <div class="ep-card-b if-ml">
            <p class="font-bold"><i class="bi bi-shop"></i> Para o Mercado Livre</p>
            @if($result['ml_title'])<p>Título sugerido: <b>{{ $result['ml_title'] }}</b>@if($result['ml_type']) · {{ $result['ml_type'] === 'premium' ? 'Premium' : 'Clássico' }}@endif</p>@endif
            @if($result['ml_link'])<p>Anúncio de referência: <a href="{{ $result['ml_link'] }}" target="_blank" rel="noopener" class="underline break-all">{{ $result['ml_link'] }}</a></p>@endif
            <p class="ep-hint"><i class="bi bi-info-circle"></i>Abra o produto e use Publicar no ML, ou vincule a um anúncio que já existe em Mercado Livre › Produtos.</p>
        </div>
        @endif
    </section>
    @endif

    <div class="if-grid">
        <section class="ep-card">
            <div class="ep-card-h">
                <span class="ep-ico"><i class="bi bi-file-earmark-text"></i></span>
                <div class="flex-1 min-w-0"><h3>Ficha</h3><p>O arquivo .md, .txt ou .json que a IA fez</p></div>
                <a href="{{ route('products.import-ficha.questionario') }}" class="ep-btn ep-btn-soft shrink-0" style="padding:.45rem .8rem;font-size:.8rem"><i class="bi bi-download"></i>Questionário</a>
            </div>
            <div class="ep-card-b space-y-3">
                <label class="if-drop {{ $fichaName ? 'has-file' : '' }}">
                    <input type="file" id="ficha-file" wire:model="fichaFile" accept=".md,.txt,.json,text/plain,application/json" class="sr-only">
                    <span wire:loading.remove wire:target="fichaFile">
                        <i class="bi {{ $fichaName ? 'bi-file-earmark-check' : 'bi-file-earmark-arrow-up' }}"></i>
                        <b>{{ $fichaName ?: 'Toque para escolher a ficha' }}</b>
                        <small>{{ $fichaName ? 'Toque para trocar' : 'ou cole o texto da ficha aqui embaixo' }}</small>
                    </span>
                    <span wire:loading.flex wire:target="fichaFile" class="is-loading"><i class="bi bi-arrow-repeat animate-spin"></i><b>Lendo a ficha...</b></span>
                </label>
                @error('fichaFile')<p class="ep-err">{{ $message }}</p>@enderror
                <details class="if-paste" @if($fichaText !== '' && $fichaName === '') open @endif>
                    <summary><i class="bi bi-clipboard"></i> Colar o texto da ficha</summary>
                    <textarea id="ficha-text" wire:model.blur="fichaText" rows="7" class="ep-input mt-2" placeholder="Cole aqui a resposta da IA (com o bloco ```json```)"></textarea>
                </details>
                @if($parseError)
                    <p class="if-alert" data-testid="ficha-error"><i class="bi bi-exclamation-triangle-fill"></i>{{ $parseError }}</p>
                @endif
            </div>
        </section>

        <section class="ep-card">
            <div class="ep-card-h">
                <span class="ep-ico pink"><i class="bi bi-images"></i></span>
                <div class="flex-1 min-w-0"><h3>Fotos</h3><p>Com os mesmos nomes que estão na ficha</p></div>
            </div>
            <div class="ep-card-b space-y-3">
                <label class="if-drop small">
                    <input type="file" id="ficha-photos" wire:model="photoBatch" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">
                    <input type="file" id="ficha-photo" wire:model="photoOne" accept="image/jpeg,image/png,image/webp" class="sr-only" tabindex="-1" aria-hidden="true">
                    <span wire:loading.remove wire:target="photoBatch,photoOne"><i class="bi bi-plus-circle-dotted"></i><b>Adicionar fotos</b><small>JPG, PNG ou WEBP até 4 MB cada</small></span>
                    <span wire:loading.flex wire:target="photoBatch,photoOne" class="is-loading"><i class="bi bi-arrow-repeat animate-spin"></i><b>Enviando...</b></span>
                </label>
                @error('photoBatch.*')<p class="ep-err">{{ $message }}</p>@enderror
                @error('photoOne')<p class="ep-err">{{ $message }}</p>@enderror
                @if(count($photos))
                    @php $missingSet = collect($plan['unused_photos'] ?? []); @endphp
                    <div class="if-thumbs" data-testid="ficha-photo-count" data-count="{{ count($photos) }}">
                        @foreach($photos as $i => $p)
                            @php $pn = $p->getClientOriginalName(); try { $src = $p->temporaryUrl(); } catch (\Throwable) { $src = null; } @endphp
                            <figure class="if-thumb {{ $plan && $missingSet->contains($pn) ? 'unused' : '' }}" wire:key="ph-{{ $i }}-{{ md5($pn) }}">
                                @if($src)<img src="{{ $src }}" alt="">@else<span class="if-noimg"><i class="bi bi-image"></i></span>@endif
                                <figcaption title="{{ $pn }}">{{ $pn }}</figcaption>
                                <button type="button" wire:click="removePhoto({{ $i }})" title="Tirar"><i class="bi bi-x-lg"></i></button>
                            </figure>
                        @endforeach
                    </div>
                @else
                    <p class="ep-hint" data-testid="ficha-photo-count" data-count="0"><i class="bi bi-info-circle"></i>A primeira foto de cada variação vira a principal; as outras vão para a galeria.</p>
                @endif
            </div>
        </section>
    </div>

    @if($plan)
    @php $pr = $plan['product']; @endphp
    <section class="ep-card mt-4" data-testid="ficha-preview" data-errors="{{ count($plan['errors']) }}" data-ok="{{ collect($plan['rows'])->where('status', 'ok')->count() }}">
        <div class="ep-card-h">
            <span class="ep-ico"><i class="bi bi-list-check"></i></span>
            <div class="flex-1 min-w-0"><h3>{{ $pr['name'] ?: '(sem nome)' }}</h3>
                <p>{{ $pr['category'] ?: 'Geral' }}@if($pr['brand']) · {{ $pr['brand'] }}@endif @if($pr['attribute']) · variação por {{ mb_strtolower($pr['attribute']) }}@endif</p></div>
        </div>

        @if($plan['errors'] || $plan['warnings'] || $plan['missing_photos'])
        <div class="ep-card-b space-y-2 pb-0">
            @foreach($plan['errors'] as $e)<p class="if-alert"><i class="bi bi-x-octagon-fill"></i>{{ $e }}</p>@endforeach
            @foreach($plan['warnings'] as $w)<p class="if-warn"><i class="bi bi-exclamation-circle"></i>{{ $w }}</p>@endforeach
            @if($plan['missing_photos'])<p class="if-warn" data-testid="ficha-missing-photos"><i class="bi bi-image"></i>Fotos da ficha que não foram enviadas: {{ implode(', ', $plan['missing_photos']) }}</p>@endif
        </div>
        @endif

        <div class="ep-card-b if-facts">
            <span><i class="bi bi-hammer"></i>{{ $pr['made_to_order'] ? 'Sob encomenda' . ($pr['production_days'] ? ' · ' . $pr['production_days'] . ' dias' : '') : 'Pronta entrega' }}</span>
            <span><i class="bi bi-shield-check"></i>Garantia {{ $pr['warranty_months'] }} {{ $pr['warranty_months'] == 1 ? 'mês' : 'meses' }}</span>
            <span><i class="bi bi-card-checklist"></i>{{ count($pr['specs']) }} itens na ficha técnica</span>
            @if($pr['cost_items'])<span><i class="bi bi-calculator"></i>Ficha de custo R$ {{ number_format(\App\Models\Product::costItemsTotal($pr['cost_items']), 2, ',', '.') }}</span>@endif
            @if($pr['ml_title'])<span><i class="bi bi-shop"></i>ML: {{ $pr['ml_title'] }}</span>@endif
        </div>
        @if($pr['description'])
            <div class="ep-card-b pt-0"><p class="if-desc">{{ \Illuminate\Support\Str::limit($pr['description'], 280) }}</p></div>
        @endif

        <div class="if-list">
            @foreach($plan['rows'] as $k => $r)
                <div class="if-item is-{{ $r['status'] }}" wire:key="row-{{ $k }}">
                    <span class="if-pic">
                        @php $first = $r['photos'][0] ?? null; $obj = $first ? collect($photos)->first(fn ($p) => $p->getClientOriginalName() === $first) : null; @endphp
                        @if($obj)@php try { $u = $obj->temporaryUrl(); } catch (\Throwable) { $u = null; } @endphp @if($u)<img src="{{ $u }}" alt="">@endif @else<i class="bi bi-image"></i>@endif
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="if-name">{{ $r['name'] }}</p>
                        <p class="if-meta">
                            @if($r['code'] !== '')<span><i class="bi bi-upc"></i>{{ $r['code'] }}</span>@endif
                            <span>Custo R$ {{ number_format($r['price'], 2, ',', '.') }}</span>
                            <span class="if-sale">Venda R$ {{ number_format($r['price_sale'], 2, ',', '.') }}</span>
                            <span><i class="bi bi-stack"></i>{{ $r['stock'] }}</span>
                            @if($r['weight_grams'])<span><i class="bi bi-box-seam"></i>{{ rtrim(rtrim(number_format($r['weight_grams'] / 1000, 2, ',', ''), '0'), ',') }} kg</span>@endif
                            @if($r['length_cm'])<span>{{ (float) $r['length_cm'] }}×{{ (float) $r['width_cm'] }}×{{ (float) $r['height_cm'] }} cm</span>@endif
                            <span><i class="bi bi-images"></i>{{ count($r['photos']) }}</span>
                        </p>
                    </div>
                    @if($r['note'] !== '')<span class="if-note">{{ $r['note'] }}</span>@endif
                </div>
            @endforeach
        </div>

        <div class="if-actions">
            <button type="button" wire:click="clear" class="ep-btn ep-btn-ghost">Cancelar</button>
            @php $canImport = !$plan['errors'] && collect($plan['rows'])->contains(fn ($r) => $r['status'] === 'ok'); @endphp
            <button type="button" wire:click="import" wire:loading.attr="disabled" wire:target="import,photoBatch,photoOne,fichaFile" class="ep-btn ep-btn-primary" @disabled(!$canImport) data-testid="ficha-import" data-mobile-save="Cadastrar produto">
                <span wire:loading.remove wire:target="import"><i class="bi bi-check2"></i> Cadastrar produto</span>
                <span wire:loading wire:target="import"><i class="bi bi-arrow-repeat animate-spin"></i> Cadastrando...</span>
            </button>
        </div>
    </section>
    @elseif(!$result)
    <section class="ep-card mt-4">
        <div class="ep-card-h">
            <span class="ep-ico teal"><i class="bi bi-lightbulb"></i></span>
            <div><h3>Como funciona</h3><p>Também é o que o robô de cadastro faz</p></div>
        </div>
        <div class="ep-card-b">
            <ol class="if-steps">
                <li>Baixe o <b>questionário</b> e mande para uma IA junto com as fotos e o que você sabe da peça.</li>
                <li>A IA pergunta o que faltar e devolve a <b>ficha</b> com o bloco JSON.</li>
                <li>Envie a ficha aqui e as <b>fotos</b> com os mesmos nomes. Confira a prévia e toque em <b>Cadastrar produto</b>.</li>
                <li>Depois é só ligar com o <b>Mercado Livre</b>, como na Boticário.</li>
            </ol>
        </div>
    </section>
    @endif

    <style>
        .import-ficha-page .if-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 900px) { .import-ficha-page .if-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: start; } }
        .import-ficha-page .if-drop { display: flex; align-items: center; justify-content: center; text-align: center; min-height: 8rem; padding: 1rem; border-radius: 1.1rem; border: 2px dashed rgba(99,102,241,.4); background: rgba(99,102,241,.05); cursor: pointer; transition: background .15s, border-color .15s; }
        .import-ficha-page .if-drop.small { min-height: 5.5rem; }
        .import-ficha-page .if-drop:hover { background: rgba(99,102,241,.09); }
        .import-ficha-page .if-drop.has-file { border-color: rgba(16,185,129,.55); background: rgba(16,185,129,.06); }
        .import-ficha-page .if-drop > span:not(.is-loading) { display: flex; }
        .import-ficha-page .if-drop > span { flex-direction: column; align-items: center; gap: .3rem; }
        .import-ficha-page .if-drop i { font-size: 1.8rem; color: #6366f1; }
        .import-ficha-page .if-drop.has-file i { color: #10b981; }
        .import-ficha-page .if-drop b { font-size: .95rem; color: #0f172a; word-break: break-all; }
        .dark .import-ficha-page .if-drop b { color: #f1f5f9; }
        .import-ficha-page .if-drop small { font-size: .76rem; color: #64748b; }
        .import-ficha-page .if-paste summary { cursor: pointer; font-size: .82rem; font-weight: 700; color: #4f46e5; }
        .dark .import-ficha-page .if-paste summary { color: #a5b4fc; }
        .import-ficha-page .if-paste textarea { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .78rem; }
        .import-ficha-page .if-alert, .import-ficha-page .if-warn { display: flex; gap: .5rem; align-items: flex-start; padding: .6rem .8rem; border-radius: .85rem; font-size: .83rem; margin: 0; }
        .import-ficha-page .if-alert { background: rgba(244,63,94,.08); color: #be123c; }
        .import-ficha-page .if-warn { background: rgba(245,158,11,.1); color: #b45309; }
        .dark .import-ficha-page .if-alert { color: #fda4af; }
        .dark .import-ficha-page .if-warn { color: #fcd34d; }
        .import-ficha-page .if-thumbs { display: grid; grid-template-columns: repeat(auto-fill, minmax(88px, 1fr)); gap: .6rem; }
        .import-ficha-page .if-thumb { position: relative; margin: 0; border-radius: .9rem; overflow: hidden; background: rgba(148,163,184,.12); border: 1px solid rgba(148,163,184,.25); }
        .import-ficha-page .if-thumb img, .import-ficha-page .if-noimg { width: 100%; aspect-ratio: 1; object-fit: cover; display: flex; align-items: center; justify-content: center; color: #94a3b8; }
        .import-ficha-page .if-thumb figcaption { font-size: .66rem; padding: .25rem .4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #475569; }
        .dark .import-ficha-page .if-thumb figcaption { color: #cbd5e1; }
        .import-ficha-page .if-thumb.unused { outline: 2px solid rgba(245,158,11,.6); }
        .import-ficha-page .if-thumb button { position: absolute; top: .3rem; right: .3rem; width: 1.6rem; height: 1.6rem; border-radius: 999px; background: rgba(15,23,42,.65); color: #fff; font-size: .7rem; display: flex; align-items: center; justify-content: center; }
        .import-ficha-page .if-facts { display: flex; flex-wrap: wrap; gap: .4rem .5rem; padding-top: .75rem; padding-bottom: .5rem; }
        .import-ficha-page .if-facts span { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .65rem; border-radius: 999px; font-size: .75rem; font-weight: 600; background: rgba(99,102,241,.08); color: #4338ca; }
        .dark .import-ficha-page .if-facts span { background: rgba(99,102,241,.16); color: #c7d2fe; }
        .import-ficha-page .if-desc { margin: 0; font-size: .82rem; line-height: 1.5; color: #475569; white-space: pre-line; }
        .dark .import-ficha-page .if-desc { color: #cbd5e1; }
        .import-ficha-page .if-list { display: grid; }
        .import-ficha-page .if-item, .import-ficha-page .if-row { display: flex; align-items: center; gap: .75rem; padding: .7rem 1rem; border-top: 1px solid rgba(148,163,184,.18); }
        .import-ficha-page .if-row { color: inherit; text-decoration: none; }
        .import-ficha-page .if-row:hover { background: rgba(99,102,241,.05); }
        .import-ficha-page .if-pic { width: 3rem; height: 3rem; flex-shrink: 0; border-radius: .75rem; overflow: hidden; display: flex; align-items: center; justify-content: center; background: rgba(148,163,184,.14); color: #94a3b8; }
        .import-ficha-page .if-pic img { width: 100%; height: 100%; object-fit: cover; }
        .import-ficha-page .if-name { margin: 0; font-size: .9rem; font-weight: 700; color: #0f172a; display: block; }
        .dark .import-ficha-page .if-name { color: #f1f5f9; }
        .import-ficha-page .if-row small { font-size: .74rem; color: #64748b; }
        .import-ficha-page .if-tag { flex-shrink: 0; padding: .2rem .55rem; border-radius: 999px; font-size: .68rem; font-weight: 800; text-transform: capitalize; background: rgba(16,185,129,.12); color: #047857; }
        .import-ficha-page .if-meta { margin: .2rem 0 0; display: flex; flex-wrap: wrap; gap: .25rem .6rem; font-size: .74rem; color: #64748b; }
        .import-ficha-page .if-meta i { margin-right: .2rem; }
        .import-ficha-page .if-sale { color: #047857; font-weight: 700; }
        .dark .import-ficha-page .if-sale { color: #6ee7b7; }
        .import-ficha-page .if-note { flex-shrink: 0; padding: .2rem .6rem; border-radius: 999px; font-size: .7rem; font-weight: 800; background: rgba(148,163,184,.18); color: #475569; }
        .import-ficha-page .is-skip { opacity: .6; }
        .import-ficha-page .is-error .if-note { background: rgba(244,63,94,.12); color: #be123c; }
        .dark .import-ficha-page .if-note { color: #cbd5e1; }
        .import-ficha-page .if-ml { border-top: 1px solid rgba(148,163,184,.18); display: grid; gap: .35rem; font-size: .84rem; color: #334155; }
        .dark .import-ficha-page .if-ml { color: #cbd5e1; }
        .import-ficha-page .if-steps { margin: 0; padding-left: 1.2rem; list-style: decimal; display: grid; gap: .45rem; font-size: .86rem; line-height: 1.45; color: #334155; }
        .dark .import-ficha-page .if-steps { color: #cbd5e1; }
        .import-ficha-page .if-actions { display: flex; justify-content: flex-end; gap: .6rem; padding: .85rem 1rem; border-top: 1px solid rgba(148,163,184,.2); }
        @media (max-width: 640px) {
            .import-ficha-page .if-item { flex-wrap: wrap; align-items: flex-start; }
            .import-ficha-page .if-item > .min-w-0 { flex-basis: calc(100% - 3.75rem); }
            .import-ficha-page .if-note { margin-left: 3.75rem; }
            .import-ficha-page .if-actions > * { flex: 1; }
        }
    </style>
</div>
