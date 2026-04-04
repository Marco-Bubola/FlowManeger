<?php

namespace App\Services;

use App\Services\AI\GeminiClient;
use Illuminate\Support\Facades\Log;

class GeminiPdfExtractorService
{
    private GeminiClient $client;
    private string $model;

    public function __construct()
    {
        $this->client = app(GeminiClient::class);
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
    }

    public function extractProductsFromPdf($text)
    {
        try {
            Log::info('Iniciando extração com Gemini AI...');

            $cleanedText = trim($text);

            Log::info('Texto recebido e preparado para Gemini: ' . strlen($cleanedText) . ' caracteres');

            if (empty($cleanedText)) {
                Log::warning('Texto para extração com Gemini está vazio. Abortando.');
                return [];
            }

            $prompt = $this->buildPrompt($cleanedText);

            $responseText = $this->client->generateText($prompt, [
                'feature' => 'products.pdf_extraction',
                'model' => $this->model,
                'temperature' => 0.1,
                'max_output_tokens' => 8192,
            ]);

            if ($responseText !== null) {
                Log::info('Gemini respondeu com sucesso', ['response_length' => strlen($responseText)]);

                return $this->parseGeminiResponse($responseText);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Erro no GeminiPdfExtractorService: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    private function buildPrompt($filteredPdfText)
    {
        return <<<PROMPT
Extraia todos os produtos do texto. Retorne APENAS um JSON com a estrutura:
{"products":[{"product_code":"...","stock_quantity":X,"name":"...","operation":"..."}]}

REGRAS:
- Extraia apenas código, quantidade, nome e operação. NADA MAIS.
- Seja conciso.
- O JSON deve ser completo e válido.
- Não inclua texto fora do JSON.

TEXTO:
{$filteredPdfText}
PROMPT;
    }

    private function parseGeminiResponse($text)
    {
        if (preg_match('/\{[\s\S]*"products"[\s\S]*\}/m', $text, $matches)) {
            $jsonText = $matches[0];
        } else {
            $jsonText = preg_replace('/```json\s*/i', '', $text);
            $jsonText = preg_replace('/```\s*/', '', $jsonText);
            $jsonText = trim($jsonText);
        }

        $data = json_decode($jsonText, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['products'])) {
            $count = count($data['products']);
            Log::info("✅ Gemini extraiu {$count} produtos com sucesso!");
            return $this->formatProducts($data['products']);
        }

        Log::warning('Falha ao parsear resposta do Gemini', [
            'json_error' => json_last_error_msg(),
            'text_preview' => substr($text, 0, 200),
            'json_extracted' => substr($jsonText ?? '', 0, 200)
        ]);
        return [];
    }

    private function formatProducts($products)
    {
        $formatted = [];

        foreach ($products as $p) {
            if (empty($p['product_code']) || empty($p['name'])) {
                continue;
            }

            $formatted[] = [
                'product_code' => $p['product_code'] ?? '',
                'name' => $p['name'] ?? '',
                'stock_quantity' => (int)($p['stock_quantity'] ?? 1),
                'operation' => $p['operation'] ?? 'Venda',
                'price_resell' => 0,
                'price_to_pay' => 0,
                'price_sale' => 0,
                'price' => 0,
                'profit' => 0,
            ];
        }

        return $formatted;
    }

    public function isConfigured()
    {
        return $this->client->isConfigured();
    }
}
