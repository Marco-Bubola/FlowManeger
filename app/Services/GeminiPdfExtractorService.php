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

                $products = $this->parseGeminiResponse($responseText);
                return $this->reconcileOperationsFromText($products, $cleanedText);
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
Você é um extrator de produtos de "Extrato de Pedido" do Boticário/Eudora. Retorne APENAS um JSON:
{"products":[{"product_code":"...","stock_quantity":X,"name":"...","operation":"..."}]}

CAMPO "operation" — valores permitidos:
- "Venda"               → linha de valores termina com a palavra "Venda".
- "Brinde"              → linha contém/termina com "Brinde".
- "Doação FIDELIDADEVD" → linha de valores termina com "Doação" E em qualquer linha próxima (geralmente a IMEDIATAMENTE seguinte) aparece "FIDELIDADEVD". Esta é a regra MAIS IMPORTANTE — releia abaixo.
- "Doação"              → linha termina com "Doação" SEM "FIDELIDADEVD" próximo.
- "Bonificação"         → linha contém "Bonificação".
- "Troca"               → linha contém "Troca".

⚠️ REGRA CRÍTICA — "Doação FIDELIDADEVD":
No PDF, "Doação" e "FIDELIDADEVD" ficam em LINHAS SEPARADAS. O padrão exato é:

CODIGO QTD NOME DO PRODUTO
(continuação opcional do nome)
VALOR1 VALOR2 VALOR3 VALOR4 VALOR5 Doação
FIDELIDADEVD

EXEMPLOS REAIS deste pedido (TODOS são "Doação FIDELIDADEVD", NUNCA "Venda"):

Exemplo 1:
  51.124 2 CBEM SAB BARRA BOA
  NOITE V2 2x80g
  47,80 0,00 47,80 0,00 47,80 Doação
  FIDELIDADEVD
  → {"product_code":"51.124","stock_quantity":2,"name":"CBEM SAB BARRA BOA NOITE V2 2x80g","operation":"Doação FIDELIDADEVD"}

Exemplo 2:
  51.166 1 CBEM SAB BARRA
  ROSA/ALGODAO V8
  2x80g
  23,90 0,00 23,90 0,00 23,90 Doação
  FIDELIDADEVD
  → {"product_code":"51.166","stock_quantity":1,"name":"CBEM SAB BARRA ROSA/ALGODAO V8 2x80g","operation":"Doação FIDELIDADEVD"}

Exemplo 3:
  51.225 2 BOTICOLL SAB BARR
  CPO PERF 5x80g V2
  109,80 0,00 109,80 0,00 109,80 Doação
  FIDELIDADEVD
  → {"product_code":"51.225","stock_quantity":2,"name":"BOTICOLL SAB BARR CPO PERF 5x80g V2","operation":"Doação FIDELIDADEVD"}

REGRAS DE PROCESSAMENTO:
1. Para CADA produto, ANTES de definir "operation", verifique a linha de valores (5 números) e a próxima linha.
2. Se a linha de valores termina em "Doação" → operation NUNCA é "Venda". É "Doação FIDELIDADEVD" se a linha seguinte for "FIDELIDADEVD", senão "Doação".
3. O nome do produto pode ocupar 1, 2 ou 3 linhas — junte-as em uma só string separadas por espaço.
4. Inclua TODOS os produtos do extrato (Venda, Brinde, Doação, Bonificação, Troca). Não pule nenhum.
5. Retorne SOMENTE JSON válido, sem markdown e sem texto extra.

TEXTO DO EXTRATO:
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

    private function reconcileOperationsFromText(array $products, string $rawText): array
    {
        if (empty($products)) {
            return $products;
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawText);
        $lines = array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== ''));
        $total = count($lines);

        foreach ($products as $idx => $p) {
            $code = $p['product_code'] ?? '';
            if ($code === '') {
                continue;
            }

            $codePattern = '/^' . preg_quote($code, '/') . '\b/';
            $startLine = null;
            for ($i = 0; $i < $total; $i++) {
                if (preg_match($codePattern, $lines[$i])) {
                    $startLine = $i;
                    break;
                }
            }
            if ($startLine === null) {
                continue;
            }

            $endLine = $total;
            for ($i = $startLine + 1; $i < $total; $i++) {
                if (preg_match('/^\d{2,3}\.\d{3}\s+\d+\s/', $lines[$i])) {
                    $endLine = $i;
                    break;
                }
            }

            $detected = null;
            for ($i = $startLine; $i < $endLine; $i++) {
                $line = $lines[$i];

                if (preg_match('/\bDoação\s*$/u', $line)) {
                    $next = $lines[$i + 1] ?? '';
                    $nextNext = $lines[$i + 2] ?? '';
                    if (stripos($next, 'FIDELIDADEVD') !== false || stripos($nextNext, 'FIDELIDADEVD') !== false) {
                        $detected = 'Doação FIDELIDADEVD';
                    } else {
                        $detected = 'Doação';
                    }
                    break;
                }
                if (preg_match('/\bBrinde\s*$/u', $line)) { $detected = 'Brinde'; break; }
                if (preg_match('/\bBonificação\b/u', $line)) { $detected = 'Bonificação'; break; }
                if (preg_match('/\bTroca\b/u', $line)) { $detected = 'Troca'; break; }
                if (preg_match('/\bVenda\s*$/u', $line)) { $detected = 'Venda'; break; }
            }

            if ($detected !== null && $detected !== ($p['operation'] ?? null)) {
                Log::info("🔧 Operation corrigida via texto bruto", [
                    'product_code' => $code,
                    'antes' => $p['operation'] ?? null,
                    'depois' => $detected,
                ]);
                $products[$idx]['operation'] = $detected;
            }
        }

        return $products;
    }

    public function isConfigured()
    {
        return $this->client->isConfigured();
    }
}
