<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cofrinho extends Model
{
    use HasFactory;

    protected $table = 'cofrinhos';

    // O Livro caixa é a conta corrente: uma DESPESA ligada ao cofrinho é dinheiro que sai da
    // conta e ENTRA no cofrinho (guardar); uma RECEITA é dinheiro que sai do cofrinho e volta
    // para a conta (retirar).
    public const TIPO_GUARDAR = 2;
    public const TIPO_RETIRAR = 1;

    protected $fillable = [
        'user_id',
        'nome',
        'meta_valor',
        'status',
        'icone',
    ];

    // Relacionamento com usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com lançamentos do cashbook
    public function cashbooks()
    {
        return $this->hasMany(Cashbook::class, 'cofrinho_id');
    }
}
