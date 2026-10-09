<link rel="stylesheet" href="{{ asset('assets/css/settings.css') }}">
<script src="{{ asset('assets/js/settings.js') }}" defer></script>
@php
    $appearancePrefs = auth()->user()?->preferences['appearance'] ?? null;
@endphp
@if($appearancePrefs)
<script>
    (function () {
        try {
            var appearancePrefs = @js($appearancePrefs);
            if (appearancePrefs && typeof appearancePrefs === 'object') {
                localStorage.setItem('flowmanager:appearance', JSON.stringify(appearancePrefs));
            }
        } catch (e) {}
    })();
</script>
@endif

@php
    $settingsUser = auth()->user();
@endphp

@include('partials.settings-heading')

<div class="settings-wrap">
    {{-- CONTEÚDO --}}
    <div class="settings-content-area">
        {{ $slot }}
    </div>
</div>

<style>
/* O menu lateral virou abas no cabeçalho: o conteúdo usa a largura toda, alinhado ao cabeçalho. */
.settings-wrap { display: block; padding: 1rem 1.5rem 1.5rem; }
.settings-content-area { min-width: 0; max-width: 100%; }
.settings-content-area > * { min-width: 0; max-width: 100%; }
.s-pg-grid > .s-col-main, .s-pg-grid > .s-col-side { min-width: 0; }
@media (max-width: 767px) { .s-pg-grid { grid-template-columns: minmax(0, 1fr) !important; } }
@media (max-width: 1023px) { .settings-wrap { padding: 1rem 1.25rem; } }
@media (max-width: 639px) { .settings-wrap { padding: 0.75rem; } }
/* A prévia da foto só aparece depois de escolher um arquivo (antes mostrava o texto alternativo quebrado). */
.settings-avatar-img.hidden, #avatarPlaceholder.hidden { display: none !important; }
</style>


