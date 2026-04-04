<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'invited_by',
        'role',
        'permissions',
        'joined_at',
        'last_accessed_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'joined_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEMBER = 'member';
    public const ROLE_VIEWER = 'viewer';

    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'Administrador da equipe',
        self::ROLE_MEMBER => 'Colaborador',
        self::ROLE_VIEWER => 'Visualizador',
    ];

    public const PERMISSION_LABELS = [
        'access_products' => 'Acesso compartilhado a produtos',
        'access_clients' => 'Acesso compartilhado a clientes',
        'access_sales' => 'Acesso compartilhado a vendas',
        'access_finances' => 'Acesso compartilhado a finanças',
        'invite_members' => 'Convidar usuários',
        'remove_members' => 'Remover usuários',
        'manage_team_settings' => 'Gerenciar configurações da equipe',
        'transfer_records' => 'Transferir registros entre usuários',
    ];

    protected static function booted(): void
    {
        static::saving(function (TeamMember $member) {
            $member->permissions = static::normalizePermissions($member->role, $member->permissions ?? []);
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function hasPermission(string $permission): bool
    {
        return (bool) data_get($this->permissions ?? [], $permission, false);
    }

    public static function defaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            self::ROLE_ADMIN => [
                'access_products' => true,
                'access_clients' => true,
                'access_sales' => true,
                'access_finances' => true,
                'invite_members' => true,
                'remove_members' => true,
                'manage_team_settings' => true,
                'transfer_records' => true,
            ],
            self::ROLE_VIEWER => [
                'access_products' => true,
                'access_clients' => true,
                'access_sales' => false,
                'access_finances' => false,
                'invite_members' => false,
                'remove_members' => false,
                'manage_team_settings' => false,
                'transfer_records' => false,
            ],
            default => [
                'access_products' => true,
                'access_clients' => true,
                'access_sales' => true,
                'access_finances' => false,
                'invite_members' => false,
                'remove_members' => false,
                'manage_team_settings' => false,
                'transfer_records' => false,
            ],
        };
    }

    public static function normalizePermissions(string $role, array $overrides = []): array
    {
        $base = static::defaultPermissionsForRole($role);

        foreach (array_keys(static::PERMISSION_LABELS) as $key) {
            if (array_key_exists($key, $overrides)) {
                $base[$key] = filter_var($overrides[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                $base[$key] = $base[$key] ?? false;
            }
        }

        return $base;
    }
}