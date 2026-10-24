<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Opções da vitrine da loja: produtos em destaque e o aviso fino no topo do catálogo. */
class PortalSetting extends Model
{
    protected $fillable = ['user_id', 'featured_title', 'featured_ids', 'notice_text', 'notice_link', 'notice_active'];

    protected $casts = [
        'featured_ids'  => 'array',
        'notice_active' => 'boolean',
    ];

    public static function for(int $userId): self
    {
        return static::firstOrNew(['user_id' => $userId]);
    }
}
