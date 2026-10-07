<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Registro de envio de promoção por WhatsApp (para quem e quando). */
class PromotionSend extends Model
{
    protected $fillable = ['user_id', 'promotion_id', 'client_id', 'kind', 'channel', 'promotion_ids'];

    protected $casts = ['promotion_ids' => 'array'];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
