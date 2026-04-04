@php
    $hUser      = Auth::user();
    $hVerified  = $hUser->hasVerifiedEmail();
    $hHasPhone  = ! empty($hUser->phone);
    $hHasAvatar = ! empty($hUser->profile_picture);
    $hHasGoogle = ! empty($hUser->google_id);
    $hDays      = $hUser->created_at ? (int) $hUser->created_at->diffInDays(now()) : 0;

    $hScore    = 0;
    if ($hVerified)              $hScore++;
    if (! empty($hUser->password)) $hScore++;
    if ($hHasPhone)              $hScore++;
    if ($hHasAvatar)             $hScore++;
    $hScorePct = intval($hScore / 4 * 100);

    if ($hScorePct >= 75) {
        $hScoreClr = '#10b981';
        $hScoreLbl = 'Boa';
    } elseif ($hScorePct >= 50) {
        $hScoreClr = '#f59e0b';
        $hScoreLbl = 'Razoavel';
    } else {
        $hScoreClr = '#ef4444';
        $hScoreLbl = 'Fraca';
    }

    $routeName = request()->route() ? request()->route()->getName() : 'settings.profile';
    $routeParts = explode('.', $routeName ?? 'settings.profile');
    $hCurrentPage = count($routeParts) > 1 ? $routeParts[count($routeParts) - 1] : 'profile';

    $hTitles = [
        'profile'       => 'Perfil',
        'password'      => 'Senha e Login',
        'security'      => 'Seguranca',
        'appearance'    => 'Aparencia',
        'system'        => 'Sistema e Regiao',
        'notifications' => 'Notificacoes',
        'devices'       => 'Dispositivos',
        'plan'          => 'Plano e Assinatura',
        'connections'   => 'Conexoes',
    ];
    $hCurrentTitle = $hTitles[$hCurrentPage] ?? 'Configuracoes';
@endphp

<div class="sh-header">
    {{-- Decorações de fundo --}}
    <div class="sh-bg-blob sh-bg-blob-1"></div>
    <div class="sh-bg-blob sh-bg-blob-2"></div>

    <div class="sh-inner">
        {{-- Linha principal: avatar + info + breadcrumb --}}
        <div class="sh-top-row">
            <div class="sh-user-block">
                {{-- Avatar --}}
                <div class="sh-avatar">
                    @if($hHasAvatar)
                        <img src="{{ asset('storage/'.$hUser->profile_picture) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">
                    @else
                        <span class="sh-avatar-init">{{ $hUser->initials() }}</span>
                    @endif
                    <div class="sh-avatar-ring"></div>
                </div>
                {{-- Nome + meta --}}
                <div class="sh-user-info">
                    <div class="sh-user-name-row">
                        <h1 class="sh-user-name">{{ $hUser->name }}</h1>
                        @if($hVerified)
                            <span class="sh-verified-badge" title="E-mail verificado">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.9rem;height:0.9rem"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                            </span>
                        @endif
                    </div>
                    <span class="sh-user-email">{{ $hUser->email }}</span>
                    <div class="sh-user-meta">
                        <span class="sh-meta-chip">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.7rem;height:0.7rem"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/></svg>
                            {{ $hDays }} dias de conta
                        </span>
                        @if($hHasPhone)
                        <span class="sh-meta-chip">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.7rem;height:0.7rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            {{ $hUser->phone }}
                        </span>
                        @endif
                        @if($hHasGoogle)
                        <span class="sh-meta-chip sh-meta-chip--google">
                            <svg viewBox="0 0 24 24" style="width:0.7rem;height:0.7rem" fill="currentColor"><path d="M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 1 1 0-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0 0 12.545 2C7.021 2 2.543 6.477 2.543 12s4.478 10 10.002 10c8.396 0 10.249-7.85 9.426-11.748l-9.426-.013Z"/></svg>
                            Google
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Breadcrumb/Página atual --}}
            <div class="sh-page-crumb">
                <span class="sh-crumb-sep">Configurações</span>
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.8rem;height:0.8rem;color:#94a3b8"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <span class="sh-crumb-current">{{ $hCurrentTitle }}</span>
            </div>
        </div>

        {{-- Cards de stats --}}
        <div class="sh-stats-row">
            {{-- Score de segurança --}}
            <div class="sh-stat-card">
                <div class="sh-stat-icon" style="background:{{ $hScoreClr }}1a;color:{{ $hScoreClr }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                </div>
                <div class="sh-stat-body">
                    <p class="sh-stat-label">Segurança</p>
                    <p class="sh-stat-val" style="color:{{ $hScoreClr }}">{{ $hScorePct }}% · <span style="font-weight:500;font-size:0.8rem">{{ $hScoreLbl }}</span></p>
                </div>
            </div>

            {{-- E-mail --}}
            <div class="sh-stat-card">
                <div class="sh-stat-icon" style="background:{{ $hVerified ? '#10b98120' : '#f59e0b20' }};color:{{ $hVerified ? '#10b981' : '#f59e0b' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                </div>
                <div class="sh-stat-body">
                    <p class="sh-stat-label">E-mail</p>
                    <p class="sh-stat-val" style="color:{{ $hVerified ? '#10b981' : '#f59e0b' }}">{{ $hVerified ? 'Verificado' : 'Pendente' }}</p>
                </div>
            </div>

            {{-- Conta desde --}}
            <div class="sh-stat-card">
                <div class="sh-stat-icon" style="background:rgba(var(--s-accent-rgb),0.1);color:var(--s-accent)">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/></svg>
                </div>
                <div class="sh-stat-body">
                    <p class="sh-stat-label">Membro desde</p>
                    <p class="sh-stat-val">{{ $hUser->created_at ? $hUser->created_at->format('M/Y') : '—' }}</p>
                </div>
            </div>

            {{-- Completude do perfil --}}
            <div class="sh-stat-card">
                <div class="sh-stat-icon" style="background:rgba(99,102,241,0.1);color:#6366f1">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
                <div class="sh-stat-body">
                    <p class="sh-stat-label">Perfil</p>
                    <p class="sh-stat-val" style="color:#6366f1" id="sh-profile-pct">—</p>
                </div>
            </div>

            {{-- Plano --}}
            <div class="sh-stat-card">
                <div class="sh-stat-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>
                </div>
                <div class="sh-stat-body">
                    <p class="sh-stat-label">Plano</p>
                    <p class="sh-stat-val" style="color:#f59e0b">Gratuito</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        // Calcula completude do perfil dinamicamente
        const fields = [
            document.querySelector('[wire\\:model="name"]'),
            document.querySelector('[wire\\:model="email"]'),
            document.querySelector('[wire\\:model="phone"]'),
            document.querySelector('[wire\\:model="location"]'),
            document.querySelector('[wire\\:model="about_me"]'),
        ];
        const hasAvatar = {{ $hHasAvatar ? 'true' : 'false' }};
        let filled = hasAvatar ? 1 : 0;
        let total = 6;
        fields.forEach(f => { if (f && f.value && f.value.trim()) filled++; });
        const pct = Math.round(filled / total * 100);
        const el = document.getElementById('sh-profile-pct');
        if (el) el.textContent = pct + '%';
        // Também atualiza a barra de completude do card de perfil se existir
        const fill = document.getElementById('completionFill');
        const pctEl = document.getElementById('completionPct');
        if (fill) fill.style.width = pct + '%';
        if (pctEl) pctEl.textContent = pct + '%';
    })();
</script>
