<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <!-- Botão de Notificações - Estilo Sidebar -->
    <button wire:click="toggleDropdown" @click.away="open = false"
        class="w-full flex items-center gap-3 p-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-200 group">
        <div class="relative">
            <i class="bi bi-bell-fill text-xl text-slate-600 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"></i>

            <!-- Badge de Contador -->
            @if ($unreadCount > 0)
                <span class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-br from-red-500 to-pink-600 rounded-full flex items-center justify-center text-[10px] font-bold text-white shadow-lg ring-2 ring-white dark:ring-slate-900 animate-pulse">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </div>

        <div class="sidebar-text flex-1 text-left">
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Notificações</p>
            @if ($unreadCount > 0)
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $unreadCount }} não {{ $unreadCount > 1 ? 'lidas' : 'lida' }}</p>
            @else
                <p class="text-xs text-slate-500 dark:text-slate-400">Nenhuma nova</p>
            @endif
        </div>

        <svg class="sidebar-text w-5 h-5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown de Notificações -->
    <div x-show="open" x-cloak
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed w-96 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 z-[9999] overflow-hidden notification-dropdown"
        style="max-height: calc(100vh - 120px); left: 280px; bottom: 80px;">

        <style>
            /* Ajuste responsivo do dropdown */
            @media (max-width: 1024px) {
                .notification-dropdown {
                    left: 50% !important;
                    transform: translateX(-50%);
                    bottom: 100px !important;
                    max-width: calc(100vw - 2rem) !important;
                }
            }

            /* Ajuste para sidebar compact */
            body.sidebar-compact .notification-dropdown {
                left: 100px !important;
            }
        </style>

        <!-- Header -->
        <div class="p-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="bi bi-bell-fill"></i>
                    Notificações
                </h3>
                <button wire:click="refreshNotifications"
                    class="p-1.5 hover:bg-white/20 rounded-lg transition-all"
                    title="Atualizar notificações">
                    <i class="bi bi-arrow-clockwise text-sm"></i>
                </button>
            </div>

            @if ($unreadCount > 0)
                <p class="text-sm text-white/80">
                    Você tem <strong>{{ $unreadCount }}</strong> notificação(ões) não lida(s)
                </p>
            @else
                <p class="text-sm text-white/80">
                    ✅ Você está em dia com todas as notificações
                </p>
            @endif
        </div>

        <!-- Actions Bar -->
        @if ($notifications->count() > 0)
            <div class="p-3 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-600">
                <div class="flex items-center justify-between gap-2">
                    @if ($unreadCount > 0)
                        <button wire:click="markAllAsRead"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors">
                            <i class="bi bi-check-all mr-1"></i>Marcar todas como lidas
                        </button>
                    @endif

                    <button wire:click="toggleShowAll"
                        class="text-xs text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold transition-colors ml-auto">
                        {{ $showAll ? 'Mostrar menos' : 'Ver todas' }}
                    </button>
                </div>
            </div>
        @endif

        <!-- Lista de Notificações -->
        <div class="max-h-96 overflow-y-auto">
            @forelse ($notifications as $notification)
                <div wire:key="notification-{{ $notification->id }}"
                    class="group relative p-4 border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all {{ $notification->is_read ? 'opacity-60' : 'bg-blue-50/30 dark:bg-blue-900/10' }}">

                    <!-- Status Indicator -->
                    @if (!$notification->is_read)
                        <div class="absolute left-2 top-1/2 -translate-y-1/2 w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                    @endif

                    <!-- Content -->
                    <div class="flex gap-3 {{ $notification->is_read ? '' : 'ml-2' }}">
                        <!-- Icon -->
                        <div
                            class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center {{ $notification->is_read ? 'bg-slate-200 dark:bg-slate-600' : 'bg-gradient-to-br from-' . $notification->color . '-500 to-' . $notification->color . '-600' }}">
                            <i
                                class="{{ $notification->icon }} {{ $notification->is_read ? 'text-slate-600 dark:text-slate-300' : 'text-white' }} text-lg"></i>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h4
                                    class="font-bold text-sm text-slate-900 dark:text-white {{ $notification->priority === 'high' ? 'text-red-600 dark:text-red-400' : '' }}">
                                    {{ $notification->title }}
                                </h4>

                                <!-- Priority Badge -->
                                @if ($notification->priority === 'high')
                                    <span class="flex-shrink-0 px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded">
                                        URGENTE
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-2 line-clamp-2">
                                {{ $notification->message }}
                            </p>

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500 dark:text-slate-500 flex items-center gap-1">
                                    <i class="bi bi-clock"></i>
                                    {{ $notification->time_ago }}
                                </span>

                                <!-- Actions -->
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @if ($notification->action_url)
                                        <button wire:click="markAsRead({{ $notification->id }})"
                                            class="px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded transition-colors"
                                            title="Ir para ação">
                                            <i class="bi bi-arrow-right-circle"></i>
                                        </button>
                                    @endif

                                    @if (!$notification->is_read)
                                        <button wire:click="markAsRead({{ $notification->id }})"
                                            class="px-2 py-1 bg-green-500 hover:bg-green-600 text-white text-xs rounded transition-colors"
                                            title="Marcar como lida">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    @else
                                        <button wire:click="markAsUnread({{ $notification->id }})"
                                            class="px-2 py-1 bg-slate-500 hover:bg-slate-600 text-white text-xs rounded transition-colors"
                                            title="Marcar como não lida">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endif

                                    <button wire:click="delete({{ $notification->id }})"
                                        class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded transition-colors"
                                        title="Remover">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="p-12 text-center">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-bell-slash text-4xl text-slate-400"></i>
                    </div>
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhuma notificação</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-500">
                        Você não tem notificações de consórcios no momento.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if ($notifications->count() > 0)
            <div class="p-3 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-600">
                <a href="{{ route('consortiums.index') }}"
                    class="block text-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                    <i class="bi bi-collection-fill mr-1"></i>Ver todos os consórcios
                </a>
            </div>
        @endif
    </div>
</div>
