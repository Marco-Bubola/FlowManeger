<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Uma linha do extrato de pontos do cliente (positivo ganha, negativo gasta). */
class PortalPoint extends Model
{
    protected $fillable = ['user_id', 'client_id', 'quote_id', 'points', 'kind', 'note'];

    protected $casts = ['points' => 'integer'];

    public function client()
    {
        return $this->belongsTo(Client::class)->withoutGlobalScope('team_visibility');
    }
}
