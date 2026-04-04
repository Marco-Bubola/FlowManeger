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

<section class="settings-team-page w-full mobile-393-base">
    <x-settings.layout :heading="''">
        @if(session('success'))
            <div class="settings-flash settings-flash--success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="settings-flash settings-flash--error">
                {{ session('error') }}
            </div>
        @endif

        <div class="settings-team-layout">
            <div class="settings-team-main">
                @if($team)
                    <div class="settings-card settings-card-highlight">
                        <div class="settings-card-header">
                            <div class="settings-card-title-row">
                                <div class="settings-card-icon" style="background:rgba(99,102,241,.12);color:#4f46e5">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.035.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.645-6.375-1.766a11.944 11.944 0 0 1-.035-.666l.001-.031m12.409 0a5.969 5.995 0 0 0-1.963-2.312m0 0a5.995 5.995 0 0 0-8.074 0m8.074 0A5.995 5.995 0 0 1 12 15c-1.51 0-2.89.56-3.963 1.484m0 0a5.969 5.969 0 0 0-1.963 2.312M15 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM7.5 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                </div>
                                <div>
                                    <p class="settings-card-title">{{ $team->name }}</p>
                                    <p class="settings-card-desc">{{ $team->description ?: 'Equipe ativa para compartilhamento controlado de dados.' }}</p>
                                </div>
                            </div>
                            <span class="s-badge s-badge-accent">{{ $user->isTeamOwner($team) ? 'Proprietário' : ($roleLabels[$teamMember?->role] ?? 'Membro') }}</span>
                        </div>

                        <div class="settings-team-stats-grid">
                            <div class="settings-info-tile">
                                <p class="settings-info-tile-label">Responsável</p>
                                <p class="settings-info-tile-val">{{ $team->owner->name }}</p>
                            </div>
                            <div class="settings-info-tile">
                                <p class="settings-info-tile-label">Usuários ativos</p>
                                <p class="settings-info-tile-val">{{ $team->currentMemberCount() }} / {{ $team->allowedSeatCount() }}</p>
                            </div>
                            <div class="settings-info-tile">
                                <p class="settings-info-tile-label">Seu papel</p>
                                <p class="settings-info-tile-val">{{ $user->isTeamOwner($team) ? 'Proprietário' : ($roleLabels[$teamMember?->role] ?? 'Membro') }}</p>
                            </div>
                            <div class="settings-info-tile">
                                <p class="settings-info-tile-label">Módulos compartilhados</p>
                                <p class="settings-info-tile-val">{{ $sharedModuleCount }}</p>
                            </div>
                        </div>
                    </div>

                    @if($canManageTeam)
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div class="settings-card-title-row">
                                    <div class="settings-card-icon">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    </div>
                                    <div>
                                        <p class="settings-card-title">Configurações de compartilhamento</p>
                                        <p class="settings-card-desc">Defina como a equipe enxerga módulos, limite interno e descrição operacional.</p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('settings.team.update') }}" class="settings-team-stack">
                                @csrf
                                @method('PATCH')

                                <div class="settings-team-form-grid">
                                    <div>
                                        <label class="settings-field-label">Nome da equipe</label>
                                        <input name="name" type="text" value="{{ $team->name }}" class="settings-select">
                                    </div>
                                    <div>
                                        <label class="settings-field-label">Máximo interno de usuários</label>
                                        <input name="max_members" type="number" min="1" max="{{ $team->allowedSeatCount() }}" value="{{ $team->max_members }}" class="settings-select">
                                    </div>
                                    <div style="grid-column:1 / -1">
                                        <label class="settings-field-label">Descrição</label>
                                        <textarea name="description" rows="3" class="settings-select" style="padding:.75rem">{{ $team->description }}</textarea>
                                    </div>
                                </div>

                                <div class="settings-team-module-grid">
                                    @foreach($teamModules as $module => $label)
                                        <label class="settings-info-tile settings-team-split" data-surface="plain" style="cursor:pointer">
                                            <div class="settings-value-stack">
                                                <p class="settings-info-tile-label">Compartilhar</p>
                                                <p class="settings-info-tile-val">{{ $label }}</p>
                                            </div>
                                            <input type="checkbox" name="share_{{ $module }}" value="1" {{ !empty($teamSettings['share_' . $module]) ? 'checked' : '' }} style="width:1rem;height:1rem">
                                        </label>
                                    @endforeach
                                </div>

                                <div class="settings-actions-row">
                                    <button type="submit" class="settings-save-btn">Salvar equipe</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-title-row">
                                <div class="settings-card-icon" style="background:rgba(14,165,233,.12);color:#0284c7">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.035.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.645-6.375-1.766a11.944 11.944 0 0 1-.035-.666l.001-.031m12.409 0a5.969 5.995 0 0 0-1.963-2.312m0 0a5.995 5.995 0 0 0-8.074 0m8.074 0A5.995 5.995 0 0 1 12 15c-1.51 0-2.89.56-3.963 1.484m0 0a5.969 5.995 0 0 0-1.963 2.312M15 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM7.5 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                </div>
                                <div>
                                    <p class="settings-card-title">Usuários conectados</p>
                                    <p class="settings-card-desc">Cada conta continua isolada por permissão. A equipe apenas libera a visibilidade do que foi autorizado.</p>
                                </div>
                            </div>
                        </div>

                        <div class="settings-team-stack">
                            <article class="settings-info-tile settings-team-card">
                                <div class="settings-team-split">
                                    <div>
                                        <p class="settings-info-tile-label">Proprietário</p>
                                        <p class="settings-info-tile-val">{{ $team->owner->name }}</p>
                                        <p class="settings-note">{{ $team->owner->email }}</p>
                                    </div>
                                    <span class="s-badge s-badge-accent">Controle total</span>
                                </div>
                            </article>

                            @foreach($members as $member)
                                <article class="settings-info-tile settings-team-card">
                                    <div class="settings-team-split">
                                        <div>
                                            <p class="settings-info-tile-label">Membro</p>
                                            <p class="settings-info-tile-val">{{ $member->user->name }}</p>
                                            <p class="settings-note">{{ $member->user->email }} · entrou {{ optional($member->joined_at)->format('d/m/Y') ?: 'agora' }}</p>
                                        </div>
                                        <span class="s-badge">{{ $roleLabels[$member->role] ?? ucfirst($member->role) }}</span>
                                    </div>

                                    <form method="POST" action="{{ route('settings.team.members.update', $member) }}" class="settings-team-inline-form">
                                        @csrf
                                        @method('PATCH')

                                        @if($canManageTeam)
                                            <div>
                                                <label class="settings-field-label">Nível de acesso</label>
                                                <select name="role" class="settings-select settings-team-select-sm">
                                                    @foreach($roleLabels as $role => $roleLabel)
                                                        <option value="{{ $role }}" {{ $member->role === $role ? 'selected' : '' }}>{{ $roleLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <input type="hidden" name="role" value="{{ $member->role }}">
                                        @endif

                                        <div class="settings-team-permissions-grid">
                                            @foreach($permissionLabels as $permission => $label)
                                                <label class="settings-info-tile settings-team-split" data-surface="plain">
                                                    <span class="settings-team-permission-label">{{ $label }}</span>
                                                    <input type="checkbox" name="{{ $permission }}" value="1" {{ !empty($member->permissions[$permission]) ? 'checked' : '' }} {{ $canManageTeam ? '' : 'disabled' }}>
                                                </label>
                                            @endforeach
                                        </div>

                                        @if($canManageTeam || $canRemoveMembers)
                                            <div class="settings-team-member-actions">
                                                @if($canManageTeam)
                                                    <button type="submit" class="settings-save-btn">Salvar permissões</button>
                                                @endif

                                                @if($canRemoveMembers)
                                                    <button type="submit" form="remove-member-{{ $member->id }}" class="settings-btn-danger">Remover da equipe</button>
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
                                </article>
                            @endforeach

                            @if(!$user->isTeamOwner($team))
                                <div class="settings-actions-row">
                                    <form method="POST" action="{{ route('settings.team.leave') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="settings-btn-secondary">Sair da equipe</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    @if($incomingInvitations->isNotEmpty())
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div class="settings-card-title-row">
                                    <div class="settings-card-icon" style="background:rgba(59,130,246,.12);color:#2563eb">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25m5.25-2.25a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </div>
                                    <div>
                                        <p class="settings-card-title">Convites pendentes</p>
                                        <p class="settings-card-desc">Aceite um convite para começar a enxergar somente os dados liberados pela equipe.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-team-stack">
                                @foreach($incomingInvitations as $invitation)
                                    <article class="settings-info-tile settings-team-card">
                                        <div class="settings-team-split">
                                            <div>
                                                <p class="settings-info-tile-label">Equipe</p>
                                                <p class="settings-info-tile-val">{{ $invitation->team->name }}</p>
                                                <p class="settings-note">Papel: {{ $roleLabels[$invitation->role] ?? ucfirst($invitation->role) }} · expira {{ optional($invitation->expires_at)->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <div class="settings-actions-row">
                                                <form method="POST" action="{{ route('settings.team.invitations.accept', $invitation) }}">
                                                    @csrf
                                                    <button type="submit" class="settings-save-btn">Aceitar</button>
                                                </form>
                                                <form method="POST" action="{{ route('settings.team.invitations.destroy', $invitation) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="settings-btn-secondary">Recusar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($canCreateTeam)
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div class="settings-card-title-row">
                                    <div class="settings-card-icon" style="background:rgba(168,85,247,.12);color:#7c3aed">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.035.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.645-6.375-1.766a11.944 11.944 0 0 1-.035-.666l.001-.031m12.409 0a5.969 5.995 0 0 0-1.963-2.312m0 0a5.995 5.995 0 0 0-8.074 0m8.074 0A5.995 5.995 0 0 1 12 15c-1.51 0-2.89.56-3.963 1.484m0 0a5.969 5.995 0 0 0-1.963 2.312M15 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM7.5 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                    </div>
                                    <div>
                                        <p class="settings-card-title">Criar equipe</p>
                                        <p class="settings-card-desc">Monte uma equipe com visibilidade controlada, sem abrir todos os módulos para todos os usuários.</p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('settings.team.store') }}" class="settings-team-stack">
                                @csrf
                                <div class="settings-team-form-grid">
                                    <div>
                                        <label class="settings-field-label">Nome da equipe</label>
                                        <input name="name" type="text" required class="settings-select" placeholder="Ex: Comercial Alpha">
                                    </div>
                                    <div style="grid-column:1 / -1">
                                        <label class="settings-field-label">Descrição</label>
                                        <textarea name="description" rows="3" class="settings-select" style="padding:.75rem" placeholder="Explique o objetivo da equipe."></textarea>
                                    </div>
                                </div>

                                <div class="settings-actions-row">
                                    <button type="submit" class="settings-save-btn">Criar equipe</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    @if(!$canCreateTeam && $incomingInvitations->isEmpty())
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div class="settings-card-title-row">
                                    <div class="settings-card-icon" style="background:rgba(245,158,11,.12);color:#d97706">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z"/></svg>
                                    </div>
                                    <div>
                                        <p class="settings-card-title">Equipe indisponível</p>
                                        <p class="settings-card-desc">Sua conta ainda não pode criar equipe e não há convite ativo no momento.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-team-stack">
                                <div class="settings-info-tile">
                                    <p class="settings-info-tile-label">Status atual</p>
                                    <p class="settings-info-tile-val">Aguardando liberação ou convite</p>
                                    <p class="settings-note">Quando houver vínculo, a visibilidade vai seguir o papel e as permissões configuradas pelo responsável da equipe.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <aside class="settings-team-side">
                @if($team)
                    @if($canInvite)
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div class="settings-card-title-row">
                                    <div class="settings-card-icon" style="background:rgba(16,185,129,.12);color:#059669">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h-3m3 0h3M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/></svg>
                                    </div>
                                    <div>
                                        <p class="settings-card-title">Convidar usuário</p>
                                        <p class="settings-card-desc">Defina papel e permissões antes do usuário entrar.</p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('settings.team.invite') }}" class="settings-team-stack">
                                @csrf
                                <div class="settings-team-form-grid">
                                    <div>
                                        <label class="settings-field-label">E-mail do usuário</label>
                                        <input type="email" name="email" required class="settings-select" placeholder="usuario@empresa.com">
                                    </div>
                                    <div>
                                        <label class="settings-field-label">Nível de acesso</label>
                                        <select name="role" class="settings-select">
                                            @foreach($roleLabels as $role => $roleLabel)
                                                <option value="{{ $role }}">{{ $roleLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="settings-team-permissions-grid">
                                    @foreach($permissionLabels as $permission => $label)
                                        <label class="settings-info-tile settings-team-split" data-surface="plain">
                                            <span class="settings-team-permission-label">{{ $label }}</span>
                                            <input type="checkbox" name="{{ $permission }}" value="1">
                                        </label>
                                    @endforeach
                                </div>

                                <div class="settings-actions-row">
                                    <button type="submit" class="settings-save-btn">Gerar convite</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    @if($pendingTeamInvitations->isNotEmpty())
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div class="settings-card-title-row">
                                    <div class="settings-card-icon" style="background:rgba(245,158,11,.12);color:#d97706">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25m5.25-2.25a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </div>
                                    <div>
                                        <p class="settings-card-title">Convites enviados</p>
                                        <p class="settings-card-desc">Convites ainda não aceitos ficam listados aqui.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-team-stack">
                                @foreach($pendingTeamInvitations as $invitation)
                                    <article class="settings-info-tile settings-team-card">
                                        <div>
                                            <p class="settings-info-tile-label">Convite</p>
                                            <p class="settings-info-tile-val">{{ $invitation->email }}</p>
                                            <p class="settings-note">{{ $roleLabels[$invitation->role] ?? ucfirst($invitation->role) }} · expira {{ optional($invitation->expires_at)->format('d/m/Y H:i') }}</p>
                                        </div>

                                        <div class="settings-actions-row">
                                            <form method="POST" action="{{ route('settings.team.invitations.destroy', $invitation) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="settings-btn-secondary">Cancelar convite</button>
                                            </form>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($canTransferRecords && $teamUsers->count() > 1)
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div class="settings-card-title-row">
                                    <div class="settings-card-icon" style="background:rgba(236,72,153,.12);color:#db2777">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 12h9m0 0-3-3m3 3-3 3M3.75 6.75h16.5m-16.5 10.5h16.5"/></svg>
                                    </div>
                                    <div>
                                        <p class="settings-card-title">Transferir informações</p>
                                        <p class="settings-card-desc">Redistribua dados entre usuários da mesma equipe sem sair da página.</p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('settings.team.transfer') }}" class="settings-team-stack">
                                @csrf
                                <div class="settings-team-form-grid">
                                    <div>
                                        <label class="settings-field-label">Origem</label>
                                        <select name="source_user_id" class="settings-select">
                                            @foreach($teamUsers as $teamUser)
                                                <option value="{{ $teamUser->id }}">{{ $teamUser->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="settings-field-label">Destino</label>
                                        <select name="target_user_id" class="settings-select">
                                            @foreach($teamUsers as $teamUser)
                                                <option value="{{ $teamUser->id }}">{{ $teamUser->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="settings-field-label">Módulo</label>
                                        <select name="module" class="settings-select">
                                            <option value="products">Produtos</option>
                                            <option value="clients">Clientes</option>
                                            <option value="sales">Vendas</option>
                                            <option value="all">Tudo acima</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="settings-actions-row">
                                    <button type="submit" class="settings-save-btn">Transferir registros</button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endif

                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-title-row">
                            <div class="settings-card-icon" style="background:rgba(59,130,246,.12);color:#2563eb">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 9.75 9 12m0 0 2.25 2.25M9 12h6.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            </div>
                            <div>
                                <p class="settings-card-title">Como a visibilidade funciona</p>
                                <p class="settings-card-desc">A estrutura agora fica organizada em blocos fixos e a regra de acesso continua clara em qualquer dispositivo.</p>
                            </div>
                        </div>
                    </div>

                    <div class="settings-team-guide-grid">
                        <div class="settings-team-check">
                            <span class="settings-team-check-mark">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" style="width:.82rem;height:.82rem"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                            </span>
                            <div>
                                <p class="settings-team-check-title">Papel define alcance</p>
                                <p class="settings-team-check-desc">Proprietário, gestor e membro enxergam níveis diferentes de ação.</p>
                            </div>
                        </div>

                        <div class="settings-team-check">
                            <span class="settings-team-check-mark">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" style="width:.82rem;height:.82rem"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                            </span>
                            <div>
                                <p class="settings-team-check-title">Permissão afina o detalhe</p>
                                <p class="settings-team-check-desc">Mesmo dentro da equipe, cada módulo pode ser liberado separadamente.</p>
                            </div>
                        </div>

                        <div class="settings-team-check">
                            <span class="settings-team-check-mark">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" style="width:.82rem;height:.82rem"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                            </span>
                            <div>
                                <p class="settings-team-check-title">Grade responsiva</p>
                                <p class="settings-team-check-desc">Os cards entram lado a lado no desktop e colapsam em coluna no mobile.</p>
                            </div>
                        </div>

                        <div class="settings-team-check">
                            <span class="settings-team-check-mark">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" style="width:.82rem;height:.82rem"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                            </span>
                            <div>
                                <p class="settings-team-check-title">Dark e light alinhados</p>
                                <p class="settings-team-check-desc">Campos e superfícies não dependem mais de fundo branco fixo.</p>
                            </div>
                        </div>
                    </div>

                    <div class="settings-team-stats-grid">
                        <div class="settings-info-tile">
                            <p class="settings-info-tile-label">Capacidade do plano</p>
                            <p class="settings-info-tile-val">{{ $team ? $team->allowedSeatCount() : $teamSeatLimit }} usuários</p>
                        </div>
                        <div class="settings-info-tile">
                            <p class="settings-info-tile-label">Módulos prontos</p>
                            <p class="settings-info-tile-val">{{ count($teamModules) }}</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </x-settings.layout>
</section>