<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    private ?string $apiKey;
    private string $defaultModel;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->defaultModel = config('services.gemini.model', 'gemini-2.5-flash');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    public function generateText(string $prompt, array $options = []): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('GeminiClient chamado sem GEMINI_API_KEY configurada', [
                'feature' => $options['feature'] ?? null,
            ]);

            return null;
        }

        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.2;
        $maxOutputTokens = $options['max_output_tokens'] ?? 8192;
        $timeout = $options['timeout'] ?? 120;
        $connectTimeout = $options['connect_timeout'] ?? 30;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxOutputTokens,
            ],
        ];

        if (!empty($options['response_mime_type'])) {
            $payload['generationConfig']['responseMimeType'] = $options['response_mime_type'];
        }

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}", $payload);

            if (!$response->successful()) {
                Log::error('Erro ao chamar Gemini API', [
                    'feature' => $options['feature'] ?? null,
                    'model' => $model,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $result = $response->json();
            $text = data_get($result, 'candidates.0.content.parts.0.text');

            if (!is_string($text) || $text === '') {
                Log::warning('Gemini retornou resposta vazia', [
                    'feature' => $options['feature'] ?? null,
                    'model' => $model,
                    'response' => $result,
                ]);

                return null;
            }

            return $text;
        } catch (\Throwable $e) {
            Log::error('Falha inesperada ao chamar Gemini', [
                'feature' => $options['feature'] ?? null,
                'model' => $model,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}