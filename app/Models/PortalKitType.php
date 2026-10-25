<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tipo de kit que o cliente monta no catálogo (cesta, caixa com visor, banheira…), com preço da base e limite de itens. */
class PortalKitType extends Model
{
    protected $fillable = ['user_id', 'name', 'style', 'description', 'image', 'base_price', 'min_items', 'max_items', 'category_ids', 'active', 'position', 'options', 'settings'];

    protected $casts = [
        'base_price'   => 'decimal:2',
        'min_items'    => 'integer',
        'max_items'    => 'integer',
        'category_ids' => 'array',
        'active'       => 'boolean',
        'position'     => 'integer',
        'options'      => 'array',
        'settings'     => 'array',
    ];

    /** O que o cliente pode mandar junto: fotos (até quantas) e data de entrega. */
    public function setting(string $key, $default = null)
    {
        return ($this->settings ?? [])[$key] ?? $default;
    }
}
