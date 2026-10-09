<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-10 space-y-5" wire:poll.60s>
    {{-- Cabeçalho --}}
    <div class="relative overflow-hidden rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] shadow-[0_20px_60px_rgba(15,23,42,0.10)]">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.10),transparent_32%)]"></div>
        <div class="relative flex flex-col gap-4 px-4 py-4 sm:px-6 sm:py-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-500 shadow-lg sm:h-14 sm:w-14">
                    <i class="bi bi-bell-fill text-2xl text-white"></i>
                    @if ($totalUnread > 0)
                        <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900">{{ $totalUnread > 99 ? '99+' : $totalUnread }}</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <h1 class="truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 bg-clip-text text-xl font-bold text-transparent dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 sm:text-2xl">Notificações</h1>
                    <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-400">
                        @if ($totalUnread > 0)
                            Você tem <strong class="text-slate-900 dark:text-white">{{ $totalUnread }}</strong> {{ $totalUnread === 1 ? 'notificação não lida' : 'notificações não lidas' }}
                        @else
                            Tudo em dia por aqui
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="markAllAsRead" @disabled($scopeCounts['unread'] === 0)
                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="bi bi-check2-all"></i> Marcar {{ $category ? 'categoria' : 'todas' }} como lidas
                </button>
                <button type="button" wire:click="clearRead"
                    wire:confirm="Excluir as notificações já lidas{{ $category ? ' de ' . $categories[$category]['label'] : '' }}?"
                    @disabled($scopeCounts['total'] - $scopeCounts['unread'] === 0)
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white/80 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:border-rose-300 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:text-rose-300">
                    <i class="bi bi-trash3"></i> Limpar lidas
                </button>
                <a href="{{ route('settings.notifications') }}" wire:navigate
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white/80 text-slate-600 transition hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:text-indigo-300"
                    title="Preferências de notificação">
                    <i class="bi bi-sliders"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Abas + categorias --}}
    <div class="space-y-3">
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            @foreach (['all' => ['Todas', $scopeCounts['total']], 'unread' => ['Não lidas', $scopeCounts['unread']]] as $key => [$label, $num])
                <button type="button" wire:click="setTab('{{ $key }}')"
                    class="inline-flex items-center gap-2 rounded-lg px-3.5 py-1.5 text-sm font-semibold transition {{ $tab === $key ? 'bg-indigo-600 text-white shadow' : 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white' }}">
                    {{ $label }}
                    <span class="rounded-md px-1.5 py-0.5 text-[11px] font-bold {{ $tab === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $num }}</span>
                </button>
            @endforeach
        </div>

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
                                    <span class="mt-1 block text-sm leading-relaxed text-slate-600 line-clamp-3 dark:text-slate-400">{{ $n->message }}</span>
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
            <div class="flex flex-col items-center rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/40">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-500/15 dark:to-purple-500/15">
                    <i class="bi {{ $tab === 'unread' ? 'bi-check2-circle' : 'bi-bell-slash' }} text-3xl text-indigo-500 dark:text-indigo-300"></i>
                </div>
                <p class="text-base font-semibold text-slate-800 dark:text-slate-100">
                    {{ $tab === 'unread' ? 'Nenhuma notificação não lida' : 'Nenhuma notificação' }}{{ $category ? ' em ' . $categories[$category]['label'] : '' }}
                </p>
                <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                    {{ $tab === 'unread' ? 'Você já leu tudo. Bom trabalho!' : 'Avisos de vendas, estoque, Mercado Livre, Shopee, consórcios e finanças aparecem aqui.' }}
                </p>
                @if ($tab === 'unread' || $category)
                    <button type="button" wire:click="{{ $category ? "setCategory('')" : "setTab('all')" }}"
                        class="mt-4 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        {{ $category ? 'Ver todas as categorias' : 'Ver todas' }}
                    </button>
                @endif
            </div>
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
