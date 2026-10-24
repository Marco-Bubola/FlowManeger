<?php

namespace App\Services\Portal;

use App\Models\PaymentSetting;
use App\Models\PortalShippingZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Frete por CEP do portal. A loja cadastra regiões (faixa de CEP ou cidade);
 * o CEP do cliente cai na faixa mais justa, depois na cidade (ViaCEP). Fora das
 * regiões vale a taxa padrão da entrega, ou a loja não entrega ali.
 */
class ShippingService
{
    /** Entrega ligada para esta loja (o carrinho só oferece entrega com o pagamento pelo site ligado). */
    public static function deliveryOn(?PaymentSetting $s): bool
    {
        return $s && $s->enabled && $s->delivery_enabled;
    }

    /** Bairro, cidade e UF do CEP (ViaCEP, guardado por 30 dias). */
    public static function lookup(string $cep): ?array
    {
        $cep = PortalShippingZone::cleanCep($cep);
        if (strlen($cep) !== 8) {
            return null;
        }

        return Cache::remember('viacep:' . $cep, now()->addDays(30), function () use ($cep) {
            try {
                $r = Http::timeout(4)->acceptJson()->get("https://viacep.com.br/ws/{$cep}/json/");
                $j = $r->ok() ? $r->json() : null;
                if (! is_array($j) || ! empty($j['erro'])) {
                    return null;
                }

                return ['bairro' => (string) ($j['bairro'] ?? ''), 'cidade' => (string) ($j['localidade'] ?? ''), 'uf' => (string) ($j['uf'] ?? ''), 'rua' => (string) ($j['logradouro'] ?? '')];
            } catch (\Throwable $e) {
                return null;
            }
        }) ?: null;
    }

    /**
     * Frete para o CEP. $subtotal = produtos já com desconto (para o frete grátis acima de).
     * Volta ok + fee (0 = grátis) + região/prazo, ou ok=false com a mensagem para o cliente.
     */
    public static function quote(int $userId, ?string $cep, float $subtotal, ?PaymentSetting $s = null): array
    {
        $s ??= PaymentSetting::for($userId);
        $cep = PortalShippingZone::cleanCep($cep);
        if (strlen($cep) !== 8) {
            return ['ok' => false, 'message' => 'Digite o CEP com 8 números.'];
        }
        if (! self::deliveryOn($s)) {
            return ['ok' => false, 'message' => 'Esta loja combina a entrega com você depois do pedido.'];
        }

        $zones = Schema::hasTable('portal_shipping_zones')
            ? PortalShippingZone::where('user_id', $userId)->where('active', true)->get()
            : collect();
        $addr = null;

        // 1) Faixa de CEP (a mais estreita ganha); 2) cidade.
        $zone = $zones->where('kind', 'cep')
            ->filter(fn ($z) => $z->cep_from && $z->cep_to && $cep >= $z->cep_from && $cep <= $z->cep_to)
            ->sortBy(fn ($z) => (int) $z->cep_to - (int) $z->cep_from)
            ->first();
        $cityZones = $zones->where('kind', 'city');
        if (! $zone && $cityZones->isNotEmpty() && ($addr = self::lookup($cep))) {
            $norm = fn ($v) => Str::lower(Str::ascii(trim((string) $v)));
            $zone = $cityZones->first(fn ($z) => $norm($z->city) === $norm($addr['cidade']) && (! $z->uf || strtoupper($z->uf) === strtoupper($addr['uf'])));
        }
        $addr ??= self::lookup($cep);
        $place = $addr ? trim(implode(', ', array_filter([$addr['bairro'], $addr['cidade'] ? $addr['cidade'] . '/' . $addr['uf'] : null]))) : PortalShippingZone::formatCep($cep);

        if ($zone) {
            $free = $zone->free_above !== null && (float) $zone->free_above > 0 && $subtotal >= (float) $zone->free_above;

            return [
                'ok' => true, 'fee' => $free ? 0.0 : (float) $zone->fee, 'base' => (float) $zone->fee,
                'freeAbove' => $zone->free_above !== null && (float) $zone->free_above > 0 ? (float) $zone->free_above : null,
                'zone' => $zone->name, 'eta' => $zone->eta ?: ($s->delivery_note ?: null), 'place' => $place, 'cep' => PortalShippingZone::formatCep($cep),
            ];
        }
        if ($zones->isNotEmpty() && ($s->shipping_outside ?? 'fee') === 'none') {
            return ['ok' => false, 'place' => $place, 'message' => 'Ainda não entregamos em ' . $place . '. Você pode retirar com a loja.'];
        }

        return [
            'ok' => true, 'fee' => (float) $s->delivery_fee, 'base' => (float) $s->delivery_fee, 'freeAbove' => null,
            'zone' => null, 'eta' => $s->delivery_note ?: null, 'place' => $place, 'cep' => PortalShippingZone::formatCep($cep),
        ];
    }
}
