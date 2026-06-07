<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Frase motivacional do dia.
 * APIs externas: ZenQuotes (https://zenquotes.io/api/today) e Quotable
 * (https://api.quotable.io/random). Com cache diário e fallback em PT-BR.
 */
class QuoteService
{
    /**
     * Retorna ['text' => ..., 'author' => ..., 'source' => 'api|local'].
     * Cacheado por dia (1 frase por dia para todos — estável e barato).
     */
    public function quoteOfTheDay(): array
    {
        $cacheKey = 'fm:quote-of-day:' . now()->toDateString();

        return Cache::remember($cacheKey, now()->endOfDay(), function () {
            // 1) Tenta ZenQuotes (frase do dia)
            try {
                $resp = Http::timeout(4)->retry(1, 200)->get('https://zenquotes.io/api/today');
                if ($resp->ok() && isset($resp->json()[0]['q'])) {
                    $q = $resp->json()[0];
                    return ['text' => trim($q['q']), 'author' => trim($q['a'] ?? ''), 'source' => 'api'];
                }
            } catch (\Throwable $e) {
                Log::info('QuoteService ZenQuotes falhou: ' . $e->getMessage());
            }

            // 2) Tenta Quotable (random com tags motivacionais)
            try {
                $resp = Http::timeout(4)->get('https://api.quotable.io/random', [
                    'tags' => 'inspirational|motivational|success',
                ]);
                if ($resp->ok() && $resp->json('content')) {
                    return [
                        'text'   => trim($resp->json('content')),
                        'author' => trim($resp->json('author') ?? ''),
                        'source' => 'api',
                    ];
                }
            } catch (\Throwable $e) {
                Log::info('QuoteService Quotable falhou: ' . $e->getMessage());
            }

            // 3) Fallback local em PT-BR (determinístico por dia)
            return $this->localQuoteForToday();
        });
    }

    protected function localQuoteForToday(): array
    {
        $quotes = [
            ['Disciplina é a ponte entre metas e conquistas.', 'Jim Rohn'],
            ['Não conte os dias, faça os dias contarem.', 'Muhammad Ali'],
            ['Um pequeno progresso a cada dia soma grandes resultados.', 'Anônimo'],
            ['A persistência realiza o impossível.', 'Provérbio'],
            ['Você não precisa ser grande para começar, mas precisa começar para ser grande.', 'Zig Ziglar'],
            ['Sucesso é a soma de pequenos esforços repetidos dia após dia.', 'Robert Collier'],
            ['O segredo de ir em frente é começar.', 'Mark Twain'],
            ['Cuide dos seus hábitos, eles moldam o seu futuro.', 'Anônimo'],
            ['Foco no progresso, não na perfeição.', 'Anônimo'],
            ['Grandes jornadas começam com um único passo.', 'Lao Tsé'],
            ['A motivação te faz começar; o hábito te mantém indo.', 'Jim Ryun'],
            ['Faça hoje o que seu eu de amanhã vai agradecer.', 'Anônimo'],
            ['Constância vence intensidade.', 'Anônimo'],
            ['Cada dia é uma nova chance de melhorar 1%.', 'Anônimo'],
        ];

        $index = (int) now()->dayOfYear % count($quotes);
        [$text, $author] = $quotes[$index];

        return ['text' => $text, 'author' => $author, 'source' => 'local'];
    }
}
