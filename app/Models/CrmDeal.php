<?php

namespace App\Models;

use App\Services\Clients\ClientContact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Negócio do funil de vendas: um orçamento ou conversa que pode virar venda.
 * Pode ser de um cliente ou de um lead (só nome + telefone, ainda sem cadastro).
 */
class CrmDeal extends Model
{
    protected $table = 'crm_deals';

    protected $fillable = [
        'user_id', 'client_id', 'name', 'phone', 'source', 'title', 'value', 'stage',
        'lost_reason', 'lost_note', 'expected_date', 'notes', 'sale_id', 'position',
        'stage_at', 'closed_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_date' => 'date',
        'stage_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public const STAGES = [
        'contato' => ['label' => 'Primeiro contato', 'short' => 'Contato', 'icon' => 'bi-chat-dots', 'tone' => 'sky'],
        'orcamento' => ['label' => 'Orçamento enviado', 'short' => 'Orçamento', 'icon' => 'bi-file-earmark-text', 'tone' => 'indigo'],
        'negociando' => ['label' => 'Negociando', 'short' => 'Negociando', 'icon' => 'bi-arrow-left-right', 'tone' => 'amber'],
        'ganho' => ['label' => 'Ganho', 'short' => 'Ganho', 'icon' => 'bi-trophy', 'tone' => 'emerald'],
        'perdido' => ['label' => 'Perdido', 'short' => 'Perdido', 'icon' => 'bi-x-circle', 'tone' => 'rose'],
    ];

    public const OPEN = ['contato', 'orcamento', 'negociando'];

    public const LOST_REASONS = [
        'preco' => 'Achou caro',
        'prazo' => 'Prazo de entrega',
        'sumiu' => 'Parou de responder',
        'concorrente' => 'Comprou em outro lugar',
        'desistiu' => 'Desistiu / não precisa mais',
        'sem_estoque' => 'Não tinha o produto',
        'outro' => 'Outro motivo',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withoutGlobalScopes();
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class)->withoutGlobalScopes();
    }

    public function scopeOwnedBy(Builder $q, $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('stage', self::OPEN);
    }

    public function isLead(): bool
    {
        return $this->client_id === null;
    }

    public function isOpen(): bool
    {
        return in_array($this->stage, self::OPEN, true);
    }

    public function displayName(): string
    {
        return $this->client?->name ?: ($this->name ?: 'Sem nome');
    }

    public function contactPhone(): ?string
    {
        return $this->client?->phone ?: $this->phone;
    }

    public function whatsappUrl(string $text = ''): ?string
    {
        return ClientContact::whatsappUrl($this->contactPhone(), $text);
    }

    /** Dias parado na etapa atual. */
    public function daysInStage(): int
    {
        $since = $this->stage_at ?: $this->created_at;

        return $since ? (int) $since->copy()->startOfDay()->diffInDays(now()->startOfDay()) : 0;
    }

    public static function stageInfo(string $stage): array
    {
        return self::STAGES[$stage] ?? self::STAGES['contato'];
    }
}
