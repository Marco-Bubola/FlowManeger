<div class="client-tags-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header title="Etiquetas e status" subtitle="Separe os clientes em grupos e veja quem está ativo ou sumido" icon="bi-tags"
                       section="clientes" tabs-section="clientes" tabs-active="etiquetas" />

        @php
            $tones = \App\Models\ConsortiumNotification::TONES;
            $statuses = \App\Services\Clients\ClientStatus::STATUSES;
            $sources = \App\Services\Clients\ClientStatus::SOURCES;
        @endphp

        {{-- Status: clicáveis, abrem a lista filtrada --}}
        <div class="crm-status-grid mb-4">
            @foreach($statuses as $key => $st)
                <a href="{{ route('clients.index', ['statusFilter' => $key]) }}" wire:navigate class="cmx-item crm-status-card">
                    <span class="cmx-label"><i class="bi {{ $st['icon'] }}" style="color: {{ $tones[$st['tone']] }}"></i>{{ $st['label'] }}</span>
                    <span class="cmx-value">{{ $statusCounts[$key] ?? 0 }}</span>
                    <span class="cmx-hint">{{ $st['hint'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="crm-grid">
            <div class="crm-side">
                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-pink-500 to-rose-500"><i class="bi bi-tag"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="crs-title">Nova etiqueta</h2>
                            <p class="crs-sub">Ex.: VIP, Atacado, Revendedora, Móveis sob medida</p>
                        </div>
                    </div>
                    <form wire:submit="create" class="p-3 sm:p-4 flex flex-col gap-3">
                        <input type="text" wire:model="name" maxlength="40" class="crm-input" placeholder="Nome da etiqueta">
                        @error('name') <p class="crm-err -mt-2">{{ $message }}</p> @enderror
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach(\App\Models\ClientTag::COLORS as $c)
                                <button type="button" wire:click="$set('color', '{{ $c }}')" class="w-7 h-7 rounded-full ring-offset-2 dark:ring-offset-slate-900 {{ $color === $c ? 'ring-2 ring-slate-500' : '' }}" style="background: {{ $tones[$c] }}" title="{{ $c }}"></button>
                            @endforeach
                        </div>
                        <button type="submit" class="crm-btn crm-btn-primary justify-center"><i class="bi bi-plus-lg"></i>Criar etiqueta</button>
                    </form>
                </section>

                <section class="crs-card mt-4">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-sky-500 to-indigo-600"><i class="bi bi-signpost-split"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="crs-title">Como conheceram a loja</h2>
                            <p class="crs-sub">Toque para ver os clientes de cada origem</p>
                        </div>
                    </div>
                    @php $totalSrc = max(1, $sourceCounts->sum()); @endphp
                    <div class="crs-list">
                        @foreach($sources as $key => $src)
                            @php $n = (int) ($sourceCounts[$key] ?? 0); @endphp
                            @if($n > 0)
                                <a href="{{ route('clients.index', ['origem' => $key]) }}" wire:navigate class="crs-row crs-row-link">
                                    <span class="crs-row-icon bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300"><i class="bi {{ $src['icon'] }}"></i></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="crs-row-title">{{ $src['label'] }}</p>
                                        <div class="mt-1 h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden"><div class="h-full rounded-full bg-sky-500" style="width: {{ round($n / $totalSrc * 100) }}%"></div></div>
                                    </div>
                                    <span class="crs-row-value">{{ $n }}</span>
                                </a>
                            @endif
                        @endforeach
                        @php $sem = (int) ($sourceCounts['sem'] ?? 0); @endphp
                        @if($sem > 0)
                            <a href="{{ route('clients.index', ['origem' => 'sem']) }}" wire:navigate class="crs-row crs-row-link">
                                <span class="crs-row-icon bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"><i class="bi bi-question-circle"></i></span>
                                <div class="min-w-0 flex-1">
                                    <p class="crs-row-title">Não informado</p>
                                    <p class="crs-row-meta">Preencha em Editar cliente › Relacionamento</p>
                                </div>
                                <span class="crs-row-value">{{ $sem }}</span>
                            </a>
                        @endif
                    </div>
                </section>
            </div>

            <section class="crs-card crm-main">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-tags"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">Etiquetas <span class="crs-count">{{ $tags->count() }}</span></h2>
                        <p class="crs-sub">Marque nos clientes em Editar cliente › Relacionamento</p>
                    </div>
                </div>
                <div class="crs-list">
                    @forelse($tags as $tag)
                        <div class="crs-row" wire:key="tag-{{ $tag->id }}">
                            @if($editingId === $tag->id)
                                <form wire:submit="saveEdit" class="flex-1 flex flex-col sm:flex-row sm:items-center gap-2">
                                    <input type="text" wire:model="editName" maxlength="40" class="crm-input sm:max-w-xs !py-2">
                                    <div class="flex items-center gap-1.5">
                                        @foreach(\App\Models\ClientTag::COLORS as $c)
                                            <button type="button" wire:click="$set('editColor', '{{ $c }}')" class="w-6 h-6 rounded-full ring-offset-2 dark:ring-offset-slate-900 {{ $editColor === $c ? 'ring-2 ring-slate-500' : '' }}" style="background: {{ $tones[$c] }}"></button>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="crm-btn crm-btn-primary !h-9"><i class="bi bi-check2"></i>Salvar</button>
                                        <button type="button" wire:click="cancelEdit" class="crm-btn !h-9">Cancelar</button>
                                    </div>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-bold shrink-0" style="{{ $tag->style(0.16) }}"><i class="bi bi-tag-fill text-xs"></i>{{ $tag->name }}</span>
                                <a href="{{ route('clients.index', ['etiqueta' => $tag->id]) }}" wire:navigate class="min-w-0 flex-1 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600">
                                    {{ (int) ($counts[$tag->id] ?? 0) }} {{ (int) ($counts[$tag->id] ?? 0) === 1 ? 'cliente' : 'clientes' }} <i class="bi bi-chevron-right text-xs"></i>
                                </a>
                                <button type="button" wire:click="edit({{ $tag->id }})" class="crm-icon-btn" title="Editar"><i class="bi bi-pencil"></i></button>
                                <button type="button" wire:click="delete({{ $tag->id }})" wire:confirm="Apagar a etiqueta {{ $tag->name }}? Os clientes não são apagados." class="crm-icon-btn hover:!text-rose-600" title="Apagar"><i class="bi bi-trash"></i></button>
                            @endif
                        </div>
                    @empty
                        <div class="crs-empty py-12">
                            <i class="bi bi-tags"></i>
                            <p class="font-semibold text-slate-600 dark:text-slate-300">Nenhuma etiqueta ainda</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Crie a primeira ao lado.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <x-client-rows-css />
    <x-crm-css />
    <style>
        .crm-status-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
        @media (min-width: 768px) { .crm-status-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        .crm-status-card:hover { border-color: rgb(165 180 252); }
        @media (max-width: 640px) { .crm-status-card .cmx-hint { display: block; white-space: normal; font-size: .6rem; } }
    </style>
</div>
