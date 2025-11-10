<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InvoiceCategoryLearning extends Model
{
    protected $table = 'invoice_category_learning';

    protected $fillable = [
        'user_id',
        'description_pattern',
        'category_id',
        'confidence',
    ];

    /**
     * Relacionamento com Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id_category');
    }

    /**
     * Relacionamento com User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Normaliza uma descrição para criar um padrão de busca
     * Remove acentos, caracteres especiais, números de fim e converte para maiúsculas
     */
    public static function normalizeDescription($description)
    {
        // Converter para maiúsculas
        $pattern = strtoupper($description);

        // Remover acentos
        $pattern = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $pattern);

        // Remover números, pontuações e caracteres especiais (manter apenas letras e espaços)
        $pattern = preg_replace('/[^A-Z\s]/', '', $pattern);

        // Remover espaços múltiplos
        $pattern = preg_replace('/\s+/', ' ', $pattern);

        // Trim
        $pattern = trim($pattern);

        return $pattern;
    }

    /**
     * Busca um padrão aprendido para uma descrição
     * Retorna o category_id mais confiável (maior confidence) ou null
     */
    public static function findCategoryForDescription($description, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $pattern = self::normalizeDescription($description);

        if (empty($pattern)) {
            return null;
        }

        // Buscar padrão exato primeiro
        $learning = self::where('user_id', $userId)
            ->where('description_pattern', $pattern)
            ->orderBy('confidence', 'desc')
            ->first();

        if ($learning) {
            return $learning->category_id;
        }

        // Buscar padrões que contenham palavras-chave significativas
        $words = explode(' ', $pattern);
        $significantWords = array_filter($words, function($word) {
            return strlen($word) >= 4; // Palavras com 4+ caracteres
        });

        foreach ($significantWords as $word) {
            $learning = self::where('user_id', $userId)
                ->where('description_pattern', 'LIKE', "%{$word}%")
                ->orderBy('confidence', 'desc')
                ->first();

            if ($learning) {
                return $learning->category_id;
            }
        }

        return null;
    }

    /**
     * Salva ou atualiza um padrão de aprendizado
     * Incrementa o confidence se já existir
     */
    public static function learn($description, $categoryId, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $pattern = self::normalizeDescription($description);

        if (empty($pattern) || empty($categoryId)) {
            return null;
        }

        // Buscar se já existe
        $learning = self::where('user_id', $userId)
            ->where('description_pattern', $pattern)
            ->where('category_id', $categoryId)
            ->first();

        if ($learning) {
            // Incrementar confiança
            $learning->increment('confidence');
            return $learning;
        }

        // Criar novo registro
        return self::create([
            'user_id' => $userId,
            'description_pattern' => $pattern,
            'category_id' => $categoryId,
            'confidence' => 1,
        ]);
    }

    /**
     * Retorna todos os padrões aprendidos para um usuário
     * Ordenados por confiança (mais usados primeiro)
     */
    public static function getAllPatternsForUser($userId = null)
    {
        $userId = $userId ?? Auth::id();

        return self::where('user_id', $userId)
            ->orderBy('confidence', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}
