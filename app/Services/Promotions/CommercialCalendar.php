<?php

namespace App\Services\Promotions;

use App\Models\Promotion;
use Carbon\Carbon;

/**
 * Datas comemorativas do comércio no Brasil, com o período sugerido para a
 * promoção (e para o banner). Sempre a próxima ocorrência: se o período deste
 * ano já passou, vem a do ano que vem.
 */
class CommercialCalendar
{
    /** chave => [nome, emoji] */
    public const DATES = [
        'ano_novo'    => ['Ano Novo', '🥂'],
        'volta_aulas' => ['Volta às aulas', '📚'],
        'carnaval'    => ['Carnaval', '🎭'],
        'mulher'      => ['Dia da Mulher', '🌷'],
        'consumidor'  => ['Dia do Consumidor', '🛍️'],
        'pascoa'      => ['Páscoa', '🐣'],
        'maes'        => ['Dia das Mães', '💐'],
        'namorados'   => ['Dia dos Namorados', '💘'],
        'junina'      => ['Festa Junina', '🌽'],
        'pais'        => ['Dia dos Pais', '👔'],
        'cliente'     => ['Dia do Cliente', '🤝'],
        'criancas'    => ['Dia das Crianças', '🎈'],
        'black'       => ['Black Friday', '🖤'],
        'natal'       => ['Natal', '🎄'],
    ];

    /**
     * Todas as datas na ordem em que começam, a partir de hoje.
     * Cada uma: key, name, emoji, day (o dia da data), start, end (período da promoção), now (já está no período).
     */
    public static function upcoming(?Carbon $today = null): array
    {
        $today = ($today ?? now(Promotion::tz()))->copy()->startOfDay();
        $out = [];
        foreach (array_keys(self::DATES) as $key) {
            $out[] = self::next($key, $today);
        }
        usort($out, fn ($a, $b) => $a['start'] <=> $b['start']);

        return $out;
    }

    /** Próxima ocorrência de uma data (null se a chave não existe). */
    public static function next(string $key, ?Carbon $today = null): ?array
    {
        if (! isset(self::DATES[$key])) {
            return null;
        }
        $today = ($today ?? now(Promotion::tz()))->copy()->startOfDay();
        foreach ([$today->year, $today->year + 1] as $year) {
            [$day, $start, $end] = self::window($key, $year);
            if ($end->gte($today)) {
                break;
            }
        }
        [$name, $emoji] = self::DATES[$key];

        return ['key' => $key, 'name' => $name, 'emoji' => $emoji, 'day' => $day, 'start' => $start, 'end' => $end, 'now' => $start->lte($today)];
    }

    /** [dia da data, começo, fim] do período sugerido no ano. */
    private static function window(string $key, int $y): array
    {
        $tz = Promotion::tz();
        $d = fn (int $m, int $day) => Carbon::create($y, $m, $day, 0, 0, 0, $tz);
        $easter = self::easter($y);

        return match ($key) {
            'ano_novo'    => [$d(12, 31), $d(12, 26), $d(12, 31)],
            'volta_aulas' => [$d(2, 1), $d(1, 15), $d(2, 10)],
            'carnaval'    => [$c = $easter->copy()->subDays(47), $c->copy()->subDays(10), $c->copy()],
            'mulher'      => [$d(3, 8), $d(3, 1), $d(3, 8)],
            'consumidor'  => [$d(3, 15), $d(3, 10), $d(3, 16)],
            'pascoa'      => [$easter, $easter->copy()->subDays(14), $easter->copy()],
            'maes'        => [$m = self::nthSunday($y, 5, 2), $m->copy()->subDays(12), $m->copy()],
            'namorados'   => [$d(6, 12), $d(6, 1), $d(6, 12)],
            'junina'      => [$d(6, 24), $d(6, 13), $d(6, 30)],
            'pais'        => [$p = self::nthSunday($y, 8, 2), $p->copy()->subDays(12), $p->copy()],
            'cliente'     => [$d(9, 15), $d(9, 10), $d(9, 16)],
            'criancas'    => [$d(10, 12), $d(10, 1), $d(10, 12)],
            // Sexta depois da 4ª quinta de novembro; a semana toda até a Cyber Monday.
            'black'       => [$bf = $d(11, 1)->nthOfMonth(4, Carbon::THURSDAY)->addDay(), $bf->copy()->subDays(4), $bf->copy()->addDays(3)],
            default       => [$d(12, 25), $d(12, 1), $d(12, 24)], // natal
        };
    }

    private static function nthSunday(int $y, int $month, int $n): Carbon
    {
        return Carbon::create($y, $month, 1, 0, 0, 0, Promotion::tz())->nthOfMonth($n, Carbon::SUNDAY);
    }

    /** Domingo de Páscoa (calendário gregoriano). */
    private static function easter(int $y): Carbon
    {
        $a = $y % 19;
        $b = intdiv($y, 100);
        $c = $y % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($y, $month, $day, 0, 0, 0, Promotion::tz());
    }

    /** "01/12 a 24/12" */
    public static function label(array $date): string
    {
        return $date['start']->format('d/m') . ' a ' . $date['end']->format('d/m');
    }
}
