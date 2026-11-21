<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Página especial de um produto no catálogo (no produto principal da família). */
class PortalProductPage extends Model
{
    protected $fillable = ['user_id', 'product_id', 'active', 'settings', 'blocks'];

    protected $casts = [
        'active'   => 'boolean',
        'settings' => 'array',
        'blocks'   => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('team_visibility');
    }
}
