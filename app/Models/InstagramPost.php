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
        'user_id', 'product_id', 'promotion_id', 'kind', 'format', 'caption', 'images', 'video', 'share_to_feed',
        'status', 'scheduled_at', 'published_at', 'media_id', 'container_id', 'permalink', 'metrics', 'metrics_at',
        'error', 'attempts',
    ];

    /** Resultados mostrados no card: chave => [nome, ícone]. */
    public const METRICS = [
        'views' => ['Visualizações', 'bi-eye'],
        'reach' => ['Alcance', 'bi-people'],
        'likes' => ['Curtidas', 'bi-heart'],
        'comments' => ['Comentários', 'bi-chat'],
        'saved' => ['Salvos', 'bi-bookmark'],
        'shares' => ['Compartilhados', 'bi-send'],
        'replies' => ['Respostas', 'bi-reply'],
    ];

    protected $casts = [
        'images' => 'array',
        'metrics' => 'array',
        'metrics_at' => 'datetime',
        'share_to_feed' => 'boolean',
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

    public function videoUrl(): ?string
    {
        return $this->video ? Storage::disk('public')->url($this->video) : null;
    }

    /** Soma de curtidas, comentários, salvos e compartilhados (para ordenar os melhores). */
    public function interactions(): int
    {
        $m = $this->metrics ?? [];

        return (int) (($m['likes'] ?? 0) + ($m['comments'] ?? 0) + ($m['saved'] ?? 0) + ($m['shares'] ?? 0));
    }

    public function kindLabel(): string
    {
        if ($this->kind === 'reel') {
            return 'Reels';
        }
        if ($this->kind === 'story') {
            return 'Story';
        }

        return count($this->images ?? []) > 1 ? 'Carrossel · ' . count($this->images) . ' fotos' : 'Foto no feed';
    }

    public function deleteFiles(): void
    {
        foreach (array_filter(array_merge($this->images ?? [], [$this->video])) as $p) {
            Storage::disk('public')->delete($p);
        }
    }
}
