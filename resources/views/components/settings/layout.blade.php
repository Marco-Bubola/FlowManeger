@props(['heading' => '', 'hub' => false])

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
    // Moldura de todas as Configurações: menu por grupos do lado (computador) ou numa janela (celular/iPad),
    // e o cabeçalho da página aberta. O mapa das páginas fica em App\Support\SettingsMenu.
    $stgUser = auth()->user();
    $stgGroups = \App\Support\SettingsMenu::groups($stgUser);
    $stgCurrent = \App\Support\SettingsMenu::current($stgUser);
    $stgRoute = \App\Support\SettingsMenu::currentRouteName();
    $stgItem = $stgCurrent[2] ?? null;
    $stgHasAvatar = ! empty($stgUser->profile_picture);
    $stgPlan = null;
    try { $stgPlan = $stgUser->plan(); } catch (\Throwable $e) { $stgPlan = null; }
    $stgHome = \Illuminate\Support\Facades\Route::has('settings.index') ? route('settings.index') : route('settings.profile');
@endphp

<div class="stg {{ $hub ? 'is-hub' : '' }}" x-data="{ menu: false, q: '', has(k) { return k.includes(this.q.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim()); } }" @keydown.escape.window="menu = false">

    @unless($hub)
        {{-- Menu das Configurações: fixo do lado no computador, janela de baixo no celular e iPad --}}
        <div x-cloak x-show="menu" x-transition.opacity class="stg-scrim" @click="menu = false"></div>
        <aside class="stg-nav" :class="menu && 'is-open'" aria-label="Configurações">
            <div class="stg-nav-sheet-head">
                <b>Configurações</b>
                <button type="button" @click="menu = false" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
            </div>

            <a href="{{ $stgHome }}" wire:navigate class="stg-me">
                <span class="stg-me-av bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
                    @if($stgHasAvatar)
                        <img src="{{ asset('storage/' . $stgUser->profile_picture) }}" alt="">
                    @else
                        {{ $stgUser->initials() }}
                    @endif
                </span>
                <span class="min-w-0">
                    <b>{{ $stgUser->name }}</b>
                    <small>Plano {{ $stgPlan?->name ?? 'Grátis' }} · ver tudo</small>
                </span>
                <i class="bi bi-grid-3x3-gap"></i>
            </a>

            <label class="stg-search">
                <i class="bi bi-search"></i>
                <input type="search" x-model="q" placeholder="Buscar configuração" aria-label="Buscar configuração">
            </label>

            <div class="stg-nav-groups">
                @foreach($stgGroups as $gKey => [$gName, $gIcon, $gItems])
                    <div class="stg-nav-group" x-show="!q || [...$el.querySelectorAll('[data-k]')].some(a => has(a.dataset.k))">
                        <p class="stg-nav-title">{{ $gName }}</p>
                        @foreach($gItems as [$iRoute, $iLabel, $iIcon, $iDesc, $iTone])
                            @php $on = \App\Support\SettingsMenu::isActive($iRoute, $stgRoute); @endphp
                            <a href="{{ route($iRoute) }}" @if(str_starts_with($iRoute, 'settings.')) wire:navigate @endif
                               class="stg-nav-item {{ $on ? 'is-on' : '' }}" @if($on) aria-current="page" @endif
                               data-k="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($iLabel . ' ' . $iDesc . ' ' . $gName)) }}"
                               x-show="!q || has($el.dataset.k)">
                                <span class="stg-nav-ico {{ $on ? 'bg-gradient-to-br ' . $iTone : '' }}"><i class="bi {{ $iIcon }}"></i></span>
                                <span class="min-w-0">
                                    <b>{{ $iLabel }}</b>
                                    <small>{{ $iDesc }}</small>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </aside>
    @endunless

    <div class="stg-main">
        @unless($hub)
            {{-- Cabeçalho da página aberta --}}
            <header class="stg-head">
                <a href="{{ $stgHome }}" wire:navigate class="stg-back" aria-label="Todas as configurações"><i class="bi bi-chevron-left"></i></a>
                <span class="stg-head-ico bg-gradient-to-br {{ $stgItem[4] ?? 'from-indigo-500 to-purple-600' }}"><i class="bi {{ $stgItem[2] ?? 'bi-gear' }}"></i></span>
                <div class="stg-head-txt">
                    <nav class="stg-crumb">
                        <a href="{{ $stgHome }}" wire:navigate>Configurações</a>
                        @if($stgCurrent)
                            <i class="bi bi-chevron-right"></i><span>{{ $stgCurrent[1] }}</span>
                        @endif
                    </nav>
                    <h1>{{ $stgItem[1] ?? ($heading ?: 'Configurações') }}</h1>
                    <p>{{ $stgItem[3] ?? '' }}</p>
                </div>
                <div class="stg-head-actions">
                    <button type="button" class="stg-menu-btn" @click="menu = true" aria-label="Todas as configurações">
                        <i class="bi bi-grid-3x3-gap"></i><span>Trocar</span>
                    </button>
                    <x-header-bell />
                </div>
            </header>
        @endunless

        <div class="settings-content-area">
            {{ $slot }}
        </div>
    </div>
</div>

<style>
/* Moldura das Configurações */
.stg { display: grid; grid-template-columns: 270px minmax(0, 1fr); gap: 1.25rem; align-items: start; }
.stg.is-hub { display: block; }
.stg-main { min-width: 0; display: flex; flex-direction: column; gap: 1rem; }
.settings-content-area { min-width: 0; max-width: 100%; }
.settings-content-area > * { min-width: 0; max-width: 100%; }
.s-pg-grid > .s-col-main, .s-pg-grid > .s-col-side { min-width: 0; }

/* Menu lateral */
.stg-nav { position: sticky; top: 1rem; max-height: calc(100vh - 2rem); overflow-y: auto; display: flex; flex-direction: column; gap: .6rem; padding: .7rem; border-radius: 1.4rem; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -18px rgba(15, 23, 42, .25); scrollbar-width: thin; }
.dark .stg-nav { background: #0f172a; border-color: #1e293b; box-shadow: none; }
.stg-nav-sheet-head { display: none; }
.stg-me { display: flex; align-items: center; gap: .65rem; padding: .55rem; border-radius: 1rem; background: linear-gradient(135deg, var(--color-indigo-50), var(--color-purple-50)); border: 1px solid var(--color-indigo-100); }
.dark .stg-me { background: linear-gradient(135deg, rgba(99, 102, 241, .14), rgba(168, 85, 247, .1)); border-color: rgba(99, 102, 241, .25); }
.stg-me-av { flex: none; display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border-radius: .85rem; overflow: hidden; color: #fff; font-weight: 800; font-size: .85rem; }
.stg-me-av img { width: 100%; height: 100%; object-fit: cover; }
.stg-me > span:nth-child(2) { flex: 1; }
.stg-me b { display: block; font-size: .85rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stg-me small { display: block; font-size: .7rem; color: #64748b; }
.stg-me > i { color: var(--color-indigo-500); }
.dark .stg-me b { color: #f1f5f9; }
.dark .stg-me small { color: #94a3b8; }
.stg-search { display: flex; align-items: center; gap: .5rem; padding: 0 .7rem; height: 2.4rem; border-radius: .85rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; }
.stg-search:focus-within { border-color: var(--color-indigo-300); box-shadow: 0 0 0 3px color-mix(in oklab, var(--color-indigo-500) 15%, transparent); }
.stg-search input { flex: 1; min-width: 0; border: 0; outline: 0; background: transparent; font-size: .82rem; color: #0f172a; padding: 0; }
.stg-search input:focus { box-shadow: none; }
.dark .stg-search { background: #111827; border-color: #1f2937; }
.dark .stg-search input { color: #f1f5f9; }
.stg-nav-title { padding: .55rem .55rem .25rem; font-size: .66rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: #94a3b8; }
.stg-nav-item { display: flex; align-items: center; gap: .6rem; padding: .42rem .5rem; border-radius: .85rem; transition: background .15s; }
.stg-nav-item:hover { background: #f8fafc; }
.dark .stg-nav-item:hover { background: #111827; }
.stg-nav-item.is-on { background: var(--color-indigo-50); box-shadow: inset 0 0 0 1px var(--color-indigo-200); }
.dark .stg-nav-item.is-on { background: rgba(99, 102, 241, .14); box-shadow: inset 0 0 0 1px rgba(99, 102, 241, .35); }
.stg-nav-ico { flex: none; display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: .65rem; background-color: #f1f5f9; color: #64748b; font-size: .92rem; }
.stg-nav-item.is-on .stg-nav-ico { color: #fff; box-shadow: 0 6px 14px -8px rgba(15, 23, 42, .6); }
.dark .stg-nav-ico { background-color: #1e293b; color: #94a3b8; }
.stg-nav-item b { display: block; font-size: .82rem; font-weight: 700; color: #334155; }
.stg-nav-item small { display: block; font-size: .68rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stg-nav-item.is-on b { color: var(--color-indigo-700); }
.dark .stg-nav-item b { color: #cbd5e1; }
.dark .stg-nav-item.is-on b { color: var(--color-indigo-200); }

/* Cabeçalho da página */
.stg-head { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.15rem; border-radius: 1.4rem; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -18px rgba(15, 23, 42, .25); }
.dark .stg-head { background: #0f172a; border-color: #1e293b; box-shadow: none; }
.stg-back { display: none; }
.stg-head-ico { flex: none; display: grid; place-items: center; width: 3rem; height: 3rem; border-radius: 1rem; color: #fff; font-size: 1.3rem; box-shadow: 0 10px 20px -10px rgba(15, 23, 42, .55); }
.stg-head-txt { flex: 1; min-width: 0; }
.stg-crumb { display: flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 600; color: #94a3b8; }
.stg-crumb a:hover { color: var(--color-indigo-600); }
.stg-crumb i { font-size: .55rem; }
.stg-crumb span { color: var(--color-indigo-600); }
.dark .stg-crumb span { color: var(--color-indigo-300); }
.stg-head h1 { font-size: 1.35rem; font-weight: 900; line-height: 1.2; color: #0f172a; }
.stg-head p { font-size: .8rem; color: #64748b; }
.dark .stg-head h1 { color: #f8fafc; }
.dark .stg-head p { color: #94a3b8; }
.stg-head-actions { flex: none; display: flex; align-items: center; gap: .45rem; }
.stg-menu-btn { display: none; align-items: center; gap: .4rem; height: 2.5rem; padding: 0 .85rem; border-radius: .8rem; font-size: .8rem; font-weight: 800; color: var(--color-indigo-700); background: var(--color-indigo-50); border: 1px solid var(--color-indigo-100); }
.dark .stg-menu-btn { color: var(--color-indigo-200); background: rgba(99, 102, 241, .14); border-color: rgba(99, 102, 241, .3); }
.stg-scrim { display: none; }

@media (min-width: 1800px) { .stg { grid-template-columns: 290px minmax(0, 1fr); gap: 1.5rem; } }

/* Celular e iPad: menu numa janela de baixo, aberto pelo botão Trocar */
@media (max-width: 1023px) {
    .stg { display: block; }
    .stg-scrim { display: block; position: fixed; inset: 0; z-index: 96; background: rgba(15, 23, 42, .5); backdrop-filter: blur(3px); }
    .stg-nav { position: fixed; inset: auto 0 0 0; top: auto; z-index: 97; max-height: 86vh; border-radius: 1.6rem 1.6rem 0 0; padding: .8rem .8rem calc(1rem + env(safe-area-inset-bottom)); transform: translateY(105%); visibility: hidden; transition: transform .22s ease, visibility .22s; }
    .stg-nav.is-open { transform: none; visibility: visible; }
    .stg-nav-sheet-head { display: flex; align-items: center; justify-content: space-between; padding: .1rem .3rem .2rem; }
    .stg-nav-sheet-head b { font-size: 1rem; color: #0f172a; }
    .dark .stg-nav-sheet-head b { color: #f1f5f9; }
    .stg-nav-sheet-head button { width: 2.2rem; height: 2.2rem; border-radius: .7rem; color: #64748b; }
    .stg-nav-groups { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 .6rem; }
    .stg-menu-btn { display: inline-flex; }
    .stg-back { display: grid; place-items: center; flex: none; width: 2.3rem; height: 2.3rem; border-radius: .75rem; background: #f1f5f9; color: #475569; }
    .dark .stg-back { background: #1e293b; color: #cbd5e1; }
}
@media (max-width: 767px) {
    .stg-head { gap: .6rem; padding: .7rem .75rem; border-radius: 1.2rem; }
    .stg-head-ico { display: none; }
    .stg-crumb { display: none; }
    .stg-head h1 { font-size: 1.05rem; }
    .stg-head p { font-size: .72rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .stg-menu-btn { width: 2.3rem; height: 2.3rem; padding: 0; justify-content: center; }
    .stg-menu-btn span { display: none; }
    .stg-head-actions .app-ph-bell { width: 2.3rem; height: 2.3rem; }
    .stg-nav-groups { display: block; }
}
/* A prévia da foto só aparece depois de escolher um arquivo (antes mostrava o texto alternativo quebrado). */
.settings-avatar-img.hidden, #avatarPlaceholder.hidden { display: none !important; }
@media (max-width: 767px) { .s-pg-grid { grid-template-columns: minmax(0, 1fr) !important; } }
</style>
