<?php

namespace App\Services\Portal;

use App\Models\Client;
use App\Models\ClientQuoteRequest;
use App\Models\ConsortiumNotification;
use App\Models\PortalCart;
use App\Models\PortalSetting;
use App\Models\Product;
use App\Models\WhatsAppSetting;
use App\Services\WhatsApp\WhatsAppClient;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Carrinho abandonado: o carrinho do cliente logado no catálogo fica guardado;
 * se ele não fechar o pedido em X horas, a loja manda uma lembrança no WhatsApp
 * (sozinha pelo WhatsApp conectado, ou avisando a loja para mandar em um clique).
 * Liga e desliga em Vitrine › Carrinhos abandonados. Guardado em portal_settings.home.cart.
 */
class CartRecovery
{
    public const HOURS = [1 => '1 hora', 3 => '3 horas', 6 => '6 horas', 12 => '12 horas', 24 => '1 dia', 48 => '2 dias'];

    public const MODES = [
        'avisar' => ['Me avisar para eu mandar', 'Você recebe um aviso no sino e manda a lembrança em um clique pelo seu WhatsApp.'],
        'auto'   => ['Mandar sozinho pelo WhatsApp conectado', 'O app manda a mensagem pelo número conectado em Configurações › WhatsApp. Se não conseguir, avisa você.'],
    ];

    public const VARS = ['{nome}' => 'primeiro nome do cliente', '{produtos}' => 'produtos do carrinho', '{total}' => 'valor do carrinho', '{link}' => 'link que abre o carrinho', '{loja}' => 'nome da loja'];

    public const DEFAULT_MSG = "Oi, {nome}! Vi que você deixou {produtos} no carrinho da {loja}. Ainda quer? Separei pra você: {link}";

    /** Carrinhos mais velhos que isso não recebem lembrete (e somem da lista de abertos). */
    public const MAX_DAYS = 7;

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('portal_carts');
    }

    public static function settings(?PortalSetting $s): array
    {
        $c = array_merge(['on' => false, 'hours' => 3, 'mode' => 'avisar', 'msg' => self::DEFAULT_MSG, 'once' => true], (array) (($s?->home ?? [])['cart'] ?? []));

        return [
            'on' => (bool) $c['on'],
            'hours' => isset(self::HOURS[(int) $c['hours']]) ? (int) $c['hours'] : 3,
            'mode' => isset(self::MODES[$c['mode']]) ? $c['mode'] : 'avisar',
            'msg' => trim((string) $c['msg']) !== '' ? mb_substr((string) $c['msg'], 0, 700) : self::DEFAULT_MSG,
            'once' => (bool) $c['once'],
        ];
    }

    public static function settingsFor(int $ownerId): array
    {
        return self::settings(PortalSetting::where('user_id', $ownerId)->first());
    }

    public static function saveSettings(int $ownerId, array $data): array
    {
        $s = PortalSetting::for($ownerId);
        $clean = self::settings(new PortalSetting(['home' => ['cart' => $data]]));
        $s->home = array_merge($s->home ?? [], ['cart' => $clean]);
        $s->save();

        return $clean;
    }

    /**
     * Guarda o carrinho do cliente (chamado pelo catálogo a cada mudança).
     * Itens vêm do navegador: só os produtos da loja entram, com o preço do banco.
     */
    public static function sync(Client $client, array $raw): ?PortalCart
    {
        if (! self::ready()) {
            return null;
        }
        $qty = [];
        foreach (array_slice($raw, 0, 60) as $i) {
            $id = (int) ($i['id'] ?? 0);
            if ($id > 0) {
                $qty[$id] = max(1, min(999, (int) ($i['qty'] ?? 1)));
            }
        }
        $products = $qty ? Product::withoutGlobalScope('team_visibility')->where('user_id', $client->user_id)->whereIn('id', array_keys($qty))->with('activePromotion')->get()->keyBy('id') : collect();
        $items = [];
        $total = 0;
        foreach ($qty as $id => $q) {
            if (! $p = $products->get($id)) {
                continue;
            }
            $price = (float) ($p->activePromotion?->promo_price ?? $p->price_sale ?? 0);
            $items[] = ['id' => $id, 'name' => $p->name, 'qty' => $q, 'price' => round($price, 2)];
            $total += $price * $q;
        }

        $cart = PortalCart::firstOrNew(['user_id' => $client->user_id, 'client_id' => $client->id]);
        $changed = json_encode($cart->items ?? []) !== json_encode($items);
        $cart->items = $items;
        $cart->total = round($total, 2);
        $cart->token ??= Str::random(32);
        if ($changed) {
            $cart->last_activity_at = now();
            // Mexeu de novo no carrinho: conta como um carrinho novo.
            if ($items) {
                $cart->reminded_at = null;
                $cart->reminder_via = null;
                $cart->recovered_at = null;
                $cart->quote_id = null;
                $cart->dismissed_at = null;
            }
        }
        $cart->save();

        return $cart;
    }

    /** O cliente fechou o pedido: o carrinho conta como recuperado se tinha lembrete. */
    public static function ordered(ClientQuoteRequest $quote): void
    {
        if (! self::ready() || ! $quote->client_id) {
            return;
        }
        $cart = PortalCart::where('user_id', $quote->user_id)->where('client_id', $quote->client_id)->first();
        if (! $cart) {
            return;
        }
        $cart->forceFill(['recovered_at' => now(), 'quote_id' => $quote->id])->save();
    }

    /** Carrinhos que já passaram do tempo e ainda não receberam lembrete. */
    public static function due(int $ownerId, int $hours)
    {
        return PortalCart::where('user_id', $ownerId)
            ->whereNull('reminded_at')->whereNull('recovered_at')->whereNull('dismissed_at')
            ->where('total', '>', 0)
            ->where('last_activity_at', '<=', now()->subHours($hours))
            ->where('last_activity_at', '>=', now()->subDays(self::MAX_DAYS))
            ->with('client')
            ->get();
    }

    public static function link(PortalCart $cart): string
    {
        return route('portal.cart.restore', ['userId' => $cart->user_id, 'token' => $cart->token]);
    }

    public static function message(PortalCart $cart, ?array $settings = null, ?string $store = null): string
    {
        $settings ??= self::settingsFor($cart->user_id);
        $store ??= StoreIdentity::for($cart->user_id)['name'];
        $items = collect($cart->items ?? []);
        $names = $items->take(2)->map(fn ($i) => ($i['qty'] > 1 ? $i['qty'] . 'x ' : '') . $i['name'])->implode(' e ');
        if ($items->count() > 2) {
            $names = $items->take(2)->pluck('name')->implode(', ') . ' e mais ' . ($items->count() - 2) . ' ' . ($items->count() - 2 === 1 ? 'produto' : 'produtos');
        }
        $first = trim(explode(' ', trim((string) $cart->client?->name))[0] ?? '');

        return strtr($settings['msg'], [
            '{nome}' => $first ?: 'tudo bem',
            '{produtos}' => $names ?: 'alguns produtos',
            '{total}' => 'R$ ' . number_format((float) $cart->total, 2, ',', '.'),
            '{link}' => self::link($cart),
            '{loja}' => $store,
        ]);
    }

    public static function phone(?Client $client): ?string
    {
        $d = WhatsAppSetting::normalizeNumber($client?->phone);

        return strlen($d) >= 12 ? $d : null;
    }

    public static function waUrl(PortalCart $cart, ?array $settings = null): ?string
    {
        $phone = self::phone($cart->client);

        return $phone ? 'https://wa.me/' . $phone . '?text=' . rawurlencode(self::message($cart, $settings)) : null;
    }

    /** Roda pelo agendador: manda (ou avisa) os lembretes de todas as lojas com a opção ligada. */
    public static function run(): array
    {
        $out = ['sent' => 0, 'noticed' => 0];
        if (! self::ready()) {
            return $out;
        }
        $owners = PortalCart::whereNull('reminded_at')->whereNull('recovered_at')->distinct()->pluck('user_id');
        foreach ($owners as $ownerId) {
            $set = self::settingsFor((int) $ownerId);
            if (! $set['on']) {
                continue;
            }
            $carts = self::due((int) $ownerId, $set['hours']);
            if ($carts->isEmpty()) {
                continue;
            }
            $wa = $set['mode'] === 'auto' && Schema::hasTable('whatsapp_settings') ? WhatsAppSetting::where('user_id', $ownerId)->first() : null;
            $client = $wa && $wa->ready() ? new WhatsAppClient($wa) : null;
            $manual = [];
            foreach ($carts as $cart) {
                $phone = self::phone($cart->client);
                if ($client && $phone && $client->sendText($phone, self::message($cart, $set))) {
                    $cart->forceFill(['reminded_at' => now(), 'reminder_via' => 'whatsapp'])->save();
                    $out['sent']++;
                    continue;
                }
                $cart->forceFill(['reminded_at' => now(), 'reminder_via' => 'aviso'])->save();
                $manual[] = $cart;
            }
            if ($manual) {
                $out['noticed'] += count($manual);
                $n = count($manual);
                $names = collect($manual)->map(fn ($c) => $c->client?->name)->filter()->take(3)->implode(', ');
                ConsortiumNotification::createGeneric('portal', 'abandoned_cart', (int) $ownerId,
                    $n === 1 ? 'Carrinho esquecido no site' : "{$n} carrinhos esquecidos no site",
                    ($names ?: 'Clientes') . ($n === 1 ? ' deixou' : ' deixaram') . ' produtos no carrinho e não fechou o pedido. Mande a lembrança em um clique.',
                    ['priority' => 'medium', 'action_url' => route('promotions.carts'), 'data' => ['carts' => collect($manual)->pluck('id')->all()]]);
            }
        }

        return $out;
    }
}
