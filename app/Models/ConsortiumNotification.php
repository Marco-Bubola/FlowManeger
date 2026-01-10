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
     * Ícone da notificação baseado no tipo
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'draw_available' => 'bi-trophy-fill',
            'redemption_pending' => 'bi-exclamation-triangle-fill',
            default => 'bi-bell-fill',
        };
    }

    /**
     * Cor da notificação baseado no tipo/prioridade
     */
    public function getColorAttribute(): string
    {
        if ($this->priority === 'high') {
            return 'red';
        }

        return match($this->type) {
            'draw_available' => 'purple',
            'redemption_pending' => 'amber',
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
}
