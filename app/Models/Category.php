<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'category'; // Nome da tabela
    protected $primaryKey = 'id_category'; // Nome da chave primária

    // Campos que podem ser preenchidos
    protected $fillable = [
        'parent_id',
        'name',
        'desc_category',
        'hexcolor_category',
        'icone',
        'descricao_detalhada',
        'tipo',
        'limite_orcamento',
        'compartilhavel',
        'tags',
        'regras_auto_categorizacao',
        'id_bank',
        'id_clients',
        'id_produtos_clientes',
        'historico_alteracoes',
        'is_active',
        'description',
        'user_id',
        'type',
        'sort_order',
    ];

    // Casts para garantir tipos corretos
    protected $casts = [
        'compartilhavel' => 'boolean',
        'is_active' => 'integer',
        'limite_orcamento' => 'decimal:2',
        'tipo' => 'string',
    ];

    // Relacionamentos
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'id_bank');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_clients');
    }
}
