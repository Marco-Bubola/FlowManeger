@props([
    'target'  => '',
    'message' => 'Carregando...',
    'cover'   => 'section', {{-- 'section' | 'page' --}}
])

{{--
  Uso:
    <x-loading-overlay target="save" message="Salvando..." />
    <x-loading-overlay target="loadData, filterProducts" message="Filtrando..." cover="page" />
--}}

<div
    wire:loading.flex
    @if($target) wire:target="{{ $target }}" @endif
    class="fm-lo-backdrop {{ $cover === 'page' ? 'fm-lo-page' : 'fm-lo-section' }}"
>
    <div class="fm-lo-card">
        <div class="fm-lo-spinner"></div>
        <span class="fm-lo-msg">{{ $message }}</span>
    </div>
</div>

<style>
.fm-lo-backdrop {
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    background: rgba(0,0,0,.18);
    display: none; /* padrão; wire:loading.flex sobrescreve para flex */
}
.fm-lo-section {
    position: absolute;
    inset: 0;
    border-radius: inherit;
}
.fm-lo-page {
    position: fixed;
    inset: 0;
}
.dark .fm-lo-backdrop {
    background: rgba(0,0,0,.42);
}
.fm-lo-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .55rem;
    padding: 1.25rem 1.75rem;
    border-radius: .875rem;
    background: var(--fm-surface, #fff);
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    border: 1px solid rgba(255,255,255,.18);
}
.dark .fm-lo-card {
    background: var(--fm-surface-dark, #1e293b);
    border-color: rgba(255,255,255,.08);
}
.fm-lo-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid rgba(99,102,241,.18);
    border-top-color: #6366f1;
    border-radius: 50%;
    animation: fm-spin .7s linear infinite;
}
.dark .fm-lo-spinner {
    border-color: rgba(139,92,246,.18);
    border-top-color: #818cf8;
}
.fm-lo-msg {
    font-size: .8125rem;
    font-weight: 500;
    color: #475569;
    white-space: nowrap;
}
.dark .fm-lo-msg {
    color: #94a3b8;
}
/* fm-spin definida em flow-theme.css — fallback local */
@keyframes fm-spin { to { transform: rotate(360deg); } }
</style>
