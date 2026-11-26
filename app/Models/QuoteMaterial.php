<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Linha da tabela de materiais e horas (custo dos orçamentos e do robô). */
class QuoteMaterial extends Model
{
    protected $fillable = ['user_id', 'category', 'name', 'unit', 'price', 'note', 'estimated', 'position'];

    protected $casts = [
        'price' => 'float',
        'estimated' => 'boolean',
        'position' => 'integer',
    ];
}
