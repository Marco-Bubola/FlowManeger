<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-10 space-y-5" wire:poll.60s>
    {{-- Cabeçalho no padrão das outras telas: título, ações em ícones no celular e abas Todas / Não lidas --}}
    <div class="notif-ph app-ph relative overflow-hidden rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] shadow-[0_20px_60px_rgba(15,23,42,0.10)]">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.10),transparent_32%)]"></div>
        <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
            <div class="app-ph-main flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                    <div class="app-ph-icon relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-500 shadow-lg sm:h-14 sm:w-14">
                        <i class="bi bi-bell-fill text-2xl text-white"></i>
                    </div>
                    <div class="min-w-0">
                        <h1 class="truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 bg-clip-text text-xl font-bold text-transparent dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 sm:text-2xl">Notificações</h1>
                        <p class="app-ph-sub mt-0.5 text-sm text-slate-600 dark:text-slate-400">
                            @if ($totalUnread > 0)
                                <strong class="text-slate-900 dark:text-white">{{ $totalUnread }}</strong> {{ $totalUnread === 1 ? 'não lida' : 'não lidas' }}
                            @else
                                Tudo em dia por aqui
                            @endif
                        </p>
                    </div>
                </div>
                <div class="app-ph-actions flex flex-wrap items-center gap-2 lg:justify-end">
                    <button type="button" wire:click="markAllAsRead" @disabled($scopeCounts['unread'] === 0)
                        aria-label="Marcar {{ $category ? 'categoria' : 'todas' }} como lidas" title="Marcar {{ $category ? 'categoria' : 'todas' }} como lidas"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="bi bi-check2-all"></i><span class="app-ph-label">Marcar {{ $category ? 'categoria' : 'todas' }} como lidas</span>
                    </button>
                    <button type="button" wire:click="clearRead"
                        wire:confirm="Excluir as notificações já lidas{{ $category ? ' de ' . $categories[$category]['label'] : '' }}?"
                        @disabled($scopeCounts['total'] - $scopeCounts['unread'] === 0)
                        aria-label="Limpar lidas" title="Limpar lidas"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white/80 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-rose-300 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:text-rose-300">
                        <i class="bi bi-trash3"></i><span class="app-ph-label">Limpar lidas</span>
                    </button>
                    <a href="{{ route('settings.notifications') }}" wire:navigate aria-label="Preferências de notificação" title="Preferências de notificação"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white/80 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:text-indigo-300">
                        <i class="bi bi-sliders"></i><span class="app-ph-label">Preferências</span>
                    </a>
                </div>
            </div>

            <div class="app-ph-tabs mt-4 -mx-1 overflow-x-auto">
                <nav class="flex min-w-max items-center gap-1 px-1 pb-1">
                    @foreach (['all' => ['Todas', $scopeCounts['total'], 'bi-inbox'], 'unread' => ['Não lidas', $scopeCounts['unread'], 'bi-envelope']] as $key => [$label, $num, $ico])
                        <button type="button" wire:click="setTab('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold transition {{ $tab === $key ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md shadow-indigo-500/25' : 'text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800/80 hover:text-indigo-700 dark:hover:text-indigo-300' }}">
                            <i class="bi {{ $ico }}"></i>{{ $label }}
                            <span class="rounded-md px-1.5 py-0.5 text-[11px] font-bold {{ $tab === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $num }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>

    {{-- Categorias --}}
    <div class="space-y-3">
        <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
            <div class="flex min-w-max items-center gap-2 pb-1 sm:min-w-0 sm:flex-wrap">
                <button type="button" wire:click="setCategory('')"
                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm font-semibold transition {{ $category === '' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:border-indigo-400 dark:bg-indigo-500/15 dark:text-indigo-200' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                    <i class="bi bi-grid"></i> Todas
                </button>
                @foreach ($categories as $key => $cat)
                    @php $c = $counts[$key]; @endphp
                    <button type="button" wire:click="setCategory('{{ $key }}')" wire:key="cat-{{ $key }}"
                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm font-semibold transition {{ $category === $key ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:border-indigo-400 dark:bg-indigo-500/15 dark:text-indigo-200' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }} {{ $c['total'] === 0 && $category !== $key ? 'opacity-60' : '' }}">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-[11px]" style="{{ \App\Models\ConsortiumNotification::toneStyle($cat['tone'], 0.16) }}"><i class="bi {{ $cat['icon'] }}"></i></span>
                        {{ $cat['label'] }}
                        @if ($c['unread'] > 0)
                            <span class="rounded-full bg-rose-500 px-1.5 text-[10px] font-bold leading-4 text-white">{{ $c['unread'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Lista agrupada por dia --}}
    <div class="relative space-y-5">
        <div wire:loading.flex wire:target="setTab,setCategory,loadMore"
            class="absolute inset-0 z-10 hidden items-start justify-center rounded-2xl bg-white/50 pt-16 dark:bg-slate-900/50">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>

        @forelse ($groups as $label => $items)
            <section wire:key="group-{{ \Illuminate\Support\Str::slug($label) }}">
                <h2 class="mb-2 flex items-center gap-2 px-1 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    {{ $label }}
                    <span class="h-px flex-1 bg-slate-200 dark:bg-slate-700/70"></span>
                </h2>
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80">
                    @foreach ($items as $n)
                        <article wire:key="n-{{ $n->id }}"
                            class="group relative flex gap-3 border-b border-slate-100 px-4 py-4 transition last:border-b-0 dark:border-slate-800 sm:gap-4 sm:px-5 {{ $n->is_read ? 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40' : 'bg-indigo-50/40 hover:bg-indigo-50/70 dark:bg-indigo-500/[0.06] dark:hover:bg-indigo-500/10' }}">
                            @if (!$n->is_read)
                                <span class="absolute inset-y-3 left-0 w-1 rounded-r-full bg-indigo-500"></span>
                            @endif

                            <button type="button" wire:click="visit({{ $n->id }})" class="flex min-w-0 flex-1 gap-3 text-left sm:gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $n->is_read ? 'opacity-70' : '' }}" style="{{ $n->tone_style }}">
                                    <i class="bi {{ $n->icon }} text-lg"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <span class="text-sm {{ $n->is_read ? 'font-medium text-slate-700 dark:text-slate-300' : 'font-semibold text-slate-900 dark:text-white' }}">{{ $n->title }}</span>
                                        @if ($n->priority === 'high')
                                            <span class="rounded-md bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">Urgente</span>
                                        @elseif ($n->priority === 'low')
                                            <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">Baixa</span>
                                        @endif
                                    </span>
                                    <span class="mt-1 block text-sm leading-relaxed text-slate-600 line-clamp-2 sm:line-clamp-3 dark:text-slate-400">{{ $n->message }}</span>
                                    <span class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-400 dark:text-slate-500">
                                        <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 font-semibold" style="{{ \App\Models\ConsortiumNotification::toneStyle(\App\Models\ConsortiumNotification::CATEGORIES[$n->category]['tone'], 0.12) }}">
                                            <i class="bi {{ \App\Models\ConsortiumNotification::CATEGORIES[$n->category]['icon'] }}"></i>{{ $n->category_label }}
                                        </span>
                                        <span title="{{ $n->local_created_at?->format('d/m/Y H:i') }}">{{ $n->time_ago }}</span>
                                        <span class="hidden sm:inline">&middot; {{ $n->local_created_at?->format('d/m H:i') }}</span>
                                        @if ($n->link)
                                            <span class="inline-flex items-center gap-1 font-semibold text-indigo-600 dark:text-indigo-300">&middot; Abrir <i class="bi bi-arrow-right"></i></span>
                                        @endif
                                    </span>
                                </span>
                            </button>

                            <div class="flex shrink-0 flex-col items-center gap-1 sm:flex-row sm:items-start sm:opacity-60 sm:transition sm:group-hover:opacity-100">
                                @if ($n->is_read)
                                    <button type="button" wire:click="markAsUnread({{ $n->id }})"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-indigo-300"
                                        title="Marcar como não lida"><i class="bi bi-envelope"></i></button>
                                @else
                                    <button type="button" wire:click="markAsRead({{ $n->id }})"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-600 dark:text-slate-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300"
                                        title="Marcar como lida"><i class="bi bi-check2"></i></button>
                                @endif
                                <button type="button" wire:click="delete({{ $n->id }})"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-300"
                                    title="Excluir"><i class="bi bi-trash3"></i></button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <x-empty-state :icon="$tab === 'unread' ? 'bi-check2-circle' : 'bi-bell-slash'"
                :title="($tab === 'unread' ? 'Nenhuma notificação não lida' : 'Nenhuma notificação') . ($category ? ' em ' . $categories[$category]['label'] : '')"
                :text="$tab === 'unread' ? 'Você já leu tudo.' : 'Avisos de vendas, estoque, Mercado Livre, Shopee, consórcios e finanças aparecem aqui.'">
                @if ($tab === 'unread' || $category)
                    <button type="button" wire:click="{{ $category ? "setCategory('')" : "setTab('all')" }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-500/10">
                        {{ $category ? 'Ver todas as categorias' : 'Ver todas' }}
                    </button>
                @endif
            </x-empty-state>
        @endforelse

        @if ($hasMore)
            <div class="flex justify-center">
                <button type="button" wire:click="loadMore"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    <i class="bi bi-chevron-down"></i> Carregar mais
                </button>
            </div>
        @endif
    </div>
</div>
