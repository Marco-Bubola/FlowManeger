{{-- Importar do catálogo do Mercado Livre: buscar → escolher o produto → marcar o que trazer. --}}
@if($showMlImport)
@php
    $mlDesc = !empty($mlData) ? $this->mlDescriptionText() : '';
    $mlChosen = collect($mlPhotos)->filter()->count();
@endphp
<style>
    .mi-bg { position: fixed; inset: 0; z-index: 80; background: rgba(15,23,42,.55); backdrop-filter: blur(2px); }
    .mi { position: fixed; z-index: 81; left: 0; right: 0; bottom: 0; max-height: 92vh; display: flex; flex-direction: column; background: #fff; border-radius: 22px 22px 0 0; box-shadow: 0 -10px 40px rgba(15,23,42,.25); }
    .dark .mi { background: #0f172a; }
    @media (min-width: 768px) { .mi { left: 50%; top: 50%; bottom: auto; right: auto; transform: translate(-50%, -50%); width: min(820px, 94vw); max-height: 88vh; border-radius: 22px; } }
    .mi-h { display: flex; align-items: center; gap: .7rem; padding: 1rem 1rem .75rem; border-bottom: 1px solid rgba(148,163,184,.25); }
    .mi-h h3 { margin: 0; font-size: 1rem; font-weight: 800; color: #0f172a; }
    .dark .mi-h h3 { color: #f1f5f9; }
    .mi-h p { margin: 0; font-size: .74rem; color: #64748b; }
    .mi-close { margin-left: auto; width: 2.2rem; height: 2.2rem; border-radius: .8rem; display: flex; align-items: center; justify-content: center; color: #64748b; background: rgba(148,163,184,.14); }
    .mi-body { overflow-y: auto; padding: 1rem; overscroll-behavior: contain; }
    .mi-f { display: flex; gap: .6rem; padding: .85rem 1rem calc(.85rem + env(safe-area-inset-bottom)); border-top: 1px solid rgba(148,163,184,.25); }
    .mi-search { display: flex; gap: .5rem; }
    .mi-cands { display: grid; gap: .6rem; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); margin-top: 1rem; }
    .mi-cand { text-align: left; border: 1px solid rgba(148,163,184,.35); border-radius: 1rem; overflow: hidden; background: #fff; transition: border-color .15s, transform .15s; }
    .dark .mi-cand { background: rgba(30,41,59,.7); border-color: rgba(71,85,105,.7); }
    .mi-cand:hover { border-color: #818cf8; transform: translateY(-2px); }
    .mi-cand .img { aspect-ratio: 1; background: #fff; display: flex; align-items: center; justify-content: center; }
    .mi-cand .img img { width: 100%; height: 100%; object-fit: contain; }
    .mi-cand .t { padding: .55rem .65rem .7rem; font-size: .78rem; font-weight: 600; line-height: 1.3; color: #0f172a; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .dark .mi-cand .t { color: #e2e8f0; }
    .mi-cand .b { padding: 0 .65rem .7rem; font-size: .68rem; color: #64748b; }
    .mi-top { display: flex; gap: .8rem; align-items: center; padding: .75rem; border-radius: 1rem; background: rgba(255,230,0,.12); border: 1px solid rgba(234,179,8,.3); }
    .mi-top img { width: 3.6rem; height: 3.6rem; border-radius: .8rem; object-fit: contain; background: #fff; flex-shrink: 0; }
    .mi-top .n { font-size: .88rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
    .dark .mi-top .n { color: #f1f5f9; }
    .mi-sec { margin-top: 1rem; border: 1px solid rgba(148,163,184,.3); border-radius: 1rem; }
    .dark .mi-sec { border-color: rgba(71,85,105,.6); }
    .mi-sec-h { display: flex; align-items: center; gap: .6rem; padding: .7rem .85rem; cursor: pointer; }
    .mi-sec-h input { width: 1.15rem; height: 1.15rem; accent-color: #6366f1; flex-shrink: 0; }
    .mi-sec-h b { font-size: .88rem; color: #0f172a; }
    .dark .mi-sec-h b { color: #f1f5f9; }
    .mi-sec-h small { margin-left: auto; font-size: .72rem; color: #64748b; text-align: right; }
    .mi-sec-b { padding: 0 .85rem .85rem; font-size: .82rem; color: #475569; }
    .dark .mi-sec-b { color: #cbd5e1; }
    .mi-desc { white-space: pre-line; max-height: 9.5rem; overflow: hidden; position: relative; line-height: 1.5; }
    .mi-desc::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 2.5rem; background: linear-gradient(transparent, #fff); }
    .dark .mi-desc::after { background: linear-gradient(transparent, #0f172a); }
    .mi-specs { display: grid; grid-template-columns: minmax(0, 1fr); gap: .2rem .9rem; }
    @media (min-width: 640px) { .mi-specs { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .mi-specs div { display: flex; gap: .4rem; min-width: 0; }
    .mi-specs dt { font-weight: 700; white-space: nowrap; }
    .mi-specs dd { margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mi-photos { display: grid; grid-template-columns: repeat(auto-fill, minmax(76px, 1fr)); gap: .45rem; }
    .mi-photo { position: relative; aspect-ratio: 1; border-radius: .8rem; overflow: hidden; border: 2px solid rgba(148,163,184,.35); background: #fff; cursor: pointer; }
    .mi-photo img { width: 100%; height: 100%; object-fit: contain; }
    .mi-photo input { position: absolute; opacity: 0; pointer-events: none; }
    .mi-photo .ck { position: absolute; top: .3rem; right: .3rem; width: 1.3rem; height: 1.3rem; border-radius: 999px; background: rgba(255,255,255,.9); border: 1px solid rgba(148,163,184,.6); display: flex; align-items: center; justify-content: center; font-size: .7rem; color: transparent; }
    .mi-photo:has(input:checked) { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.18); }
    .mi-photo:has(input:checked) .ck { background: #6366f1; border-color: #6366f1; color: #fff; }
    .mi-err { margin-top: .8rem; padding: .65rem .8rem; border-radius: .9rem; font-size: .82rem; background: rgba(244,63,94,.1); color: #be123c; }
    .dark .mi-err { color: #fda4af; }
    .mi-load { display: none; align-items: center; gap: .5rem; margin-top: 1rem; font-size: .84rem; color: #64748b; }
</style>

<div class="mi-bg" wire:click="closeMlImport"></div>
<div class="mi" role="dialog" aria-modal="true" aria-label="Importar do Mercado Livre" x-data @keydown.escape.window="$wire.closeMlImport()">
    <div class="mi-h">
        <span class="ep-ico ml"><i class="bi bi-cloud-download"></i></span>
        <div class="min-w-0">
            <h3>Importar do Mercado Livre</h3>
            <p>{{ empty($mlData) ? 'Escolha o produto certo no catálogo' : (!empty($mlUploadMode) ? 'Marque o que levar. Entra no produto quando você salvar a lista.' : 'Marque o que quer trazer para este produto') }}</p>
        </div>
        <button type="button" class="mi-close" wire:click="closeMlImport" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="mi-body">
        @if(empty($mlData))
            <form class="mi-search" wire:submit.prevent="searchMlCatalog">
                <input type="search" wire:model="mlQuery" class="ep-input" placeholder="Código de barras ou nome do produto" autofocus>
                <button type="submit" class="ep-btn ep-btn-primary"><i class="bi bi-search"></i><span class="hidden sm:inline">Buscar</span></button>
            </form>

            <div class="mi-load" wire:loading.flex wire:target="searchMlCatalog,pickMlProduct"><i class="bi bi-arrow-repeat animate-spin"></i>Consultando o Mercado Livre...</div>

            @if($mlSearchError)<div class="mi-err" wire:loading.remove wire:target="searchMlCatalog,pickMlProduct">{{ $mlSearchError }}</div>@endif

            @if(count($mlCandidates))
                <div class="mi-cands" wire:loading.remove wire:target="searchMlCatalog,pickMlProduct">
                    @foreach($mlCandidates as $c)
                        <button type="button" class="mi-cand" wire:click="pickMlProduct(@js($c['id']))" wire:key="cand-{{ $c['id'] }}">
                            <div class="img">@if($c['picture'])<img src="{{ $c['picture'] }}" alt="" loading="lazy">@else<i class="bi bi-image text-3xl text-slate-300"></i>@endif</div>
                            <div class="t">{{ $c['name'] }}</div>
                            @if($c['brand'] || $c['model'])<div class="b">{{ collect([$c['brand'], $c['model']])->filter()->implode(' · ') }}</div>@endif
                        </button>
                    @endforeach
                </div>
            @endif
        @else
            <div class="mi-top">
                @if(!empty($mlData['pictures'][0]))<img src="{{ $mlData['pictures'][0] }}" alt="">@endif
                <div class="min-w-0 flex-1">
                    <div class="n">{{ $mlData['name'] }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">Catálogo {{ $mlData['id'] }}</div>
                </div>
                @if(count($mlCandidates) > 1)
                    <button type="button" class="ep-btn ep-btn-ghost" style="padding:.4rem .7rem;font-size:.76rem" wire:click="backToMlResults"><i class="bi bi-arrow-left"></i>Trocar</button>
                @endif
            </div>

            @if(count($mlData['pictures']))
            <div class="mi-sec">
                <div class="mi-sec-h"><i class="bi bi-images text-pink-500"></i><b>Fotos</b><small>{{ $mlChosen }} de {{ count($mlData['pictures']) }} marcadas · vão para a galeria</small></div>
                <div class="mi-sec-b">
                    <div class="mi-photos">
                        @foreach($mlData['pictures'] as $i => $url)
                            <label class="mi-photo" wire:key="mp-{{ $i }}">
                                <input type="checkbox" wire:model.live="mlPhotos.{{ $i }}">
                                <img src="{{ $url }}" alt="Foto {{ $i + 1 }}" loading="lazy">
                                <span class="ck"><i class="bi bi-check-lg"></i></span>
                            </label>
                        @endforeach
                    </div>
                    <label class="flex items-center gap-2 mt-3 cursor-pointer">
                        <input type="checkbox" wire:model="mlTake.cover" class="w-4 h-4" style="accent-color:#6366f1">
                        <span>Usar a primeira foto marcada como imagem principal</span>
                    </label>
                </div>
            </div>
            @endif

            @if($mlDesc !== '')
            <div class="mi-sec">
                <label class="mi-sec-h"><input type="checkbox" wire:model="mlTake.desc"><b>Descrição</b><small>substitui a atual</small></label>
                <div class="mi-sec-b"><div class="mi-desc">{{ $mlDesc }}</div></div>
            </div>
            @endif

            @if(count($mlData['specs']))
            <div class="mi-sec">
                <label class="mi-sec-h"><input type="checkbox" wire:model="mlTake.specs"><b>Ficha técnica</b><small>{{ count($mlData['specs']) }} itens · junta com a atual</small></label>
                <div class="mi-sec-b">
                    <dl class="mi-specs">
                        @foreach(array_slice($mlData['specs'], 0, 10) as $s)
                            <div><dt>{{ $s['n'] }}:</dt><dd title="{{ $s['v'] }}">{{ $s['v'] }}</dd></div>
                        @endforeach
                    </dl>
                    @if(count($mlData['specs']) > 10)<p class="mt-1.5 text-xs text-slate-500">e mais {{ count($mlData['specs']) - 10 }}</p>@endif
                </div>
            </div>
            @endif

            @if($mlData['brand'] || $mlData['model'] || $mlData['gtin'])
            <div class="mi-sec">
                <label class="mi-sec-h"><input type="checkbox" wire:model="mlTake.brand"><b>Marca e modelo</b>
                    <small>{{ collect([$mlData['brand'], $mlData['model'], $mlData['gtin'] && empty($barcode) ? 'EAN ' . $mlData['gtin'] : null])->filter()->implode(' · ') }}</small></label>
            </div>
            @endif

            @if($mlData['name'] !== '')
            <div class="mi-sec">
                <label class="mi-sec-h"><input type="checkbox" wire:model="mlTake.name"><b>Nome do catálogo</b><small class="truncate" style="max-width:60%">{{ $mlData['name'] }}</small></label>
            </div>
            @endif

            @if(($famCount = $this->mlFamilyCount) > 0)
            <div class="mi-sec" style="border-color: rgba(139,92,246,.45); background: rgba(139,92,246,.07);">
                <label class="mi-sec-h"><input type="checkbox" wire:model="mlToFamily"><b><i class="bi bi-diagram-3"></i> Levar também para as {{ $famCount }} outras {{ $famCount === 1 ? 'variação' : 'variações' }}</b>
                    <small>Fotos, descrição, ficha técnica e marca. Nome, código e EAN de cada uma ficam como estão; capa só em quem não tem foto.</small></label>
            </div>
            @endif

            @if($mlSearchError)<div class="mi-err">{{ $mlSearchError }}</div>@endif
        @endif
    </div>

    <div class="mi-f">
        <button type="button" class="ep-btn ep-btn-ghost flex-1 sm:flex-none" wire:click="closeMlImport">Cancelar</button>
        @if(!empty($mlRowSet))
            <button type="button" class="ep-btn ep-btn-ghost flex-1 sm:flex-none" style="color:#e11d48" wire:click="clearMlRow" title="Tirar o que foi escolhido do ML para este produto"><i class="bi bi-x-circle"></i><span class="hidden sm:inline">Não usar o ML</span></button>
        @endif
        @if(!empty($mlData))
            <button type="button" class="ep-btn ep-btn-primary flex-1 sm:ml-auto" wire:click="applyMlImport" wire:loading.attr="disabled" wire:target="applyMlImport">
                <span wire:loading.remove wire:target="applyMlImport"><i class="bi bi-download"></i> {{ !empty($mlUploadMode) ? 'Usar neste produto' : 'Trazer para o produto' }}</span>
                <span wire:loading wire:target="applyMlImport"><i class="bi bi-arrow-repeat animate-spin"></i> Trazendo...</span>
            </button>
        @endif
    </div>
</div>
@endif
