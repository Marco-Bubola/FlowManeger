<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Etiqueta livre do cliente (VIP, Atacado, Revendedora, Móveis Stylo...). */
class ClientTag extends Model
{
    protected $fillable = ['user_id', 'name', 'color'];

    /** Cores das etiquetas (mesmos tons dos avisos). */
    public const COLORS = ['indigo', 'violet', 'pink', 'rose', 'orange', 'amber', 'emerald', 'sky', 'slate'];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_tag', 'client_tag_id', 'client_id');
    }

    public function style(float $alpha = 0.14): string
    {
        return ConsortiumNotification::toneStyle(in_array($this->color, self::COLORS, true) ? $this->color : 'indigo', $alpha);
    }
}
