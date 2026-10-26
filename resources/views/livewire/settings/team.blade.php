<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

@php
    $user = auth()->user();
    $team = $user->activeTeam();
    $teamMember = $user->activeTeamMember();
    $incomingInvitations = $user->pendingTeamInvitations();
    $roleLabels = \App\Models\TeamMember::ROLE_LABELS;
    $permissionLabels = \App\Models\TeamMember::PERMISSION_LABELS;
    $teamModules = \App\Models\Team::MODULES;
    $teamSettings = $team?->settings ?? \App\Models\Team::DEFAULT_SETTINGS;
    $canCreateTeam = $user->canCreateOrManageTeam() && !$team;
    $canManageTeam = $team && ($user->isTeamOwner($team) || $user->teamCan('manage_team_settings', $team));
    $canInvite = $team && ($user->isTeamOwner($team) || $user->teamCan('invite_members', $team));
    $canRemoveMembers = $team && ($user->isTeamOwner($team) || $user->teamCan('remove_members', $team));
    $canTransferRecords = $team && ($user->isTeamOwner($team) || $user->teamCan('transfer_records', $team));
    $members = $team ? $team->members()->with('user')->latest()->get() : collect();
    $pendingTeamInvitations = $team ? $team->invitations()->pending()->latest()->get() : collect();
    $teamUsers = $team ? collect([$team->owner])->filter()->merge($members->pluck('user')->filter())->unique('id')->values() : collect();
    $teamSeatLimit = $user->isAdmin() ? 50 : max(1, (int) ($user->plan()->max_users ?? 1));
    $sharedModuleCount = $team
        ? collect($teamModules)->keys()->filter(fn($module) => $team->shareEnabled($module))->count()
        : 0;
@endphp
@php
    $myRole = $team ? ($user->isTeamOwner($team) ? 'Proprietário' : ($roleLabels[$teamMember?->role] ?? 'Membro')) : null;
    $seatsUsed = $team ? $team->currentMemberCount() : 1;
    $seatsMax = $team ? max(1, $team->allowedSeatCount()) : $teamSeatLimit;
    $seatsPct = min(100, (int) round($seatsUsed / max(1, $seatsMax) * 100));
    $initials = fn ($name) => collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
    $tones = ['from-sky-500 to-blue-600', 'from-emerald-500 to-teal-500', 'from-amber-500 to-orange-500', 'from-pink-500 to-rose-500', 'from-violet-500 to-purple-600', 'from-cyan-500 to-sky-600'];
    $moduleIcons = ['products' => 'bi-box-seam', 'clients' => 'bi-people', 'sales' => 'bi-cart-check', 'finances' => 'bi-wallet2'];
    $permIcons = [
        'access_products' => 'bi-box-seam', 'access_clients' => 'bi-people', 'access_sales' => 'bi-cart-check', 'access_finances' => 'bi-wallet2',
        'invite_members' => 'bi-person-plus', 'remove_members' => 'bi-person-dash', 'manage_team_settings' => 'bi-sliders', 'transfer_records' => 'bi-arrow-left-right',
    ];
@endphp

<section class="settings-team-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="tm-page">
        @if(session('success'))
            <div class="pl-flash"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="pl-flash is-error"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- Topo: equipe, papel e vagas usadas --}}
        <div class="dv-hero bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
            <span class="dv-hero-glow"></span>
            <div class="dv-hero-ico"><i class="bi bi-people-fill"></i></div>
            <div class="dv-hero-txt">
                <span class="pl-kicker"><i class="bi bi-diagram-3"></i> {{ $team ? 'Sua equipe' : 'Equipe' }}</span>
                <h2>{{ $team?->name ?? 'Trabalhe junto com sua equipe' }}</h2>
                <p>
                    @if($team)
                        {{ $team->description ?: 'Cada pessoa vê só o que você liberar.' }}
                    @else
                        Convide quem vende ou ajuda na loja e escolha o que cada pessoa pode ver e fazer.
                    @endif
                </p>
                @if($team)
                    <div class="tm-chips">
                        <span><i class="bi bi-person-badge"></i> {{ $myRole }}</span>
                        <span><i class="bi bi-person-check"></i> Responsável: {{ $team->owner->name }}</span>
                        <span><i class="bi bi-share"></i> {{ $sharedModuleCount }} de {{ count($teamModules) }} áreas compartilhadas</span>
                    </div>
                @endif
            </div>
            <div class="tm-seats">
                <div class="tm-seats-top"><b>{{ $seatsUsed }}</b><small>de {{ $seatsMax }} {{ $seatsMax === 1 ? 'usuário' : 'usuários' }}</small></div>
                <div class="tm-seats-bar"><span style="width: {{ $seatsPct }}%"></span></div>
                <small>{{ $seatsMax - $seatsUsed > 0 ? ($seatsMax - $seatsUsed) . ' ' . ($seatsMax - $seatsUsed === 1 ? 'vaga livre' : 'vagas livres') : 'Sem vagas livres' }}</small>
            </div>
        </div>

        <div class="pl-grid">
            <div class="pl-main">
                @if($team)
                    {{-- Pessoas da equipe --}}
                    <div class="ap-card">
                        <div class="ap-card-head">
                            <span class="ap-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-people"></i></span>
                            <div class="min-w-0">
                                <h2>Pessoas da equipe</h2>
                                <p>Abra uma pessoa para mudar o nível de acesso e as permissões.</p>
                            </div>
                        </div>

                        <div class="tm-list">
                            <div class="tm-row is-owner">
                                <span class="tm-av bg-gradient-to-br from-indigo-500 to-purple-600">{{ $initials($team->owner->name) }}</span>
                                <div class="min-w-0">
                                    <b>{{ $team->owner->name }} @if($team->owner->id === $user->id)<em>você</em>@endif</b>
                                    <small>{{ $team->owner->email }}</small>
                                </div>
                                <span class="tm-role is-owner"><i class="bi bi-star-fill"></i> <span>Proprietário</span></span>
                            </div>

                            @foreach($members as $member)
                                @php $mOn = collect($permissionLabels)->keys()->filter(fn ($p) => ! empty($member->permissions[$p]))->count(); @endphp
                                <details class="tm-member" wire:key="member-{{ $member->id }}">
                                    <summary class="tm-row">
                                        <span class="tm-av bg-gradient-to-br {{ $tones[$loop->index % count($tones)] }}">{{ $initials($member->user->name) }}</span>
                                        <div class="min-w-0">
                                            <b>{{ $member->user->name }} @if($member->user_id === $user->id)<em>você</em>@endif</b>
                                            <small>{{ $member->user->email }} · entrou {{ optional($member->joined_at)->format('d/m/Y') ?: 'agora' }} · {{ $mOn }} {{ $mOn === 1 ? 'permissão' : 'permissões' }}</small>
                                        </div>
                                        <span class="tm-role">{{ $roleLabels[$member->role] ?? ucfirst($member->role) }}</span>
                                        <i class="bi bi-chevron-down tm-caret"></i>
                                    </summary>

                                    <form method="POST" action="{{ route('settings.team.members.update', $member) }}" class="tm-member-body">
                                        @csrf
                                        @method('PATCH')

                                        @if($canManageTeam)
                                            <label class="tm-label">Nível de acesso</label>
                                            <div class="tm-roles">
                                                @foreach($roleLabels as $role => $roleLabel)
                                                    <label class="tm-role-opt">
                                                        <input type="radio" name="role" value="{{ $role }}" {{ $member->role === $role ? 'checked' : '' }}>
                                                        <span>{{ $roleLabel }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <input type="hidden" name="role" value="{{ $member->role }}">
                                        @endif

                                        <label class="tm-label">O que essa pessoa pode fazer</label>
                                        <div class="tm-perms">
                                            @foreach($permissionLabels as $permission => $label)
                                                <label class="tm-perm">
                                                    <i class="bi {{ $permIcons[$permission] ?? 'bi-check2' }}"></i>
                                                    <span>{{ $label }}</span>
                                                    <input type="checkbox" class="tm-switch" name="{{ $permission }}" value="1" {{ ! empty($member->permissions[$permission]) ? 'checked' : '' }} {{ $canManageTeam ? '' : 'disabled' }}>
                                                </label>
                                            @endforeach
                                        </div>

                                        @if($canManageTeam || $canRemoveMembers)
                                            <div class="tm-actions">
                                                @if($canRemoveMembers)
                                                    <button type="submit" form="remove-member-{{ $member->id }}" class="tm-btn-danger" onclick="return confirm('Remover {{ addslashes($member->user->name) }} da equipe?')"><i class="bi bi-person-dash"></i> Remover da equipe</button>
                                                @endif
                                                @if($canManageTeam)
                                                    <button type="submit" class="tm-btn"><i class="bi bi-check2"></i> Salvar permissões</button>
                                                @endif
                                            </div>
                                        @endif
                                    </form>

                                    @if($canRemoveMembers)
                                        <form id="remove-member-{{ $member->id }}" method="POST" action="{{ route('settings.team.members.destroy', $member) }}" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </details>
                            @endforeach

                            @if($members->isEmpty())
                                <div class="tm-empty"><i class="bi bi-person-plus"></i> Ninguém entrou ainda. Mande um convite ao lado.</div>
                            @endif
                        </div>

                        @if(! $user->isTeamOwner($team))
                            <form method="POST" action="{{ route('settings.team.leave') }}" class="tm-actions">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tm-btn-ghost" onclick="return confirm('Sair desta equipe?')"><i class="bi bi-box-arrow-right"></i> Sair da equipe</button>
                            </form>
                        @endif
                    </div>

                    @if($canManageTeam)
                        {{-- O que a equipe compartilha --}}
                        <div class="ap-card">
                            <div class="ap-card-head">
                                <span class="ap-ico bg-gradient-to-br from-violet-500 to-fuchsia-500"><i class="bi bi-sliders"></i></span>
                                <div class="min-w-0">
                                    <h2>Dados da equipe e áreas compartilhadas</h2>
                                    <p>Escolha quais áreas do app a equipe enxerga.</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('settings.team.update') }}">
                                @csrf
                                @method('PATCH')

                                <div class="tm-form-grid">
                                    <div>
                                        <label class="tm-label">Nome da equipe</label>
                                        <input name="name" type="text" value="{{ $team->name }}" class="tm-input">
                                    </div>
                                    <div>
                                        <label class="tm-label">Limite de pessoas</label>
                                        <input name="max_members" type="number" min="1" max="{{ $team->allowedSeatCount() }}" value="{{ $team->max_members }}" class="tm-input">
                                    </div>
                                    <div class="tm-span">
                                        <label class="tm-label">Descrição</label>
                                        <textarea name="description" rows="2" class="tm-input" placeholder="Ex.: vendedoras da loja">{{ $team->description }}</textarea>
                                    </div>
                                </div>

                                <label class="tm-label">Áreas compartilhadas</label>
                                <div class="tm-modules">
                                    @foreach($teamModules as $module => $label)
                                        <label class="tm-module">
                                            <input type="checkbox" name="share_{{ $module }}" value="1" {{ ! empty($teamSettings['share_' . $module]) ? 'checked' : '' }}>
                                            <span class="tm-module-ico"><i class="bi {{ $moduleIcons[$module] ?? 'bi-grid' }}"></i></span>
                                            <b>{{ $label }}</b>
                                            <small class="tm-on">Compartilhado</small>
                                            <small class="tm-off">Só seu</small>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="tm-actions">
                                    <button type="submit" class="tm-btn"><i class="bi bi-check2"></i> Salvar equipe</button>
                                </div>
                            </form>
                        </div>
                    @endif
                @else
                    @if($incomingInvitations->isNotEmpty())
                        <div class="ap-card">
                            <div class="ap-card-head">
                                <span class="ap-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-envelope-open"></i></span>
                                <div class="min-w-0">
                                    <h2>Convites para você</h2>
                                    <p>Aceite para começar a ver os dados que a equipe liberou.</p>
                                </div>
                            </div>
                            <div class="tm-list">
                                @foreach($incomingInvitations as $invitation)
                                    <div class="tm-row tm-row-wrap">
                                        <span class="tm-av bg-gradient-to-br from-indigo-500 to-purple-600">{{ $initials($invitation->team->name) }}</span>
                                        <div class="min-w-0">
                                            <b>{{ $invitation->team->name }}</b>
                                            <small>{{ $roleLabels[$invitation->role] ?? ucfirst($invitation->role) }} · expira {{ optional($invitation->expires_at)->format('d/m/Y H:i') }}</small>
                                        </div>
                                        <div class="tm-row-actions">
                                            <form method="POST" action="{{ route('settings.team.invitations.destroy', $invitation) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="tm-btn-ghost">Recusar</button>
                                            </form>
                                            <form method="POST" action="{{ route('settings.team.invitations.accept', $invitation) }}">
                                                @csrf
                                                <button type="submit" class="tm-btn"><i class="bi bi-check2"></i> Aceitar</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($canCreateTeam)
                        <div class="ap-card">
                            <div class="ap-card-head">
                                <span class="ap-ico bg-gradient-to-br from-violet-500 to-fuchsia-500"><i class="bi bi-plus-lg"></i></span>
                                <div class="min-w-0">
                                    <h2>Criar sua equipe</h2>
                                    <p>Depois de criar, você convida as pessoas pelo e-mail.</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('settings.team.store') }}">
                                @csrf
                                <div class="tm-form-grid">
                                    <div class="tm-span">
                                        <label class="tm-label">Nome da equipe</label>
                                        <input name="name" type="text" required class="tm-input" placeholder="Ex.: Equipe da loja">
                                    </div>
                                    <div class="tm-span">
                                        <label class="tm-label">Descrição</label>
                                        <textarea name="description" rows="2" class="tm-input" placeholder="Para que serve essa equipe?"></textarea>
                                    </div>
                                </div>
                                <div class="tm-actions">
                                    <button type="submit" class="tm-btn"><i class="bi bi-people"></i> Criar equipe</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    @if(! $canCreateTeam && $incomingInvitations->isEmpty())
                        <div class="ap-card tm-upsell">
                            <div class="tm-upsell-art">
                                @foreach(['from-sky-500 to-blue-600', 'from-emerald-500 to-teal-500', 'from-amber-500 to-orange-500'] as $t)
                                    <span class="tm-av bg-gradient-to-br {{ $t }}"><i class="bi bi-person-fill"></i></span>
                                @endforeach
                                <span class="tm-av tm-av-plus"><i class="bi bi-plus-lg"></i></span>
                            </div>
                            <h2>Seu plano é para uma pessoa só</h2>
                            <p>Para colocar vendedoras ou ajudantes no app, cada uma com o próprio login, mude para um plano com equipe. Se alguém te convidar, o convite aparece aqui.</p>
                            <ul class="tm-upsell-list">
                                <li><i class="bi bi-check2-circle"></i> Cada pessoa com login próprio</li>
                                <li><i class="bi bi-check2-circle"></i> Você escolhe o que cada uma vê: produtos, clientes, vendas ou finanças</li>
                                <li><i class="bi bi-check2-circle"></i> Passe clientes e vendas de uma pessoa para outra</li>
                            </ul>
                            @if(\Illuminate\Support\Facades\Route::has('subscription.plans'))
                                <a href="{{ route('subscription.plans') }}" class="tm-btn"><i class="bi bi-stars"></i> Ver planos com equipe</a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>

            <aside class="pl-side">
                @if($team && $canInvite)
                    <div class="ap-card">
                        <div class="ap-card-head">
                            <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-person-plus"></i></span>
                            <div class="min-w-0">
                                <h2>Convidar pessoa</h2>
                                <p>Escolha o acesso antes de mandar o convite.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('settings.team.invite') }}">
                            @csrf
                            <label class="tm-label">E-mail</label>
                            <input type="email" name="email" required class="tm-input" placeholder="pessoa@email.com">
                            <label class="tm-label">Nível de acesso</label>
                            <select name="role" class="tm-input">
                                @foreach($roleLabels as $role => $roleLabel)
                                    <option value="{{ $role }}">{{ $roleLabel }}</option>
                                @endforeach
                            </select>
                            <details class="tm-more">
                                <summary><i class="bi bi-toggles"></i> Permissões <i class="bi bi-chevron-down tm-caret"></i></summary>
                                <div class="tm-perms is-compact">
                                    @foreach($permissionLabels as $permission => $label)
                                        <label class="tm-perm">
                                            <i class="bi {{ $permIcons[$permission] ?? 'bi-check2' }}"></i>
                                            <span>{{ $label }}</span>
                                            <input type="checkbox" class="tm-switch" name="{{ $permission }}" value="1">
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                            <button type="submit" class="tm-btn tm-btn-block"><i class="bi bi-send"></i> Gerar convite</button>
                        </form>
                    </div>
                @endif

                @if($team && $pendingTeamInvitations->isNotEmpty())
                    <div class="ap-card">
                        <div class="ap-card-head">
                            <span class="ap-ico bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-hourglass-split"></i></span>
                            <div class="min-w-0">
                                <h2>Convites enviados</h2>
                                <p>Ainda não aceitos.</p>
                            </div>
                        </div>
                        <div class="tm-list">
                            @foreach($pendingTeamInvitations as $invitation)
                                <div class="tm-row">
                                    <span class="tm-av tm-av-wait"><i class="bi bi-envelope"></i></span>
                                    <div class="min-w-0">
                                        <b>{{ $invitation->email }}</b>
                                        <small>{{ $roleLabels[$invitation->role] ?? ucfirst($invitation->role) }} · expira {{ optional($invitation->expires_at)->format('d/m') }}</small>
                                    </div>
                                    <form method="POST" action="{{ route('settings.team.invitations.destroy', $invitation) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dv-end" title="Cancelar convite"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($team && $canTransferRecords && $teamUsers->count() > 1)
                    <div class="ap-card">
                        <div class="ap-card-head">
                            <span class="ap-ico bg-gradient-to-br from-pink-500 to-rose-500"><i class="bi bi-arrow-left-right"></i></span>
                            <div class="min-w-0">
                                <h2>Passar registros</h2>
                                <p>Mova produtos, clientes ou vendas de uma pessoa para outra.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('settings.team.transfer') }}">
                            @csrf
                            <div class="tm-transfer">
                                <div>
                                    <label class="tm-label">De</label>
                                    <select name="source_user_id" class="tm-input">
                                        @foreach($teamUsers as $teamUser)
                                            <option value="{{ $teamUser->id }}">{{ $teamUser->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <i class="bi bi-arrow-right tm-transfer-arrow"></i>
                                <div>
                                    <label class="tm-label">Para</label>
                                    <select name="target_user_id" class="tm-input">
                                        @foreach($teamUsers as $teamUser)
                                            <option value="{{ $teamUser->id }}" @selected($loop->index === 1)>{{ $teamUser->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <label class="tm-label">O quê</label>
                            <select name="module" class="tm-input">
                                <option value="products">Produtos</option>
                                <option value="clients">Clientes</option>
                                <option value="sales">Vendas</option>
                                <option value="all">Tudo acima</option>
                            </select>
                            <button type="submit" class="tm-btn tm-btn-block" onclick="return confirm('Passar esses registros agora?')"><i class="bi bi-arrow-left-right"></i> Transferir registros</button>
                        </form>
                    </div>
                @endif

                {{-- Como funcionam os níveis --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-indigo-500 to-blue-600"><i class="bi bi-info-lg"></i></span>
                        <div class="min-w-0">
                            <h2>Como funcionam os níveis</h2>
                            <p>As permissões de cada pessoa ajustam o detalhe.</p>
                        </div>
                    </div>
                    <div class="tm-levels">
                        <div><span class="tm-level-ico bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-star-fill"></i></span><p><b>Proprietário</b><small>Controla tudo, inclusive o plano e quem entra.</small></p></div>
                        @foreach($roleLabels as $role => $roleLabel)
                            <div>
                                <span class="tm-level-ico bg-gradient-to-br {{ ['from-sky-500 to-blue-600', 'from-emerald-500 to-teal-500', 'from-slate-400 to-slate-500'][$loop->index % 3] }}"><i class="bi {{ ['bi-shield-check', 'bi-person-workspace', 'bi-eye'][$loop->index % 3] }}"></i></span>
                                <p><b>{{ $roleLabel }}</b><small>{{ ['Ajuda a cuidar da equipe e das permissões.', 'Usa as áreas liberadas no dia a dia.', 'Só consulta, sem alterar nada.'][$loop->index % 3] }}</small></p>
                            </div>
                        @endforeach
                    </div>
                    <div class="tm-plan-line">
                        <i class="bi bi-patch-check"></i>
                        <span>Seu plano permite <b>{{ $seatsMax }} {{ $seatsMax === 1 ? 'usuário' : 'usuários' }}</b>.</span>
                        @if(\Illuminate\Support\Facades\Route::has('settings.plan'))
                            <a href="{{ route('settings.plan') }}" wire:navigate>Meu plano</a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .tm-page { display: flex; flex-direction: column; gap: 1rem; }
        .pl-flash { display: flex; align-items: center; gap: .5rem; padding: .8rem 1rem; border-radius: 1rem; font-size: .84rem; font-weight: 600; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .pl-flash.is-error { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
        .dark .pl-flash { background: rgba(16, 185, 129, .1); color: #6ee7b7; border-color: rgba(16, 185, 129, .3); }
        .dark .pl-flash.is-error { background: rgba(239, 68, 68, .1); color: #fca5a5; border-color: rgba(239, 68, 68, .3); }
        .pl-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .9; }
        .pl-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 1rem; align-items: start; }
        .pl-main, .pl-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .dv-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.1rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .dv-hero-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .dv-hero-ico { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .18); font-size: 1.8rem; border: 1px solid rgba(255, 255, 255, .3); }
        .dv-hero-txt { position: relative; flex: 1; min-width: 0; }
        .dv-hero-txt h2 { font-size: 1.5rem; font-weight: 900; line-height: 1.15; margin-top: .2rem; }
        .dv-hero-txt p { font-size: .82rem; opacity: .9; }
        .tm-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem; }
        .tm-chips span { display: inline-flex; align-items: center; gap: .3rem; font-size: .7rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .25); }
        .tm-seats { position: relative; flex: none; width: 190px; padding: .8rem 1rem; border-radius: 1.1rem; background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .25); }
        .tm-seats-top { display: flex; align-items: baseline; gap: .35rem; }
        .tm-seats-top b { font-size: 1.8rem; font-weight: 900; line-height: 1; }
        .tm-seats small { font-size: .72rem; opacity: .9; }
        .tm-seats-bar { height: .4rem; border-radius: 999px; background: rgba(255, 255, 255, .25); margin: .5rem 0 .35rem; overflow: hidden; }
        .tm-seats-bar span { display: block; height: 100%; border-radius: inherit; background: #fff; }
        .tm-list { display: flex; flex-direction: column; gap: .45rem; }
        .tm-row { display: flex; align-items: center; gap: .75rem; padding: .65rem .8rem; border-radius: 1rem; background: #f8fafc; border: 1px solid #eef2f7; list-style: none; }
        .tm-row::-webkit-details-marker { display: none; }
        .dark .tm-row { background: #111827; border-color: #1f2937; }
        .tm-row.is-owner { background: linear-gradient(135deg, rgba(99, 102, 241, .07), rgba(168, 85, 247, .07)); border-color: rgba(99, 102, 241, .2); }
        .tm-row > div { flex: 1; }
        .tm-row b { display: flex; align-items: center; gap: .35rem; font-size: .86rem; color: #0f172a; }
        .tm-row b em { font-style: normal; font-size: .62rem; font-weight: 800; text-transform: uppercase; color: var(--color-indigo-600); background: var(--color-indigo-50); padding: .05rem .4rem; border-radius: 999px; }
        .tm-row small { display: block; font-size: .72rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .tm-row b { color: #f1f5f9; }
        .dark .tm-row b em { background: rgba(99, 102, 241, .18); color: var(--color-indigo-300); }
        .dark .tm-row small { color: #94a3b8; }
        .tm-av { flex: none; display: grid; place-items: center; width: 2.6rem; height: 2.6rem; border-radius: 999px; color: #fff; font-size: .82rem; font-weight: 800; box-shadow: 0 6px 14px -8px rgba(15, 23, 42, .5); }
        .tm-av-wait { background: #fef3c7; color: #b45309; box-shadow: none; }
        .dark .tm-av-wait { background: rgba(245, 158, 11, .15); color: #fcd34d; }
        .tm-role { flex: none; font-size: .68rem; font-weight: 800; color: #475569; background: #fff; border: 1px solid #e2e8f0; padding: .25rem .6rem; border-radius: 999px; }
        .tm-role.is-owner { color: var(--color-indigo-700); border-color: var(--color-indigo-200); display: inline-flex; gap: .3rem; align-items: center; }
        .dark .tm-role { background: transparent; color: #cbd5e1; border-color: #334155; }
        .dark .tm-role.is-owner { color: var(--color-indigo-300); border-color: rgba(99, 102, 241, .4); }
        .tm-caret { flex: none; color: #94a3b8; transition: transform .2s; }
        details[open] > summary .tm-caret { transform: rotate(180deg); }
        .tm-member summary { cursor: pointer; }
        .tm-member[open] summary { border-bottom-left-radius: 0; border-bottom-right-radius: 0; border-color: var(--color-indigo-200); }
        .tm-member-body { padding: .9rem; border: 1px solid var(--color-indigo-200); border-top: 0; border-radius: 0 0 1rem 1rem; background: #fff; }
        .dark .tm-member[open] summary, .dark .tm-member-body { border-color: rgba(99, 102, 241, .35); }
        .dark .tm-member-body { background: #0b1220; }
        .tm-label { display: block; font-size: .72rem; font-weight: 800; color: #475569; margin: .8rem 0 .35rem; }
        .tm-label:first-child { margin-top: 0; }
        .dark .tm-label { color: #94a3b8; }
        .tm-input { width: 100%; font-size: .86rem; padding: .6rem .75rem; border-radius: .8rem; border: 1px solid #e2e8f0; background: #fff; color: #0f172a; }
        .tm-input:focus { outline: none; border-color: var(--color-indigo-400); box-shadow: 0 0 0 3px color-mix(in oklab, var(--color-indigo-500) 18%, transparent); }
        .dark .tm-input { background: #111827; border-color: #334155; color: #f1f5f9; }
        .tm-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 .8rem; }
        .tm-form-grid .tm-label { margin-top: .8rem; }
        .tm-form-grid > div:nth-child(-n+2) .tm-label { margin-top: 0; }
        .tm-span { grid-column: 1 / -1; }
        .tm-roles { display: flex; flex-wrap: wrap; gap: .4rem; }
        .tm-role-opt input { position: absolute; opacity: 0; pointer-events: none; }
        .tm-role-opt span { display: inline-flex; font-size: .78rem; font-weight: 700; padding: .45rem .8rem; border-radius: .8rem; border: 1px solid #e2e8f0; color: #475569; cursor: pointer; background: #fff; }
        .tm-role-opt input:checked + span { color: #fff; border-color: transparent; background: linear-gradient(135deg, var(--color-indigo-600), var(--color-purple-600)); box-shadow: 0 8px 16px -10px var(--color-indigo-600); }
        .dark .tm-role-opt span { background: #111827; border-color: #334155; color: #cbd5e1; }
        .tm-perms { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .4rem; }
        .tm-perms.is-compact { grid-template-columns: minmax(0, 1fr); margin-top: .5rem; }
        .tm-perm { display: flex; align-items: center; gap: .55rem; padding: .5rem .6rem; border-radius: .8rem; border: 1px solid #eef2f7; background: #f8fafc; font-size: .76rem; font-weight: 600; color: #334155; cursor: pointer; }
        .tm-perm > i { color: var(--color-indigo-500); }
        .tm-perm > span { flex: 1; min-width: 0; }
        .dark .tm-perm { background: #111827; border-color: #1f2937; color: #cbd5e1; }
        .tm-switch { appearance: none; flex: none; position: relative; width: 2.1rem; height: 1.2rem; border-radius: 999px; background: #cbd5e1; cursor: pointer; transition: background .2s; }
        .tm-switch::after { content: ''; position: absolute; top: .15rem; left: .15rem; width: .9rem; height: .9rem; border-radius: 999px; background: #fff; transition: transform .2s; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); }
        .tm-switch:checked { background: var(--color-indigo-600); }
        .tm-switch:checked::after { transform: translateX(.9rem); }
        .tm-switch:disabled { opacity: .55; cursor: not-allowed; }
        .dark .tm-switch { background: #334155; }
        .dark .tm-switch:checked { background: var(--color-indigo-500); }
        .tm-modules { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .5rem; }
        .tm-module { position: relative; display: flex; flex-direction: column; align-items: flex-start; gap: .2rem; padding: .8rem; border-radius: 1rem; border: 1px solid #e2e8f0; background: #f8fafc; cursor: pointer; transition: all .15s; }
        .tm-module input { position: absolute; opacity: 0; pointer-events: none; }
        .tm-module-ico { display: grid; place-items: center; width: 2.1rem; height: 2.1rem; border-radius: .7rem; background: #e2e8f0; color: #64748b; margin-bottom: .2rem; }
        .tm-module b { font-size: .84rem; color: #0f172a; }
        .tm-module small { font-size: .68rem; font-weight: 700; color: #94a3b8; }
        .tm-module .tm-on, .tm-module:has(input:checked) .tm-off { display: none; }
        .tm-module:has(input:checked) .tm-on { display: block; color: var(--color-indigo-600); }
        .tm-module:has(input:checked) { border-color: var(--color-indigo-300); background: var(--color-indigo-50); }
        .tm-module:has(input:checked) .tm-module-ico { background: linear-gradient(135deg, var(--color-indigo-500), var(--color-purple-600)); color: #fff; }
        .dark .tm-module { background: #111827; border-color: #1f2937; }
        .dark .tm-module b { color: #f1f5f9; }
        .dark .tm-module-ico { background: #1f2937; }
        .dark .tm-module:has(input:checked) { background: rgba(99, 102, 241, .12); border-color: rgba(99, 102, 241, .45); }
        .dark .tm-module:has(input:checked) .tm-on { color: var(--color-indigo-300); }
        .tm-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .5rem; margin-top: 1rem; }
        .tm-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; font-size: .82rem; font-weight: 800; color: #fff; padding: .6rem 1.05rem; border-radius: .9rem; background: linear-gradient(135deg, var(--color-indigo-600), var(--color-purple-600)); box-shadow: 0 10px 20px -12px var(--color-indigo-600); }
        .tm-btn:hover { filter: brightness(1.07); }
        .tm-btn-block { width: 100%; margin-top: 1rem; }
        .tm-btn-ghost { display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 700; color: #475569; padding: .55rem .95rem; border-radius: .9rem; border: 1px solid #e2e8f0; background: #fff; }
        .dark .tm-btn-ghost { background: transparent; border-color: #334155; color: #cbd5e1; }
        .tm-btn-danger { display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 700; color: #dc2626; padding: .55rem .95rem; border-radius: .9rem; border: 1px solid #fecaca; background: #fff; margin-right: auto; }
        .dark .tm-btn-danger { background: transparent; border-color: rgba(239, 68, 68, .35); color: #fca5a5; }
        .dv-end { flex: none; display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; font-weight: 800; color: #dc2626; background: #fff; border: 1px solid #fecaca; padding: .4rem .6rem; border-radius: .75rem; }
        .dark .dv-end { background: transparent; border-color: rgba(239, 68, 68, .35); color: #fca5a5; }
        .tm-row-actions { display: flex; gap: .4rem; }
        .tm-empty { display: flex; align-items: center; gap: .5rem; padding: .9rem; border-radius: 1rem; border: 1px dashed #cbd5e1; font-size: .8rem; color: #64748b; }
        .dark .tm-empty { border-color: #334155; color: #94a3b8; }
        .tm-more { margin-top: .8rem; border: 1px solid #eef2f7; border-radius: .9rem; padding: .55rem .7rem; }
        .dark .tm-more { border-color: #1f2937; }
        .tm-more summary { display: flex; align-items: center; gap: .45rem; font-size: .78rem; font-weight: 800; color: #475569; cursor: pointer; list-style: none; }
        .tm-more summary::-webkit-details-marker { display: none; }
        .tm-more summary .tm-caret { margin-left: auto; }
        .dark .tm-more summary { color: #cbd5e1; }
        .tm-transfer { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); gap: .5rem; align-items: end; }
        .tm-transfer-arrow { padding-bottom: .7rem; color: #94a3b8; }
        .tm-levels { display: flex; flex-direction: column; gap: .7rem; }
        .tm-levels > div { display: flex; gap: .7rem; align-items: flex-start; }
        .tm-level-ico { flex: none; display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: .7rem; color: #fff; font-size: .85rem; }
        .tm-levels b { display: block; font-size: .82rem; color: #0f172a; }
        .tm-levels small { display: block; font-size: .72rem; color: #64748b; }
        .dark .tm-levels b { color: #f1f5f9; }
        .dark .tm-levels small { color: #94a3b8; }
        .tm-plan-line { display: flex; align-items: center; gap: .45rem; margin-top: 1rem; padding-top: .85rem; border-top: 1px dashed #e2e8f0; font-size: .78rem; color: #475569; }
        .tm-plan-line i { color: var(--color-indigo-500); }
        .tm-plan-line a { margin-left: auto; font-weight: 800; color: var(--color-indigo-600); }
        .dark .tm-plan-line { border-color: #1e293b; color: #cbd5e1; }
        .dark .tm-plan-line a { color: var(--color-indigo-300); }
        .tm-upsell { text-align: center; padding: 2rem 1.5rem; }
        .tm-upsell-art { display: flex; justify-content: center; margin-bottom: 1rem; }
        .tm-upsell-art .tm-av { width: 3.2rem; height: 3.2rem; font-size: 1.2rem; margin-left: -.6rem; border: 3px solid #fff; }
        .dark .tm-upsell-art .tm-av { border-color: #0f172a; }
        .tm-av-plus { background: #eef2ff; color: var(--color-indigo-600); }
        .dark .tm-av-plus { background: #1e293b; color: var(--color-indigo-300); }
        .tm-upsell h2 { font-size: 1.15rem; font-weight: 900; color: #0f172a; }
        .tm-upsell > p { font-size: .84rem; color: #64748b; max-width: 30rem; margin: .4rem auto 0; }
        .dark .tm-upsell h2 { color: #f1f5f9; }
        .dark .tm-upsell > p { color: #94a3b8; }
        .tm-upsell-list { display: flex; width: fit-content; max-width: 100%; flex-direction: column; gap: .4rem; text-align: left; margin: 1.1rem auto 1.3rem; font-size: .82rem; color: #334155; }
        .tm-upsell-list i { color: #10b981; margin-right: .35rem; }
        .dark .tm-upsell-list { color: #cbd5e1; }
        @media (max-width: 1279px) { .pl-grid { grid-template-columns: minmax(0, 1fr) 320px; } .tm-modules { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 1023px) { .pl-grid { grid-template-columns: minmax(0, 1fr); } .pl-side { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .dv-hero { flex-wrap: wrap; padding: 1.1rem; border-radius: 1.3rem; }
            .dv-hero-ico { width: 3.2rem; height: 3.2rem; font-size: 1.4rem; }
            .dv-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .dv-hero-txt h2 { font-size: 1.2rem; }
            .tm-seats { width: 100%; }
            .pl-side { display: flex; }
            .tm-perms, .tm-form-grid { grid-template-columns: minmax(0, 1fr); }
            .tm-form-grid > div:nth-child(2) .tm-label { margin-top: .8rem; }
            .tm-role { display: none; }
            .tm-row-wrap { flex-wrap: wrap; }
            .tm-row-wrap .tm-row-actions { width: 100%; justify-content: flex-end; }
            .tm-upsell { padding: 1.5rem 1rem; }
        }
    </style>
    </x-settings.layout>
</section>
