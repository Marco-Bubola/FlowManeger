<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Uma visita a uma página do catálogo (para o relatório do site). */
class PortalPageView extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'day', 'kind', 'product_id', 'visitor', 'client_id', 'source', 'created_at'];

    protected $casts = ['day' => 'date'];
}
