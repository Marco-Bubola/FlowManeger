<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'avatar',
        'max_members',
        'settings',
    ];

    protected $casts = [
        'max_members' => 'integer',
        'settings' => 'array',
    ];

    public const MODULES = [
        'products' => 'Produtos',
        'clients' => 'Clientes',
        'sales' => 'Vendas',
        'finances' => 'Financeiro',
    ];

    public const DEFAULT_SETTINGS = [
        'share_products' => true,
        'share_clients' => true,
        'share_sales' => true,
        'share_finances' => false,
    ];

    protected static function booted(): void
    {
        static::creating(function (Team $team) {
            if (blank($team->slug)) {
                $team->slug = static::uniqueSlugFrom($team->name);
            }

            $team->settings = static::normalizeSettings($team->settings ?? []);
        });

        static::updating(function (Team $team) {
            if ($team->isDirty('name') && !$team->isDirty('slug') && blank($team->slug)) {
                $team->slug = static::uniqueSlugFrom($team->name, $team->id);
            }

            $team->settings = static::normalizeSettings($team->settings ?? []);
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function allUserIds(): array
    {
        return collect([$this->owner_id])
            ->merge($this->members()->pluck('user_id'))
            ->unique()
            ->values()
            ->all();
    }

    public function currentMemberCount(): int
    {
        return 1 + $this->members()->count();
    }

    public function allowedSeatCount(): int
    {
        $owner = $this->relationLoaded('owner') ? $this->owner : $this->owner()->first();
        $planSeats = $owner?->isAdmin() ? max($this->max_members, 50) : (int) ($owner?->plan()->max_users ?? 1);

        return max(1, min((int) $this->max_members, max(1, $planSeats)));
    }

    public function hasSeatAvailable(): bool
    {
        return $this->currentMemberCount() < $this->allowedSeatCount();
    }

    public function shareEnabled(string $module): bool
    {
        return (bool) data_get($this->settings ?? static::DEFAULT_SETTINGS, 'share_' . $module, false);
    }

    public static function normalizeSettings(array $settings): array
    {
        $normalized = static::DEFAULT_SETTINGS;

        foreach (array_keys(static::DEFAULT_SETTINGS) as $key) {
            if (array_key_exists($key, $settings)) {
                $normalized[$key] = filter_var($settings[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                $normalized[$key] = $normalized[$key] ?? false;
            }
        }

        return $normalized;
    }

    public static function uniqueSlugFrom(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : 'team';
        $counter = 1;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}