<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Uma ficha do Robô Móveis Stylo (ficha.md + fotos), guardada no site para o robô de qualquer PC. */
class RoboFicha extends Model
{
    use SoftDeletes;

    protected $table = 'robo_fichas';

    protected $fillable = ['user_id', 'pasta', 'situacao', 'codigo', 'nome', 'conteudo', 'arquivos', 'historico', 'versao', 'completa', 'editada_em'];

    protected $casts = ['arquivos' => 'array', 'historico' => 'array', 'completa' => 'boolean', 'versao' => 'integer', 'editada_em' => 'datetime'];

    /** Pasta dos arquivos no disco local (storage/app/private). */
    public function dir(): string
    {
        return 'robo-fichas/' . $this->user_id . '/' . $this->id;
    }
}
