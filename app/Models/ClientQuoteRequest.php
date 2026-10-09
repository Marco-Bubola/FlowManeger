<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientQuoteRequest extends Model
{
    protected $table = 'client_quote_requests';

    protected $fillable = [
        'client_id', 'user_id', 'status',
        'items', 'extra_items',
        'client_notes', 'admin_notes',
        'quoted_total', 'valid_until',
        'payment_preference',
        'responded_at', 'client_seen_status', 'client_notified_at',
    ];

    protected $casts = [
        'items'        => 'array',
        'extra_items'  => 'array',
        'quoted_total' => 'decimal:2',
        'valid_until'  => 'date',
        'responded_at' => 'datetime',
        'client_notified_at' => 'datetime',
    ];

    /** Status em que a loja já deu uma resposta que o cliente precisa ver. */
    public const CLIENT_UPDATE_STATUSES = ['quoted', 'approved', 'rejected'];

    public const STATUS_LABELS = [
        'pending'   => 'Aguardando Análise',
        'reviewing' => 'Em Análise',
        'quoted'    => 'Orçamento Enviado',
        'approved'  => 'Aprovado',
        'rejected'  => 'Recusado',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'amber',
        'reviewing' => 'blue',
        'quoted'    => 'purple',
        'approved'  => 'green',
        'rejected'  => 'red',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Venda criada quando a loja confirmou o pedido. Sem o escopo de equipe
     * porque o portal não tem usuário da loja logado; a busca já é pelo id.
     */
    public function sale()
    {
        return $this->hasOne(Sale::class, 'portal_quote_id')
            ->withoutGlobalScope('team_visibility')
            ->orderByDesc('id');
    }

    /** Respostas da loja que o cliente ainda não abriu no portal. */
    public function scopeUnseenByClient($query)
    {
        return $query->whereNotNull('responded_at')
            ->whereIn('status', self::CLIENT_UPDATE_STATUSES)
            ->where(fn ($q) => $q->whereNull('client_seen_status')->orWhereColumn('client_seen_status', '!=', 'status'));
    }

    public function getHasUnseenUpdateAttribute(): bool
    {
        return $this->responded_at !== null
            && in_array($this->status, self::CLIENT_UPDATE_STATUSES, true)
            && $this->client_seen_status !== $this->status;
    }

    /** Marca a resposta atual como vista pelo cliente (sem mexer no updated_at). */
    public function markSeenByClient(): void
    {
        if ($this->client_seen_status === $this->status) {
            return;
        }

        $this->forceFill(['client_seen_status' => $this->status]);
        $this->timestamps = false;
        $this->save();
        $this->timestamps = true;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getCanEditAttribute(): bool
    {
        return in_array($this->status, ['pending', 'reviewing']);
    }
}
