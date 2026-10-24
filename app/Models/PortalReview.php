<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Avaliação de um produto no catálogo (nota 1 a 5 e comentário), no produto principal da família. */
class PortalReview extends Model
{
    protected $fillable = ['user_id', 'product_id', 'client_id', 'name', 'rating', 'comment', 'reply', 'replied_at', 'verified', 'hidden'];

    protected $casts = [
        'rating'     => 'integer',
        'verified'   => 'boolean',
        'hidden'     => 'boolean',
        'replied_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Primeiro nome e a inicial do sobrenome: "Ana P." */
    public static function shortName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = $parts[0] ?? 'Cliente';
        $last = count($parts) > 1 ? mb_strtoupper(mb_substr(end($parts), 0, 1)) . '.' : '';

        return trim(mb_convert_case(mb_strtolower($first), MB_CASE_TITLE) . ' ' . $last) ?: 'Cliente';
    }
}
