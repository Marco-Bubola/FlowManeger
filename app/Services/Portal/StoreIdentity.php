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

    /** Formas de pagamento que o rodapé pode mostrar. */
    public const PAYMENTS = ['pix' => 'Pix', 'cartao' => 'Cartão', 'boleto' => 'Boleto', 'dinheiro' => 'Dinheiro'];

    public const FIELDS = ['name', 'tagline', 'logo', 'font', 'whatsapp', 'instagram', 'facebook', 'tiktok', 'email', 'phone', 'address', 'hours', 'payments', 'show_name'];

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
            'address' => '', 'hours' => '', 'payments' => [],
        ];
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
