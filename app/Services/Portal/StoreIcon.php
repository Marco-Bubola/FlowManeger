<?php

namespace App\Services\Portal;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;

/**
 * Ícone da loja (favicon, ícone do iPhone e do Android ao "Adicionar à tela de início")
 * feito a partir da logo de Vitrine › Informações da loja. Sem logo, fica o ícone do FlowManager.
 * Vem por rota (o deploy não envia public/) e a imagem pronta fica guardada no disco public.
 */
class StoreIcon
{
    public const SIZES = [16, 32, 180, 192, 512];

    /** Endereço do ícone ou null quando a loja ainda não pôs logo. */
    public static function url(?int $ownerId, int $size): ?string
    {
        $logo = $ownerId ? (StoreIdentity::for($ownerId)['logo'] ?? null) : null;
        if (! $logo) {
            return null;
        }

        return route('portal.icon', ['userId' => $ownerId, 'size' => $size, 'v' => substr(md5($logo), 0, 8)]);
    }

    /** PNG quadrado da logo no tamanho pedido (centralizada; fundo branco nos ícones grandes). */
    public static function png(int $ownerId, int $size): ?string
    {
        $logo = StoreIdentity::for($ownerId)['logo'] ?? null;
        $disk = Storage::disk('public');
        if (! $logo || ! $disk->exists($logo) || ! in_array($size, self::SIZES, true)) {
            return null;
        }
        $cache = 'portal-icons/' . $ownerId . '-' . substr(md5($logo), 0, 8) . '-' . $size . '.png';
        if ($disk->exists($cache)) {
            return $disk->get($cache);
        }
        if (! function_exists('imagecreatefromstring')) {
            return $disk->get($logo);
        }
        $src = @imagecreatefromstring($disk->get($logo));
        if (! $src) {
            return null;
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $out = imagecreatetruecolor($size, $size);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        // Ícone da tela de início não pode ser transparente (o iPhone pinta de preto); o favicon pode.
        $solid = $size >= 180;
        imagefill($out, 0, 0, $solid ? imagecolorallocatealpha($out, 255, 255, 255, 0) : imagecolorallocatealpha($out, 0, 0, 0, 127));
        imagealphablending($out, true);
        $pad = $solid ? 0.14 : 0.0;
        $box = $size * (1 - 2 * $pad);
        $scale = min($box / $w, $box / $h);
        $dw = max(1, (int) round($w * $scale));
        $dh = max(1, (int) round($h * $scale));
        imagecopyresampled($out, $src, (int) (($size - $dw) / 2), (int) (($size - $dh) / 2), 0, 0, $dw, $dh, $w, $h);
        ob_start();
        imagepng($out, null, 9);
        $png = (string) ob_get_clean();
        imagedestroy($src);
        imagedestroy($out);
        $disk->put($cache, $png);

        return $png;
    }

    /** Manifesto do catálogo da loja: nome, cor e ícone da loja na tela de início. */
    public static function manifest(int $ownerId): array
    {
        $id = StoreIdentity::for($ownerId);
        $name = (string) ($id['name'] ?? config('app.name'));
        $icons = [];
        foreach ([192, 512, 180] as $s) {
            $icons[] = ['src' => self::url($ownerId, $s) ?? Vite::asset('resources/images/icons/' . ($s === 180 ? 'apple-touch-icon-180.png' : 'pwa-' . $s . '.png')),
                'sizes' => $s . 'x' . $s, 'type' => 'image/png', 'purpose' => 'any'];
        }
        $start = route('portal.catalog', ['userId' => $ownerId], false);

        return [
            'name' => $name,
            'short_name' => mb_strimwidth($name, 0, 14, ''),
            'id' => $start,
            'start_url' => $start,
            'scope' => '/portal/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#ffffff',
            'lang' => 'pt-BR',
            'icons' => $icons,
        ];
    }
}
