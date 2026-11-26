<?php

namespace App\Services\Portal;

/**
 * Tela de login do portal (entrar, criar conta, esqueci a senha) do jeito da loja:
 * Vitrine › Tela de login. Fica em portal_settings.identity['login'].
 */
class LoginLook
{
    public const MODES = [
        'escuro' => ['Escuro', 'Fundo preto com brilho nas cores'],
        'claro'  => ['Claro', 'Fundo branco, leve'],
        'cor'    => ['Na cor da loja', 'Fundo com as suas cores'],
    ];

    public const LAYOUTS = [
        'centro' => ['No centro', 'Logo em cima e o quadro no meio'],
        'lado'   => ['Dividida', 'No computador: logo de um lado, quadro do outro'],
    ];

    /** [raio dos campos/botões, raio do quadro] */
    public const CORNERS = [
        'reto'    => ['Retos', 4, 8],
        'suave'   => ['Suaves', 14, 24],
        'redondo' => ['Redondos', 999, 32],
    ];

    public const BUTTONS = [
        'degrade' => 'Degradê',
        'solido'  => 'Uma cor',
    ];

    public const LOGO_BOX = [
        'caixa' => ['Num quadrado branco', 'Bom para logo com fundo'],
        'solta' => ['Solta, sem quadrado', 'Bom para logo PNG sem fundo'],
    ];

    /** Cores prontas: [nome, cor 1, cor 2]. */
    public const PALETTES = [
        'flow'    => ['Rosa e azul', '#ec4899', '#3b82f6'],
        'roxo'    => ['Roxo', '#7c3aed', '#c026d3'],
        'madeira' => ['Madeira', '#92400e', '#d97706'],
        'grafite' => ['Grafite', '#111827', '#4b5563'],
        'verde'   => ['Verde', '#047857', '#10b981'],
        'azul'    => ['Azul', '#1d4ed8', '#0ea5e9'],
        'vinho'   => ['Vinho', '#881337', '#e11d48'],
        'dourado' => ['Dourado', '#a16207', '#eab308'],
    ];

    public const DEFAULTS = [
        'google' => true,
        'register' => true,
        'show_name' => true,
        'mode' => 'escuro',
        'layout' => 'centro',
        'corners' => 'suave',
        'button' => 'degrade',
        'logo_box' => 'caixa',
        'c1' => '#ec4899',
        'c2' => '#3b82f6',
        'heading' => '',
        'subtitle' => '',
    ];

    public static function for(?int $ownerId): array
    {
        $saved = $ownerId ? (array) (StoreIdentity::for($ownerId)['login'] ?? []) : [];

        return self::clean($saved);
    }

    /** Só valores conhecidos (o que vier estranho volta ao padrão). */
    public static function clean(array $in): array
    {
        $d = self::DEFAULTS;
        $out = [];
        foreach (['google', 'register', 'show_name'] as $k) {
            $out[$k] = array_key_exists($k, $in) ? (bool) $in[$k] : $d[$k];
        }
        $pick = fn ($k, $list) => isset($list[$in[$k] ?? null]) ? $in[$k] : $d[$k];
        $out['mode'] = $pick('mode', self::MODES);
        $out['layout'] = $pick('layout', self::LAYOUTS);
        $out['corners'] = $pick('corners', self::CORNERS);
        $out['button'] = $pick('button', self::BUTTONS);
        $out['logo_box'] = $pick('logo_box', self::LOGO_BOX);
        foreach (['c1', 'c2'] as $k) {
            $v = strtolower(trim((string) ($in[$k] ?? '')));
            $out[$k] = preg_match('/^#[0-9a-f]{6}$/', $v) ? $v : $d[$k];
        }
        $out['heading'] = mb_substr(trim((string) ($in['heading'] ?? '')), 0, 60);
        $out['subtitle'] = mb_substr(trim((string) ($in['subtitle'] ?? '')), 0, 140);

        return $out;
    }

    /** "#rrggbb" + transparência → "rgba(r, g, b, a)". */
    public static function rgba(string $hex, float $a): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return "rgba($r, $g, $b, $a)";
    }

    /** Variáveis CSS da tela (cores do fundo, do quadro, dos campos e dos botões). */
    public static function css(array $l): array
    {
        [, $r, $rc] = self::CORNERS[$l['corners']];
        $grad = $l['button'] === 'solido' ? $l['c1'] : 'linear-gradient(90deg, ' . $l['c1'] . ' 0%, ' . $l['c2'] . ' 100%)';
        $base = [
            '--pa-c1' => $l['c1'], '--pa-c2' => $l['c2'], '--pa-grad' => $grad,
            '--pa-r' => $r . 'px', '--pa-rc' => $rc . 'px', '--pa-r-btn' => ($r === 999 ? 999 : $r) . 'px',
            '--pa-ring' => self::rgba($l['c1'], .18), '--pa-shadow' => self::rgba($l['c1'], .32),
        ];
        $mode = match ($l['mode']) {
            'claro' => [
                '--pa-bg' => '#f6f7fb', '--pa-card' => 'rgba(255,255,255,.96)', '--pa-line' => 'rgba(15,23,42,.10)', '--pa-input' => '#ffffff',
                '--pa-text' => '#0f172a', '--pa-muted' => '#64748b', '--pa-soft' => $l['c1'], '--pa-glow' => '.16', '--pa-icon' => '#94a3b8', '--pa-check' => '#334155', '--pa-copy' => '#94a3b8',
            ],
            'cor' => [
                '--pa-bg' => 'linear-gradient(135deg, ' . $l['c1'] . ' 0%, ' . $l['c2'] . ' 100%)', '--pa-card' => 'rgba(255,255,255,.97)', '--pa-line' => 'rgba(15,23,42,.10)', '--pa-input' => '#ffffff',
                '--pa-text' => '#0f172a', '--pa-muted' => '#64748b', '--pa-soft' => $l['c1'], '--pa-glow' => '0', '--pa-icon' => '#94a3b8', '--pa-check' => '#334155', '--pa-copy' => 'rgba(255,255,255,.85)',
            ],
            default => [
                '--pa-bg' => '#0b0b12', '--pa-card' => 'rgba(20,20,28,.86)', '--pa-line' => 'rgba(255,255,255,.09)', '--pa-input' => 'rgba(255,255,255,.045)',
                '--pa-text' => '#f8fafc', '--pa-muted' => '#94a3b8', '--pa-soft' => '#e2e8f0', '--pa-glow' => '.28', '--pa-icon' => '#64748b', '--pa-check' => '#cbd5e1', '--pa-copy' => '#475569',
            ],
        };

        return $base + $mode;
    }
}
