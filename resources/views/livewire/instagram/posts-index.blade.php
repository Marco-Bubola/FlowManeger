@php
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'sky' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'amber' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200',
        'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300',
    ];
    $tz = \App\Models\Promotion::tz();
    $tabs = [
        'todos' => ['Todos', $counts->sum()],
        'agendado' => ['Agendados', $counts['agendado'] ?? 0],
        'postado' => ['Postados', $counts['postado'] ?? 0],
        'melhores' => ['Melhores', null],
        'erro' => ['Não foram', ($counts['erro'] ?? 0) + ($counts['cancelado'] ?? 0)],
    ];
    $num = fn ($n) => $n >= 10000 ? number_format($n / 1000, $n >= 100000 ? 0 : 1, ',', '.') . ' mil' : number_format((int) $n, 0, ',', '.');
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4" @if($processing) wire:poll.10s="checkProcessing" @endif>
    <x-page-header title="Instagram" icon="bi-instagram" section="produtos"
                   subtitle="Posts feitos e agendados pelo app">
        <x-slot:meta>
            @if($setting->connected())
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-pink-50 text-pink-700 dark:bg-pink-500/15 dark:text-pink-300"><i class="bi bi-instagram"></i>{{ '@' . $setting->username }}</span>
            @else
                <a href="{{ route('settings.instagram') }}" wire:navigate class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones['amber'] }}"><i class="bi bi-exclamation-triangle"></i>Não conectado</a>
            @endif
            @if($counts['agendado'] ?? 0)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones['sky'] }}"><i class="bi bi-clock"></i>{{ $counts['agendado'] }} {{ $counts['agendado'] == 1 ? 'agendado' : 'agendados' }}</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            @if(($counts['postado'] ?? 0) > 0)
                <button type="button" wire:click="refreshResults" wire:loading.attr="disabled" wire:target="refreshResults" class="{{ $phBtn }}" title="Atualizar resultados" data-testid="ig-refresh">
                    <i class="bi bi-arrow-repeat text-pink-500" wire:loading.class="animate-spin" wire:target="refreshResults"></i><span class="hidden sm:inline">Atualizar resultados</span>
                </button>
            @endif
            <a href="{{ route('settings.instagram') }}" wire:navigate class="{{ $phBtn }}" title="Configurar"><i class="bi bi-gear text-slate-500"></i><span class="hidden sm:inline">Configurar</span></a>
            <a href="{{ route('instagram.create') }}" wire:navigate data-testid="ig-new"
               class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-orange-500 via-pink-600 to-purple-600 shadow-md shadow-pink-500/25 transition whitespace-nowrap"><i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Novo post</span></a>
        </x-slot:actions>
    </x-page-header>

    @if(session('ig_ok'))
        <div class="rounded-2xl px-4 py-3 text-sm font-semibold {{ $tones['emerald'] }}" data-testid="ig-flash"><i class="bi bi-check-circle-fill"></i> {{ session('ig_ok') }}</div>
    @endif
    @if(session('ig_error'))
        <div class="rounded-2xl px-4 py-3 text-sm font-semibold {{ $tones['rose'] }}"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('ig_error') }}</div>
    @endif

    @if($processing)
        <div class="rounded-2xl px-4 py-3 text-sm font-semibold {{ $tones['amber'] }}"><i class="bi bi-arrow-repeat animate-spin inline-block"></i> O Instagram está processando um Reels. Esta tela confere sozinha e ele sai assim que ficar pronto.</div>
    @endif

    @if($summary['posts'] > 0)
        <div class="igp-sum" data-testid="ig-summary">
            <div class="igp-sum-head">
                <b><i class="bi bi-graph-up-arrow"></i> Últimos 30 dias</b>
                <small>{{ $lastResults ? 'Atualizado ' . \Carbon\Carbon::parse($lastResults)->locale('pt_BR')->diffForHumans() : 'Os números chegam algumas horas depois do post' }}</small>
            </div>
            <div class="igp-sum-grid">
                <div><i class="bi bi-grid-3x3"></i><b>{{ $summary['posts'] }}</b><small>{{ $summary['posts'] === 1 ? 'post' : 'posts' }}</small></div>
                <div><i class="bi bi-eye"></i><b>{{ $num($summary['views']) }}</b><small>visualizações</small></div>
                <div><i class="bi bi-people"></i><b>{{ $num($summary['reach']) }}</b><small>contas alcançadas</small></div>
                <div><i class="bi bi-heart"></i><b>{{ $num($summary['interactions']) }}</b><small>interações</small></div>
                <div><i class="bi bi-bookmark"></i><b>{{ $num($summary['saved']) }}</b><small>salvos</small></div>
            </div>
            @if($summary['best'])
                @php $best = $summary['best']; $bu = $best->imageUrls()[0] ?? null; @endphp
                <div class="igp-best">
                    @if($bu)<img src="{{ $bu }}" alt="">@endif
                    <span class="min-w-0"><small><i class="bi bi-trophy-fill"></i> Melhor post</small><b class="truncate block">{{ $best->product?->name ?? $best->kindLabel() }}</b>
                        <small>{{ $num($best->metrics['views'] ?? 0) }} visualizações · {{ $num($best->interactions()) }} interações</small></span>
                </div>
            @endif
            @if($summary['noInsights'])
                <p class="igp-note"><i class="bi bi-info-circle"></i> Para ver alcance e visualizações, conecte o Instagram de novo em Configurações › Instagram (a conexão antiga não pedia essa permissão).</p>
            @endif
        </div>
    @endif

    <div class="igp-tabs">
        @foreach($tabs as $key => [$label, $n])
            <button type="button" wire:click="setTab('{{ $key }}')" class="{{ $tab === $key ? 'on' : '' }}">@if($key === 'melhores')<i class="bi bi-trophy"></i>@endif {{ $label }} @if($n !== null)<span>{{ $n }}</span>@endif</button>
        @endforeach
    </div>

    @if($posts->isEmpty())
        <x-empty-state icon="bi-instagram" tone="rose" :title="$tab === 'todos' ? 'Nenhum post ainda' : 'Nada aqui'"
                       text="Abra um produto e clique em Instagram, ou comece um post novo. Dá para postar na hora ou agendar.">
            <a href="{{ route('instagram.create') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-orange-500 via-pink-600 to-purple-600 shadow-md"><i class="bi bi-plus-lg"></i>Novo post</a>
        </x-empty-state>
    @else
        <div class="igp-grid" data-testid="ig-posts">
            @foreach($posts as $p)
                @php [$stLabel, $stIcon, $stTone] = \App\Models\InstagramPost::STATUS[$p->status] ?? ['?', 'bi-question', 'slate']; $urls = $p->imageUrls(); @endphp
                <div class="igp-card" wire:key="post{{ $p->id }}">
                    <div class="igp-media {{ in_array($p->kind, ['story', 'reel'], true) ? 'is-story' : '' }}">
                        @if($urls)<img src="{{ $urls[0] }}" alt="" loading="lazy">@elseif($p->kind === 'reel' && $p->video)<video src="{{ $p->videoUrl() }}#t=0.5" muted playsinline preload="metadata"></video>@endif
                        <span class="igp-kind"><i class="bi {{ $p->kind === 'reel' ? 'bi-camera-reels' : ($p->kind === 'story' ? 'bi-phone' : (count($urls) > 1 ? 'bi-collection' : 'bi-image')) }}"></i> {{ $p->kindLabel() }}</span>
                    </div>
                    <div class="igp-body">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold {{ $tones[$stTone] }}"><i class="bi {{ $stIcon }}"></i>{{ $stLabel }}</span>
                            <small class="text-[11px] text-slate-500 dark:text-slate-400">
                                @if($p->status === 'agendado' && $p->scheduled_at)
                                    {{ $p->scheduled_at->copy()->setTimezone($tz)->locale('pt_BR')->translatedFormat('d/m \à\s H:i') }}
                                @elseif($p->published_at)
                                    {{ $p->published_at->copy()->setTimezone($tz)->format('d/m/Y H:i') }}
                                @else
                                    {{ $p->updated_at->copy()->setTimezone($tz)->format('d/m/Y H:i') }}
                                @endif
                            </small>
                        </div>
                        @if($p->product)
                            <b class="igp-title">{{ $p->product->name }}</b>
                        @endif
                        @if($p->caption)
                            <p class="igp-caption">{{ \Illuminate\Support\Str::limit($p->caption, 140) }}</p>
                        @endif
                        @if($p->status === 'postado' && $p->metrics)
                            <div class="igp-metrics">
                                @foreach(\App\Models\InstagramPost::METRICS as $mk => [$mlabel, $micon])
                                    @if(isset($p->metrics[$mk]))
                                        <span title="{{ $mlabel }}"><i class="bi {{ $micon }}"></i>{{ $num($p->metrics[$mk]) }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @elseif($p->status === 'postado')
                            <p class="igp-wait"><i class="bi bi-hourglass-split"></i> Resultados aparecem em algumas horas.</p>
                        @elseif($p->status === 'postando' && $p->kind === 'reel')
                            <p class="igp-wait"><i class="bi bi-arrow-repeat"></i> Processando o vídeo no Instagram…</p>
                        @endif
                        @if($p->error && in_array($p->status, ['erro', 'cancelado'], true))
                            <p class="igp-error"><i class="bi bi-exclamation-triangle"></i> {{ $p->error }}</p>
                        @endif
                        <div class="igp-actions">
                            @if($p->permalink)
                                <a href="{{ $p->permalink }}" target="_blank" rel="noopener" class="igp-btn is-main"><i class="bi bi-box-arrow-up-right"></i>Ver no Instagram</a>
                            @endif
                            @if(in_array($p->status, ['agendado', 'erro'], true))
                                <a href="{{ route('instagram.edit', $p) }}" wire:navigate class="igp-btn"><i class="bi bi-pencil"></i>Editar</a>
                            @endif
                            @if(in_array($p->status, ['erro', 'cancelado'], true))
                                <button type="button" wire:click="retry({{ $p->id }})" wire:loading.attr="disabled" wire:target="retry({{ $p->id }})" class="igp-btn is-main"><i class="bi bi-arrow-repeat"></i>Postar de novo</button>
                            @endif
                            @if($p->status === 'agendado')
                                <button type="button" wire:click="cancel({{ $p->id }})" wire:confirm="Cancelar este post agendado?" class="igp-btn"><i class="bi bi-x-circle"></i>Cancelar</button>
                            @endif
                            @if($p->status !== 'postando')
                                <button type="button" wire:click="remove({{ $p->id }})" wire:confirm="{{ $p->status === 'postado' ? 'Tirar da lista? O post continua no Instagram.' : 'Excluir este post?' }}" class="igp-btn is-danger" title="Excluir"><i class="bi bi-trash3"></i></button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <x-infinite-feed :paginator="$posts" label="posts" />
    @endif

    <style>
        .igp-tabs { display: flex; gap: .35rem; overflow-x: auto; padding-bottom: .1rem; }
        .igp-tabs button { flex: none; display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .9rem; border-radius: 999px; font-size: .82rem; font-weight: 700; color: #475569; background: #fff; border: 1px solid #e2e8f0; }
        .igp-tabs button span { min-width: 1.3rem; padding: 0 .35rem; border-radius: 999px; background: #f1f5f9; font-size: .7rem; }
        .igp-tabs button.on { color: #fff; border-color: transparent; background: linear-gradient(135deg, #f58529, #dd2a7b 55%, #8134af); }
        .igp-tabs button.on span { background: rgba(255, 255, 255, .25); }
        .dark .igp-tabs button { background: #0f172a; border-color: #334155; color: #cbd5e1; } .dark .igp-tabs button span { background: #1e293b; }
        .igp-grid { display: grid; gap: .9rem; grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr)); }
        @media (min-width: 2200px) { .igp-grid { grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); } }
        .igp-card { display: flex; gap: .8rem; padding: .75rem; border-radius: 1.2rem; background: #fff; border: 1px solid rgb(226 232 240 / .9); box-shadow: 0 10px 30px -22px rgb(79 70 229 / .35); min-width: 0; }
        .dark .igp-card { background: rgb(15 23 42 / .85); border-color: rgb(51 65 85 / .7); }
        .igp-media { position: relative; flex: none; width: 7.5rem; aspect-ratio: 1; border-radius: .9rem; overflow: hidden; background: #f1f5f9; }
        .igp-media.is-story { aspect-ratio: 9 / 16; width: 5.2rem; }
        .igp-media img, .igp-media video { width: 100%; height: 100%; object-fit: cover; }
        .igp-metrics { display: flex; flex-wrap: wrap; gap: .3rem; }
        .igp-metrics span { display: inline-flex; align-items: center; gap: .25rem; padding: .15rem .45rem; border-radius: .55rem; background: #fdf2f8; color: #9d174d; font-size: .72rem; font-weight: 800; }
        .dark .igp-metrics span { background: rgba(236, 72, 153, .12); color: #f9a8d4; }
        .igp-wait { font-size: .72rem; color: #64748b; }
        .igp-sum { padding: .9rem 1rem; border-radius: 1.3rem; background: linear-gradient(135deg, rgba(245, 133, 41, .08), rgba(221, 42, 123, .08), rgba(129, 52, 175, .08)); border: 1px solid #fbcfe8; }
        .dark .igp-sum { border-color: rgba(236, 72, 153, .3); }
        .igp-sum-head { display: flex; align-items: baseline; justify-content: space-between; gap: .5rem; flex-wrap: wrap; margin-bottom: .6rem; }
        .igp-sum-head b { font-size: .9rem; color: #9d174d; } .dark .igp-sum-head b { color: #f9a8d4; }
        .igp-sum-head small { font-size: .72rem; color: #64748b; }
        .igp-sum-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .5rem; }
        .igp-sum-grid > div { padding: .6rem .7rem; border-radius: .9rem; background: #fff; border: 1px solid #fce7f3; }
        .dark .igp-sum-grid > div { background: #0f172a; border-color: #334155; }
        .igp-sum-grid i { color: #db2777; font-size: .9rem; }
        .igp-sum-grid b { display: block; font-size: 1.2rem; font-weight: 900; color: #0f172a; line-height: 1.2; } .dark .igp-sum-grid b { color: #f1f5f9; }
        .igp-sum-grid small { display: block; font-size: .68rem; color: #64748b; }
        .igp-best { display: flex; align-items: center; gap: .7rem; margin-top: .6rem; padding: .55rem .7rem; border-radius: .9rem; background: #fff; border: 1px solid #fce7f3; }
        .dark .igp-best { background: #0f172a; border-color: #334155; }
        .igp-best img { width: 2.8rem; height: 2.8rem; border-radius: .6rem; object-fit: cover; flex: none; }
        .igp-best small { display: block; font-size: .7rem; color: #64748b; } .igp-best small i { color: #f59e0b; }
        .igp-best b { font-size: .85rem; color: #0f172a; } .dark .igp-best b { color: #f1f5f9; }
        .igp-note { margin-top: .6rem; font-size: .74rem; color: #92400e; }
        @media (max-width: 767px) { .igp-sum-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .igp-sum-grid > div:first-child { grid-column: 1 / -1; } }
        @media (min-width: 768px) and (max-width: 1023px) { .igp-sum-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .igp-kind { position: absolute; left: .3rem; bottom: .3rem; right: .3rem; padding: .1rem .35rem; border-radius: .4rem; background: rgba(15, 23, 42, .72); color: #fff; font-size: .6rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .igp-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .3rem; }
        .igp-title { font-size: .86rem; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dark .igp-title { color: #f1f5f9; }
        .igp-caption { font-size: .76rem; color: #64748b; line-height: 1.35; overflow-wrap: anywhere; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .igp-error { font-size: .74rem; color: #b91c1c; } .dark .igp-error { color: #fca5a5; }
        .igp-actions { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: auto; padding-top: .3rem; }
        .igp-btn { display: inline-flex; align-items: center; gap: .3rem; padding: .38rem .65rem; border-radius: .7rem; font-size: .74rem; font-weight: 700; color: #475569; background: #f1f5f9; }
        .igp-btn.is-main { color: #be185d; background: #fdf2f8; }
        .igp-btn.is-danger { color: #e11d48; background: transparent; margin-left: auto; }
        .dark .igp-btn { background: #1e293b; color: #cbd5e1; } .dark .igp-btn.is-main { background: rgba(236, 72, 153, .12); color: #f9a8d4; }
    </style>
</div>
