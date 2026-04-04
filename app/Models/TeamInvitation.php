<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'email',
        'invited_by',
        'role',
        'permissions',
        'token',
        'expires_at',
        'accepted_at',
        'revoked_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (TeamInvitation $invitation) {
            if (blank($invitation->token)) {
                $invitation->token = Str::lower(Str::random(48));
            }

            $invitation->email = Str::lower(trim($invitation->email));
            $invitation->permissions = TeamMember::normalizePermissions(
                $invitation->role,
                $invitation->permissions ?? []
            );

            if (!$invitation->expires_at) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null
            && $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where(function ($builder) {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}