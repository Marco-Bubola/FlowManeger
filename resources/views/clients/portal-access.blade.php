<x-layouts.app title="Acesso do Portal do Cliente">
<div class="w-full">

    {{-- ═══════════════════════════════════════════════════ HEADER ══ --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-white/80 via-indigo-50/90 to-purple-50/80 dark:from-slate-800/90 dark:via-indigo-900/30 dark:to-purple-900/30 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 shadow-xl mb-5">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-400/20 via-purple-400/20 to-pink-400/20 rounded-full translate-x-12 -translate-y-12 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-sky-400/10 via-indigo-400/10 to-transparent rounded-full -translate-x-8 translate-y-8 pointer-events-none"></div>

        <div class="relative px-5 py-5 sm:px-7">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                {{-- Back + Avatar + Title --}}
                <div class="flex items-center gap-4">
                    <a href="{{ route('clients.resumo', $client->id) }}"
                       class="group inline-flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-white to-indigo-50 dark:from-slate-700 dark:to-slate-600 hover:from-indigo-50 hover:to-indigo-100 dark:hover:from-slate-600 dark:hover:to-slate-500 border border-white/50 dark:border-slate-600/50 shadow-md hover:shadow-lg transition-all duration-200 flex-shrink-0">
                        <i class="fas fa-arrow-left text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform duration-200 text-sm"></i>
                    </a>

                    <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg shadow-indigo-500/25 ring-4 ring-white/50 dark:ring-slate-700/50">
                        <i class="fas fa-user-shield text-white text-xl"></i>
                    </div>

                    <div>
                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                            <a href="{{ route('clients.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <i class="fas fa-users mr-1"></i>Clientes
                            </a>
                            <i class="fas fa-chevron-right text-[9px]"></i>
                            <a href="{{ route('clients.resumo', $client->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $client->name }}</a>
                            <i class="fas fa-chevron-right text-[9px]"></i>
                            <span class="text-slate-700 dark:text-slate-200 font-medium">Portal</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-slate-100 leading-tight">
                            Acesso do Portal
                            <span class="text-indigo-600 dark:text-indigo-400">{{ $client->name }}</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-lg hidden sm:block">
                            Gerencie credenciais, link de acesso e mensagem de convite do cliente ao portal.
                        </p>
                    </div>
                </div>

                {{-- Status Badges --}}
                <div class="flex flex-wrap gap-2 lg:justify-end">
                    @if($client->portal_active)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Portal Ativo
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/30 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>Portal Inativo
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider {{ $client->email ? 'border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                        <i class="fas fa-envelope text-[9px]"></i>
                        {{ $client->email ? 'Com e-mail' : 'Sem e-mail' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider {{ $client->google_id ? 'border-violet-200 dark:border-violet-800 bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                        <i class="fab fa-google text-[9px]"></i>
                        {{ $client->google_id ? 'Google conectado' : 'Google opcional' }}
                    </span>
                    @if($client->portal_active)
                        <a href="{{ route('portal.login') }}" target="_blank"
                           class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition">
                            <i class="fas fa-external-link-alt text-[9px]"></i>
                            Abrir portal
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ STATUS CARDS ══ --}}
    @php
        $firstAccessPending = $client->portal_force_password_change;
        $profileDone = (bool) $client->portal_profile_completed_at;
        $statusCards = [
            [
                'label'   => 'Login',
                'icon'    => 'fa-at',
                'bg'      => 'bg-indigo-500/10 dark:bg-indigo-400/15',
                'color'   => 'text-indigo-500 dark:text-indigo-400',
                'value'   => $client->portal_login ?? '—',
                'sub'     => 'nome amigável',
                'status'  => '',
            ],
            [
                'label'   => '1º Acesso',
                'icon'    => $firstAccessPending ? 'fa-clock' : 'fa-check-circle',
                'bg'      => $firstAccessPending ? 'bg-amber-500/10 dark:bg-amber-400/15' : 'bg-emerald-500/10 dark:bg-emerald-400/15',
                'color'   => $firstAccessPending ? 'text-amber-500 dark:text-amber-400' : 'text-emerald-500 dark:text-emerald-400',
                'value'   => $firstAccessPending ? 'Pendente' : 'Concluído',
                'sub'     => $firstAccessPending ? 'troca pendente' : 'senha definida',
                'status'  => $firstAccessPending ? 'warn' : 'ok',
            ],
            [
                'label'   => 'Perfil',
                'icon'    => $profileDone ? 'fa-user-check' : 'fa-user-clock',
                'bg'      => $profileDone ? 'bg-emerald-500/10 dark:bg-emerald-400/15' : 'bg-rose-500/10 dark:bg-rose-400/15',
                'color'   => $profileDone ? 'text-emerald-500 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400',
                'value'   => $profileDone ? 'Completo' : 'Incompleto',
                'sub'     => $profileDone ? 'cadastro ok' : 'faltam dados',
                'status'  => $profileDone ? 'ok' : 'err',
            ],
            [
                'label'   => 'Último acesso',
                'icon'    => 'fa-clock',
                'bg'      => $client->portal_last_login_at ? 'bg-sky-500/10 dark:bg-sky-400/15' : 'bg-slate-200/60 dark:bg-slate-700/40',
                'color'   => $client->portal_last_login_at ? 'text-sky-500 dark:text-sky-400' : 'text-slate-400 dark:text-slate-500',
                'value'   => $client->portal_last_login_at?->format('d/m/Y') ?: 'Nunca entrou',
                'sub'     => $client->portal_last_login_at?->format('H:i') ?: 'sem histórico',
                'status'  => '',
            ],
            [
                'label'   => 'Portal',
                'icon'    => $client->portal_active ? 'fa-shield-check' : 'fa-shield-xmark',
                'bg'      => $client->portal_active ? 'bg-emerald-500/10 dark:bg-emerald-400/15' : 'bg-amber-500/10 dark:bg-amber-400/15',
                'color'   => $client->portal_active ? 'text-emerald-500 dark:text-emerald-400' : 'text-amber-500 dark:text-amber-400',
                'value'   => $client->portal_active ? 'Ativo' : 'Inativo',
                'sub'     => $client->portal_active ? 'acesso habilitado' : 'acesso bloqueado',
                'status'  => $client->portal_active ? 'ok' : 'warn',
            ],
            [
                'label'   => 'Google',
                'icon'    => 'fab fa-google',
                'bg'      => $client->google_id ? 'bg-violet-500/10 dark:bg-violet-400/15' : 'bg-slate-200/60 dark:bg-slate-700/40',
                'color'   => $client->google_id ? 'text-violet-500 dark:text-violet-400' : 'text-slate-400 dark:text-slate-500',
                'value'   => $client->google_id ? 'Conectado' : 'Opcional',
                'sub'     => $client->google_id ? 'conta vinculada' : 'não vinculado',
                'status'  => '',
            ],
        ];
    @endphp
    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mb-4">
        @foreach($statusCards as $sc)
        <div class="group relative rounded-xl border border-slate-200/70 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-sm hover:shadow-md transition-all duration-200 p-3 flex flex-col items-center text-center gap-1.5 overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-0.5 {{ $sc['status'] === 'ok' ? 'bg-gradient-to-r from-emerald-400 to-teal-400' : ($sc['status'] === 'warn' ? 'bg-gradient-to-r from-amber-400 to-orange-400' : ($sc['status'] === 'err' ? 'bg-gradient-to-r from-rose-400 to-pink-400' : 'bg-gradient-to-r from-indigo-400/50 to-purple-400/50')) }}"></div>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $sc['bg'] }}">
                <i class="{{ str_starts_with($sc['icon'], 'fab') ? $sc['icon'] : 'fas ' . $sc['icon'] }} {{ $sc['color'] }} text-sm"></i>
            </div>
            <div class="w-full min-w-0">
                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 truncate">{{ $sc['label'] }}</p>
                <p class="text-xs font-black {{ $sc['color'] }} truncate leading-tight mt-0.5">{{ $sc['value'] }}</p>
                <p class="text-[9px] text-slate-400 dark:text-slate-500 truncate leading-none mt-0.5">{{ $sc['sub'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════ ALERTS ══ --}}
    @if(session('portal_access_sent'))
        <div class="mb-3 flex items-start gap-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 text-xs text-emerald-800 dark:text-emerald-300 shadow-sm">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
            <span>{!! session('portal_access_sent') !!}</span>
        </div>
    @endif

    @if(!$client->email)
        <div class="mb-3 flex items-start gap-2.5 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-4 py-3 text-xs text-amber-800 dark:text-amber-300 shadow-sm">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 flex-shrink-0"></i>
            <span>Sem e-mail — acesso via nome de usuário. O cliente pode conectar Google após o primeiro login.</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════ LINHA 1: 3 CARDS ══ --}}
    <div class="grid gap-4 grid-cols-1 md:grid-cols-3">

        {{-- ─── CREDENCIAIS ─── --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50/60 to-purple-50/40 dark:from-indigo-900/20 dark:to-purple-900/10 px-4 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
                    <i class="fas fa-key text-indigo-600 dark:text-indigo-400 text-[10px]"></i>
                </div>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Credenciais</p>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Credenciais do portal</p>
                </div>
            </div>
            <div class="p-4 flex flex-col gap-3 flex-1">
                {{-- Login --}}
                <div class="space-y-1">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 flex items-center gap-1">
                        <i class="fas fa-at text-indigo-400 text-[8px]"></i>Login
                    </p>
                    <div class="flex items-center gap-1.5">
                        <input id="portalAccessLoginPage" readonly
                               value="{{ session('portal_access_login', $client->portal_login) }}"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/50 px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-400/50 transition">
                        <button type="button" onclick="payCopy('portalAccessLoginPage', this)"
                                class="flex-shrink-0 rounded-lg bg-slate-900 dark:bg-slate-700 px-3 py-2 text-[10px] font-bold text-white transition hover:bg-indigo-600 active:scale-95">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                {{-- Senha --}}
                <div class="space-y-1">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 flex items-center gap-1">
                        <i class="fas fa-lock text-purple-400 text-[8px]"></i>Senha temporária
                    </p>
                    <div class="flex items-center gap-1.5">
                        <div class="relative flex-1">
                            <input id="portalAccessPasswordPage" readonly
                                   type="{{ session('portal_access_password') ? 'password' : 'text' }}"
                                   value="{{ session('portal_access_password', '••••••••••••') }}"
                                   class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/50 px-3 py-2 pr-8 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-400/50 transition">
                            @if(session('portal_access_password'))
                                <button type="button" onclick="toggleSecret('portalAccessPasswordPage','portalAccessPasswordEye')"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                                    <i id="portalAccessPasswordEye" class="fas fa-eye text-xs"></i>
                                </button>
                            @endif
                        </div>
                        @if(session('portal_access_password'))
                            <button type="button" onclick="payCopy('portalAccessPasswordPage', this)"
                                    class="flex-shrink-0 rounded-lg bg-slate-900 dark:bg-slate-700 px-3 py-2 text-[10px] font-bold text-white transition hover:bg-purple-600 active:scale-95">
                                <i class="fas fa-copy"></i>
                            </button>
                        @else
                            <span class="flex-shrink-0 rounded-lg border border-dashed border-slate-200 dark:border-slate-600 px-2 py-2 text-[9px] text-slate-400 bg-slate-50 dark:bg-slate-800/50 whitespace-nowrap">gere uma senha</span>
                        @endif
                    </div>
                </div>
                {{-- URL --}}
                <div class="space-y-1">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 flex items-center gap-1">
                        <i class="fas fa-link text-sky-400 text-[8px]"></i>Link do portal
                    </p>
                    <div class="flex items-center gap-1.5">
                        <input id="portalAccessUrlPage" readonly
                               value="{{ session('portal_access_url', route('portal.login')) }}"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/50 px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-400/50 transition min-w-0">
                        <button type="button" onclick="payCopy('portalAccessUrlPage', this)"
                                class="flex-shrink-0 rounded-lg bg-slate-900 dark:bg-slate-700 px-3 py-2 text-[10px] font-bold text-white transition hover:bg-sky-600 active:scale-95">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                {{-- Reset URL --}}
                @if(session('portal_access_reset_url'))
                    <div class="space-y-1">
                        <p class="text-[9px] font-bold uppercase tracking-widest flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                            <i class="fas fa-shield-alt text-[8px]"></i>Link de redefinição
                        </p>
                        <div class="flex items-center gap-1.5">
                            <input id="portalAccessResetPage" readonly
                                   value="{{ session('portal_access_reset_url') }}"
                                   class="w-full rounded-lg border border-emerald-200 dark:border-emerald-700 bg-emerald-50/50 dark:bg-emerald-900/20 px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-400/50 transition min-w-0">
                            <button type="button" onclick="payCopy('portalAccessResetPage', this)"
                                    class="flex-shrink-0 rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-2 text-[10px] font-bold text-white transition active:scale-95">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── MENSAGEM PARA ENVIO ─── --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-emerald-50/60 to-teal-50/40 dark:from-emerald-900/20 dark:to-teal-900/10 px-4 py-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50">
                        <i class="fas fa-paper-plane text-emerald-600 dark:text-emerald-400 text-[10px]"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Envio manual</p>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Mensagem pronta</p>
                    </div>
                </div>
                <div class="flex gap-1.5">
                    <button type="button" onclick="payCopy('portalWhatsappMessagePage', this)"
                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-[10px] font-bold text-white transition active:scale-95">
                        <i class="fas fa-copy text-[9px]"></i>Copiar
                    </button>
                    @if(session('portal_access_whatsapp_url'))
                        <a href="{{ session('portal_access_whatsapp_url') }}" target="_blank"
                           class="inline-flex items-center gap-1 rounded-lg bg-green-500 hover:bg-green-600 px-3 py-1.5 text-[10px] font-bold text-white transition active:scale-95">
                            <i class="fab fa-whatsapp text-xs"></i>WA
                        </a>
                    @endif
                </div>
            </div>
            <div class="p-4 flex-1 flex flex-col">
                <textarea id="portalWhatsappMessagePage" readonly
                          class="flex-1 w-full min-h-[160px] rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 px-3 py-2.5 text-xs text-slate-700 dark:text-slate-300 leading-relaxed resize-none focus:outline-none focus:ring-1 focus:ring-emerald-400/50 transition font-mono">{{ session('portal_access_whatsapp_message', 'Ola, ' . $client->name . '!\n\nPortal: ' . route('portal.login') . '\nLogin: ' . $client->portal_login . '\n\nSe quiser, depois conecte sua conta Google dentro do portal.') }}</textarea>
            </div>
        </div>

        {{-- ─── AÇÕES RÁPIDAS ─── --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-50/60 to-indigo-50/40 dark:from-sky-900/20 dark:to-indigo-900/10 px-4 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/50">
                    <i class="fas fa-bolt text-sky-600 dark:text-sky-400 text-[10px]"></i>
                </div>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Ações</p>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Ações rápidas</p>
                </div>
            </div>
            <div class="p-3 flex flex-col gap-2 flex-1">
                <form method="POST" action="{{ route('clients.portal.send-access', $client) }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 px-4 py-2.5 text-xs font-bold text-white shadow-md hover:shadow-lg transition-all duration-200 active:scale-[.98]">
                        <i class="fas fa-paper-plane text-xs"></i>
                        {{ $client->portal_active ? 'Gerar nova senha' : 'Criar acesso com senha' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('clients.portal.reset-access-link', $client) }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 px-4 py-2.5 text-xs font-bold text-indigo-700 dark:text-indigo-400 transition-all duration-200 active:scale-[.98]">
                        <i class="fas fa-link text-xs"></i>
                        Gerar link de redefinição
                    </button>
                </form>
                @if($client->portal_active)
                    <form method="POST" action="{{ route('clients.portal.revoke-access', $client) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 px-4 py-2.5 text-xs font-bold text-rose-600 dark:text-rose-400 transition-all duration-200 active:scale-[.98]">
                            <i class="fas fa-user-lock text-xs"></i>
                            Revogar acesso
                        </button>
                    </form>
                @endif
                <div class="mt-auto rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-3 py-2.5 text-[10px] text-slate-500 dark:text-slate-400 leading-4">
                    <p class="font-semibold text-slate-700 dark:text-slate-300 mb-0.5">Fluxo recomendado</p>
                    Novo cliente → senha automática. Recuperar acesso → link de redefinição.
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ LINHA 2: 2 CARDS ══ --}}
    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 mt-4">

        {{-- ─── SITUAÇÃO DO CLIENTE ─── --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-purple-50/60 to-pink-50/40 dark:from-purple-900/20 dark:to-pink-900/10 px-4 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/50">
                    <i class="fas fa-user text-purple-600 dark:text-purple-400 text-[10px]"></i>
                </div>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Cliente</p>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Situação do cliente</p>
                </div>
            </div>
            <div class="grid grid-cols-2 divide-x divide-y divide-slate-100 dark:divide-slate-700">
                @php
                    $clientInfoRows = [
                        ['label' => 'Nome', 'icon' => 'fa-user', 'color' => 'text-indigo-400', 'value' => $client->name],
                        ['label' => 'E-mail', 'icon' => 'fa-envelope', 'color' => 'text-sky-400', 'value' => $client->email ?: '<span class="text-slate-400 dark:text-slate-500 italic text-[10px]">Não informado</span>'],
                        ['label' => 'Google', 'icon' => 'fab fa-google', 'color' => 'text-rose-400', 'value' => $client->google_id ? '<span class="text-emerald-600 dark:text-emerald-400">Conectado</span>' : '<span class="text-slate-400 dark:text-slate-500">Não conectado</span>'],
                        ['label' => 'Último acesso', 'icon' => 'fa-clock', 'color' => 'text-teal-400', 'value' => $client->portal_last_login_at?->format('d/m/Y H:i') ?: '<span class="text-slate-400 dark:text-slate-500 italic text-[10px]">Ainda não entrou</span>'],
                    ];
                @endphp
                @foreach($clientInfoRows as $row)
                    <div class="flex items-center gap-2 px-4 py-3">
                        <i class="{{ (str_starts_with($row['icon'], 'fab') ? $row['icon'] : 'fas ' . $row['icon']) }} {{ $row['color'] }} text-[10px] w-3 flex-shrink-0"></i>
                        <div class="min-w-0">
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $row['label'] }}</p>
                            <p class="text-[11px] font-semibold text-slate-800 dark:text-slate-100 truncate">{!! $row['value'] !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ─── COMO FUNCIONA ─── --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-teal-50/60 to-cyan-50/40 dark:from-teal-900/20 dark:to-cyan-900/10 px-4 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-900/50">
                    <i class="fas fa-info-circle text-teal-600 dark:text-teal-400 text-[10px]"></i>
                </div>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Orientação</p>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Como funciona</p>
                </div>
            </div>
            <div class="p-3 grid grid-cols-1 gap-1.5">
                @foreach([
                    ['icon' => 'fa-at', 'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-100 dark:border-indigo-800/50', 'text' => 'Login único por cliente em formato legível, sem depender de e-mail.'],
                    ['icon' => 'fa-lock', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-100 dark:border-purple-800/50', 'text' => 'A senha temporária aparece só na geração. Recuperação via link seguro.'],
                    ['icon' => 'fab fa-google', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50 dark:bg-rose-900/20 border-rose-100 dark:border-rose-800/50', 'text' => 'O cliente pode conectar o Google opcionalmente após o primeiro login.'],
                ] as $tip)
                    <div class="flex items-start gap-2 rounded-lg border {{ $tip['bg'] }} px-3 py-2">
                        <i class="{{ (str_starts_with($tip['icon'], 'fab') ? $tip['icon'] : 'fas ' . $tip['icon']) }} {{ $tip['color'] }} text-[10px] mt-0.5 flex-shrink-0"></i>
                        <p class="text-[10px] text-slate-600 dark:text-slate-400 leading-4">{{ $tip['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function payCopy(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const val = el.tagName === 'TEXTAREA' ? el.value : el.value;
    navigator.clipboard.writeText(val).then(() => {
        if (!btn) return;
        const icon = btn.querySelector('i');
        const prev = icon ? icon.className : '';
        if (icon) { icon.className = 'fas fa-check'; }
        btn.classList.add('!bg-emerald-600', 'dark:!bg-emerald-600');
        setTimeout(() => {
            if (icon) icon.className = prev;
            btn.classList.remove('!bg-emerald-600', 'dark:!bg-emerald-600');
        }, 1500);
    });
}

function toggleSecret(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    if (!field || !icon) return;
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</x-layouts.app>