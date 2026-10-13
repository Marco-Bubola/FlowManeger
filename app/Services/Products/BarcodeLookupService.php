<?php

namespace App\Services\Products;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Descobre o código de barras (EAN) de um produto do Grupo Boticário
 * consultando o catálogo público das lojas (plataforma VTEX).
 *
 * Primeiro tenta pelo código (SKU / RefId) e, se não achar, pelo nome.
 * Devolve uma lista de candidatos ordenada do mais provável para o menos.
 */
class BarcodeLookupService
{
    /** Lojas consultadas, na ordem */
    private const STORES = [
        'https://www.boticario.com.br',
        'https://www.eudora.com.br',
        'https://www.quemdisseberenice.com.br',
    ];

    /**
     * @return array<int, array{ean: string, name: string, ref: ?string, store: string, by: string}>
     */
    public function lookup(?string $code, ?string $name): array
    {
        $code = preg_replace('/\D/', '', (string) $code);
        $name = trim((string) $name);
        $found = [];

        foreach (self::STORES as $store) {
            // 1) Pelo código: RefId exato, depois busca livre pelo número
            if (strlen($code) >= 4) {
                foreach ($this->search($store, ['fq' => 'alternateIds_RefId:' . $code]) as $c) {
                    $found[] = $c + ['by' => 'código'];
                }
                if (empty($found)) {
                    foreach ($this->search($store, ['ft' => $code]) as $c) {
                        if ($c['ref'] !== null && preg_replace('/\D/', '', $c['ref']) === $code) {
                            $found[] = $c + ['by' => 'código'];
                        }
                    }
                }
            }
            if (!empty($found)) {
                break;
            }
        }

        // 2) Pelo nome (quando o código não resolveu)
        if (empty($found) && mb_strlen($name) >= 4) {
            $term = $this->searchTerm($name);
            foreach (self::STORES as $store) {
                foreach ($this->search($store, ['ft' => $term]) as $c) {
                    $c['score'] = $this->similarity($name, $c['name']);
                    $found[] = $c + ['by' => 'nome'];
                }
            }
            usort($found, fn ($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));
            $found = array_values(array_filter($found, fn ($c) => ($c['score'] ?? 0) >= 35));
        }

        // Um EAN por candidato, sem repetir
        $unique = [];
        foreach ($found as $c) {
            $unique[$c['ean']] ??= $c;
        }

        return array_slice(array_values($unique), 0, 5);
    }

    /**
     * Busca no catálogo VTEX de uma loja e devolve os SKUs com EAN.
     */
    private function search(string $store, array $query): array
    {
        try {
            $resp = Http::timeout(8)
                ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'Mozilla/5.0 FlowManeger'])
                ->get($store . '/api/catalog_system/pub/products/search', $query + ['_from' => 0, '_to' => 9]);

            if (!$resp->successful() || !is_array($resp->json())) {
                return [];
            }

            $out = [];
            foreach ($resp->json() as $product) {
                foreach ($product['items'] ?? [] as $item) {
                    $ean = preg_replace('/\D/', '', (string) ($item['ean'] ?? ''));
                    if (strlen($ean) < 8) {
                        continue;
                    }
                    $ref = collect($item['referenceId'] ?? [])->firstWhere('Key', 'RefId')['Value'] ?? null;
                    $label = $item['nameComplete'] ?? $item['name'] ?? $product['productName'] ?? '';
                    $out[] = [
                        'ean' => $ean,
                        'name' => (string) $label,
                        'ref' => $ref ?? ($product['productReference'] ?? null),
                        'store' => parse_url($store, PHP_URL_HOST),
                    ];
                }
            }

            return $out;
        } catch (\Throwable $e) {
            Log::info('BarcodeLookup: falha ao consultar ' . $store . ': ' . $e->getMessage());
            return [];
        }
    }

    /** Tira abreviações típicas do PDF (CBEM, DES, COL...) e mantém as palavras úteis */
    private function searchTerm(string $name): string
    {
        $words = preg_split('/\s+/', Str::lower(Str::ascii($name)));
        $skip = ['cbem', 'des', 'col', 'v2', 'v3', 'ref', 'refil', 'kit', 'de', 'da', 'do', 'e', '+'];
        $words = array_filter($words, fn ($w) => $w !== '' && !in_array($w, $skip, true) && !preg_match('/^\d+(ml|g|gr|kg|l)?$/', $w));

        return implode(' ', array_slice(array_values($words), 0, 4));
    }

    /** Parecença de 0 a 100 entre dois nomes (palavras em comum + tamanho da embalagem) */
    private function similarity(string $a, string $b): int
    {
        $norm = fn ($s) => array_filter(preg_split('/[^a-z0-9]+/', Str::lower(Str::ascii($s))), fn ($w) => strlen($w) > 1);
        $wa = array_unique($norm($a));
        $wb = array_unique($norm($b));
        if (empty($wa) || empty($wb)) {
            return 0;
        }
        $score = (int) round(100 * count(array_intersect($wa, $wb)) / max(count($wa), 1));

        // Tamanho diferente (200ml x 100ml) derruba a nota
        preg_match('/(\d+)\s*(ml|g)\b/i', $a, $sa);
        preg_match('/(\d+)\s*(ml|g)\b/i', $b, $sb);
        if (!empty($sa) && !empty($sb) && $sa[1] !== $sb[1]) {
            $score = (int) ($score / 3);
        }

        return $score;
    }
}
