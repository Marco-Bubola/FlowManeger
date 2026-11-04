<?php

namespace App\Services\Instagram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Prepara as fotos para o Instagram: JPEG no tamanho certo (quadrado 1080x1080,
 * retrato 1080x1350 ou story 1080x1920), guardado no disco public para a Meta baixar.
 */
class InstagramImages
{
    public const SIZES = [
        'quadrado' => [1080, 1080],
        'retrato' => [1080, 1350],
        'story' => [1080, 1920],
    ];

    /**
     * @param string $source caminho de arquivo, ou link (do próprio site ou de fora, como as fotos do ML)
     * @param string $fit 'inteira' (foto toda, com fundo) ou 'preencher' (corta as bordas)
     * @return string caminho no disco public
     */
    public static function prepare(int $userId, string $source, string $size = 'quadrado', string $fit = 'inteira', string $bg = '#ffffff'): string
    {
        $data = self::read($source);
        $img = $data !== null && function_exists('imagecreatefromstring') ? @imagecreatefromstring($data) : false;
        if (! $img) {
            throw new \RuntimeException('Não deu para abrir uma das fotos. Escolha outra.');
        }

        [$W, $H] = self::SIZES[$size] ?? self::SIZES['quadrado'];
        $w = imagesx($img);
        $h = imagesy($img);
        $canvas = imagecreatetruecolor($W, $H);
        [$r, $g, $b] = sscanf(ltrim(preg_match('/^#[0-9a-f]{6}$/i', $bg) ? $bg : '#ffffff', '#'), '%02x%02x%02x');
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, $r, $g, $b));

        $scale = $fit === 'preencher' ? max($W / $w, $H / $h) : min($W / $w, $H / $h);
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);
        imagecopyresampled($canvas, $img, (int) (($W - $nw) / 2), (int) (($H - $nh) / 2), 0, 0, $nw, $nh, $w, $h);

        $path = 'instagram/' . $userId . '/' . now()->format('Ym') . '/' . Str::random(24) . '.jpg';
        ob_start();
        imagejpeg($canvas, null, 90);
        Storage::disk('public')->put($path, ob_get_clean());
        imagedestroy($img);
        imagedestroy($canvas);

        return $path;
    }

    private static function read(string $source): ?string
    {
        if (is_file($source)) {
            return @file_get_contents($source) ?: null;
        }

        // Foto do próprio site: lê do disco em vez de baixar
        $path = parse_url($source, PHP_URL_PATH) ?: '';
        if (str_starts_with($path, '/storage/')) {
            $local = storage_path('app/public/' . substr(rawurldecode($path), 9));
            if (is_file($local)) {
                return @file_get_contents($local) ?: null;
            }
        }
        if (str_starts_with($source, 'http')) {
            try {
                $r = Http::timeout(20)->get($source);

                return $r->successful() ? $r->body() : null;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
