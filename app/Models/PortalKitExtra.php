<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Enfeite ou adicional do kit (balão, chocolate, laço, cartão…), com preço; não é produto do estoque. */
class PortalKitExtra extends Model
{
    protected $fillable = ['user_id', 'name', 'group', 'description', 'image', 'price', 'max_qty', 'active', 'position'];

    protected $casts = [
        'price'    => 'decimal:2',
        'max_qty'  => 'integer',
        'active'   => 'boolean',
        'position' => 'integer',
    ];
}
