<div class="import-sheet-page">
    <x-product-page-header title="Importar planilha" icon="bi-file-earmark-spreadsheet" active="planilha"
        subtitle="Cadastre vários produtos de uma vez, com tamanhos como variações">
        @if(count($items))
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-check2-circle"></i>{{ $okCount }} prontos</span>
            @if($skipCount)<span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300"><i class="bi bi-skip-forward"></i>{{ $skipCount }} já existem</span>@endif
            @if($errorCount)<span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300"><i class="bi bi-exclamation-triangle"></i>{{ $errorCount }} com erro</span>@endif
        </x-slot:meta>
        @endif
    </x-product-page-header>

    @include('livewire.products.partials.ep-styles')

    <div class="is-grid">
        <section class="ep-card">
            <div class="ep-card-h">
                <span class="ep-ico green"><i class="bi bi-upload"></i></span>
                <div class="flex-1 min-w-0"><h3>Planilha</h3><p>Excel (.xlsx) ou .csv, uma linha por produto</p></div>
                <a href="{{ route('products.import-sheet.template') }}" class="ep-btn ep-btn-soft shrink-0" style="padding:.45rem .8rem;font-size:.8rem"><i class="bi bi-download"></i>Baixar modelo</a>
            </div>
            <div class="ep-card-b space-y-3">
                <label class="is-drop {{ $fileName ? 'has-file' : '' }}" data-testid="sheet-drop">
                    <input type="file" wire:model="file" accept=".xlsx,.csv,.txt" class="sr-only">
                    <span wire:loading.remove wire:target="file">
                        <i class="bi {{ $fileName ? 'bi-file-earmark-check' : 'bi-file-earmark-arrow-up' }}"></i>
                        <b>{{ $fileName ?: 'Toque para escolher a planilha' }}</b>
                        <small>{{ $fileName ? 'Toque para trocar' : 'Use o modelo: os nomes das colunas precisam ser os mesmos' }}</small>
                    </span>
                    <span wire:loading.flex wire:target="file" class="is-loading"><i class="bi bi-arrow-repeat animate-spin"></i><b>Lendo a planilha...</b></span>
                </label>
                @error('file')<p class="ep-err">{{ $message }}</p>@enderror
                @if($missing)
                    <p class="is-alert"><i class="bi bi-exclamation-triangle-fill"></i>Faltam as colunas: <b>{{ implode(', ', $missing) }}</b>. Baixe o modelo e copie o cabeçalho dele.</p>
                @endif
                @if($ignored)
                    <p class="ep-hint"><i class="bi bi-info-circle"></i>Colunas que não entram: {{ implode(', ', $ignored) }}</p>
                @endif
            </div>
        </section>

        <section class="ep-card">
            <div class="ep-card-h">
                <span class="ep-ico teal"><i class="bi bi-lightbulb"></i></span>
                <div><h3>Como preencher</h3><p>Só nome e venda são obrigatórios</p></div>
            </div>
            <div class="ep-card-b">
                <ul class="is-tips">
                    <li><b>Tamanhos da mesma peça</b>: repita o <b>nome</b> em cada linha e mude a <b>variação</b> (30 cm, 40 cm). A primeira linha vira o principal e as outras, variações dele.</li>
                    <li><b>Tamanho e cor juntos</b>: escreva a variação assim: <b>30 cm · Preto</b>.</li>
                    <li><b>Código</b> vazio: o app cria um. Código que já existe é pulado.</li>
                    <li><b>Categoria</b> que não existe é criada. Vazia vai para "Geral".</li>
                    <li><b>Sob encomenda</b>: escreva <b>sim</b> e o <b>prazo</b> em dias.</li>
                    <li>As <b>fotos</b> você põe depois, em Editar produto.</li>
                </ul>
            </div>
        </section>
    </div>

    @if(count($items))
    <section class="ep-card mt-4">
        <div class="ep-card-h">
            <span class="ep-ico"><i class="bi bi-list-check"></i></span>
            <div class="flex-1 min-w-0"><h3>O que vai entrar</h3><p>{{ $okCount }} {{ $okCount === 1 ? 'produto' : 'produtos' }}@if($groupCount), {{ $groupCount }} {{ $groupCount === 1 ? 'grupo' : 'grupos' }} de variações @endif · confira antes de importar</p></div>
        </div>
        <div class="is-list">
            @foreach($items as $it)
                <div class="is-row is-{{ $it['status'] }}" wire:key="row-{{ $it['line'] }}">
                    <span class="is-line">{{ $it['line'] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="is-name">{{ $it['name'] ?: '(sem nome)' }}</p>
                        <p class="is-meta">
                            @if($it['code'] !== '')<span><i class="bi bi-upc"></i>{{ $it['code'] }}</span>@endif
                            @if($it['value'] !== '')<span class="is-var">{{ $it['attribute'] }}: {{ $it['value'] }}</span>@endif
                            <span>Custo R$ {{ number_format($it['price'], 2, ',', '.') }}</span>
                            <span class="is-sale">Venda R$ {{ number_format($it['price_sale'], 2, ',', '.') }}</span>
                            <span><i class="bi bi-stack"></i>{{ $it['stock'] }}</span>
                            @if($it['made_to_order'])<span class="is-var"><i class="bi bi-hammer"></i>Sob encomenda{{ $it['production_days'] ? ' · ' . $it['production_days'] . ' dias' : '' }}</span>@endif
                            @if($it['weight_grams'])<span><i class="bi bi-box-seam"></i>{{ rtrim(rtrim(number_format($it['weight_grams'] / 1000, 2, ',', ''), '0'), ',') }} kg</span>@endif
                        </p>
                    </div>
                    @if($it['note'] !== '')
                        <span class="is-note">{{ $it['note'] }}</span>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="is-actions">
            <button type="button" wire:click="clear" class="ep-btn ep-btn-ghost">Cancelar</button>
            <button type="button" wire:click="import" wire:loading.attr="disabled" wire:target="import" class="ep-btn ep-btn-primary" @disabled($okCount === 0) data-testid="sheet-import" data-mobile-save="Importar {{ $okCount }}">
                <span wire:loading.remove wire:target="import"><i class="bi bi-check2"></i> Importar {{ $okCount }} {{ $okCount === 1 ? 'produto' : 'produtos' }}</span>
                <span wire:loading wire:target="import"><i class="bi bi-arrow-repeat animate-spin"></i> Importando...</span>
            </button>
        </div>
    </section>
    @endif

    <style>
        .import-sheet-page .is-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 900px) { .import-sheet-page .is-grid { grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); align-items: start; } }
        .import-sheet-page .is-drop { display: flex; align-items: center; justify-content: center; text-align: center; min-height: 9rem; padding: 1rem; border-radius: 1.1rem; border: 2px dashed rgba(99,102,241,.4); background: rgba(99,102,241,.05); cursor: pointer; transition: background .15s, border-color .15s; }
        .import-sheet-page .is-drop:hover { background: rgba(99,102,241,.09); }
        .import-sheet-page .is-drop.has-file { border-color: rgba(16,185,129,.55); background: rgba(16,185,129,.06); }
        .import-sheet-page .is-drop > span:not(.is-loading) { display: flex; }
        .import-sheet-page .is-drop > span { flex-direction: column; align-items: center; gap: .3rem; }
        .import-sheet-page .is-drop i { font-size: 2rem; color: #6366f1; }
        .import-sheet-page .is-drop.has-file i { color: #10b981; }
        .import-sheet-page .is-drop b { font-size: .95rem; color: #0f172a; word-break: break-all; }
        .dark .import-sheet-page .is-drop b { color: #f1f5f9; }
        .import-sheet-page .is-drop small { font-size: .76rem; color: #64748b; }
        .import-sheet-page .is-alert { display: flex; gap: .5rem; align-items: flex-start; padding: .65rem .8rem; border-radius: .85rem; font-size: .83rem; background: rgba(244,63,94,.08); color: #be123c; }
        .dark .import-sheet-page .is-alert { color: #fda4af; }
        .import-sheet-page .is-tips { margin: 0; padding-left: 1.1rem; list-style: disc; display: grid; gap: .45rem; font-size: .84rem; line-height: 1.45; color: #334155; }
        .dark .import-sheet-page .is-tips { color: #cbd5e1; }
        .import-sheet-page .is-list { display: grid; }
        .import-sheet-page .is-row { display: flex; align-items: center; gap: .75rem; padding: .7rem 1rem; border-top: 1px solid rgba(148,163,184,.18); }
        .import-sheet-page .is-row:first-child { border-top: 0; }
        .import-sheet-page .is-line { width: 2rem; flex-shrink: 0; text-align: center; font-size: .7rem; font-weight: 800; color: #94a3b8; }
        .import-sheet-page .is-name { margin: 0; font-size: .9rem; font-weight: 700; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dark .import-sheet-page .is-name { color: #f1f5f9; }
        .import-sheet-page .is-meta { margin: .2rem 0 0; display: flex; flex-wrap: wrap; gap: .25rem .6rem; font-size: .74rem; color: #64748b; }
        .import-sheet-page .is-meta i { margin-right: .2rem; }
        .import-sheet-page .is-sale { color: #047857; font-weight: 700; }
        .dark .import-sheet-page .is-sale { color: #6ee7b7; }
        .import-sheet-page .is-var { color: #6d28d9; font-weight: 700; }
        .dark .import-sheet-page .is-var { color: #c4b5fd; }
        .import-sheet-page .is-note { flex-shrink: 0; max-width: 40%; padding: .2rem .6rem; border-radius: 999px; font-size: .7rem; font-weight: 800; background: rgba(139,92,246,.12); color: #6d28d9; text-align: center; }
        .import-sheet-page .is-skip { opacity: .6; }
        .import-sheet-page .is-skip .is-note { background: rgba(148,163,184,.18); color: #475569; }
        .import-sheet-page .is-error .is-note { background: rgba(244,63,94,.12); color: #be123c; }
        .dark .import-sheet-page .is-note { color: #ddd6fe; }
        .dark .import-sheet-page .is-error .is-note { color: #fda4af; }
        .dark .import-sheet-page .is-skip .is-note { color: #cbd5e1; }
        .import-sheet-page .is-actions { display: flex; justify-content: flex-end; gap: .6rem; padding: .85rem 1rem; border-top: 1px solid rgba(148,163,184,.2); }
        @media (max-width: 640px) {
            .import-sheet-page .is-row { flex-wrap: wrap; align-items: flex-start; }
            .import-sheet-page .is-row > .min-w-0 { flex-basis: calc(100% - 2.75rem); }
            .import-sheet-page .is-name { white-space: normal; }
            .import-sheet-page .is-note { max-width: none; margin-left: 2.75rem; }
            .import-sheet-page .is-actions > * { flex: 1; }
        }
    </style>
</div>
