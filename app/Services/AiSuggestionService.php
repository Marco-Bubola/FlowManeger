<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sugestões inteligentes via Claude API (Anthropic).
 * - Sugerir hábitos para atingir uma meta.
 * - Quebrar uma meta em milestones.
 * Requer ANTHROPIC_API_KEY no .env. Sem a chave, retorna sugestões locais.
 */
class AiSuggestionService
{
    protected ?string $key;
    protected string $model;

    public function __construct()
    {
        $this->key   = config('services.anthropic.key');
        $this->model = config('services.anthropic.model', 'claude-3-5-haiku-latest');
    }

    public function isConfigured(): bool
    {
        return !empty($this->key);
    }

    /**
     * Sugere hábitos diários para ajudar a alcançar a meta.
     * Retorna array de ['name','icon','description'].
     */
    public function suggestHabitsForGoal(string $goalTitle, ?string $goalDescription = null): array
    {
        $prompt = "Você é um coach de produtividade. A meta do usuário é: \"{$goalTitle}\"."
            . ($goalDescription ? " Detalhes: \"{$goalDescription}\"." : '')
            . " Sugira de 3 a 5 hábitos diários objetivos que ajudem a alcançar essa meta. "
            . "Responda APENAS com um JSON array, cada item com as chaves: "
            . "name (curto, em português), icon (um nome de Bootstrap Icon como 'bi-book', 'bi-droplet', 'bi-heart-pulse'), "
            . "description (uma frase curta). Sem texto fora do JSON.";

        $json = $this->ask($prompt);
        $parsed = $this->extractJsonArray($json);

        if (!empty($parsed)) {
            return array_map(fn ($h) => [
                'name'        => (string) ($h['name'] ?? 'Hábito'),
                'icon'        => (string) ($h['icon'] ?? 'bi-check2-circle'),
                'description' => (string) ($h['description'] ?? ''),
            ], $parsed);
        }

        return $this->localHabitFallback();
    }

    /**
     * Quebra uma meta em milestones (etapas). Retorna ['titulo','data_alvo'(opcional)].
     */
    public function suggestMilestonesForGoal(string $goalTitle, ?string $goalDescription = null): array
    {
        $prompt = "Quebre a meta \"{$goalTitle}\""
            . ($goalDescription ? " ({$goalDescription})" : '')
            . " em 3 a 6 etapas (milestones) sequenciais e mensuráveis. "
            . "Responda APENAS com um JSON array, cada item com a chave 'titulo' (curto, em português). Sem texto fora do JSON.";

        $json = $this->ask($prompt);
        $parsed = $this->extractJsonArray($json);

        if (!empty($parsed)) {
            return array_map(fn ($m, $i) => [
                'titulo' => (string) ($m['titulo'] ?? ('Etapa ' . ($i + 1))),
                'order'  => $i,
            ], $parsed, array_keys($parsed));
        }

        return [
            ['titulo' => 'Definir o objetivo e o prazo', 'order' => 0],
            ['titulo' => 'Planejar os primeiros passos', 'order' => 1],
            ['titulo' => 'Executar e acompanhar o progresso', 'order' => 2],
            ['titulo' => 'Revisar e concluir', 'order' => 3],
        ];
    }

    /**
     * Chamada genérica ao Claude. Retorna o texto da resposta ou null.
     */
    protected function ask(string $prompt): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $resp = Http::withHeaders([
                'x-api-key'         => $this->key,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
                'model'      => $this->model,
                'max_tokens' => 1024,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if ($resp->ok()) {
                return $resp->json('content.0.text');
            }

            Log::warning('AiSuggestionService Anthropic erro: ' . $resp->status() . ' ' . $resp->body());
        } catch (\Throwable $e) {
            Log::warning('AiSuggestionService exceção: ' . $e->getMessage());
        }

        return null;
    }

    protected function extractJsonArray(?string $text): array
    {
        if (!$text) return [];

        // tenta achar o primeiro array JSON no texto
        if (preg_match('/\[[\s\S]*\]/', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }

        $decoded = json_decode($text, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function localHabitFallback(): array
    {
        return [
            ['name' => 'Planejar o dia', 'icon' => 'bi-calendar-check', 'description' => 'Defina 3 prioridades pela manhã.'],
            ['name' => 'Revisar progresso', 'icon' => 'bi-graph-up-arrow', 'description' => 'Acompanhe o avanço ao fim do dia.'],
            ['name' => 'Foco sem distrações', 'icon' => 'bi-bullseye', 'description' => '25 min de foco profundo (Pomodoro).'],
        ];
    }
}
