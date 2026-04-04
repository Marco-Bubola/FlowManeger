<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_id', 'status', 'billing_cycle', 'price_paid',
        'gateway', 'gateway_subscription_id', 'gateway_customer_id', 'gateway_payment_id',
        'trial_ends_at', 'current_period_start', 'current_period_end',
        'canceled_at', 'paused_at', 'notes', 'metadata',
    ];

    protected $casts = [
        'price_paid'           => 'decimal:2',
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'canceled_at'          => 'datetime',
        'paused_at'            => 'datetime',
        'metadata'             => 'array',
    ];

    // Status constants
    const STATUS_ACTIVE    = 'active';
    const STATUS_TRIALING  = 'trialing';
    const STATUS_CANCELED  = 'canceled';
    const STATUS_PAST_DUE  = 'past_due';
    const STATUS_PAUSED    = 'paused';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_PENDING   = 'pending';

    // Gateway constants
    const GATEWAY_STRIPE      = 'stripe';
    const GATEWAY_PAGSEGURO   = 'pagseguro';
    const GATEWAY_MERCADOPAGO = 'mercadopago';
    const GATEWAY_MANUAL      = 'manual';

    // ── Relationships ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ── Status helpers ────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->current_period_end === null || $this->current_period_end->isFuture());
    }

    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIALING
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function isValid(): bool
    {
        return $this->isActive() || $this->isTrialing();
    }

    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    public function isPastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }
        if ($this->current_period_end && $this->current_period_end->isPast()) {
            return true;
        }
        return false;
    }

    public function daysLeft(): int
    {
        $end = $this->isTrialing() ? $this->trial_ends_at : $this->current_period_end;
        if (!$end || $end->isPast()) {
            return 0;
        }
        return (int) now()->diffInDays($end);
    }

    public function daysLeftInTrial(): int
    {
        if (!$this->isTrialing() || !$this->trial_ends_at) {
            return 0;
        }
        return max(0, (int) now()->diffInDays($this->trial_ends_at));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE   => 'Ativo',
            self::STATUS_TRIALING => 'Em teste',
            self::STATUS_CANCELED => 'Cancelado',
            self::STATUS_PAST_DUE => 'Pagamento pendente',
            self::STATUS_PAUSED   => 'Pausado',
            self::STATUS_EXPIRED  => 'Expirado',
            self::STATUS_PENDING  => 'Aguardando',
            default               => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE   => 'green',
            self::STATUS_TRIALING => 'blue',
            self::STATUS_CANCELED => 'red',
            self::STATUS_PAST_DUE => 'orange',
            self::STATUS_PAUSED   => 'yellow',
            self::STATUS_EXPIRED  => 'gray',
            default               => 'gray',
        };
    }

    public function gatewayLabel(): string
    {
        return match ($this->gateway) {
            self::GATEWAY_STRIPE      => 'Stripe',
            self::GATEWAY_PAGSEGURO   => 'PagSeguro',
            self::GATEWAY_MERCADOPAGO => 'Mercado Pago',
            self::GATEWAY_MANUAL      => 'Manual (Admin)',
            default                   => $this->gateway ?? '—',
        };
    }

    public function billingCycleLabel(): string
    {
        return $this->billing_cycle === 'annual' ? 'Anual' : 'Mensal';
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->whereNull('current_period_end')
                  ->orWhere('current_period_end', '>', now());
            });
    }

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_ACTIVE)
              ->where(function ($inner) {
                  $inner->whereNull('current_period_end')
                        ->orWhere('current_period_end', '>', now());
              });
        })->orWhere(function ($q) {
            $q->where('status', self::STATUS_TRIALING)
              ->where('trial_ends_at', '>', now());
        });
    }
}
