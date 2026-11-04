<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Anotação de um contato com o cliente (ligação, WhatsApp, visita...). */
class ClientNote extends Model
{
    protected $fillable = ['user_id', 'client_id', 'kind', 'body', 'happened_at'];

    protected $casts = ['happened_at' => 'datetime'];

    /** Tipos de contato: rótulo e ícone (Bootstrap Icons). */
    public const KINDS = [
        'nota' => ['label' => 'Anotação', 'icon' => 'bi-sticky'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'bi-whatsapp'],
        'ligacao' => ['label' => 'Ligação', 'icon' => 'bi-telephone'],
        'visita' => ['label' => 'Visita / loja', 'icon' => 'bi-shop'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'bi-instagram'],
        'email' => ['label' => 'E-mail', 'icon' => 'bi-envelope'],
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function kindInfo(): array
    {
        return self::KINDS[$this->kind] ?? self::KINDS['nota'];
    }
}
