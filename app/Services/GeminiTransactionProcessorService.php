<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiTransactionProcessorService
{
    private $apiKey;
    private $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash-exp');
    }

    /**
     * Processa transações usando Gemini AI para extrair parcelas e categorizar
     *
     * @param array $transactions Array de transações com date, description, value
     * @param array $availableCategories Lista de categorias disponíveis
     * @return array Transações processadas com installments e category_id
     */
    public function processTransactions(array $transactions, array $availableCategories)
    {
        try {
            if (empty($transactions)) {
                Log::warning('Nenhuma transação para processar com Gemini');
                return [];
            }

            Log::info('Iniciando processamento de transações com Gemini AI', [
                'total_transactions' => count($transactions),
                'total_categories' => count($availableCategories)
            ]);

            $prompt = $this->buildPrompt($transactions, $availableCategories);

            $response = Http::timeout(120)
                ->connectTimeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 8192,
                    ]
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                Log::info('Gemini respondeu com sucesso', ['response_length' => strlen($text)]);

                return $this->parseGeminiResponse($text, $transactions);
            }

            Log::error('Erro ao chamar Gemini API', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Fallback: retornar transações sem processamento
            return $this->fallbackProcessing($transactions);

        } catch (\Exception $e) {
            Log::error('Erro no GeminiTransactionProcessorService: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback: retornar transações sem processamento
            return $this->fallbackProcessing($transactions);
        }
    }

    private function buildPrompt(array $transactions, array $availableCategories)
    {
        $categoriesText = implode("\n", array_map(function($cat) {
            return "- ID: {$cat['id']}, Nome: {$cat['name']}";
        }, $availableCategories));

        $transactionsText = '';
        foreach ($transactions as $index => $transaction) {
            $transactionsText .= "Transação {$index}: \"{$transaction['description']}\" - R$ {$transaction['value']}\n";
        }

        return <<<PROMPT
Você é um assistente especializado em processar transações financeiras de cartão de crédito.

**CATEGORIAS DISPONÍVEIS:**
{$categoriesText}

**TRANSAÇÕES PARA PROCESSAR:**
{$transactionsText}

**SUA TAREFA:**
Para cada transação, você deve:
1. **Extrair informações de parcelas** da descrição (ex: "Parc 2/12", "3 de 6", "05/12", etc.)
2. **Limpar a descrição** removendo informações de parcela
3. **Sugerir a categoria mais apropriada** baseada na descrição

**REGRAS IMPORTANTES:**
- Parcelas podem estar em formatos: "Parc X/Y", "X de Y", "X/Y", "parcela X de Y"
- Se não houver parcela, retorne "Compra à vista"
- Se houver parcela, retorne no formato "X de Y" (ex: "2 de 12")
- A descrição limpa não deve conter informações de parcela
- Escolha a categoria que melhor se encaixa com base no nome do estabelecimento/descrição
- Se não tiver certeza da categoria, use a categoria com ID mais genérico

**FORMATO DE RESPOSTA (JSON):**
Retorne APENAS um JSON válido, sem texto adicional, no formato:
```json
{
  "transactions": [
    {
      "index": 0,
      "installments": "2 de 12",
      "clean_description": "Mp *Melimais",
      "category_id": 5,
      "reasoning": "breve explicação da escolha"
    }
  ]
}
```

IMPORTANTE: Retorne APENAS o JSON, sem markdown, sem explicações fora do JSON.
PROMPT;
    }

    private function parseGeminiResponse(string $text, array $originalTransactions)
    {
        // Remover markdown code blocks se existirem
        $jsonText = preg_replace('/```json\s*/i', '', $text);
        $jsonText = preg_replace('/```\s*/', '', $jsonText);
        $jsonText = trim($jsonText);

        // Tentar extrair JSON do texto
        if (preg_match('/\{[\s\S]*"transactions"[\s\S]*\}/m', $jsonText, $matches)) {
            $jsonText = $matches[0];
        }

        Log::debug('JSON extraído do Gemini', ['json' => $jsonText]);

        try {
            $data = json_decode($jsonText, true);

            if (!isset($data['transactions']) || !is_array($data['transactions'])) {
                Log::warning('Resposta do Gemini não contém array de transactions');
                return $this->fallbackProcessing($originalTransactions);
            }

            $processedTransactions = [];

            foreach ($data['transactions'] as $processed) {
                $index = $processed['index'] ?? null;

                if ($index === null || !isset($originalTransactions[$index])) {
                    Log::warning('Transação com index inválido', ['processed' => $processed]);
                    continue;
                }

                $original = $originalTransactions[$index];

                $processedTransactions[] = [
                    'date' => $original['date'],
                    'description' => $processed['clean_description'] ?? $original['description'],
                    'installments' => $processed['installments'] ?? 'Compra à vista',
                    'value' => $original['value'],
                    'category_id' => $processed['category_id'] ?? null,
                    'client_id' => null,
                    'gemini_reasoning' => $processed['reasoning'] ?? null,
                ];
            }

            Log::info('Transações processadas com sucesso pelo Gemini', [
                'total_processed' => count($processedTransactions)
            ]);

            return $processedTransactions;

        } catch (\Exception $e) {
            Log::error('Erro ao parsear resposta do Gemini', [
                'error' => $e->getMessage(),
                'json_text' => $jsonText
            ]);

            return $this->fallbackProcessing($originalTransactions);
        }
    }

    /**
     * Processamento fallback sem Gemini
     */
    private function fallbackProcessing(array $transactions)
    {
        Log::info('Usando processamento fallback (sem Gemini)');

        return array_map(function($transaction) {
            // Tentar extrair parcelas da descrição usando regex
            $description = $transaction['description'];
            $installments = 'Compra à vista';

            // Padrões de parcela: "Parc 2/12", "2 de 12", "2/12", etc.
            $patterns = [
                '/Parc(?:ela)?\s*(\d+)\s*\/\s*(\d+)/i',
                '/(\d+)\s+de\s+(\d+)/i',
                '/(\d+)\s*\/\s*(\d+)/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $description, $matches)) {
                    $installments = "{$matches[1]} de {$matches[2]}";
                    // Remover informação de parcela da descrição
                    $description = trim(preg_replace($pattern, '', $description));
                    break;
                }
            }

            return [
                'date' => $transaction['date'],
                'description' => $description,
                'installments' => $installments,
                'value' => $transaction['value'],
                'category_id' => null, // Será determinado depois
                'client_id' => null,
            ];
        }, $transactions);
    }
}
