<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Credenciais da Pluggy (Open Finance) de cada usuário. */
class BankConnection extends Model
{
    protected $fillable = ['user_id', 'provider', 'enabled', 'client_id', 'client_secret', 'item_ids', 'last_sync_at', 'last_error'];

    protected $casts = [
        'enabled' => 'boolean',
        'client_secret' => 'encrypted',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = ['client_secret'];

    public static function for(int $userId): ?self
    {
        return static::where('user_id', $userId)->first();
    }

    public function ready(): bool
    {
        return $this->enabled && filled($this->client_id) && filled($this->client_secret) && $this->itemIdList() !== [];
    }

    public function itemIdList(): array
    {
        return array_values(array_unique(array_filter(array_map('trim', preg_split('/[\s,;]+/', (string) $this->item_ids) ?: []))));
    }

    public function accounts()
    {
        return $this->hasMany(BankConnectionAccount::class, 'user_id', 'user_id');
    }
}
