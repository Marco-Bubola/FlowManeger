<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Orçamento de produto sob medida (outra cor, outra medida, peça nova) ou de reforma.
 * O custo (items.*.costs, cost_total, admin_notes) é só da loja: o cliente vê
 * apenas o que Quote::publicItems() devolve.
 */
class Quote extends Model
{
    use SoftDeletes;

    public const KINDS = [
        'produto' => ['label' => 'Produto novo', 'icon' => 'bi-rulers', 'tone' => 'indigo'],
        'reforma' => ['label' => 'Reforma', 'icon' => 'bi-tools', 'tone' => 'amber'],
    ];

    /** Ordem = ordem das abas da lista. */
    public const STATUSES = [
        'pedido' => ['label' => 'Pedido novo', 'icon' => 'bi-inbox', 'tone' => 'pink', 'hint' => 'chegou pelo portal'],
        'rascunho' => ['label' => 'Rascunho', 'icon' => 'bi-pencil', 'tone' => 'slate', 'hint' => 'ainda não enviado'],
        'enviado' => ['label' => 'Enviado', 'icon' => 'bi-send', 'tone' => 'sky', 'hint' => 'esperando o cliente'],
        'aprovado' => ['label' => 'Aprovado', 'icon' => 'bi-hand-thumbs-up', 'tone' => 'emerald', 'hint' => 'pronto para virar venda'],
        'convertido' => ['label' => 'Virou venda', 'icon' => 'bi-cart-check', 'tone' => 'violet', 'hint' => 'venda criada'],
        'recusado' => ['label' => 'Recusado', 'icon' => 'bi-x-circle', 'tone' => 'rose', 'hint' => 'o cliente não quis'],
        'vencido' => ['label' => 'Vencido', 'icon' => 'bi-hourglass-bottom', 'tone' => 'amber', 'hint' => 'passou da validade'],
    ];

    /** Situações em que o orçamento ainda está "em aberto" (conta no valor em aberto). */
    public const OPEN = ['pedido', 'rascunho', 'enviado', 'aprovado'];

    protected $fillable = [
        'user_id', 'number', 'token', 'client_id', 'contact_name', 'contact_phone',
        'kind', 'status', 'source', 'title', 'request_text', 'request_photos', 'request_data', 'product_id',
        'items', 'services', 'pickup', 'after_photos',
        'discount', 'delivery_fee', 'total', 'cost_total', 'production_days', 'valid_until', 'payment_terms',
        'client_notes', 'admin_notes', 'change_request', 'events', 'reminders',
        'sent_at', 'viewed_at', 'approved_at', 'rejected_at', 'edited_after_sent_at', 'sale_id',
    ];

    protected $casts = [
        'request_photos' => 'array',
        'request_data' => 'array',
        'items' => 'array',
        'services' => 'array',
        'after_photos' => 'array',
        'events' => 'array',
        'reminders' => 'array',
        'discount' => 'float',
        'delivery_fee' => 'float',
        'total' => 'float',
        'cost_total' => 'float',
        'production_days' => 'integer',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'edited_after_sent_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function code(): string
    {
        return 'ORC-' . str_pad((string) $this->number, 4, '0', STR_PAD_LEFT);
    }

    public function statusInfo(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['rascunho'];
    }

    public function kindInfo(): array
    {
        return self::KINDS[$this->kind] ?? self::KINDS['produto'];
    }

    public function customerName(): string
    {
        return (string) ($this->client?->name ?: ($this->contact_name ?: 'Sem cliente'));
    }

    public function customerPhone(): ?string
    {
        return $this->client?->phone ?: $this->contact_phone;
    }

    public function displayTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }
        $first = collect($this->items ?? [])->pluck('name')->filter()->first();

        return $first ?: ($this->kind === 'reforma' ? 'Reforma' : 'Orçamento');
    }

    public function publicUrl(): string
    {
        return route('orcamento.public', $this->token);
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->lt(Carbon::today(ConsortiumNotification::DISPLAY_TZ));
    }

    /** O cliente ainda pode aprovar? */
    public function canBeApproved(): bool
    {
        return $this->status === 'enviado' && !$this->isExpired();
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN, true);
    }

    /** Fotos de capa do card: a do primeiro item, ou a que o cliente mandou. */
    public function coverPhoto(): ?string
    {
        foreach ($this->items ?? [] as $it) {
            if (!empty($it['photos'][0])) {
                return $it['photos'][0];
            }
        }

        return $this->request_photos[0] ?? null;
    }

    /**
     * Itens como o CLIENTE vê: sem custo, sem materiais, sem margem.
     * @return array<int, array{name:string, description:string, qty:int, price:float, line:float, photos:array, attrs:array}>
     */
    public function publicItems(): array
    {
        return collect($this->items ?? [])->map(fn ($it) => [
            'name' => (string) ($it['name'] ?? ''),
            'description' => (string) ($it['description'] ?? ''),
            'qty' => max(1, (int) ($it['qty'] ?? 1)),
            'price' => round((float) ($it['price'] ?? 0), 2),
            'line' => round((float) ($it['price'] ?? 0) * max(1, (int) ($it['qty'] ?? 1)), 2),
            'photos' => array_values(array_filter((array) ($it['photos'] ?? []))),
            'attrs' => array_filter((array) ($it['attrs'] ?? []), fn ($v) => trim((string) $v) !== ''),
        ])->filter(fn ($it) => $it['name'] !== '')->values()->all();
    }

    public function subtotal(): float
    {
        return round(collect($this->publicItems())->sum('line'), 2);
    }

    public function addEvent(string $type, string $text, array $extra = []): void
    {
        $events = $this->events ?? [];
        $events[] = ['t' => $type, 'txt' => $text, 'at' => now()->toIso8601String()] + $extra;
        $this->events = array_slice($events, -60);
    }

    public static function photoUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
