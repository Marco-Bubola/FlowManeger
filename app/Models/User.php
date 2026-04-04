<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'phone',
        'location',
        'about_me',
        'profile_picture',
        'google_id',
        'avatar',
        'userscol',
        'website',
        'twitter',
        'instagram',
        'linkedin',
        'birth_date',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the consortiums for the user.
     */
    public function consortiums(): HasMany
    {
        return $this->hasMany(Consortium::class);
    }

    // ── Subscription / Plan ───────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->latest();
    }

    public function ownedTeam(): HasOne
    {
        return $this->hasOne(Team::class, 'owner_id');
    }

    public function teamMembership(): HasOne
    {
        return $this->hasOne(TeamMember::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where(function ($q) {
                $q->where(function ($active) {
                    $active->where('status', Subscription::STATUS_ACTIVE)
                           ->where(function ($inner) {
                               $inner->whereNull('current_period_end')
                                     ->orWhere('current_period_end', '>', now());
                           });
                })
                ->orWhere(function ($trialing) {
                    $trialing->where('status', Subscription::STATUS_TRIALING)
                             ->where('trial_ends_at', '>', now());
                });
            })
            ->latestOfMany();
    }

    /** Retorna o plano ativo. Se nenhum, devolve o plano gratuito/padrão. */
    public function plan(): Plan
    {
        $sub = $this->activeSubscription;
        if ($sub) {
            return $sub->plan;
        }
        return Plan::getDefault();
    }

    public function onPlan(string $slug): bool
    {
        return $this->plan()->slug === $slug;
    }

    public function activeTeam(): ?Team
    {
        if ($this->relationLoaded('ownedTeam') && $this->ownedTeam) {
            return $this->ownedTeam;
        }

        if ($this->relationLoaded('teamMembership') && $this->teamMembership?->team) {
            return $this->teamMembership->team;
        }

        return $this->ownedTeam()->first()
            ?? $this->teamMembership()->with('team')->first()?->team;
    }

    public function activeTeamMember(): ?TeamMember
    {
        if ($this->relationLoaded('teamMembership')) {
            return $this->teamMembership;
        }

        return $this->teamMembership()->first();
    }

    public function hasActiveTeam(): bool
    {
        return $this->activeTeam() !== null;
    }

    public function canCreateOrManageTeam(): bool
    {
        return $this->isAdmin()
            || $this->hasActiveTeam()
            || (($this->plan()->max_users ?? 1) > 1);
    }

    public function isTeamOwner(?Team $team = null): bool
    {
        $team = $team ?? $this->activeTeam();

        return $team !== null && (int) $team->owner_id === (int) $this->id;
    }

    public function teamCan(string $permission, ?Team $team = null): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $team = $team ?? $this->activeTeam();
        if (!$team) {
            return false;
        }

        if ($this->isTeamOwner($team)) {
            return true;
        }

        $membership = $this->activeTeamMember();
        if (!$membership || (int) $membership->team_id !== (int) $team->id) {
            return false;
        }

        return $membership->hasPermission($permission);
    }

    public function canAccessTeamModule(string $module): bool
    {
        $team = $this->activeTeam();

        if (!$team) {
            return false;
        }

        if ($this->isAdmin() || $this->isTeamOwner($team)) {
            return $team->shareEnabled($module);
        }

        return $team->shareEnabled($module) && $this->teamCan('access_' . $module, $team);
    }

    public function visibleTeamUserIds(string $module): array
    {
        $team = $this->activeTeam();

        if (!$team) {
            return [$this->id];
        }

        if ($this->canAccessTeamModule($module)) {
            return $team->allUserIds();
        }

        return [$this->id];
    }

    public function pendingTeamInvitations(): Collection
    {
        return TeamInvitation::query()
            ->with(['team.owner'])
            ->pending()
            ->where('email', Str::lower($this->email))
            ->latest()
            ->get();
    }

    public function hasPlanFeature(string $feature): bool
    {
        // Admin sempre tem todas as features
        if ($this->isAdmin()) {
            return true;
        }
        $plan = $this->plan();
        return (bool) ($plan->{$feature} ?? false);
    }

    public static function adminPermissionCatalog(): array
    {
        return [
            'view-admin-panel' => 'Painel admin',
            'manage-plans' => 'Gerenciar planos',
            'manage-users' => 'Gerenciar usuarios',
            'manage-subscriptions' => 'Gerenciar assinaturas',
        ];
    }

    public function currentPlan(): Plan
    {
        if ($this->relationLoaded('activeSubscription') && $this->activeSubscription?->plan) {
            return $this->activeSubscription->plan;
        }

        return $this->plan();
    }

    public function effectivePermissions(): array
    {
        $plan = $this->currentPlan();
        $permissions = [];

        foreach (static::adminPermissionCatalog() as $ability => $label) {
            $permissions[] = [
                'ability' => $ability,
                'label' => $label,
                'enabled' => $this->isAdmin(),
                'source' => $this->isAdmin() ? 'admin' : 'admin-only',
            ];
        }

        foreach ($plan->permissionMatrix() as $permission) {
            $permissions[] = [
                'ability' => $permission['ability'],
                'label' => $permission['label'],
                'enabled' => $this->isAdmin() || $permission['enabled'],
                'source' => $this->isAdmin() ? 'admin' : ($permission['enabled'] ? 'plan' : 'none'),
            ];
        }

        return $permissions;
    }

    // ── Admin ────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
