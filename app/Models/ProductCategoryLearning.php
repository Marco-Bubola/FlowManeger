<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryLearning extends Model
{
    use HasFactory;

    protected $table = 'product_category_learning';

    protected $fillable = [
        'user_id',
        'product_name_pattern',
        'product_code',
        'category_id',
        'confidence',
        'last_used_at',
    ];

    protected $casts = [
        'confidence' => 'integer',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com categoria
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id_category');
    }

    /**
     * Incrementa a confiança (peso) do aprendizado
     */
    public function incrementConfidence()
    {
        $this->increment('confidence');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Busca sugestão de categoria por nome do produto
     */
    public static function suggestCategory($userId, $productName)
    {
        // Normalizar nome do produto
        $normalizedName = self::normalizeProductName($productName);

        // Buscar por padrão exato
        $exactMatch = self::where('user_id', $userId)
            ->where('product_name_pattern', $normalizedName)
            ->orderBy('confidence', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->first();

        if ($exactMatch) {
            return $exactMatch->category_id;
        }

        // Buscar por similaridade (primeiras palavras)
        $words = explode(' ', $normalizedName);
        if (count($words) > 1) {
            $firstWords = implode(' ', array_slice($words, 0, min(3, count($words))));

            $similarMatch = self::where('user_id', $userId)
                ->where('product_name_pattern', 'LIKE', $firstWords . '%')
                ->orderBy('confidence', 'desc')
                ->orderBy('last_used_at', 'desc')
                ->first();

            if ($similarMatch) {
                return $similarMatch->category_id;
            }
        }

        return null;
    }

    /**
     * Normaliza nome do produto para padronizar busca
     */
    public static function normalizeProductName($name)
    {
        $normalized = strtolower(trim($name));
        // Remove caracteres especiais
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        // Remove espaços duplicados
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        // Limita tamanho
        return substr($normalized, 0, 255);
    }
}
