<?php

namespace App\Services\Portal;

use App\Models\PortalSetting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Identidade da loja no catálogo: logo e nome no topo, fonte dos títulos, contatos do rodapé
 * e as páginas da loja (Sobre nós, Trocas e devoluções, Entrega). Tudo em portal_settings.
 */
class StoreIdentity
{
    /** Fontes dos títulos: [nome, família CSS, parâmetro do Google Fonts ou null]. */
    public const FONTS = [
        'padrao'     => ['Padrão (Inter)', "'Inter', system-ui, sans-serif", null],
        'poppins'    => ['Poppins (moderna)', "'Poppins', sans-serif", 'Poppins:wght@600;700;800'],
        'montserrat' => ['Montserrat (forte)', "'Montserrat', sans-serif", 'Montserrat:wght@600;700;800'],
        'playfair'   => ['Playfair (elegante)', "'Playfair Display', serif", 'Playfair+Display:wght@600;700;800'],
        'lora'       => ['Lora (clássica)', "'Lora', serif", 'Lora:wght@600;700'],
        'nunito'     => ['Nunito (suave)', "'Nunito', sans-serif", 'Nunito:wght@700;800;900'],
        'raleway'    => ['Raleway (fina)', "'Raleway', sans-serif", 'Raleway:wght@600;700;800'],
        'oswald'     => ['Oswald (alta)', "'Oswald', sans-serif", 'Oswald:wght@500;600;700'],
    ];

    /** Páginas da loja: [título, ícone, texto de exemplo]. */
    public const PAGES = [
        'sobre'   => ['Sobre nós', 'fa-store', "Conte quem você é, desde quando vende e o que tem de especial.\nEx.: Somos uma loja de Franca que trabalha com perfumes e maquiagem desde 2019."],
        'trocas'  => ['Trocas e devoluções', 'fa-rotate-left', "Ex.: Você pode trocar em até 7 dias depois de receber, com o produto lacrado.\nFale com a gente pelo WhatsApp para combinar."],
        'entrega' => ['Entrega e retirada', 'fa-truck', "Ex.: Entregamos em Franca de segunda a sábado. Pedidos até 14h saem no mesmo dia.\nTambém dá para retirar na loja."],
    ];

    /**
     * Páginas que a lei pede para quem vende pela internet. Têm texto pronto (com os dados
     * da loja) e aparecem sempre no rodapé; a loja pode trocar o texto em Vitrine › Site dentro da lei.
     */
    public const LEGAL_PAGES = [
        'privacidade' => ['Política de privacidade', 'fa-user-shield'],
        'termos'      => ['Termos de compra', 'fa-file-contract'],
    ];

    /** Formas de pagamento que o rodapé pode mostrar. */
    public const PAYMENTS = ['pix' => 'Pix', 'cartao' => 'Cartão', 'boleto' => 'Boleto', 'dinheiro' => 'Dinheiro'];

    public const FIELDS = ['name', 'tagline', 'logo', 'font', 'whatsapp', 'instagram', 'facebook', 'tiktok', 'email', 'phone', 'address', 'hours', 'payments', 'show_name', 'legal_name', 'document', 'privacy_bar'];

    private static array $memo = [];

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('portal_settings') && Schema::hasColumn('portal_settings', 'identity');
    }

    /** Dados da loja já prontos para mostrar (com padrões). */
    public static function for(?int $ownerId): array
    {
        if (! $ownerId) {
            return self::defaults(null);
        }
        if (isset(self::$memo[$ownerId])) {
            return self::$memo[$ownerId];
        }
        $s = self::ready() ? PortalSetting::where('user_id', $ownerId)->first() : null;
        $id = array_merge(self::defaults($ownerId), array_filter((array) ($s?->identity ?? []), fn ($v) => $v !== null && $v !== ''));
        $id['pages'] = array_filter((array) ($s?->pages ?? []), fn ($v) => trim((string) $v) !== '');
        $id['font_css'] = self::FONTS[$id['font']][1] ?? self::FONTS['padrao'][1];
        $id['font_url'] = ($g = self::FONTS[$id['font']][2] ?? null) ? 'https://fonts.googleapis.com/css2?family=' . $g . '&display=swap' : null;
        $id['logo_url'] = $id['logo'] ? asset('storage/' . $id['logo']) : null;
        $id['wa_link'] = self::waLink($id['whatsapp']);
        $id['ig_link'] = self::social($id['instagram'], 'https://instagram.com/');
        $id['fb_link'] = self::social($id['facebook'], 'https://facebook.com/');
        $id['tt_link'] = self::social($id['tiktok'], 'https://tiktok.com/@');
        $id['document_text'] = self::formatDocument($id['document']);

        return self::$memo[$ownerId] = $id;
    }

    public static function forget(int $ownerId): void
    {
        unset(self::$memo[$ownerId]);
    }

    private static function defaults(?int $ownerId): array
    {
        $user = $ownerId ? User::find($ownerId) : null;

        return [
            'name' => $user?->name ?: config('app.name'), 'tagline' => '', 'logo' => null, 'font' => 'padrao', 'show_name' => true,
            'whatsapp' => $user?->phone ?? '', 'instagram' => '', 'facebook' => '', 'tiktok' => '', 'email' => '', 'phone' => '',
            'address' => '', 'hours' => '', 'payments' => [], 'legal_name' => '', 'document' => '', 'privacy_bar' => true,
        ];
    }

    /** Todas as páginas da loja: [chave => [título, ícone]]. */
    public static function allPages(): array
    {
        return array_map(fn ($p) => [$p[0], $p[1]], self::PAGES) + self::LEGAL_PAGES;
    }

    /** Páginas que aparecem no rodapé: as escritas, mais Trocas e as da lei (com texto pronto). */
    public static function footerPages(array $id): array
    {
        return array_filter(self::allPages(), fn ($v, $k) => isset($id['pages'][$k]) || $k === 'trocas' || isset(self::LEGAL_PAGES[$k]), ARRAY_FILTER_USE_BOTH);
    }

    /** Texto de uma página: o que a loja escreveu ou, para Trocas e as páginas da lei, o texto pronto. */
    public static function pageText(array $id, string $page): ?string
    {
        $own = trim((string) ($id['pages'][$page] ?? ''));
        if ($own !== '') {
            return $own;
        }

        return ($page === 'trocas' || isset(self::LEGAL_PAGES[$page])) ? self::legalDefault($page, $id) : null;
    }

    /** CPF (11) ou CNPJ (14) com pontos; outro texto volta como veio. */
    public static function formatDocument(?string $doc): string
    {
        $d = preg_replace('/\D+/', '', (string) $doc);
        if (strlen($d) === 11) {
            return 'CPF ' . preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $d);
        }
        if (strlen($d) === 14) {
            return 'CNPJ ' . preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $d);
        }

        return trim((string) $doc);
    }

    /**
     * Textos prontos, escritos em linguagem simples a partir das regras do Código de Defesa do
     * Consumidor (art. 49, arrependimento em 7 dias), do Decreto 7.962/2013 (comércio eletrônico)
     * e da LGPD (Lei 13.709/2018). Usam os dados da Identidade da loja.
     */
    public static function legalDefault(string $page, array $id): string
    {
        $store = $id['legal_name'] ?: $id['name'];
        $doc = $id['document_text'] ?? self::formatDocument($id['document'] ?? '');
        $who = $store . ($doc ? ' (' . $doc . ')' : '');
        $contact = collect([
            $id['whatsapp'] ? 'WhatsApp ' . $id['whatsapp'] : null,
            $id['email'] ? 'e-mail ' . $id['email'] : null,
            $id['phone'] ? 'telefone ' . $id['phone'] : null,
        ])->filter()->implode(', ') ?: 'os contatos que aparecem no rodapé do site';
        $addr = $id['address'] ? "\nEndereço: {$id['address']}." : '';

        return match ($page) {
            'trocas' => "Direito de arrependimento: em compras feitas pelo site, você pode desistir em até 7 dias corridos a partir do dia em que recebeu o produto, sem precisar explicar o motivo (Código de Defesa do Consumidor, art. 49). Devolvemos o valor pago, inclusive o frete, pela mesma forma de pagamento.\n\n"
                . "Produto com defeito: avise em até 30 dias (produtos não duráveis) ou 90 dias (produtos duráveis) depois de receber. Trocamos o produto, devolvemos o dinheiro ou abatemos o valor, como a lei garante.\n\n"
                . "Troca por gosto ou tamanho (sem defeito, depois dos 7 dias): combine com a gente; o produto precisa estar sem uso e na embalagem.\n\n"
                . "Como pedir: fale com a gente por {$contact}, com o número do pedido. Confirmamos o recebimento do seu pedido de troca ou cancelamento na hora.",
            'privacidade' => "Quem cuida dos seus dados: {$who}.{$addr}\n\n"
                . "O que guardamos: nome, celular, e-mail, CPF e endereço que você informa ao criar a conta ou fazer um pedido; os produtos que você pediu, favoritou ou deixou no carrinho; e as avaliações que você escreve.\n\n"
                . "Para que usamos: para confirmar e entregar os seus pedidos, emitir cobrança e nota, falar com você sobre o pedido (inclusive pelo WhatsApp, por exemplo para lembrar de um carrinho que ficou aberto) e para avisar quando um produto favorito volta ao estoque. Não vendemos nem passamos seus dados para outras empresas, a não ser as que fazem o pagamento (Mercado Pago) e a entrega, só com o necessário.\n\n"
                . "Visitas ao site: contamos as visitas de forma anônima, sem cookies de propaganda e sem guardar o seu IP. O site guarda no seu aparelho o carrinho e os favoritos, para você não perder o que escolheu.\n\n"
                . "Por quanto tempo: enquanto você tiver conta e pelo tempo que a lei pede para os registros de venda (fiscais).\n\n"
                . "Seus direitos (LGPD, art. 18): ver os dados que temos, corrigir, pedir para apagar o que não precisamos guardar por lei e encerrar a sua conta. Você pode fazer isso em Meu perfil ou pedir por {$contact}.",
            'termos' => "Loja: {$who}.{$addr}\nAtendimento: {$contact}.\n\n"
                . "Preços e estoque: os preços do site valem para compras pelo site e podem mudar sem aviso; o valor que vale é o que aparece no resumo do seu pedido. Promoções valem enquanto durar o estoque ou até a data informada.\n\n"
                . "Como funciona o pedido: você escolhe os produtos, confere o resumo (produtos, quantidades, frete, descontos e total) e envia. A loja confirma o pedido e combina pagamento e entrega com você. Você recebe a confirmação no site e pode acompanhar tudo em Meus pedidos.\n\n"
                . "Pagamento: Pix, cartão ou boleto pelo Mercado Pago, quando ligado, ou como for combinado com a loja. O pedido só é separado depois do pagamento confirmado ou do combinado com a loja.\n\n"
                . "Entrega: o prazo e o valor do frete aparecem antes de você enviar o pedido. Você pode acompanhar a entrega em Meus pedidos.\n\n"
                . "Cancelamento e arrependimento: você pode desistir da compra em até 7 dias depois de receber (veja Trocas e devoluções).\n\n"
                . "Ao enviar um pedido você concorda com estes termos e com a Política de privacidade.",
            default => '',
        };
    }

    public static function waLink(?string $number): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $number);
        if (strlen($d) < 10) {
            return null;
        }

        return 'https://wa.me/' . (strlen($d) <= 11 ? '55' . $d : $d);
    }

    /** "@loja", "loja" ou o link inteiro viram o link da rede. */
    public static function social(?string $v, string $base): ?string
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        if (preg_match('~^https?://~i', $v)) {
            return $v;
        }

        return $base . ltrim($v, '@/');
    }
}
