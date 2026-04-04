<?php

namespace App\Models;

use App\Traits\HasTeamScope;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Client extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasFactory;
    use HasTeamScope;
    use Authenticatable;
    use CanResetPassword;
    use Notifiable;

    protected string $teamScopeModule = 'clients';

    // Definindo a tabela associada ao model
    protected $table = 'clients';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'name', 'email', 'portal_login', 'phone', 'address', 'user_id',
        'created_at', 'updated_at', 'caminho_foto',
        'portal_password', 'portal_active', 'portal_token',
        'google_id', 'google_avatar',
        'portal_token_expires_at', 'portal_last_login_at',
        'portal_force_password_change', 'portal_profile_completed_at',
        'cpf_cnpj', 'birth_date', 'company', 'portal_notes',
        'cep', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
    ];

    protected $hidden = ['portal_password', 'portal_token', 'remember_token'];

    protected $casts = [
        'portal_active'           => 'boolean',
        'portal_force_password_change' => 'boolean',
        'portal_token_expires_at' => 'datetime',
        'portal_last_login_at'    => 'datetime',
        'portal_profile_completed_at' => 'datetime',
        'birth_date'              => 'date',
    ];

    /** Override auth password column para não colidir com 'password' dos users */
    public function getAuthPasswordName(): string
    {
        return 'portal_password';
    }

    /**
     * Boot method to ensure UTF-8 encoding
     */
    protected static function booted()
    {
        static::creating(function (Client $client) {
            if (blank($client->portal_login)) {
                $client->portal_login = static::generateUniquePortalLogin($client->name);
            }
        });

        static::retrieved(function ($client) {
            // Limpa caracteres UTF-8 inválidos
            foreach (['name', 'email', 'phone', 'address'] as $field) {
                if ($client->$field) {
                    $client->$field = mb_convert_encoding($client->$field, 'UTF-8', 'UTF-8');
                }
            }
        });
    }

    public static function generateUniquePortalLogin(?string $name = null, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::ascii((string) $name), '-');
        $base = preg_replace('/-+/', '-', trim((string) $base, '-'));
        $base = $base !== '' ? $base : 'cliente';
        $base = Str::limit($base, 40, '');

        $login = $base;
        $suffix = 2;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('portal_login', $login)
            ->exists()) {
            $trimmedBase = Str::limit($base, max(1, 40 - strlen((string) $suffix) - 1), '');
            $trimmedBase = rtrim($trimmedBase, '-');
            $login = $trimmedBase . '-' . $suffix;
            $suffix++;
        }

        return $login;
    }

    public function hasLegacyPortalLogin(): bool
    {
        if (blank($this->portal_login)) {
            return false;
        }

        return Str::startsWith((string) $this->portal_login, 'cli-');
    }

    // No modelo Client
    public function sales()
    {
        return $this->hasMany(Sale::class);  // Um cliente pode ter várias vendas
    }

    // Relacionamento com o usuário
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacionamento com participações em consórcios
    public function consortiumParticipants()
    {
        return $this->hasMany(ConsortiumParticipant::class, 'client_id');
    }

    // Relacionamento com participações ativas em consórcios
    public function activeConsortiumParticipants()
    {
        return $this->hasMany(ConsortiumParticipant::class, 'client_id')
            ->where('status', 'active');
    }

    public function quoteRequests()
    {
        return $this->hasMany(ClientQuoteRequest::class);
    }

    public function hasRequiredPortalProfileData(): bool
    {
        foreach (['phone', 'cpf_cnpj', 'cep', 'street', 'number', 'neighborhood', 'city', 'state'] as $field) {
            if (blank($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    public function needsPortalOnboarding(): bool
    {
        return $this->portal_force_password_change || ! $this->hasRequiredPortalProfileData();
    }

    public function formattedPortalAddress(): string
    {
        $parts = array_filter([
            $this->street,
            $this->number,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state,
            $this->cep,
        ]);

        return implode(', ', $parts);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ClientResetPasswordNotification($token));
    }

}
