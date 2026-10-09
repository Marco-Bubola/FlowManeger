{{-- Sino de notificações dentro do cabeçalho das páginas (celular/iPad, onde a sidebar fica
     escondida). Não é outro componente Livewire: abre o painel do sino da sidebar
     (evento open-notifications) e acompanha o contador dele. --}}
@php
    $__bellUnread = once(function () {
        try {
            return auth()->check() ? \App\Models\ConsortiumNotification::unreadCountForUser(auth()->id()) : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    });
@endphp
<button type="button" x-data="{ count: {{ (int) $__bellUnread }} }"
    @notifications-count.window="count = $event.detail.count"
    @click="$dispatch('open-notifications', { anchor: $el })"
    :aria-label="count > 0 ? `Notificações (${count} não lidas)` : 'Notificações'" title="Notificações"
    class="app-ph-bell relative inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 shadow-sm text-slate-600 dark:text-slate-300 transition"
    :class="count > 0 && '!text-indigo-500 dark:!text-indigo-300'">
    <i class="bi {{ $__bellUnread > 0 ? 'bi-bell-fill' : 'bi-bell' }}" :class="count > 0 ? 'bi-bell-fill' : 'bi-bell'"></i>
    <span class="absolute -top-1.5 -right-1.5 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full bg-rose-500 text-white text-[0.62rem] font-bold leading-[1.15rem] text-center ring-2 ring-white dark:ring-slate-900"
        x-show="count > 0" x-text="count > 99 ? '99+' : count" @if(!$__bellUnread) style="display:none" @endif>{{ $__bellUnread > 99 ? '99+' : $__bellUnread }}</span>
</button>
