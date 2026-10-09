<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Notificação in-app (sino + /notificacoes). Apesar do nome histórico, é a
 * tabela genérica de TODOS os módulos (consórcio, Mercado Livre, Shopee,
 * estoque, financeiro...). Crie sempre por createGeneric().
 *
 * O mapa central tipo/módulo -> categoria, ícone e cor fica aqui
 * (CATEGORIES, MODULE_CATEGORY, TYPES, TONES).
 */
class ConsortiumNotification extends Model
{
    use HasFactory;
    use SoftDeletes {
        SoftDeletes::bootSoftDeletes as protected traitBootSoftDeletes;
        SoftDeletes::performDeleteOnModel as protected softPerformDeleteOnModel;
    }

    public const DISPLAY_TZ = 'America/Sao_Paulo';

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
        'user_id' => 'integer',
    ];

    // ==================== MAPA CENTRAL ====================

    /** Cores (hex) usadas por ícones/chips. Inline style: não depende do scan do Tailwind. */
    public const TONES = [
        'emerald' => '#10b981',
        'amber' => '#d97706',
        'orange' => '#f97316',
        'yellow' => '#ca8a04',
        'violet' => '#8b5cf6',
        'sky' => '#0ea5e9',
        'indigo' => '#6366f1',
        'rose' => '#f43f5e',
        'pink' => '#ec4899',
        'fuchsia' => '#d946ef',
        'slate' => '#64748b',
    ];

    /** Categorias exibidas nos filtros (ordem = ordem dos chips). */
    public const CATEGORIES = [
        'vendas' => ['label' => 'Vendas', 'icon' => 'bi-cart3', 'tone' => 'emerald'],
        'estoque' => ['label' => 'Estoque', 'icon' => 'bi-box-seam', 'tone' => 'amber'],
        'mercadolivre' => ['label' => 'Mercado Livre', 'icon' => 'bi-shop', 'tone' => 'yellow'],
        'shopee' => ['label' => 'Shopee', 'icon' => 'bi-bag-heart', 'tone' => 'orange'],
        'consorcio' => ['label' => 'Consórcio', 'icon' => 'bi-people', 'tone' => 'violet'],
        'financeiro' => ['label' => 'Financeiro', 'icon' => 'bi-wallet2', 'tone' => 'sky'],
        'sistema' => ['label' => 'Sistema', 'icon' => 'bi-gear', 'tone' => 'slate'],
    ];

    /** Módulo gravado -> categoria (o módulo tem precedência sobre o tipo). */
    public const MODULE_CATEGORY = [
        'consortium' => 'consorcio', 'consorcio' => 'consorcio', 'consortiums' => 'consorcio',
        'mercadolivre' => 'mercadolivre', 'mercado_livre' => 'mercadolivre', 'ml' => 'mercadolivre',
        'shopee' => 'shopee',
        'sale' => 'vendas', 'sales' => 'vendas', 'vendas' => 'vendas', 'client' => 'vendas', 'clients' => 'vendas',
        'quote' => 'vendas', 'quotes' => 'vendas', 'portal' => 'vendas', 'promotion' => 'vendas', 'promotions' => 'vendas',
        'estoque' => 'estoque', 'stock' => 'estoque', 'product' => 'estoque', 'products' => 'estoque', 'inventory' => 'estoque',
        'payment' => 'financeiro', 'payments' => 'financeiro', 'financeiro' => 'financeiro', 'finance' => 'financeiro',
        'cashbook' => 'financeiro', 'invoice' => 'financeiro', 'invoices' => 'financeiro', 'bank' => 'financeiro',
        'banks' => 'financeiro', 'receivables' => 'financeiro',
        'system' => 'sistema', 'sistema' => 'sistema',
    ];

    /**
     * Tipo -> rótulo, ícone (Bootstrap Icons), tom e categoria (usada só quando o
     * módulo não é conhecido; null = depende do módulo).
     */
    public const TYPES = [
        'draw_available' => ['label' => 'Sorteio disponível', 'icon' => 'bi-trophy-fill', 'tone' => 'violet', 'category' => 'consorcio'],
        'redemption_pending' => ['label' => 'Resgate pendente', 'icon' => 'bi-hourglass-split', 'tone' => 'amber', 'category' => 'consorcio'],
        'sale_pending' => ['label' => 'Venda pendente', 'icon' => 'bi-cart', 'tone' => 'orange', 'category' => 'vendas'],
        'sale_completed' => ['label' => 'Venda concluída', 'icon' => 'bi-cart-check-fill', 'tone' => 'emerald', 'category' => 'vendas'],
        'client_new' => ['label' => 'Novo cliente', 'icon' => 'bi-person-plus-fill', 'tone' => 'sky', 'category' => 'vendas'],
        'client_birthday' => ['label' => 'Aniversário de cliente', 'icon' => 'bi-gift-fill', 'tone' => 'pink', 'category' => 'vendas'],
        'portal_quote' => ['label' => 'Orçamento do portal', 'icon' => 'bi-file-earmark-text-fill', 'tone' => 'indigo', 'category' => 'vendas'],
        'promotion_started' => ['label' => 'Promoção iniciada', 'icon' => 'bi-megaphone-fill', 'tone' => 'fuchsia', 'category' => 'vendas'],
        'promotion_ended' => ['label' => 'Promoção encerrada', 'icon' => 'bi-megaphone', 'tone' => 'slate', 'category' => 'vendas'],
        'low_stock' => ['label' => 'Estoque baixo', 'icon' => 'bi-box-seam', 'tone' => 'amber', 'category' => 'estoque'],
        'out_of_stock' => ['label' => 'Produto esgotado', 'icon' => 'bi-x-octagon-fill', 'tone' => 'rose', 'category' => 'estoque'],
        'payment_due' => ['label' => 'Pagamento a vencer', 'icon' => 'bi-calendar-event-fill', 'tone' => 'sky', 'category' => 'financeiro'],
        'payment_overdue' => ['label' => 'Pagamento atrasado', 'icon' => 'bi-exclamation-circle-fill', 'tone' => 'rose', 'category' => 'financeiro'],
        'payment_received' => ['label' => 'Pagamento recebido', 'icon' => 'bi-cash-coin', 'tone' => 'emerald', 'category' => 'financeiro'],
        'order_received' => ['label' => 'Novo pedido', 'icon' => 'bi-bag-check-fill', 'tone' => 'emerald', 'category' => 'vendas'],
        'question_received' => ['label' => 'Nova pergunta', 'icon' => 'bi-patch-question-fill', 'tone' => 'sky', 'category' => null],
        'message_received' => ['label' => 'Nova mensagem', 'icon' => 'bi-chat-dots-fill', 'tone' => 'indigo', 'category' => null],
        'claim_opened' => ['label' => 'Reclamação', 'icon' => 'bi-exclamation-octagon-fill', 'tone' => 'rose', 'category' => null],
        'sync_error' => ['label' => 'Erro de sincronização', 'icon' => 'bi-arrow-repeat', 'tone' => 'amber', 'category' => 'sistema'],
        'system_backup' => ['label' => 'Backup', 'icon' => 'bi-cloud-check-fill', 'tone' => 'slate', 'category' => 'sistema'],
        'system_update' => ['label' => 'Atualização', 'icon' => 'bi-stars', 'tone' => 'indigo', 'category' => 'sistema'],
        'system_error' => ['label' => 'Erro do sistema', 'icon' => 'bi-bug-fill', 'tone' => 'rose', 'category' => 'sistema'],
    ];

    public const PRIORITIES = [
        'high' => ['label' => 'Urgente', 'tone' => 'rose'],
        'medium' => ['label' => 'Normal', 'tone' => 'slate'],
        'low' => ['label' => 'Baixa', 'tone' => 'slate'],
    ];

    /** Categoria de um par módulo/tipo. */
    public static function categoryFor(?string $module, ?string $type): string
    {
        $module = $module ? strtolower($module) : null;

        return self::MODULE_CATEGORY[$module] ?? (self::TYPES[$type]['category'] ?? null) ?? 'sistema';
    }

    /** Módulos que caem numa categoria. */
    public static function modulesFor(string $category): array
    {
        return array_keys(array_filter(self::MODULE_CATEGORY, fn ($c) => $c === $category));
    }

    /** Estilos inline (fundo translúcido + cor) de um tom; funciona no claro e no escuro. */
    public static function toneStyle(string $tone, float $alpha = 0.14): string
    {
        $hex = self::TONES[$tone] ?? self::TONES['slate'];
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return "background-color: rgba({$r},{$g},{$b},{$alpha}); color: {$hex};";
    }

    // ==================== SOFT DELETES TOLERANTES ====================
    // Algumas bases antigas não têm consortium_notifications.deleted_at: sem a
    // coluna, o escopo de soft delete quebraria todas as consultas. Nesses
    // casos o model funciona sem soft delete (exclusão definitiva).

    protected static ?bool $hasDeletedAt = null;

    public static function supportsSoftDeletes(): bool
    {
        if (self::$hasDeletedAt === null) {
            try {
                self::$hasDeletedAt = Schema::hasColumn((new static)->getTable(), 'deleted_at');
            } catch (\Throwable $e) {
                self::$hasDeletedAt = true;
            }
        }

        return self::$hasDeletedAt;
    }

    public static function bootSoftDeletes()
    {
        if (static::supportsSoftDeletes()) {
            static::traitBootSoftDeletes();
        }
    }

    protected function performDeleteOnModel()
    {
        if (static::supportsSoftDeletes()) {
            return $this->softPerformDeleteOnModel();
        }

        $this->setKeysForSaveQuery($this->newModelQuery())->delete();
        $this->exists = false;
    }

    /** Exclusão definitiva por query, com ou sem coluna deleted_at. */
    public static function purge(Builder $query): int
    {
        return static::supportsSoftDeletes() ? (int) $query->forceDelete() : (int) $query->delete();
    }

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

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', 'high');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(7));
    }

    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        // user_id nulo nunca deve listar notificações de ninguém.
        return $query->where('user_id', $userId ?? 0);
    }

    public function scopeOfModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeForEntity(Builder $query, string $entityType, int $entityId): Builder
    {
        return $query->where('entity_type', $entityType)->where('entity_id', $entityId);
    }

    /** Filtra pela categoria exibida (mesma regra de categoryFor). */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        $allModules = array_keys(self::MODULE_CATEGORY);
        $typesForCategory = array_keys(array_filter(self::TYPES, fn ($t) => ($t['category'] ?? null) === $category));
        $typesWithOtherCategory = array_keys(array_filter(self::TYPES, fn ($t) => ($t['category'] ?? null) !== null && $t['category'] !== $category));

        return $query->where(function (Builder $q) use ($category, $allModules, $typesForCategory, $typesWithOtherCategory) {
            $q->whereIn('module', self::modulesFor($category))
                ->orWhere(function (Builder $q) use ($category, $allModules, $typesForCategory, $typesWithOtherCategory) {
                    $q->where(fn (Builder $m) => $m->whereNull('module')->orWhereNotIn('module', $allModules));
                    if ($category === 'sistema') {
                        $q->whereNotIn('type', $typesWithOtherCategory);
                    } else {
                        $q->whereIn('type', $typesForCategory ?: ['__none__']);
                    }
                });
        });
    }

    // ==================== METHODS ====================

    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        return $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false, 'read_at' => null]);
    }

    // ==================== ACCESSORS ====================

    public function getCategoryAttribute(): string
    {
        return self::categoryFor($this->module, $this->type);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category]['label'];
    }

    public function getIconAttribute(): string
    {
        return self::TYPES[$this->type]['icon'] ?? self::CATEGORIES[$this->category]['icon'] ?? 'bi-bell-fill';
    }

    /** Nome do tom (ver TONES). Alta prioridade não muda a cor do tipo: vira selo "Urgente". */
    public function getColorAttribute(): string
    {
        return self::TYPES[$this->type]['tone'] ?? self::CATEGORIES[$this->category]['tone'] ?? 'indigo';
    }

    public function getToneStyleAttribute(): string
    {
        return self::toneStyle($this->color);
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? 'Notificação';
    }

    /** Data/hora no fuso de exibição (a aplicação grava em UTC). */
    public function getLocalCreatedAtAttribute(): ?Carbon
    {
        return $this->created_at?->copy()->timezone(self::DISPLAY_TZ)->locale('pt_BR');
    }

    public function getTimeAgoAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }
        if ($this->created_at->gt(now()->subMinute())) {
            return 'agora';
        }

        return $this->local_created_at->diffForHumans();
    }

    /**
     * Link seguro para a ação. URLs absolutas do próprio app viram caminho relativo
     * (notificações criadas pelo cron usam APP_URL, que pode estar errado).
     */
    public function getLinkAttribute(): ?string
    {
        $url = trim((string) $this->action_url);
        if ($url === '' || str_starts_with(strtolower($url), 'javascript:')) {
            return null;
        }
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) {
            return '/' . ltrim($url, '/');
        }

        $own = array_filter([
            parse_url((string) config('app.url'), PHP_URL_HOST),
            request()?->getHost(),
            'localhost', '127.0.0.1',
        ]);
        if (in_array($parts['host'], $own, true)) {
            return ($parts['path'] ?? '/')
                . (isset($parts['query']) ? '?' . $parts['query'] : '')
                . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
        }

        return $url;
    }

    public function getIsExternalLinkAttribute(): bool
    {
        return (bool) preg_match('#^https?://#i', (string) $this->link);
    }

    // ==================== PREFERÊNCIAS ====================

    /**
     * O usuário quer notificações in-app desta categoria?
     * preferences.notifications.inapp.<categoria> === false desliga (padrão: ligado).
     */
    public static function userWants(int $userId, string $category): bool
    {
        if ($category === 'sistema') {
            return true;
        }

        try {
            $prefs = User::query()->whereKey($userId)->value('preferences');
            $prefs = is_string($prefs) ? json_decode($prefs, true) : $prefs;
            $inapp = is_array($prefs) ? ($prefs['notifications']['inapp'] ?? []) : [];
        } catch (\Throwable $e) {
            $inapp = [];
        }

        return ($inapp[$category] ?? true) !== false;
    }

    // ==================== STATIC METHODS ====================

    public static function createDrawAvailable(Consortium $consortium): ?self
    {
        $eligible = $consortium->eligibleParticipantsCount();

        return self::createGeneric(
            'consortium',
            'draw_available',
            (int) $consortium->user_id,
            'Sorteio disponível',
            "O consórcio \"{$consortium->name}\" está pronto para um novo sorteio. {$eligible} participante(s) elegível(is) aguardando.",
            [
                'entity_type' => 'Consortium',
                'entity_id' => $consortium->id,
                'consortium_id' => $consortium->id,
                'priority' => 'high',
                'action_url' => route('consortiums.draw', $consortium, false),
                'data' => [
                    'eligible_count' => $eligible,
                    'last_draw_date' => $consortium->draws()->latest('draw_date')->value('draw_date'),
                ],
            ]
        );
    }

    public static function createRedemptionPending(ConsortiumParticipant $participant): ?self
    {
        $date = $participant->contemplation->contemplation_date;
        $days = (int) floor($date->diffInDays(now()));
        $clientName = $participant->client?->name ?? 'Participante';

        return self::createGeneric(
            'consortium',
            'redemption_pending',
            (int) $participant->consortium->user_id,
            'Resgate pendente',
            "{$clientName} foi contemplado(a) há {$days} " . ($days === 1 ? 'dia' : 'dias') . " no consórcio \"{$participant->consortium->name}\" e ainda não resgatou.",
            [
                'entity_type' => 'ConsortiumParticipant',
                'entity_id' => $participant->id,
                'consortium_id' => $participant->consortium_id,
                'related_participant_id' => $participant->id,
                'priority' => $days > 30 ? 'high' : 'medium',
                'action_url' => route('consortiums.show', $participant->consortium, false) . '#contemplated',
                'data' => [
                    'contemplation_date' => $date?->toDateString(),
                    'days_since' => $days,
                    'client_name' => $clientName,
                ],
            ]
        );
    }

    public static function unreadCountForUser(?int $userId): int
    {
        return self::unread()->forUser($userId)->count();
    }

    public static function markAllAsReadForUser(?int $userId, ?string $category = null): int
    {
        return self::unread()->forUser($userId)
            ->when($category, fn (Builder $q) => $q->inCategory($category))
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Criar notificação genérica para qualquer módulo.
     *
     * Retorna null quando o usuário desligou a categoria em Configurações > Notificações.
     *
     * @param array $options priority, action_url, data, entity_type, entity_id, consortium_id, related_participant_id, force
     */
    public static function createGeneric(
        string $module,
        string $type,
        int $userId,
        string $title,
        string $message,
        array $options = []
    ): ?self {
        $category = self::categoryFor($module, $type);
        if (empty($options['force']) && !self::userWants($userId, $category)) {
            return null;
        }

        $priority = $options['priority'] ?? 'medium';
        if (!isset(self::PRIORITIES[$priority])) {
            $priority = 'medium';
        }

        $actionUrl = $options['action_url'] ?? null;
        if ($actionUrl !== null && mb_strlen($actionUrl) > 255) {
            Log::warning('Notificação com action_url longa demais; descartada', ['type' => $type]);
            $actionUrl = null;
        }

        return self::create([
            'module' => $module,
            'entity_type' => $options['entity_type'] ?? null,
            'entity_id' => $options['entity_id'] ?? null,
            'consortium_id' => $options['consortium_id'] ?? null,
            'user_id' => $userId,
            'related_participant_id' => $options['related_participant_id'] ?? null,
            'type' => $type,
            'title' => mb_substr($title, 0, 255),
            'message' => $message,
            'priority' => $priority,
            'action_url' => $actionUrl,
            'data' => $options['data'] ?? [],
        ]);
    }
}
