@php
    // Cabeçalho único das Configurações, no mesmo visual de Clientes: usuário, abas e resumo da conta.
    $hUser      = Auth::user();
    $hVerified  = $hUser->hasVerifiedEmail();
    $hHasPhone  = ! empty($hUser->phone);
    $hHasAvatar = ! empty($hUser->profile_picture);
    $hHasGoogle = ! empty($hUser->google_id);
    $hDays      = $hUser->created_at ? (int) $hUser->created_at->diffInDays(now()) : 0;

    $hScore = 0;
    if ($hVerified)                $hScore++;
    if (! empty($hUser->password)) $hScore++;
    if ($hHasPhone)                $hScore++;
    if ($hHasAvatar)               $hScore++;
    $hScorePct = intval($hScore / 4 * 100);
    [$hScoreTone, $hScoreLbl] = $hScorePct >= 75 ? ['emerald', 'Boa'] : ($hScorePct >= 50 ? ['amber', 'Razoável'] : ['rose', 'Fraca']);

    $hPlan = null;
    try { $hPlan = $hUser->plan(); } catch (\Throwable $e) { $hPlan = null; }

    $hTabs = [
        ['settings.profile', 'Perfil', 'bi-person'],
        ['settings.password', 'Senha e login', 'bi-key'],
        ['settings.security', 'Segurança', 'bi-shield-check'],
        ['settings.appearance', 'Aparência', 'bi-palette'],
        ['settings.system', 'Sistema e região', 'bi-translate'],
        ['settings.notifications', 'Notificações', 'bi-bell'],
        ['settings.devices', 'Dispositivos', 'bi-display'],
        ['settings.team', 'Equipe e acesso', 'bi-people'],
        ['settings.plan', 'Plano e assinatura', 'bi-patch-check'],
        ['subscription.plans', 'Trocar plano', 'bi-stars'],
        ['access.center', 'Acesso e permissões', 'bi-person-lock'],
        ['settings.connections', 'Conexões', 'bi-link-45deg'],
    ];
    if ($hUser->isAdmin()) {
        $hTabs[] = ['admin.plans.index', 'Planos (admin)', 'bi-gear'];
        $hTabs[] = ['admin.subscriptions.index', 'Assinaturas (admin)', 'bi-receipt'];
        $hTabs[] = ['admin.plans.users', 'Usuários (admin)', 'bi-person-gear'];
    }
    $hTabs = array_values(array_filter($hTabs, fn ($t) => \Illuminate\Support\Facades\Route::has($t[0])));
    $hCurrent = collect($hTabs)->first(fn ($t) => request()->routeIs($t[0] . '*'));
    $hCurrentTitle = $hCurrent[1] ?? 'Configurações';
@endphp

<div class="settings-page-header app-ph relative mx-3 sm:mx-5 lg:mx-6 mb-2 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
    <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-[28px]">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.12),transparent_32%)]"></div>
        <div class="absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>
    </div>

    <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="relative flex items-center justify-center w-14 h-14 shrink-0 rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg">
                    @if($hHasAvatar)
                        <img src="{{ asset('storage/'.$hUser->profile_picture) }}" alt="" class="w-full h-full object-cover">
                    @else
                        <span class="text-lg font-bold text-white">{{ $hUser->initials() }}</span>
                    @endif
                </div>

                <div class="min-w-0">
                    <nav class="hidden sm:flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <a href="{{ route('settings.profile') }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi bi-gear mr-1"></i>Configurações</a>
                        <i class="bi bi-chevron-right text-[10px]"></i>
                        <span class="truncate text-indigo-600 dark:text-indigo-300">{{ $hCurrentTitle }}</span>
                    </nav>
                    <h1 class="flex items-center gap-2 text-xl sm:text-2xl font-bold truncate">
                        <span class="truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">{{ $hUser->name }}</span>
                        @if($hVerified)
                            <i class="bi bi-patch-check-fill text-base text-emerald-500" title="E-mail verificado"></i>
                        @endif
                    </h1>
                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-slate-600 dark:text-slate-400 truncate">{{ $hUser->email }}</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 dark:bg-indigo-500/15 px-2 py-0.5 font-semibold text-indigo-700 dark:text-indigo-300">
                            <i class="bi bi-calendar3"></i>{{ $hDays }} {{ $hDays === 1 ? 'dia' : 'dias' }} de conta
                        </span>
                        @if($hHasPhone)
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 font-semibold text-slate-600 dark:text-slate-300"><i class="bi bi-telephone"></i>{{ $hUser->phone }}</span>
                        @endif
                        @if($hHasGoogle)
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 dark:bg-sky-500/15 px-2 py-0.5 font-semibold text-sky-700 dark:text-sky-300"><i class="bi bi-google"></i>Google</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="app-ph-tabs mt-4 -mx-1 overflow-x-auto">
            <nav class="flex min-w-max items-center gap-1 px-1 pb-1" aria-label="Configurações">
                @foreach($hTabs as [$hRoute, $hLabel, $hIcon])
                    @php $hIsActive = request()->routeIs($hRoute . '*'); @endphp
                    <a href="{{ route($hRoute) }}" @if(str_starts_with($hRoute, 'settings.')) wire:navigate @endif
                       class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold transition
                              {{ $hIsActive
                                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md shadow-indigo-500/25'
                                    : 'text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800/80 hover:text-indigo-700 dark:hover:text-indigo-300' }}">
                        <i class="bi {{ $hIcon }}"></i>{{ $hLabel }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>
</div>

<div class="hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-3 mx-3 sm:mx-5 lg:mx-6 mt-4">
    <x-gestao-stat label="Segurança" :value="$hScorePct . '% · ' . $hScoreLbl" icon="bi-shield-check" :tone="$hScoreTone" />
    <x-gestao-stat label="E-mail" :value="$hVerified ? 'Verificado' : 'Pendente'" icon="bi-envelope" :tone="$hVerified ? 'emerald' : 'amber'" />
    <x-gestao-stat label="Membro desde" :value="$hUser->created_at ? ucfirst($hUser->created_at->locale('pt_BR')->translatedFormat('M/Y')) : '—'" icon="bi-calendar-check" tone="sky" />
    <x-gestao-stat label="Plano" :value="$hPlan?->name ?? 'Grátis'" icon="bi-patch-check" tone="indigo" />
</div>
