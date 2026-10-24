<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cupom de desconto do catálogo. Vale sobre os produtos do pedido (sem a entrega).
 * Usos = pedidos com o código que não foram recusados.
 */
class PortalCoupon extends Model
{
    protected $fillable = [
        'user_id', 'code', 'kind', 'value', 'min_total', 'starts_at', 'ends_at',
        'max_uses', 'once_per_client', 'active',
    ];

    protected $casts = [
        'value'           => 'decimal:2',
        'min_total'       => 'decimal:2',
        'starts_at'       => 'date',
        'ends_at'         => 'date',
        'max_uses'        => 'integer',
        'once_per_client' => 'boolean',
        'active'          => 'boolean',
    ];

    public static function normalize(?string $code): string
    {
        return mb_strtoupper(preg_replace('/\s+/', '', (string) $code));
    }

    public static function findFor(int $userId, ?string $code): ?self
    {
        $code = self::normalize($code);

        return $code === '' ? null : self::where('user_id', $userId)->where('code', $code)->first();
    }

    /** Pedidos que usaram este cupom (fora os recusados). */
    public function usesQuery(?int $clientId = null)
    {
        return ClientQuoteRequest::where('user_id', $this->user_id)
            ->where('coupon_code', $this->code)
            ->where('status', '!=', 'rejected')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId));
    }

    public function label(): string
    {
        return $this->kind === 'fixed'
            ? 'R$ ' . number_format((float) $this->value, 2, ',', '.') . ' de desconto'
            : rtrim(rtrim(number_format((float) $this->value, 2, ',', ''), '0'), ',') . '% de desconto';
    }

    public function discountFor(float $subtotal): float
    {
        $d = $this->kind === 'fixed' ? (float) $this->value : $subtotal * (float) $this->value / 100;

        return round(max(0, min($d, $subtotal)), 2);
    }

    /**
     * Confere se o cupom vale para este pedido.
     *
     * @return array{ok: bool, message: string, discount: float}
     */
    public function check(float $subtotal, ?int $clientId): array
    {
        $no = fn (string $m) => ['ok' => false, 'message' => $m, 'discount' => 0.0];
        $today = now()->startOfDay();
        if (! $this->active) {
            return $no('Este cupom não está mais valendo.');
        }
        if ($this->starts_at && $today->lt($this->starts_at)) {
            return $no('Este cupom começa a valer em ' . $this->starts_at->format('d/m') . '.');
        }
        if ($this->ends_at && $today->gt($this->ends_at)) {
            return $no('Este cupom venceu em ' . $this->ends_at->format('d/m') . '.');
        }
        if ($this->max_uses && $this->usesQuery()->count() >= $this->max_uses) {
            return $no('Este cupom já foi usado o máximo de vezes.');
        }
        if ($this->once_per_client && $clientId && $this->usesQuery($clientId)->exists()) {
            return $no('Você já usou este cupom em outro pedido.');
        }
        if ($this->min_total && $subtotal + 0.009 < (float) $this->min_total) {
            return $no('Este cupom vale para pedidos a partir de R$ ' . number_format((float) $this->min_total, 2, ',', '.') . '.');
        }

        return ['ok' => true, 'message' => 'Cupom aplicado: ' . $this->label() . '.', 'discount' => $this->discountFor($subtotal)];
    }
}
