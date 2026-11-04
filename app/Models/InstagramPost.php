<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/** Post feito (ou agendado) pelo app no Instagram da loja. */
class InstagramPost extends Model
{
    public const AGENDADO = 'agendado';
    public const POSTANDO = 'postando';
    public const POSTADO = 'postado';
    public const ERRO = 'erro';
    public const CANCELADO = 'cancelado';

    public const STATUS = [
        self::AGENDADO => ['Agendado', 'bi-clock', 'sky'],
        self::POSTANDO => ['Postando', 'bi-arrow-repeat', 'amber'],
        self::POSTADO => ['Postado', 'bi-check-circle-fill', 'emerald'],
        self::ERRO => ['Não foi', 'bi-exclamation-triangle-fill', 'rose'],
        self::CANCELADO => ['Cancelado', 'bi-x-circle', 'slate'],
    ];

    protected $fillable = [
        'user_id', 'product_id', 'promotion_id', 'kind', 'format', 'caption', 'images', 'status',
        'scheduled_at', 'published_at', 'media_id', 'permalink', 'error', 'attempts',
    ];

    protected $casts = [
        'images' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('team_visibility');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /** Links públicos das fotos (a Meta baixa daqui). */
    public function imageUrls(): array
    {
        return array_map(fn ($p) => Storage::disk('public')->url($p), $this->images ?? []);
    }

    public function kindLabel(): string
    {
        if ($this->kind === 'story') {
            return 'Story';
        }

        return count($this->images ?? []) > 1 ? 'Carrossel · ' . count($this->images) . ' fotos' : 'Foto no feed';
    }

    public function deleteFiles(): void
    {
        foreach ($this->images ?? [] as $p) {
            Storage::disk('public')->delete($p);
        }
    }
}
