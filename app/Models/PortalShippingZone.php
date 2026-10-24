<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Região de entrega do portal: faixa de CEP ou cidade, com taxa, frete grátis acima de e prazo. */
class PortalShippingZone extends Model
{
    protected $fillable = ['user_id', 'name', 'kind', 'cep_from', 'cep_to', 'city', 'uf', 'fee', 'free_above', 'eta', 'active'];

    protected $casts = [
        'fee'        => 'decimal:2',
        'free_above' => 'decimal:2',
        'active'     => 'boolean',
    ];

    public static function cleanCep(?string $cep): string
    {
        return substr(preg_replace('/\D+/', '', (string) $cep), 0, 8);
    }

    public static function formatCep(?string $cep): string
    {
        $c = self::cleanCep($cep);

        return strlen($c) === 8 ? substr($c, 0, 5) . '-' . substr($c, 5) : $c;
    }

    /** Onde vale, para mostrar na lista: "14000-000 a 14099-999" ou "Ribeirão Preto/SP". */
    public function placeLabel(): string
    {
        return $this->kind === 'city'
            ? trim($this->city . ($this->uf ? '/' . strtoupper($this->uf) : ''))
            : self::formatCep($this->cep_from) . ' a ' . self::formatCep($this->cep_to);
    }
}
