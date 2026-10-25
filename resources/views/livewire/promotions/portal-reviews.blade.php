{{-- Avaliações do catálogo: responder, esconder ou excluir. --}}
@php
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $primary = 'inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 shadow-md shadow-violet-500/25 transition whitespace-nowrap';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $filters = ['todas' => ['Todas', 'bi-chat-square-text'], 'sem_resposta' => ['Sem resposta', 'bi-reply'], 'baixas' => ['Notas baixas', 'bi-emoji-frown'], 'escondidas' => ['Escondidas', 'bi-eye-slash']];
    $stars = fn ($v) => '<span class="rv-st" style="--v: ' . (float) $v . '">★★★★★</span>';
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
    <style>
        .rv-st { --v: 0; display: inline-block; letter-spacing: 1px; line-height: 1; background: linear-gradient(90deg, #f5b301 calc(var(--v) / 5 * 100%), #d4d4d8 0); -webkit-background-clip: text; background-clip: text; color: transparent; }
    </style>
    <x-page-header title="Avaliações" icon="bi-star-half" section="produtos" back-route="promotions.storefront"
                   subtitle="O que os clientes acharam dos produtos no catálogo">
        <x-slot:actions>
            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }}"><i class="bi bi-box-arrow-up-right text-violet-500"></i><span class="hidden sm:inline">Ver catálogo</span></a>
        </x-slot:actions>
    </x-page-header>

    <x-portal-settings-nav current="avaliacoes">

    <div class="grid gap-3 grid-cols-2 lg:grid-cols-4">
        <div class="{{ $card }} p-4"><small class="block text-xs font-semibold text-slate-500">Nota média</small><b class="text-2xl font-black text-slate-900 dark:text-white">{{ $total ? number_format($avg, 1, ',', '') : '–' }}</b> <span class="text-lg">{!! $stars($avg) !!}</span></div>
        <div class="{{ $card }} p-4"><small class="block text-xs font-semibold text-slate-500">No catálogo</small><b class="text-2xl font-black text-slate-900 dark:text-white">{{ $total }}</b></div>
        <div class="{{ $card }} p-4"><small class="block text-xs font-semibold text-slate-500">Sem resposta</small><b class="text-2xl font-black text-amber-600">{{ $counts['sem_resposta'] }}</b></div>
        <div class="{{ $card }} p-4"><small class="block text-xs font-semibold text-slate-500">Notas baixas (1 a 3)</small><b class="text-2xl font-black text-rose-600">{{ $counts['baixas'] }}</b></div>
    </div>

    <nav class="flex gap-1 overflow-x-auto rounded-2xl bg-slate-100 dark:bg-slate-800/70 p-1 [scrollbar-width:none]" aria-label="Filtro">
        @foreach($filters as $k => [$n, $ico])
            <button type="button" wire:click="$set('filter', '{{ $k }}')" data-testid="rv-filter-{{ $k }}"
                    class="flex-1 min-w-max inline-flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition {{ $filter === $k ? 'bg-white dark:bg-slate-900 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500 dark:text-slate-400' }}">
                <i class="bi {{ $ico }}"></i>{{ $n }} <span class="text-xs opacity-70">{{ $counts[$k] }}</span>
            </button>
        @endforeach
    </nav>

    @if($reviews->isEmpty())
        <x-empty-state icon="bi-star" tone="violet" title="{{ $filter === 'todas' ? 'Nenhuma avaliação ainda' : 'Nada por aqui' }}"
                       text="Os clientes avaliam na página do produto, no catálogo, depois de entrar na conta. As notas aparecem nos cards com estrelas." />
    @else
        <div class="grid gap-3 grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3" data-testid="rv-list">
            @foreach($reviews as $r)
                @php $p = $products[$r->product_id] ?? null; $img = $p?->all_images[0] ?? null; @endphp
                <article wire:key="rv-{{ $r->id }}" class="{{ $card }} p-4 flex flex-col gap-2 {{ $r->hidden ? 'opacity-60' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden">@if($img)<img src="{{ $img }}" alt="" class="w-full h-full object-contain" loading="lazy">@endif</div>
                        <div class="min-w-0 flex-1">
                            <b class="block text-sm text-slate-800 dark:text-slate-100 truncate">{{ $p?->name ?? 'Produto excluído' }}</b>
                            <div class="flex items-center gap-2 flex-wrap text-xs text-slate-500">
                                <span class="text-base">{!! $stars($r->rating) !!}</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $r->name }}</span>
                                @if($r->verified)<span class="text-emerald-600 font-semibold"><i class="bi bi-patch-check-fill"></i> Comprou</span>@endif
                                <span>{{ $r->created_at?->format('d/m/Y H:i') }}</span>
                                @if($r->hidden)<span class="rounded-full px-2 py-0.5 bg-slate-100 dark:bg-slate-800 font-semibold">Escondida</span>@endif
                            </div>
                        </div>
                    </div>
                    @if($r->comment)<p class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $r->comment }}</p>@endif
                    @if($replyingId === $r->id)
                        <div class="space-y-2">
                            <textarea wire:model="replyText" rows="3" maxlength="600" placeholder="Obrigado pela avaliação! …" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm" data-testid="rv-reply-text"></textarea>
                            <div class="flex gap-2"><button type="button" wire:click="saveReply" class="{{ $primary }}" data-testid="rv-reply-save"><i class="bi bi-send"></i>Publicar resposta</button><button type="button" wire:click="$set('replyingId', null)" class="{{ $phBtn }}">Cancelar</button></div>
                        </div>
                    @elseif($r->reply)
                        <div class="rounded-xl bg-violet-50 dark:bg-violet-500/10 border-l-4 border-violet-400 px-3 py-2 text-sm text-slate-700 dark:text-slate-200"><b class="block text-xs text-violet-700 dark:text-violet-300">Sua resposta</b>{{ $r->reply }}</div>
                    @endif
                    <div class="flex items-center gap-1.5 flex-wrap mt-auto pt-1">
                        <button type="button" wire:click="startReply({{ $r->id }})" class="{{ $phBtn }} !py-1.5" data-testid="rv-reply"><i class="bi bi-reply text-violet-500"></i>{{ $r->reply ? 'Editar resposta' : 'Responder' }}</button>
                        <button type="button" wire:click="toggleHidden({{ $r->id }})" class="{{ $phBtn }} !py-1.5"><i class="bi {{ $r->hidden ? 'bi-eye' : 'bi-eye-slash' }} text-slate-500"></i>{{ $r->hidden ? 'Mostrar' : 'Esconder' }}</button>
                        @if($p)<a href="{{ \App\Http\Controllers\ClientPortalController::productUrl($p) }}#avaliacoes" target="_blank" rel="noopener" class="{{ $phBtn }} !py-1.5"><i class="bi bi-box-arrow-up-right text-slate-500"></i>Ver</a>@endif
                        <button type="button" x-data @click="fmConfirm({ title: 'Excluir esta avaliação?', message: 'Ela some do catálogo e não volta.', confirmText: 'Excluir' }).then(ok => ok && $wire.delete({{ $r->id }}))" class="ml-auto inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200/70 dark:border-slate-700/70 text-rose-500" title="Excluir"><i class="bi bi-trash3"></i></button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
    </x-portal-settings-nav>
</div>
