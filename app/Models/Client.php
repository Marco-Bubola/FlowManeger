<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Definindo a tabela associada ao model
    protected $table = 'clients';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'user_id',
        'created_at',
        'updated_at',
        'caminho_foto', // Caminho para a foto do cliente
    ];

    /**
     * Boot method to ensure UTF-8 encoding
     */
    protected static function booted()
    {
        static::retrieved(function ($client) {
            // Limpa caracteres UTF-8 inválidos
            foreach (['name', 'email', 'phone', 'address'] as $field) {
                if ($client->$field) {
                    $client->$field = mb_convert_encoding($client->$field, 'UTF-8', 'UTF-8');
                }
            }
        });
    }

    // No modelo Client
    public function sales()
    {
        return $this->hasMany(Sale::class);  // Um cliente pode ter várias vendas
    }

    // Relacionamento com o usuário
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacionamento com participações em consórcios
    public function consortiumParticipants()
    {
        return $this->hasMany(ConsortiumParticipant::class, 'client_id');
    }

    // Relacionamento com participações ativas em consórcios
    public function activeConsortiumParticipants()
    {
        return $this->hasMany(ConsortiumParticipant::class, 'client_id')
            ->where('status', 'active');
    }


}
