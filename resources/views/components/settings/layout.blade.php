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
    $showTeamNav = (bool) $settingsUser;
    $settingsCurrentPlan = $settingsUser?->plan();
    $settingsPlanBadge = $settingsCurrentPlan?->name ? \Illuminate\Support\Str::limit($settingsCurrentPlan->name, 10, '') : 'Plano';
@endphp

@include('partials.settings-heading')

<div class="settings-wrap">
    {{-- SIDEBAR --}}
    <nav class="settings-nav-panel max-md:hidden" aria-label="Configurações">
        <span class="settings-nav-label">Conta</span>

        <a href="{{ route('settings.profile') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.profile') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a8.25 8.25 0 0 1 15 0v.75A2.25 2.25 0 0 1 17.25 22.5H6.75A2.25 2.25 0 0 1 4.5 20.25v-.75Z"/></svg>
            Perfil
        </a>

        <a href="{{ route('settings.password') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.password') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75A4.5 4.5 0 0 0 7.5 6.75v3.75m9 0a2.25 2.25 0 0 1 2.25 2.25v6A2.25 2.25 0 0 1 16.5 21H7.5a2.25 2.25 0 0 1-2.25-2.25v-6a2.25 2.25 0 0 1 2.25-2.25m9 0h-9"/></svg>
            Senha &amp; Login
        </a>

        <a href="{{ route('settings.security') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.security') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
            Segurança
        </a>

        <span class="settings-nav-label" style="margin-top:0.75rem">Aparência</span>

        <a href="{{ route('settings.appearance') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.appearance') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z"/></svg>
            Aparência
            <span class="settings-nav-badge">Cor</span>
        </a>

        <a href="{{ route('settings.system') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.system') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/></svg>
            Sistema &amp; Região
        </a>

        <span class="settings-nav-label" style="margin-top:0.75rem">Comunicação</span>

        <a href="{{ route('settings.notifications') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.notifications') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
            Notificações
        </a>

        <span class="settings-nav-label" style="margin-top:0.75rem">Plano &amp; Dispositivos</span>

        <a href="{{ route('settings.devices') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.devices') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg>
            Dispositivos
        </a>

          @if($showTeamNav ?? false)
          <a href="{{ route('settings.team') }}" wire:navigate
              class="settings-nav-item {{ request()->routeIs('settings.team') ? 's-active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.035.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.645-6.375-1.766a11.944 11.944 0 0 1-.035-.666l.001-.031m12.409 0a5.969 5.969 0 0 0-1.963-2.312m0 0a5.995 5.995 0 0 0-8.074 0m8.074 0A5.995 5.995 0 0 1 12 15c-1.51 0-2.89.56-3.963 1.484m0 0a5.969 5.969 0 0 0-1.963 2.312M15 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM7.5 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                Equipe &amp; Acesso
                <span class="settings-nav-badge">Time</span>
          </a>
          @endif

        <a href="{{ route('settings.plan') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.plan') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>
            Plano &amp; Assinatura
            <span class="settings-nav-badge">{{ $settingsPlanBadge }}</span>
        </a>

        <a href="{{ route('subscription.plans') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('subscription.plans') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-1.813-2.846a4.5 4.5 0 0 0-1.344-1.344L3 12.75l2.846-.813a4.5 4.5 0 0 0 1.344-1.344L9 7.75l.813 2.843a4.5 4.5 0 0 0 1.344 1.344L14 12.75l-2.843.81a4.5 4.5 0 0 0-1.344 1.344ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.455L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.455L18 2.25l.259 1.036a3.375 3.375 0 0 0 2.455 2.455L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/></svg>
            Trocar plano
            <span class="settings-nav-badge">Upgrade</span>
        </a>

        <a href="{{ route('access.center') }}"
           class="settings-nav-item {{ request()->routeIs('access.center') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.035.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.645-6.375-1.766a11.944 11.944 0 0 1-.035-.666l.001-.031m12.409 0a5.969 5.969 0 0 0-1.963-2.312m0 0a5.995 5.995 0 0 0-8.074 0m8.074 0A5.995 5.995 0 0 1 12 15c-1.51 0-2.89.56-3.963 1.484m0 0a5.969 5.969 0 0 0-1.963 2.312M15 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM7.5 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
            Acesso &amp; Permissoes
            <span class="settings-nav-badge">Conta</span>
        </a>

        <span class="settings-nav-label" style="margin-top:0.75rem">Integrações</span>

        <a href="{{ route('settings.connections') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('settings.connections') ? 's-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
            Conexões
            <span class="settings-nav-badge">Novo</span>
        </a>

        @if(auth()->check() && auth()->user()->isAdmin())
        <span class="settings-nav-label" style="margin-top:0.75rem;color:#ec4899">Administração</span>

        <a href="{{ route('admin.plans.index') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('admin.plans*') ? 's-active' : '' }}" style="color:inherit">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>
            Planos &amp; Assinaturas
        </a>

        <a href="{{ route('admin.subscriptions.index') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('admin.subscriptions*') ? 's-active' : '' }}" style="color:inherit">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            Assinaturas
        </a>

        <a href="{{ route('admin.plans.users') }}" wire:navigate
           class="settings-nav-item {{ request()->routeIs('admin.plans.users') ? 's-active' : '' }}" style="color:inherit">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.05rem;height:1.05rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.035.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.645-6.375-1.766a11.944 11.944 0 0 1-.035-.666l.001-.031m12.409 0a5.969 5.995 0 0 0-1.963-2.312m0 0a5.995 5.995 0 0 0-8.074 0m8.074 0A5.995 5.995 0 0 1 12 15c-1.51 0-2.89.56-3.963 1.484m0 0a5.969 5.969 0 0 0-1.963 2.312M15 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM7.5 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
            Usuarios &amp; Permissoes
        </a>
        @endif
    </nav>

    {{-- TABS MOBILE (horizontal scroll) --}}
    <div class="md:hidden w-full mb-4 overflow-x-auto">
        <div class="flex gap-1 p-1 rounded-xl w-max min-w-full" style="background:rgba(0,0,0,0.04)">
            @foreach(array_values(array_filter([
                ['profile',      'Perfil',         'M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a8.25 8.25 0 0 1 15 0'],
                ['password',     'Senha',           'M16.5 10.5V6.75A4.5 4.5 0 0 0 7.5 6.75v3.75m9 0a2.25 2.25 0 0 1 2.25 2.25v6A2.25 2.25 0 0 1 16.5 21H7.5a2.25 2.25 0 0 1-2.25-2.25v-6a2.25 2.25 0 0 1 2.25-2.25m9 0h-9'],
                ['security',     'Segurança',       'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                ['appearance',   'Aparência',       'M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z'],
                ['system',       'Sistema',         'm10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502'],
                ['notifications','Alertas',         'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0'],
                ['devices',      'Dispositivos',    'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3'],
                ($showTeamNav ?? false) ? ['team', 'Equipe', 'M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.035.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.645-6.375-1.766a11.944 11.944 0 0 1-.035-.666l.001-.031m12.409 0a5.969 5.995 0 0 0-1.963-2.312m0 0a5.995 5.995 0 0 0-8.074 0m8.074 0A5.995 5.995 0 0 1 12 15c-1.51 0-2.89.56-3.963 1.484m0 0a5.969 5.969 0 0 0-1.963 2.312M15 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM7.5 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'] : null,
                ['plan',         'Plano',           'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296'],
                ['connections',  'Conexões',        'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244'],
            ])) as [$route, $label, $icon])
            <a href="{{ route('settings.'.$route) }}" wire:navigate
               class="flex items-center justify-center gap-1 px-3 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ request()->routeIs('settings.'.$route) ? 'bg-white dark:bg-zinc-700 shadow' : '' }}"
               style="{{ request()->routeIs('settings.'.$route) ? 'color:var(--s-accent)' : 'color:#64748b' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- CONTEÚDO --}}
    <div class="settings-content-area">
        {{ $slot }}
    </div>
</div>

<style>
@media (max-width: 767px) {
    .settings-wrap { flex-direction: column; padding: 1rem; gap: 0; }
    .settings-wrap > .md\:hidden { display: flex !important; }
    .settings-nav-panel { display: none !important; }
}
</style>


