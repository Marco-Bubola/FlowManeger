<div class="relative" wire:poll.45s
    x-data="{
        open: false,
        style: '',
        mobile: false,
        place() {
            const r = this.$refs.bell.getBoundingClientRect();
            this.mobile = window.innerWidth < 640;
            if (this.mobile) { this.style = ''; return; }
            const w = Math.min(410, window.innerWidth - 16);
            let left = r.right + 12;
            if (left + w > window.innerWidth - 8) left = Math.max(8, r.left - w + r.width);
            const vertical = r.top > window.innerHeight / 2
                ? `bottom:${Math.max(8, window.innerHeight - r.bottom)}px`
                : `top:${Math.max(8, r.top)}px`;
            this.style = `left:${left}px;${vertical};width:${w}px`;
        },
        toggle() { this.open = !this.open; if (this.open) this.$nextTick(() => this.place()); },
    }"
    @keydown.escape.window="open = false"
    @resize.window="open && place()">

    <button type="button" x-ref="bell" @click="toggle()"
        class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 bg-slate-50/90 text-slate-600 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-white hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-indigo-300"
        :class="open && '!border-indigo-400 !text-indigo-600 dark:!text-indigo-300'"
        title="Notificações" aria-label="Notificações{{ $unreadCount ? " ({$unreadCount} não lidas)" : '' }}" :aria-expanded="open">
        <i class="bi {{ $unreadCount > 0 ? 'bi-bell-fill' : 'bi-bell' }} text-[15px]"></i>
        @if ($unreadCount > 0)
            <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold leading-none text-white shadow-md ring-2 ring-white dark:ring-slate-900">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[9998]" @click="open = false"
            :class="mobile ? 'bg-slate-900/40 backdrop-blur-[2px]' : ''"
            x-transition.opacity.duration.150ms></div>
    </template>

    <template x-teleport="body">
        <div x-show="open" x-cloak role="dialog" aria-label="Notificações"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            :style="style"
            :class="mobile ? 'inset-x-2 bottom-2 max-h-[85vh]' : 'max-h-[min(640px,calc(100vh-16px))]'"
            class="fixed z-[9999] flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/20 dark:border-slate-700/70 dark:bg-slate-900">

            {{-- Cabeçalho --}}
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <div class="flex min-w-0 items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md shadow-indigo-500/25">
                        <i class="bi bi-bell-fill text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Notificações</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            @if ($unreadCount > 0)
                                {{ $unreadCount }} {{ $unreadCount === 1 ? 'não lida' : 'não lidas' }}
                            @else
                                Tudo em dia
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    @if ($unreadCount > 0)
                        <button type="button" wire:click="markAllAsRead"
                            class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-500/10"
                            title="Marcar todas como lidas">
                            <i class="bi bi-check2-all text-sm"></i><span class="hidden sm:inline">Ler todas</span>
                        </button>
                    @endif
                    <a href="{{ route('settings.notifications') }}" wire:navigate @click="open = false"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        title="Preferências de notificação">
                        <i class="bi bi-gear"></i>
                    </a>
                    <button type="button" @click="open = false"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 sm:hidden"
                        title="Fechar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Lista --}}
            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
                @forelse ($notifications as $n)
                    <div wire:key="bell-{{ $n->id }}"
                        class="group relative flex cursor-pointer gap-3 border-b border-slate-100 px-4 py-3 transition last:border-b-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/60 {{ $n->is_read ? '' : 'bg-indigo-50/40 dark:bg-indigo-500/[0.06]' }}"
                        wire:click="visit({{ $n->id }})" @click="open = false">

                        <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $n->is_read ? 'opacity-70' : '' }}" style="{{ $n->tone_style }}">
                            <i class="bi {{ $n->icon }} text-base"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start gap-2">
                                <p class="min-w-0 flex-1 truncate text-sm {{ $n->is_read ? 'font-medium text-slate-600 dark:text-slate-300' : 'font-semibold text-slate-900 dark:text-white' }}">
                                    {{ $n->title }}
                                </p>
                                @if (!$n->is_read)
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500" title="Não lida"></span>
                                @endif
                            </div>
                            <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $n->message }}</p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-400 dark:text-slate-500">
                                @if ($n->priority === 'high' && !$n->is_read)
                                    <span class="rounded-md bg-rose-100 px-1.5 py-0.5 font-bold uppercase tracking-wide text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">Urgente</span>
                                @endif
                                <span class="font-medium">{{ $n->category_label }}</span>
                                <span>&middot;</span>
                                <span title="{{ $n->local_created_at?->format('d/m/Y H:i') }}">{{ $n->time_ago }}</span>
                            </div>
                        </div>

                        <div class="absolute right-2 top-2 hidden items-center gap-0.5 rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm group-hover:flex dark:border-slate-700 dark:bg-slate-800">
                            @if ($n->is_read)
                                <button type="button" wire:click.stop="markAsUnread({{ $n->id }})" @click.stop
                                    class="flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-700"
                                    title="Marcar como não lida"><i class="bi bi-envelope"></i></button>
                            @else
                                <button type="button" wire:click.stop="markAsRead({{ $n->id }})" @click.stop
                                    class="flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-emerald-600 dark:hover:bg-slate-700"
                                    title="Marcar como lida"><i class="bi bi-check2"></i></button>
                            @endif
                            <button type="button" wire:click.stop="delete({{ $n->id }})" @click.stop
                                class="flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                                title="Excluir"><i class="bi bi-trash3"></i></button>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center px-6 py-12 text-center">
                        <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                            <i class="bi bi-bell-slash text-2xl text-slate-400"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Nenhuma notificação</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Avisos de vendas, estoque, marketplaces e consórcios aparecem aqui.</p>
                    </div>
                @endforelse
            </div>

            {{-- Rodapé --}}
            <a href="{{ route('notifications.index') }}" wire:navigate @click="open = false"
                class="flex items-center justify-center gap-1.5 border-t border-slate-100 bg-slate-50/80 px-4 py-3 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-slate-800 dark:bg-slate-800/50 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                Ver todas as notificações <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </template>
</div>
