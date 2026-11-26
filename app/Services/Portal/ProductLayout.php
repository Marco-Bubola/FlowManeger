<?php

namespace App\Services\Portal;

use App\Models\PortalSetting;
use Illuminate\Support\Facades\Auth;

/**
 * Montagem da página do produto no portal (vale para todos os produtos da loja):
 * ordem e visibilidade das partes ao lado da foto e das seções de baixo,
 * posição e tamanho do nome, jeito das fotos, preço e botões.
 * Fica em portal_settings.home.product_page.layout.
 */
class ProductLayout
{
    /** Partes da coluna de compra (ao lado da foto). [nome, ícone, explicação] */
    public const PARTS = [
        'preco' => ['Preço', 'bi-tag', 'Preço, desconto, Pix e pontos'],
        'variacoes' => ['Variações', 'bi-grid-3x2-gap', 'Tamanhos, cores ou modelos'],
        'avisos' => ['Prazo e economia', 'bi-clock-history', 'Sob encomenda e quanto economiza'],
        'frete' => ['Frete e entrega', 'bi-truck', 'Calcular frete pelo CEP'],
        'loja' => ['Compra direta', 'bi-shield-check', '"Compra direta com a loja"'],
        'quantidade' => ['Quantidade', 'bi-123', 'Botões de mais e menos'],
        'botoes' => ['Botões de compra', 'bi-cart-check', 'Comprar, carrinho e WhatsApp'],
        'compartilhar' => ['Compartilhar', 'bi-share', 'Enviar para alguém e copiar link'],
        'kit' => ['Montar kit', 'bi-gift', 'Só aparece se o Monte seu kit estiver ligado'],
    ];

    /** Seções de baixo da página. */
    public const SECTIONS = [
        'especial' => ['Blocos da página especial', 'bi-stars', 'Só nos produtos que têm página especial'],
        'junto' => ['Compre junto', 'bi-cart-plus', 'Produtos que combinam'],
        'sobre' => ['Descrição e características', 'bi-card-text', 'Texto e ficha do produto'],
        'avaliacoes' => ['Avaliações', 'bi-star-half', 'Estrelas e comentários'],
        'relacionados' => ['Produtos relacionados', 'bi-collection', 'Da mesma categoria'],
        'vistos' => ['Vistos recentemente', 'bi-eye', 'O que o cliente olhou antes'],
    ];

    /** Escolhas de uma opção: chave => [valor => rótulo]. */
    public const CHOICES = [
        'title_pos' => ['acima' => 'Acima da foto', 'abaixo' => 'Abaixo da foto'],
        'title_size' => ['p' => 'Pequeno', 'm' => 'Médio', 'g' => 'Grande'],
        'title_align' => ['esquerda' => 'À esquerda', 'centro' => 'Centralizado'],
        'photo_side' => ['esquerda' => 'Foto à esquerda', 'direita' => 'Foto à direita'],
        'photo_size' => ['pequena' => 'Metade', 'media' => 'Média', 'grande' => 'Grande'],
        'photo_ratio' => ['quadrada' => 'Quadrada', 'retrato' => 'Em pé', 'paisagem' => 'Deitada'],
        'photo_fit' => ['inteira' => 'Foto inteira', 'preencher' => 'Preencher o quadro'],
        'thumbs' => ['baixo' => 'Embaixo', 'lado' => 'Do lado', 'nada' => 'Sem miniaturas'],
        'price_size' => ['m' => 'Normal', 'g' => 'Grande'],
    ];

    public const DEFAULTS = [
        'parts' => ['preco', 'variacoes', 'avisos', 'frete', 'loja', 'quantidade', 'botoes', 'compartilhar', 'kit'],
        'parts_off' => [],
        'sections' => ['especial', 'junto', 'sobre', 'avaliacoes', 'relacionados', 'vistos'],
        'sections_off' => [],
        'title_pos' => 'acima',
        'title_size' => 'm',
        'title_align' => 'esquerda',
        'show_cat' => true,
        'show_rating' => true,
        'photo_side' => 'esquerda',
        'photo_size' => 'media',
        'photo_ratio' => 'quadrada',
        'photo_fit' => 'inteira',
        'thumbs' => 'baixo',
        'photo_count' => true,
        'price_size' => 'm',
        'show_deal' => true,
        'show_pix' => true,
        'show_points' => true,
        'buy_text' => 'Comprar agora',
        'cart_text' => 'Adicionar ao carrinho',
        'show_cart' => true,
        'show_whatsapp' => true,
        'buybar' => true,
    ];

    public const FLAGS = ['show_cat', 'show_rating', 'photo_count', 'show_deal', 'show_pix', 'show_points', 'show_cart', 'show_whatsapp', 'buybar'];

    /** Limpa o que veio de fora: só chaves conhecidas, ordem completa, textos curtos. */
    public static function normalize(?array $in): array
    {
        $in = (array) $in;
        $l = self::DEFAULTS;
        foreach (['parts' => self::PARTS, 'sections' => self::SECTIONS] as $k => $all) {
            $order = array_values(array_unique(array_filter((array) ($in[$k] ?? []), fn ($x) => is_string($x) && isset($all[$x]))));
            $l[$k] = array_merge($order, array_values(array_diff(array_keys($all), $order)));
            $l[$k . '_off'] = array_values(array_unique(array_filter((array) ($in[$k . '_off'] ?? []), fn ($x) => is_string($x) && isset($all[$x]))));
        }
        foreach (self::CHOICES as $k => $opts) {
            $v = (string) ($in[$k] ?? '');
            $l[$k] = isset($opts[$v]) ? $v : self::DEFAULTS[$k];
        }
        foreach (self::FLAGS as $k) {
            $l[$k] = array_key_exists($k, $in) ? (bool) $in[$k] : self::DEFAULTS[$k];
        }
        foreach (['buy_text', 'cart_text'] as $k) {
            $v = trim(strip_tags((string) ($in[$k] ?? '')));
            $l[$k] = $v !== '' ? mb_substr($v, 0, 30) : self::DEFAULTS[$k];
        }

        return $l;
    }

    /** Montagem salva da loja; o dono logado pode ver uma prévia ainda não salva (?pagina=...). */
    public static function for(int $ownerId): array
    {
        if ($preview = self::previewing($ownerId)) {
            return $preview;
        }
        static $memo = [];

        if (! isset($memo[$ownerId])) {
            $home = (array) (PortalSetting::where('user_id', $ownerId)->first()?->home ?? []);
            $memo[$ownerId] = self::normalize((array) ($home['product_page']['layout'] ?? []));
        }

        return $memo[$ownerId];
    }

    public static function save(int $ownerId, array $layout): array
    {
        $layout = self::normalize($layout);
        $s = PortalSetting::for($ownerId);
        $home = (array) ($s->home ?? []);
        $pp = (array) ($home['product_page'] ?? []);
        $pp['layout'] = $layout;
        $home['product_page'] = $pp;
        $s->home = $home;
        $s->save();

        return $layout;
    }

    public static function encode(array $layout): string
    {
        return rtrim(strtr(base64_encode((string) json_encode(self::normalize($layout))), '+/', '-_'), '=');
    }

    private static function previewing(int $ownerId): ?array
    {
        $raw = (string) request()->query('pagina', '');
        if ($raw === '' || (int) Auth::guard('web')->id() !== $ownerId) {
            return null;
        }
        $json = json_decode((string) base64_decode(strtr($raw, '-_', '+/')), true);

        return is_array($json) ? self::normalize($json) : null;
    }

    public static function on(array $l, string $key): bool
    {
        return ! in_array($key, $l['parts_off'], true) && ! in_array($key, $l['sections_off'], true);
    }

    /** Classes no <section> principal para o CSS da montagem. */
    public static function classes(array $l): string
    {
        return collect([
            'ppl-t-' . $l['title_pos'], 'ppl-ts-' . $l['title_size'], 'ppl-ta-' . $l['title_align'],
            'ppl-ps-' . $l['photo_side'], 'ppl-pz-' . $l['photo_size'], 'ppl-pr-' . $l['photo_ratio'],
            'ppl-pf-' . $l['photo_fit'], 'ppl-th-' . $l['thumbs'], 'ppl-pc-' . $l['price_size'],
            $l['photo_count'] ? '' : 'ppl-no-count',
        ])->filter()->join(' ');
    }
}
