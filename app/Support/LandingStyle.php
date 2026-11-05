<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Estilo da página inicial do próprio FlowManager (Admin › Planos › Página inicial do FlowManager).
 * "Atual" e "Sem movimento" deixam a página como sempre foi. Fica na tabela app_settings (chave landing_style).
 */
class LandingStyle
{
    public const MODELS = [
        'atual' => ['Atual', 'A página escura de sempre'],
        'editorial' => ['Editorial', 'Fundo creme, títulos grandes em maiúsculas, faixa correndo em verde-limão'],
        'elegante' => ['Elegante', 'Tons rosados, letra fina com itálico e cards de vidro'],
    ];

    public const MOTIONS = [
        'nenhum' => ['Sem movimento', 'Como sempre foi'],
        'suave' => ['Suave', 'As seções e os cards surgem ao rolar'],
        'completo' => ['Completo', 'Também título palavra por palavra, cards inclinando com o mouse e depoimentos em carrossel de vidro 3D'],
    ];

    public const DEFAULT = ['model' => 'atual', 'motion' => 'nenhum'];

    private static ?array $memo = null;

    public static function get(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }
        $raw = null;
        try {
            if (Schema::hasTable('app_settings')) {
                $raw = DB::table('app_settings')->where('key', 'landing_style')->value('value');
            }
        } catch (\Throwable $e) {
            $raw = null;
        }

        return self::$memo = self::clean(json_decode((string) $raw, true) ?: []);
    }

    public static function clean(array $v): array
    {
        return [
            'model' => isset(self::MODELS[$v['model'] ?? '']) ? $v['model'] : 'atual',
            'motion' => isset(self::MOTIONS[$v['motion'] ?? '']) ? $v['motion'] : 'nenhum',
        ];
    }

    public static function set(array $v): array
    {
        $v = self::clean($v);
        DB::table('app_settings')->updateOrInsert(['key' => 'landing_style'], ['value' => json_encode($v), 'updated_at' => now(), 'created_at' => now()]);
        self::$memo = $v;

        return $v;
    }

    /** Prévia pelo admin: ?estilo=editorial&movimento=completo na página inicial, sem salvar. */
    public static function forRequest(): array
    {
        $v = self::get();
        $u = auth()->user();
        if ($u && ($u->is_admin ?? false) && (request()->has('estilo') || request()->has('movimento'))) {
            $v = self::clean(['model' => request()->query('estilo', $v['model']), 'motion' => request()->query('movimento', $v['motion'])]);
        }

        return $v;
    }
}
