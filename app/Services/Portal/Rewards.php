<?php

namespace App\Services\Portal;

use App\Models\Client;
use App\Models\ClientQuoteRequest;
use App\Models\PortalPoint;
use App\Models\PortalSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Desconto no Pix e pontos de fidelidade do catálogo (Vitrine › Pix e pontos).
 * Configuração em portal_settings.home.rewards; extrato de pontos em portal_points.
 *
 * Pix: x% de desconto no valor dos produtos (o frete não entra) para quem paga no Pix.
 * Pontos: o cliente ganha N pontos por real quando o pedido vira venda; cada 100 pontos
 * valem R$ worth no próximo pedido (até max_pct% do valor dos produtos).
 */
class Rewards
{
    public const DEFAULTS = [
        'pix' => 0, 'pix_card' => true,
        'pts' => false, 'earn' => 1, 'worth' => 5, 'min_use' => 100, 'max_pct' => 50, 'expire' => 365, 'welcome' => 0,
    ];

    public const EXPIRE = [0 => 'Nunca', 90 => '3 meses sem comprar', 180 => '6 meses sem comprar', 365 => '1 ano sem comprar'];

    public const KINDS = [
        'ganho'   => ['Ganhou na compra', 'bi-bag-check', 'text-emerald-600'],
        'bonus'   => ['Bônus de boas-vindas', 'bi-gift', 'text-violet-600'],
        'uso'     => ['Usou no pedido', 'bi-ticket-perforated', 'text-sky-600'],
        'estorno' => ['Devolvido (pedido cancelado)', 'bi-arrow-counterclockwise', 'text-amber-600'],
        'ajuste'  => ['Ajuste da loja', 'bi-pencil', 'text-slate-500'],
        'expirou' => ['Venceram', 'bi-hourglass-bottom', 'text-rose-600'],
    ];

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('portal_points') && Schema::hasColumn('client_quote_requests', 'points_used');
    }

    public static function settings(?PortalSetting $s): array
    {
        $c = array_merge(self::DEFAULTS, (array) (($s?->home ?? [])['rewards'] ?? []));
        $num = fn ($v, $min, $max) => max($min, min($max, round((float) str_replace(',', '.', (string) $v), 2)));

        return [
            'pix'      => $num($c['pix'], 0, 30),
            'pix_card' => (bool) $c['pix_card'],
            'pts'      => (bool) $c['pts'],
            'earn'     => $num($c['earn'], 0.1, 100),
            'worth'    => $num($c['worth'], 0.5, 100),
            'min_use'  => (int) $num($c['min_use'], 0, 100000),
            'max_pct'  => (int) $num($c['max_pct'], 5, 100),
            'expire'   => isset(self::EXPIRE[(int) $c['expire']]) ? (int) $c['expire'] : 365,
            'welcome'  => (int) $num($c['welcome'], 0, 100000),
        ];
    }

    public static function for(int $ownerId): array
    {
        static $memo = [];

        return $memo[$ownerId] ??= self::settings(PortalSetting::where('user_id', $ownerId)->first());
    }

    public static function save(int $ownerId, array $data): array
    {
        $s = PortalSetting::for($ownerId);
        $clean = self::settings(new PortalSetting(['home' => ['rewards' => $data]]));
        $s->home = array_merge($s->home ?? [], ['rewards' => $clean]);
        $s->save();

        return $clean;
    }

    /* ---------- Pix ---------- */

    /** Desconto do Pix sobre um valor (com a % do pedido, se ele guardou uma). */
    public static function pixOff(float $base, float $pct): float
    {
        return $pct > 0 ? round(max(0, $base) * $pct / 100, 2) : 0.0;
    }

    /** Preço no Pix para mostrar no card e na página do produto (null se a loja não dá desconto). */
    public static function pixPrice(int $ownerId, float $price): ?float
    {
        $pct = self::for($ownerId)['pix'];

        return $pct > 0 && $price > 0 ? round($price - self::pixOff($price, $pct), 2) : null;
    }

    /** O Pix pelo site precisa estar ligado para o desconto valer na hora de pagar. */
    public static function pixOnline(int $ownerId): bool
    {
        $pay = \App\Models\PaymentSetting::for($ownerId);

        return (bool) ($pay && in_array('pix', $pay->methods(), true));
    }

    /** Valor dos produtos do pedido com cupom, pontos e kit (sem frete): é sobre ele que sai o Pix. */
    public static function productsValue(ClientQuoteRequest $quote): float
    {
        $sum = collect($quote->items ?? [])->sum(fn ($i) => (float) ($i['price_ref'] ?? 0) * max(1, (int) ($i['quantity'] ?? 1)));

        return max(0, $sum - (float) ($quote->discount ?? 0) - (float) ($quote->points_discount ?? 0)) + KitService::total($quote);
    }

    public static function quotePixOff(ClientQuoteRequest $quote): float
    {
        return self::pixOff(self::productsValue($quote), (float) ($quote->pix_pct ?? 0));
    }

    /* ---------- Pontos ---------- */

    public static function on(int $ownerId): bool
    {
        return self::ready() && self::for($ownerId)['pts'];
    }

    /** Saldo do cliente. Sem comprar no prazo da loja, os pontos vencem (fica uma linha "Venceram"). */
    public static function balance(Client $client): int
    {
        if (! self::ready()) {
            return 0;
        }
        $rows = PortalPoint::where('user_id', $client->user_id)->where('client_id', $client->id);
        $bal = (int) (clone $rows)->sum('points');
        $days = self::for((int) $client->user_id)['expire'];
        if ($bal > 0 && $days > 0) {
            $last = (clone $rows)->whereIn('kind', ['ganho', 'bonus', 'uso'])->max('created_at');
            if ($last && now()->diffInDays($last, true) > $days) {
                PortalPoint::create(['user_id' => $client->user_id, 'client_id' => $client->id, 'points' => -$bal, 'kind' => 'expirou', 'note' => "{$days} dias sem comprar"]);

                return 0;
            }
        }

        return max(0, $bal);
    }

    /** Quanto valem os pontos, em reais. */
    public static function money(int $ownerId, int $points): float
    {
        return round($points * self::for($ownerId)['worth'] / 100, 2);
    }

    /** Pontos que uma compra desse valor dá. */
    public static function earnFor(int $ownerId, float $value): int
    {
        return (int) floor(max(0, $value) * self::for($ownerId)['earn']);
    }

    /**
     * Quanto dá para usar agora num pedido de produtos de $base reais:
     * [pontos, desconto em R$]. Respeita o mínimo e o teto da loja.
     */
    public static function usable(Client $client, float $base): array
    {
        $owner = (int) $client->user_id;
        if (! self::on($owner)) {
            return [0, 0.0];
        }
        $cfg = self::for($owner);
        $bal = self::balance($client);
        if ($bal < max(1, $cfg['min_use']) || $base <= 0) {
            return [0, 0.0];
        }
        $value = min(self::money($owner, $bal), round($base * $cfg['max_pct'] / 100, 2));
        $points = min($bal, (int) ceil($value * 100 / $cfg['worth']));

        return [$points, round(min($value, self::money($owner, $points)), 2)];
    }

    /** Gasta os pontos no pedido (o desconto já foi gravado nele). */
    public static function spend(ClientQuoteRequest $quote): void
    {
        if (self::ready() && (int) $quote->points_used > 0) {
            PortalPoint::create(['user_id' => $quote->user_id, 'client_id' => $quote->client_id, 'quote_id' => $quote->id,
                'points' => -(int) $quote->points_used, 'kind' => 'uso', 'note' => 'Pedido #' . $quote->id]);
        }
    }

    /** O pedido virou venda: o cliente ganha os pontos (uma vez por pedido). */
    public static function earn(ClientQuoteRequest $quote, ?float $paid = null): void
    {
        $owner = (int) $quote->user_id;
        if (! self::on($owner) || ! $quote->client_id
            || PortalPoint::where('quote_id', $quote->id)->where('kind', 'ganho')->exists()) {
            return;
        }
        // Sobre o que foi pago pelos produtos (o frete não conta).
        $value = $paid !== null ? $paid - (float) ($quote->delivery_fee ?? 0) : self::productsValue($quote) - self::quotePixOff($quote);
        $pts = self::earnFor($owner, $value);
        if ($pts > 0) {
            PortalPoint::create(['user_id' => $owner, 'client_id' => $quote->client_id, 'quote_id' => $quote->id,
                'points' => $pts, 'kind' => 'ganho', 'note' => 'Pedido #' . $quote->id]);
        }
    }

    /** Pedido recusado ou apagado: devolve os pontos usados e tira os ganhos nele. */
    public static function undo(ClientQuoteRequest $quote): void
    {
        if (! self::ready()) {
            return;
        }
        $net = (int) PortalPoint::where('quote_id', $quote->id)->whereIn('kind', ['uso', 'ganho', 'estorno'])->sum('points');
        if ($net !== 0) {
            PortalPoint::create(['user_id' => $quote->user_id, 'client_id' => $quote->client_id, 'quote_id' => $quote->id,
                'points' => -$net, 'kind' => 'estorno', 'note' => 'Pedido #' . $quote->id . ($net < 0 ? ' cancelado: pontos de volta' : ' cancelado')]);
        }
    }

    /** Bônus para quem cria a conta no catálogo. */
    public static function welcome(Client $client): void
    {
        $owner = (int) $client->user_id;
        if (self::on($owner) && ($b = self::for($owner)['welcome']) > 0
            && ! PortalPoint::where('client_id', $client->id)->where('kind', 'bonus')->exists()) {
            PortalPoint::create(['user_id' => $owner, 'client_id' => $client->id, 'points' => $b, 'kind' => 'bonus', 'note' => 'Boas-vindas']);
        }
    }

    /** Ajuste feito pela loja (somar ou tirar pontos de um cliente). */
    public static function adjust(int $ownerId, Client $client, int $points, string $note): void
    {
        if ($points !== 0 && self::ready() && (int) $client->user_id === $ownerId) {
            PortalPoint::create(['user_id' => $ownerId, 'client_id' => $client->id, 'points' => $points, 'kind' => 'ajuste', 'note' => mb_substr($note, 0, 160) ?: null]);
        }
    }

    public static function history(Client $client, int $limit = 15)
    {
        return self::ready()
            ? PortalPoint::where('user_id', $client->user_id)->where('client_id', $client->id)->latest('id')->limit($limit)->get()
            : collect();
    }
}
