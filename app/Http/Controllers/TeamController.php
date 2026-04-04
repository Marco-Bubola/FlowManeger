<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('manage-team'), 403);

        if ($request->user()->hasActiveTeam()) {
            return back()->with('error', 'Você já está vinculado a uma equipe.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:400',
        ]);

        $user = $request->user();
        $seatLimit = $user->isAdmin() ? 50 : max(1, (int) ($user->plan()->max_users ?? 1));

        if (!$user->isAdmin() && $seatLimit <= 1) {
            return back()->with('error', 'Seu plano atual não libera equipe multiusuário.');
        }

        Team::create([
            'owner_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'max_members' => $seatLimit,
            'settings' => Team::DEFAULT_SETTINGS,
        ]);

        return back()->with('success', 'Equipe criada com sucesso. Agora você já pode convidar outros usuários.');
    }

    public function update(Request $request): RedirectResponse
    {
        $team = $this->resolveTeam($request->user());
        $this->authorizeTeamPermission($request->user(), $team, 'manage_team_settings');

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:400',
            'max_members' => 'nullable|integer|min:1|max:100',
        ]);

        $settings = [];
        foreach (array_keys(Team::DEFAULT_SETTINGS) as $settingKey) {
            $settings[$settingKey] = $request->boolean($settingKey);
        }

        $settings = Team::normalizeSettings($settings);
        $allowedSeats = $team->allowedSeatCount();
        $requestedMaxMembers = (int) ($validated['max_members'] ?? $team->max_members ?? $allowedSeats);
        $ceiling = max($allowedSeats, $team->currentMemberCount());
        $targetMaxMembers = max($team->currentMemberCount(), min($requestedMaxMembers, $ceiling));

        $team->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'max_members' => $targetMaxMembers,
            'settings' => $settings,
        ]);

        return back()->with('success', 'Configurações da equipe atualizadas.');
    }

    public function invite(Request $request): RedirectResponse
    {
        $team = $this->resolveTeam($request->user());
        $this->authorizeTeamPermission($request->user(), $team, 'invite_members');

        if (!$team->hasSeatAvailable()) {
            return back()->with('error', 'Sua equipe atingiu o limite de usuários permitido pelo plano.');
        }

        $validated = $request->validate([
            'email' => 'required|email:rfc,dns|max:190',
            'role' => 'required|in:' . implode(',', array_keys(TeamMember::ROLE_LABELS)),
        ]);

        $email = strtolower(trim($validated['email']));
        if ($email === strtolower($request->user()->email)) {
            return back()->with('error', 'Você não pode convidar sua própria conta.');
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser?->hasActiveTeam()) {
            return back()->with('error', 'Esse usuário já está vinculado a outra equipe.');
        }

        if ($existingUser && in_array($existingUser->id, $team->allUserIds(), true)) {
            return back()->with('error', 'Esse usuário já faz parte da sua equipe.');
        }

        $permissions = $this->extractPermissions($request, $validated['role']);

        $invitation = TeamInvitation::query()
            ->pending()
            ->where('team_id', $team->id)
            ->where('email', $email)
            ->first();

        if ($invitation) {
            $invitation->update([
                'role' => $validated['role'],
                'permissions' => $permissions,
                'invited_by' => $request->user()->id,
                'expires_at' => now()->addDays(7),
                'revoked_at' => null,
            ]);
        } else {
            TeamInvitation::create([
                'team_id' => $team->id,
                'email' => $email,
                'role' => $validated['role'],
                'permissions' => $permissions,
                'invited_by' => $request->user()->id,
            ]);
        }

        return back()->with('success', 'Convite criado. O usuário poderá aceitar pela página de equipe ao entrar no sistema.');
    }

    public function acceptInvitation(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        if (strtolower($user->email) !== strtolower($invitation->email)) {
            abort(403);
        }

        if (!$invitation->isPending()) {
            return back()->with('error', 'Esse convite não está mais disponível.');
        }

        if ($user->hasActiveTeam()) {
            return back()->with('error', 'Você já participa de uma equipe e não pode aceitar outro convite.');
        }

        $team = $invitation->team()->with('owner')->firstOrFail();
        if (!$team->hasSeatAvailable()) {
            return back()->with('error', 'A equipe já atingiu o limite de membros do plano.');
        }

        DB::transaction(function () use ($invitation, $user, $team) {
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'invited_by' => $invitation->invited_by,
                'role' => $invitation->role,
                'permissions' => TeamMember::normalizePermissions($invitation->role, $invitation->permissions ?? []),
                'joined_at' => now(),
                'last_accessed_at' => now(),
            ]);

            $invitation->update(['accepted_at' => now()]);
        });

        return back()->with('success', 'Você agora faz parte da equipe ' . $team->name . '.');
    }

    public function updateMember(Request $request, TeamMember $member): RedirectResponse
    {
        $team = $this->resolveTeam($request->user());
        $this->authorizeTeamPermission($request->user(), $team, 'manage_team_settings');
        abort_unless((int) $member->team_id === (int) $team->id, 404);

        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', array_keys(TeamMember::ROLE_LABELS)),
        ]);

        $member->update([
            'role' => $validated['role'],
            'permissions' => $this->extractPermissions($request, $validated['role']),
        ]);

        return back()->with('success', 'Permissões do membro atualizadas.');
    }

    public function removeMember(Request $request, TeamMember $member): RedirectResponse
    {
        $team = $this->resolveTeam($request->user());
        $this->authorizeTeamPermission($request->user(), $team, 'remove_members');
        abort_unless((int) $member->team_id === (int) $team->id, 404);

        if ((int) $member->user_id === (int) $request->user()->id) {
            return back()->with('error', 'Use a opção de sair da equipe para remover sua própria conta.');
        }

        $member->delete();

        return back()->with('success', 'Membro removido da equipe.');
    }

    public function cancelInvitation(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        $user = $request->user();
        $team = $user->activeTeam();

        $canManage = $team
            && (int) $invitation->team_id === (int) $team->id
            && ($user->isTeamOwner($team) || $user->teamCan('invite_members', $team) || $user->isAdmin());

        $isInvitee = strtolower($user->email) === strtolower($invitation->email);

        abort_unless($canManage || $isInvitee, 403);

        $invitation->update(['revoked_at' => now()]);

        return back()->with('success', $canManage ? 'Convite cancelado.' : 'Convite recusado.');
    }

    public function leave(Request $request): RedirectResponse
    {
        $membership = $request->user()->activeTeamMember();
        if (!$membership) {
            return back()->with('error', 'Você não participa de nenhuma equipe como membro.');
        }

        $membership->delete();

        return back()->with('success', 'Você saiu da equipe.');
    }

    public function transferRecords(Request $request): RedirectResponse
    {
        $team = $this->resolveTeam($request->user());
        $this->authorizeTeamPermission($request->user(), $team, 'transfer_records');

        $validated = $request->validate([
            'source_user_id' => 'required|integer',
            'target_user_id' => 'required|integer|different:source_user_id',
            'module' => 'required|in:products,clients,sales,all',
        ]);

        $allowedUserIds = $team->allUserIds();
        if (!in_array((int) $validated['source_user_id'], $allowedUserIds, true) || !in_array((int) $validated['target_user_id'], $allowedUserIds, true)) {
            return back()->with('error', 'Origem e destino precisam fazer parte da mesma equipe.');
        }

        $map = [
            'products' => Product::class,
            'clients' => Client::class,
            'sales' => Sale::class,
        ];

        $modules = $validated['module'] === 'all' ? array_keys($map) : [$validated['module']];
        $moved = [];

        DB::transaction(function () use ($modules, $map, $validated, &$moved) {
            foreach ($modules as $module) {
                $model = $map[$module];
                $count = $model::withoutGlobalScope('team_visibility')
                    ->where('user_id', $validated['source_user_id'])
                    ->update(['user_id' => $validated['target_user_id']]);

                $moved[$module] = $count;
            }
        });

        $summary = collect($moved)
            ->map(fn ($count, $module) => $count . ' ' . $module)
            ->implode(', ');

        return back()->with('success', 'Transferência concluída: ' . $summary . '.');
    }

    private function resolveTeam(User $user): Team
    {
        $team = $user->activeTeam();
        abort_unless($team instanceof Team, 404);

        return $team;
    }

    private function authorizeTeamPermission(User $user, Team $team, string $permission): void
    {
        if ($user->isAdmin() || $user->isTeamOwner($team) || $user->teamCan($permission, $team)) {
            return;
        }

        abort(403);
    }

    private function extractPermissions(Request $request, string $role): array
    {
        $permissions = [];

        foreach (array_keys(TeamMember::PERMISSION_LABELS) as $permission) {
            $permissions[$permission] = $request->boolean($permission);
        }

        return TeamMember::normalizePermissions($role, $permissions);
    }
}