<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Produto que o cliente do portal favoritou (lista de desejos), com os
 * avisos pedidos: entrar em promoção e voltar ao estoque.
 * Sem escopo de equipe: sempre filtre por client_id ou user_id.
 */
class ClientFavorite extends Model
{
    protected $fillable = ['user_id', 'client_id', 'product_id', 'notify_promo', 'notify_stock'];

    protected $casts = [
        'notify_promo' => 'boolean',
        'notify_stock' => 'boolean',
    ];

    /** Produto sem o escopo de equipe (o portal não tem usuário da loja logado). */
    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('team_visibility');
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withoutGlobalScope('team_visibility');
    }
}
