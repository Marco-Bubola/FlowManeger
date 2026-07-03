<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ConsortiumNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module',
        'entity_type',
        'entity_id',
        'consortium_id',
        'user_id',
        'related_participant_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
        'priority',
        'action_url',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function consortium(): BelongsTo
    {
        return $this->belongsTo(Consortium::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(ConsortiumParticipant::class, 'related_participant_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope para notificações não lidas
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope para notificações lidas
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope para notificações de um tipo específico
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para notificações de alta prioridade
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope para notificações recentes (últimos 7 dias)
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(7));
    }

    /**
     * Scope para notificações de um usuário
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para notificações de um módulo específico
     */
    public function scopeOfModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Scope para notificações de uma entidade específica
     */
    public function scopeForEntity(Builder $query, string $entityType, int $entityId): Builder
    {
        return $query->where('entity_type', $entityType)->where('entity_id', $entityId);
    }

    // ==================== METHODS ====================

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Marcar notificação como não lida
     */
    public function markAsUnread(): bool
    {
        return $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // ==================== ACCESSORS ====================

    /**
     * Ícone da notificação baseado no tipo e módulo
     */
    public function getIconAttribute(): string
    {
        // Ícones específicos por tipo de notificação
        return match($this->type) {
            'draw_available' => 'bi-trophy-fill',
            'redemption_pending' => 'bi-exclamation-triangle-fill',
            'sale_pending' => 'bi-cart-fill',
            'sale_completed' => 'bi-check-circle-fill',
            'payment_overdue' => 'bi-exclamation-circle-fill',
            'payment_received' => 'bi-cash-coin',
            'client_new' => 'bi-person-plus-fill',
            'client_birthday' => 'bi-cake-fill',
            'order_received' => 'bi-bag-check-fill',
            'question_received' => 'bi-patch-question-fill',
            'message_received' => 'bi-chat-dots-fill',
            'claim_opened' => 'bi-exclamation-octagon-fill',
            'sync_error' => 'bi-arrow-repeat',
            default => 'bi-bell-fill',
        };
    }

    /**
     * Cor da notificação baseado no tipo/prioridade/módulo
     */
    public function getColorAttribute(): string
    {
        if ($this->priority === 'high') {
            return 'red';
        }

        return match($this->type) {
            'draw_available' => 'purple',
            'redemption_pending' => 'amber',
            'sale_pending' => 'orange',
            'sale_completed' => 'green',
            'payment_overdue' => 'red',
            'payment_received' => 'green',
            'client_new' => 'blue',
            'client_birthday' => 'pink',
            'order_received' => 'green',
            'question_received' => 'blue',
            'message_received' => 'blue',
            'claim_opened' => 'red',
            'sync_error' => 'amber',
            default => 'blue',
        };
    }

    /**
     * Descrição amigável do tipo
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'draw_available' => 'Sorteio Disponível',
            'redemption_pending' => 'Resgate Pendente',
            'sale_pending' => 'Venda Pendente',
            'sale_completed' => 'Venda Concluída',
            'payment_overdue' => 'Pagamento Atrasado',
            'payment_received' => 'Pagamento Recebido',
            'client_new' => 'Novo Cliente',
            'client_birthday' => 'Aniversário de Cliente',
            'order_received' => 'Pedido Mercado Livre',
            'question_received' => 'Pergunta Mercado Livre',
            'message_received' => 'Mensagem Mercado Livre',
            'claim_opened' => 'Reclamação Mercado Livre',
            'sync_error' => 'Erro de Sincronização ML',
            default => 'Notificação',
        };
    }

    /**
     * Tempo relativo desde criação
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    // ==================== STATIC METHODS ====================

    /**
     * Criar notificação de sorteio disponível
     */
    public static function createDrawAvailable(Consortium $consortium): self
    {
        return self::create([
            'module' => 'consortium',
            'entity_type' => 'Consortium',
            'entity_id' => $consortium->id,
            'consortium_id' => $consortium->id,
            'user_id' => $consortium->user_id,
            'type' => 'draw_available',
            'title' => '🎯 Sorteio Disponível!',
            'message' => "O consórcio \"{$consortium->name}\" está pronto para realizar um novo sorteio. {$consortium->eligibleParticipantsCount()} participantes elegíveis aguardando.",
            'priority' => 'high',
            'action_url' => route('consortiums.draw', $consortium),
            'data' => [
                'eligible_count' => $consortium->eligibleParticipantsCount(),
                'last_draw_date' => $consortium->draws()->latest('draw_date')->first()?->draw_date,
            ],
        ]);
    }

    /**
     * Criar notificação de resgate pendente
     */
    public static function createRedemptionPending(ConsortiumParticipant $participant): self
    {
        $daysSinceContemplation = $participant->contemplation->contemplation_date->diffInDays(now());

        return self::create([
            'module' => 'consortium',
            'entity_type' => 'ConsortiumParticipant',
            'entity_id' => $participant->id,
            'consortium_id' => $participant->consortium_id,
            'user_id' => $participant->consortium->user_id,
            'related_participant_id' => $participant->id,
            'type' => 'redemption_pending',
            'title' => '⏰ Resgate Pendente',
            'message' => "O participante \"{$participant->client->name}\" foi contemplado há {$daysSinceContemplation} dias no consórcio \"{$participant->consortium->name}\" e ainda não realizou o resgate.",
            'priority' => $daysSinceContemplation > 30 ? 'high' : 'medium',
            'action_url' => route('consortiums.show', $participant->consortium) . '#contemplated',
            'data' => [
                'contemplation_date' => $participant->contemplation->contemplation_date,
                'days_since' => $daysSinceContemplation,
                'client_name' => $participant->client->name,
            ],
        ]);
    }

    /**
     * Contar notificações não lidas de um usuário
     */
    public static function unreadCountForUser(int $userId): int
    {
        return self::unread()->forUser($userId)->count();
    }

    /**
     * Marcar todas como lidas para um usuário
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::unread()->forUser($userId)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Criar notificação genérica para qualquer módulo
     *
     * @param string $module Módulo (consortium, sale, payment, client, etc)
     * @param string $type Tipo específico (draw_available, payment_overdue, etc)
     * @param int $userId ID do usuário que receberá a notificação
     * @param string $title Título da notificação
     * @param string $message Mensagem detalhada
     * @param array $options Opções adicionais: priority, action_url, data, entity_type, entity_id
     * @return self
     */
    public static function createGeneric(
        string $module,
        string $type,
        int $userId,
        string $title,
        string $message,
        array $options = []
    ): self {
        return self::create([
            'module' => $module,
            'entity_type' => $options['entity_type'] ?? null,
            'entity_id' => $options['entity_id'] ?? null,
            'consortium_id' => $options['consortium_id'] ?? null,
            'user_id' => $userId,
            'related_participant_id' => $options['related_participant_id'] ?? null,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $options['priority'] ?? 'medium',
            'action_url' => $options['action_url'] ?? null,
            'data' => $options['data'] ?? [],
        ]);
    }
}
