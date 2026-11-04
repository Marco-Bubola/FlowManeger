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
        'erro' => ['Não foram', ($counts['erro'] ?? 0) + ($counts['cancelado'] ?? 0)],
    ];
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
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

    <div class="igp-tabs">
        @foreach($tabs as $key => [$label, $n])
            <button type="button" wire:click="setTab('{{ $key }}')" class="{{ $tab === $key ? 'on' : '' }}">{{ $label }} <span>{{ $n }}</span></button>
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
                    <div class="igp-media {{ $p->kind === 'story' ? 'is-story' : '' }}">
                        @if($urls)<img src="{{ $urls[0] }}" alt="" loading="lazy">@endif
                        <span class="igp-kind"><i class="bi {{ $p->kind === 'story' ? 'bi-phone' : (count($urls) > 1 ? 'bi-collection' : 'bi-image') }}"></i> {{ $p->kindLabel() }}</span>
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
        .igp-media img { width: 100%; height: 100%; object-fit: cover; }
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
