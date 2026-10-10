<?php

namespace App\Services\MercadoLivre;

use App\Models\MlPublication;
use App\Models\Product;
use App\Models\MlStockLog;
use App\Models\MercadoLivreOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MlStockSyncService
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Sincroniza quantidade de uma publicação com o Mercado Livre
     * 
     * @param MlPublication $publication
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function syncQuantityToMercadoLivre(MlPublication $publication, bool $confirmVariations = false): array
    {
        try {
            // Publicação sem produtos vinculados (ex.: importada do ML) não tem
            // estoque local: enviar 0 zeraria o anúncio no ML.
            if (!$publication->hasStockLink()) {
                return [
                    'success' => true,
                    'message' => 'Publicação sem produtos vinculados: quantidade não enviada ao ML',
                    'data' => ['skipped' => true, 'ml_item_id' => $publication->ml_item_id],
                ];
            }

            if (!$publication->ml_item_id || str_starts_with($publication->ml_item_id, 'TEMP_')) {
                return [
                    'success' => true,
                    'message' => 'Publicação ainda não está no ML: quantidade não enviada',
                    'data' => ['skipped' => true, 'ml_item_id' => $publication->ml_item_id],
                ];
            }

            // O dono confirmou na tela: as variações ligadas passam a ter o
            // estoque mandado pelo sistema (também nas próximas mudanças).
            if ($confirmVariations && $publication->usesVariationLinks()) {
                \App\Models\MlPublicationVariation::where('ml_publication_id', $publication->id)
                    ->whereNotNull('product_id')
                    ->update(['stock_confirmed' => true]);
            }

            // Obtém token de acesso (por user_id da publicação)
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            // Lê o item atual: com variações o estoque vai por variação (o ML
            // recusa available_quantity no topo). Nunca envia título aqui.
            $item = $this->getItemFromMl($publication, $token->access_token);
            $publication->refresh();

            // Recalcula quantidade baseada no estoque
            $availableQuantity = $publication->calculateAvailableQuantity();
            $quantityPayload = $this->buildQuantityPayload($publication, $item, $availableQuantity);
            if (isset($quantityPayload['error'])) {
                throw new \Exception($quantityPayload['error']);
            }
            if (isset($quantityPayload['skip'])) {
                return [
                    'success' => true,
                    'message' => $quantityPayload['skip'],
                    'data' => ['skipped' => true, 'ml_item_id' => $publication->ml_item_id],
                ];
            }
            $pushed = $quantityPayload['_pushed'] ?? null;
            unset($quantityPayload['_pushed']);

            // Atualiza via API do ML
            $response = Http::withToken($token->access_token)
                ->put("https://api.mercadolibre.com/items/{$publication->ml_item_id}", $quantityPayload);

            if ($response->successful()) {
                $extra = [];
                if ($pushed !== null) {
                    // Variações: o ML agora tem o estoque local nas ligadas e o
                    // mesmo de antes nas outras.
                    $vars = array_map(function ($v) use ($pushed) {
                        if (array_key_exists((string) $v['id'], $pushed)) {
                            $v['available_quantity'] = $pushed[(string) $v['id']];
                        }
                        return $v;
                    }, $publication->ml_variations ?? []);
                    $extra['ml_variations'] = $vars;
                    $availableQuantity = (int) collect($vars)->sum('available_quantity');
                }
                $publication->update($extra + [
                    'available_quantity' => $availableQuantity,
                    'sync_status' => 'synced',
                    'last_sync_at' => now(),
                    'error_message' => null,
                ]);

                Log::info("Quantidade sincronizada com ML", [
                    'ml_item_id' => $publication->ml_item_id,
                    'quantity' => $availableQuantity,
                    'by_variation' => isset($quantityPayload['variations']),
                ]);

                return [
                    'success' => true,
                    'message' => 'Quantidade sincronizada com sucesso',
                    'data' => [
                        'quantity' => $availableQuantity,
                        'ml_item_id' => $publication->ml_item_id,
                        'variations' => $pushed,
                    ],
                ];
            }

            throw new \Exception(self::mlErrorMessage($response));

        } catch (\Exception $e) {
            $publication->update([
                'sync_status' => 'error',
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            Log::error("Erro ao sincronizar com ML", [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * GET /items/{id}. Guarda family_name / variações na publicação.
     * Devolve null se o ML não responder (quem chama usa o que está salvo).
     */
    protected function getItemFromMl(MlPublication $publication, string $accessToken): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get("https://api.mercadolibre.com/items/{$publication->ml_item_id}");
            if (!$response->successful()) {
                return null;
            }
            $item = $response->json();
            if (!is_array($item) || empty($item['id'])) {
                return null;
            }
            $publication->update(self::itemMeta($item));
            return $item;
        } catch (\Throwable $e) {
            Log::warning('Não foi possível ler o item no ML antes de atualizar', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Campos da publicação que vêm do item do ML e mudam o que pode ser enviado.
     */
    public static function itemMeta(array $item): array
    {
        $variations = [];
        foreach (($item['variations'] ?? []) as $v) {
            if (!is_array($v) || !isset($v['id'])) {
                continue;
            }
            $combos = collect($v['attribute_combinations'] ?? [])
                ->filter(fn ($a) => filled($a['value_name'] ?? null))
                ->map(fn ($a) => ['name' => $a['name'] ?? null, 'value' => (string) $a['value_name']])
                ->values();
            $label = $combos->pluck('value')->implode(' / ');
            $row = [
                'id' => $v['id'],
                'available_quantity' => (int) ($v['available_quantity'] ?? 0),
                'label' => $label,
            ];
            if ($combos->isNotEmpty()) {
                $row['attributes'] = $combos->all();
                $row['name_label'] = $combos->map(fn ($a) => filled($a['name']) ? $a['name'] . ': ' . $a['value'] : $a['value'])->implode(' · ');
            }
            $vAttr = function (array $ids) use ($v) {
                foreach (($v['attributes'] ?? []) as $a) {
                    if (in_array(strtoupper($a['id'] ?? ''), $ids, true) && filled($a['value_name'] ?? null)) {
                        return trim((string) $a['value_name']);
                    }
                }
                return null;
            };
            $sku = $vAttr(['SELLER_SKU']) ?? (filled($v['seller_custom_field'] ?? null) ? trim((string) $v['seller_custom_field']) : null);
            if ($sku) {
                $row['seller_sku'] = $sku;
            }
            if ($gtin = $vAttr(['GTIN', 'EAN', 'UPC'])) {
                $row['gtin'] = $gtin;
            }
            $picId = $v['picture_ids'][0] ?? null;
            if ($picId !== null) {
                foreach (($item['pictures'] ?? []) as $pic) {
                    if (($pic['id'] ?? null) == $picId) {
                        $row['picture'] = $pic['secure_url'] ?? $pic['url'] ?? null;
                        break;
                    }
                }
                if (empty($row['picture'])) {
                    $row['picture'] = 'https://http2.mlstatic.com/D_' . $picId . '-O.jpg';
                }
            }
            $variations[] = $row;
        }

        return [
            'ml_family_name' => filled($item['family_name'] ?? null) ? mb_substr((string) $item['family_name'], 0, 255) : null,
            'ml_user_product_id' => filled($item['user_product_id'] ?? null) ? (string) $item['user_product_id'] : null,
            'ml_variations' => $variations ?: null,
        ];
    }

    /**
     * Corpo do PUT de estoque: no topo para item sem variação, por variação
     * para item com UMA variação. Com várias variações vai o estoque de cada
     * variação ligada a um produto (e já confirmada pelo dono); as outras vão
     * só com o id, sem quantidade, para o ML mantê-las como estão (o ML apaga
     * variações que ficam fora da lista). Sem nenhuma variação ligada devolve
     * ['error' => ...] em vez de chutar; com ligadas mas nenhuma confirmada,
     * ['skip' => ...]. '_pushed' = [variation_id => quantidade enviada].
     */
    protected function buildQuantityPayload(MlPublication $publication, ?array $item, int $quantity, bool $onlyConfirmed = true): array
    {
        $variations = $item !== null
            ? (self::itemMeta($item)['ml_variations'] ?? [])
            : ($publication->ml_variations ?? []);

        if (count($variations) === 0) {
            return ['available_quantity' => $quantity];
        }
        if (count($variations) === 1) {
            return ['variations' => [['id' => $variations[0]['id'], 'available_quantity' => $quantity]]];
        }

        $mapped = $publication->mappedVariations()->keyBy(fn ($r) => (string) $r->ml_variation_id);
        if ($mapped->isEmpty()) {
            return ['error' => 'Este anúncio tem ' . count($variations) . ' variações no Mercado Livre: ligue um produto a cada variação na edição do anúncio para o sistema enviar o estoque (por enquanto o estoque de cada variação é ajustado no próprio ML).'];
        }

        $payload = [];
        $pushed = [];
        foreach ($variations as $v) {
            $row = $mapped->get((string) $v['id']);
            if ($row && (!$onlyConfirmed || $row->stock_confirmed)) {
                $q = MlPublication::variationAvailableQuantity($row);
                $payload[] = ['id' => $v['id'], 'available_quantity' => $q];
                $pushed[(string) $v['id']] = $q;
            } else {
                $payload[] = ['id' => $v['id']];
            }
        }

        if (empty($pushed)) {
            return ['skip' => 'Variações ligadas ainda não confirmadas: o estoque do ML não foi alterado (confirme na edição do anúncio).'];
        }

        return ['variations' => $payload, '_pushed' => $pushed];
    }

    /**
     * Mensagem curta a partir da resposta de erro do ML (mantém o corpo para diagnóstico).
     */
    public static function mlErrorMessage(\Illuminate\Http\Client\Response $response): string
    {
        return 'Erro na API do ML: ' . mb_substr($response->body(), 0, 1500);
    }

    /**
     * O ML recusou mudar o título (item com family_name / catálogo)?
     * Ex.: {"cause":374,"message":"BODY_INVALID_FIELDS","error":"You cannot modify the title if the item has a family_name"}
     */
    public static function isTitleLockedError(\Illuminate\Http\Client\Response $response): bool
    {
        if ($response->status() !== 400) {
            return false;
        }
        $body = $response->json();
        if (!is_array($body)) {
            return str_contains(strtolower($response->body()), 'title');
        }
        $causes = $body['cause'] ?? [];
        $causes = is_array($causes) ? $causes : [$causes];
        foreach ($causes as $c) {
            $code = is_array($c) ? ($c['code'] ?? $c['cause_id'] ?? null) : $c;
            $msg = is_array($c) ? strtolower((string) ($c['message'] ?? '')) : '';
            if ((string) $code === '374' || str_contains($msg, 'title') || str_contains($msg, 'family_name')) {
                return true;
            }
        }
        $text = strtolower(($body['error'] ?? '') . ' ' . ($body['message'] ?? ''));
        return str_contains($text, 'title') || str_contains($text, 'family_name');
    }

    /**
     * Processa uma venda do Mercado Livre
     * Deduz estoque de todos os produtos vinculados
     * 
     * @param string $mlOrderId
     * @param string $mlItemId
     * @param int $quantity
     * @return array ['success' => bool, 'message' => string, 'order' => MercadoLivreOrder]
     */
    public function processMercadoLivreSale(string $mlOrderId, string $mlItemId, int $quantity, $mlVariationId = null): array
    {
        try {
            // Busca a publicação
            $publication = MlPublication::where('ml_item_id', $mlItemId)->first();

            if (!$publication) {
                throw new \Exception("Publicação ML {$mlItemId} não encontrada");
            }

            // Venda de uma variação ligada a um produto: só ele sai do estoque.
            // Sem ligação, vale a regra antiga (todos os produtos do anúncio).
            $variation = $publication->mappedVariation($mlVariationId);

            // O ML reenvia a notificação do mesmo pedido várias vezes:
            // só baixa o estoque na primeira (por variação, quando houver).
            $jaBaixado = MlStockLog::where('ml_order_id', $mlOrderId)
                ->where('ml_publication_id', $publication->id)
                ->where('operation_type', 'ml_sale')
                ->when(\Illuminate\Support\Facades\Schema::hasColumn('ml_stock_logs', 'ml_variation_id'), function ($q) use ($variation) {
                    // Baixa do anúncio inteiro (sem variação) vale para o pedido todo.
                    $variation
                        ? $q->where(fn ($w) => $w->where('ml_variation_id', $variation->ml_variation_id)->orWhereNull('ml_variation_id'))
                        : $q->whereNull('ml_variation_id');
                })
                ->exists();

            if ($jaBaixado) {
                return [
                    'success' => true,
                    'message' => 'Venda já processada anteriormente',
                    'order' => MercadoLivreOrder::where('ml_order_id', $mlOrderId)->first(),
                ];
            }

            // Deduz estoque (da variação ou de todos os produtos)
            $result = $variation
                ? $publication->deductVariationStock($variation, $quantity, $mlOrderId)
                : $publication->deductStock($quantity, $mlOrderId);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Registra a ordem (se ainda não existe)
            $order = MercadoLivreOrder::firstOrCreate(
                ['ml_order_id' => $mlOrderId],
                [
                    'ml_item_id' => $mlItemId,
                    'product_id' => $variation ? $variation->product_id : ($publication->linkedProducts()->first()->id ?? null),
                    'quantity' => $quantity,
                    'order_status' => 'paid',
                    'payment_status' => 'approved',
                    'sync_status' => 'processed',
                    'date_created' => now(),
                ]
            );

            Log::info("Venda ML processada", [
                'ml_order_id' => $mlOrderId,
                'ml_item_id' => $mlItemId,
                'quantity' => $quantity,
                'ml_variation_id' => $mlVariationId,
                'products_affected' => $variation ? 1 : $publication->linkedProducts()->count(),
            ]);

            return [
                'success' => true,
                'message' => 'Venda processada e estoque atualizado',
                'order' => $order,
            ];

        } catch (\Exception $e) {
            Log::error("Erro ao processar venda ML", [
                'ml_order_id' => $mlOrderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'order' => null,
            ];
        }
    }

    /**
     * Pedido cancelado no ML: devolve o que foi baixado por ele, uma vez só.
     */
    public function restoreMercadoLivreSale(string $mlOrderId): int
    {
        $logs = MlStockLog::restoreOrder($mlOrderId, 'ml_sale', 'Pedido ML');

        foreach ($logs->pluck('ml_publication_id')->filter()->unique() as $pubId) {
            MlPublication::find($pubId)?->syncQuantityToMl();
        }

        return $logs->count();
    }

    /**
     * Sincroniza todas as publicações pendentes
     * Útil para rodar em cron/scheduler
     */
    public function syncAllPending(): array
    {
        $publications = MlPublication::pending()->active()->get();
        $results = [
            'total' => $publications->count(),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($publications as $publication) {
            $result = $this->syncQuantityToMercadoLivre($publication);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'ml_item_id' => $publication->ml_item_id,
                    'error' => $result['message'],
                ];
            }
        }

        return $results;
    }

    /**
     * Verifica e corrige inconsistências entre estoque local e ML
     * 
     * @param MlPublication $publication
     * @return array
     */
    public function auditAndFix(MlPublication $publication): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token não disponível');
            }

            // Consulta quantidade atual no ML
            $response = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$publication->ml_item_id}");

            if (!$response->successful()) {
                throw new \Exception("Erro ao consultar ML: " . $response->body());
            }

            $mlData = $response->json();
            $mlQuantity = $mlData['available_quantity'] ?? 0;
            $localQuantity = $publication->calculateAvailableQuantity();

            $issues = [];

            // Verifica diferença
            if ($mlQuantity !== $localQuantity) {
                $issues[] = "Quantidade divergente: Local={$localQuantity}, ML={$mlQuantity}";

                // Corrige automaticamente
                $syncResult = $this->syncQuantityToMercadoLivre($publication);
                
                if ($syncResult['success']) {
                    $issues[] = "✓ Corrigido automaticamente";
                } else {
                    $issues[] = "✗ Falha na correção: " . $syncResult['message'];
                }
            }

            return [
                'success' => true,
                'consistent' => empty($issues),
                'local_quantity' => $localQuantity,
                'ml_quantity' => $mlQuantity,
                'issues' => $issues,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'consistent' => false,
                'issues' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Busca quantidade disponível de um item no Mercado Livre
     * 
     * @param string $mlItemId
     * @return int|null
     */
    public function getMlItemQuantity(string $mlItemId): ?int
    {
        try {
            $publication = MlPublication::where('ml_item_id', $mlItemId)->first();
            $token = $publication ? $this->getTokenForPublication($publication) : $this->authService->getActiveToken(\Illuminate\Support\Facades\Auth::id());
            if (!$token) {
                Log::error("Token ML não disponível para consulta de quantidade", [
                    'ml_item_id' => $mlItemId
                ]);
                return null;
            }

            $response = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$mlItemId}");

            if (!$response->successful()) {
                Log::error("Erro ao consultar item no ML", [
                    'ml_item_id' => $mlItemId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();
            return $data['available_quantity'] ?? 0;

        } catch (\Exception $e) {
            Log::error("Exceção ao buscar quantidade no ML", [
                'ml_item_id' => $mlItemId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtém token para a publicação (user_id do dono da publicação).
     */
    protected function getTokenForPublication(MlPublication $publication): ?\App\Models\MercadoLivreToken
    {
        return $this->authService->getActiveToken($publication->user_id);
    }

    public const TITLE_LOCKED_MESSAGE = 'O título deste anúncio só pode ser mudado no Mercado Livre (anúncio de catálogo/família)';

    /**
     * Envia ao ML o que mudou na página de edição (título, preço, descrição e,
     * se pedido, a quantidade calculada pelo estoque).
     *
     * Lê o item antes e só manda campos diferentes. Título nunca vai para item
     * com family_name (o ML responde 400 cause 374); se o ML recusar o título
     * mesmo assim, tenta de novo uma vez sem ele.
     *
     * @param MlPublication $publication Publicação já atualizada no banco
     * @param bool $includeQuantity Também envia o estoque local (só quando o dono confirmou)
     * @return array ['success' => bool, 'message' => string, 'title_locked' => bool, 'sent' => string[]]
     */
    public function updatePublicationToMercadoLivre(MlPublication $publication, bool $includeQuantity = false): array
    {
        $titleLocked = false;
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }
            if (!$publication->ml_item_id || str_starts_with($publication->ml_item_id, 'TEMP_')) {
                throw new \Exception('Publicação ainda não foi publicada no ML');
            }

            $item = $this->getItemFromMl($publication, $token->access_token);
            $publication->refresh();
            $familyName = $item !== null ? ($item['family_name'] ?? null) : $publication->ml_family_name;
            $notes = [];

            $payload = [];
            if (filled($familyName)) {
                $titleLocked = true;
                if ($item !== null && isset($item['title']) && $item['title'] !== $publication->title) {
                    $notes[] = self::TITLE_LOCKED_MESSAGE;
                }
            } elseif ($item === null || ($item['title'] ?? null) !== $publication->title) {
                $payload['title'] = $publication->title;
            }

            if ($item === null || abs((float) ($item['price'] ?? 0) - (float) $publication->price) > 0.004) {
                $payload['price'] = (float) $publication->price;
            }

            $pushedQuantity = null;
            if ($includeQuantity && $publication->hasStockLink()) {
                $qty = $publication->calculateAvailableQuantity();
                $q = $this->buildQuantityPayload($publication, $item, $qty);
                if (isset($q['error']) || isset($q['skip'])) {
                    $notes[] = $q['error'] ?? $q['skip'];
                } else {
                    unset($q['_pushed']);
                    $payload += $q;
                    $pushedQuantity = $qty;
                }
            }

            if (!empty($payload)) {
                $response = Http::withToken($token->access_token)
                    ->put("https://api.mercadolibre.com/items/{$publication->ml_item_id}", $payload);

                // O ML não deixa mudar o título (catálogo/família): manda o resto sem ele.
                if (!$response->successful() && isset($payload['title']) && self::isTitleLockedError($response)) {
                    $titleLocked = true;
                    $notes[] = self::TITLE_LOCKED_MESSAGE;
                    unset($payload['title']);
                    if (!empty($payload)) {
                        $response = Http::withToken($token->access_token)
                            ->put("https://api.mercadolibre.com/items/{$publication->ml_item_id}", $payload);
                    } else {
                        $response = null;
                    }
                }

                if ($response !== null && !$response->successful()) {
                    throw new \Exception(self::mlErrorMessage($response));
                }
            }

            // Título travado: o local volta a ser o do ML para não ficar divergente.
            if ($titleLocked && $item !== null && isset($item['title']) && $item['title'] !== $publication->title) {
                $publication->title = $item['title'];
            }

            // Descrição no ML é atualizada em endpoint separado (falha aqui não invalida o update principal)
            if ($publication->description !== null && $publication->description !== '') {
                try {
                    $current = Http::withToken($token->access_token)
                        ->get("https://api.mercadolibre.com/items/{$publication->ml_item_id}/description");
                    $currentText = $current->successful() ? ($current->json('plain_text') ?? null) : null;
                    if ($currentText === null || trim((string) $currentText) !== trim($publication->description)) {
                        $desc = Http::withToken($token->access_token)
                            ->put("https://api.mercadolibre.com/items/{$publication->ml_item_id}/description", [
                                'plain_text' => $publication->description,
                            ]);
                        if ($desc->successful()) {
                            $payload['description'] = true;
                        } else {
                            Log::warning('ML recusou a descrição (item atualizado)', ['ml_item_id' => $publication->ml_item_id, 'body' => $desc->body()]);
                            $notes[] = 'A descrição não foi aceita pelo ML.';
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Falha ao atualizar descrição no ML (item atualizado)', ['ml_item_id' => $publication->ml_item_id, 'error' => $e->getMessage()]);
                }
            }

            $publication->fill([
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'error_message' => null,
            ]);
            if ($pushedQuantity !== null) {
                $publication->available_quantity = $pushedQuantity;
            }
            $publication->save();

            Log::info('Publicação atualizada no ML', [
                'ml_item_id' => $publication->ml_item_id,
                'fields' => array_keys($payload),
                'title_locked' => $titleLocked,
            ]);

            $message = empty($payload) ? 'Nada mudou em relação ao Mercado Livre' : 'Publicação atualizada no Mercado Livre';
            if ($notes) {
                $message .= '. ' . implode('. ', array_unique($notes));
            }

            return [
                'success' => true,
                'message' => $message,
                'title_locked' => $titleLocked,
                'sent' => array_keys($payload),
            ];
        } catch (\Exception $e) {
            $publication->update([
                'sync_status' => 'error',
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
            ]);
            Log::error('Erro ao atualizar publicação no ML', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage(), 'title_locked' => $titleLocked, 'sent' => []];
        }
    }

    /**
     * Busca os dados atuais do anúncio no Mercado Livre e atualiza a publicação local.
     * Assim, o que foi alterado no ML (título, preço, etc.) passa a refletir no sistema.
     *
     * @param MlPublication $publication
     * @return array ['success' => bool, 'message' => string, 'publication' => MlPublication|null]
     */
    public function fetchPublicationFromMercadoLivre(MlPublication $publication, bool $recordError = true): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            $itemId = $publication->ml_item_id;
            if (!$itemId) {
                return ['success' => false, 'message' => 'Publicação sem ID do ML', 'publication' => null];
            }

            $response = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$itemId}");

            if (!$response->successful()) {
                // Tratamento específico por código de erro HTTP
                $statusCode = $response->status();
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Erro desconhecido';
                
                $userFriendlyMessage = match($statusCode) {
                    404 => "Item {$itemId} não encontrado no Mercado Livre (pode ter sido excluído ou expirado)",
                    403 => "Sem permissão para acessar o item {$itemId}",
                    401 => "Token de acesso expirado ou inválido",
                    default => "Erro ao buscar item no ML ({$statusCode}): {$errorMessage}"
                };
                
                throw new \Exception($userFriendlyMessage);
            }

            $data = $response->json();

            $description = $publication->description;
            $descResponse = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$itemId}/description");
            if ($descResponse->successful()) {
                $descData = $descResponse->json();
                $description = $descData['plain_text'] ?? $description;
            }

            // Extrair shipping info
            $shipping = $data['shipping'] ?? [];
            $freeShipping = $shipping['free_shipping'] ?? $publication->free_shipping;
            $localPickup = $shipping['local_pick_up'] ?? $publication->local_pickup;

            $publication->update(self::itemMeta($data) + [
                'title' => $data['title'] ?? $publication->title,
                'price' => $data['price'] ?? $publication->price,
                'available_quantity' => (int) ($data['available_quantity'] ?? $publication->available_quantity),
                'description' => $description,
                'ml_permalink' => $data['permalink'] ?? $publication->ml_permalink,
                'status' => self::mapMlStatus($data['status'] ?? null, $publication->status),
                'condition' => self::mapMlCondition($data['condition'] ?? null, $publication->condition),
                'listing_type' => $data['listing_type_id'] ?? $publication->listing_type,
                'ml_category_id' => $data['category_id'] ?? $publication->ml_category_id,
                'warranty' => $data['warranty'] ?? $publication->warranty,
                'free_shipping' => $freeShipping,
                'local_pickup' => $localPickup,
                'ml_attributes' => $data['attributes'] ?? $publication->ml_attributes,
                'pictures' => $data['pictures'] ?? $publication->pictures,
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'error_message' => null,
            ]);

            Log::info('Publicação atualizada a partir do ML', [
                'ml_item_id' => $itemId,
                'title' => $publication->title,
            ]);

            return [
                'success' => true,
                'message' => 'Dados atualizados do Mercado Livre',
                'publication' => $publication->fresh(),
                'item' => $data,
            ];
        } catch (\Exception $e) {
            // Descarta o que ficou "sujo" de um update que falhou antes de gravar o erro
            $publication->discardChanges();
            if ($recordError) {
                $publication->update([
                    'sync_status' => 'error',
                    'error_message' => mb_substr($e->getMessage(), 0, 2000),
                ]);
            }
            Log::error('Erro ao buscar publicação do ML', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'publication' => null,
            ];
        }
    }

    /**
     * Status do ML -> enum local (pending, active, paused, closed, under_review).
     * Status desconhecidos (inactive, payment_required, not_yet_active...) viram 'paused'.
     */
    public static function mapMlStatus(?string $mlStatus, ?string $current = null): string
    {
        if ($mlStatus === null || $mlStatus === '') {
            return $current ?: 'pending';
        }
        return in_array($mlStatus, ['active', 'paused', 'closed', 'under_review'], true) ? $mlStatus : 'paused';
    }

    /**
     * Condição do ML -> enum local (new, used). not_specified e outros viram 'new'.
     */
    public static function mapMlCondition(?string $mlCondition, ?string $current = null): string
    {
        if ($mlCondition === null || $mlCondition === '') {
            return $current ?: 'new';
        }
        return $mlCondition === 'used' ? 'used' : 'new';
    }

    /**
     * Lista os IDs de itens publicados pelo vendedor no ML.
     *
     * @param int $userId User ID (para obter token)
     * @return array ['success' => bool, 'item_ids' => string[], 'message' => string]
     */
    public function fetchUserItemIdsFromMl(int $userId): array
    {
        try {
            $token = $this->authService->getActiveToken($userId);
            if (!$token || !$token->ml_user_id) {
                return ['success' => false, 'item_ids' => [], 'message' => 'Conta ML não conectada'];
            }
            $response = Http::withToken($token->access_token)
                ->get('https://api.mercadolibre.com/users/' . $token->ml_user_id . '/items/search', [
                    'limit' => 100,
                    'offset' => 0,
                ]);
            if (!$response->successful()) {
                return ['success' => false, 'item_ids' => [], 'message' => 'Erro ao listar itens no ML'];
            }
            $data = $response->json();
            $itemIds = $data['results'] ?? [];
            return ['success' => true, 'item_ids' => $itemIds, 'message' => ''];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar itens do usuário no ML', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ['success' => false, 'item_ids' => [], 'message' => $e->getMessage()];
        }
    }

    /**
     * Busca dados resumidos de vários itens no ML (batch, até 20 por request).
     *
     * @param array $itemIds
     * @param int $userId
     * @return array Lista de arrays com id, title, thumbnail, permalink, price, available_quantity, status
     */
    public function fetchItemsSummaryFromMl(array $itemIds, int $userId): array
    {
        if (empty($itemIds)) {
            return [];
        }
        $token = $this->authService->getActiveToken($userId);
        if (!$token) {
            return [];
        }
        $items = [];
        foreach (array_chunk($itemIds, 20) as $chunk) {
            $ids = implode(',', $chunk);
            $response = Http::withToken($token->access_token)
                ->get('https://api.mercadolibre.com/items', ['ids' => $ids]);
            if (!$response->successful()) {
                continue;
            }
            $data = $response->json();
            foreach ($data as $row) {
                if (isset($row['body']) && isset($row['body']['id'])) {
                    $b = $row['body'];
                    $items[] = [
                        'id' => $b['id'],
                        'title' => $b['title'] ?? '',
                        'thumbnail' => $b['thumbnail'] ?? null,
                        'permalink' => $b['permalink'] ?? null,
                        'price' => $b['price'] ?? 0,
                        'available_quantity' => (int) ($b['available_quantity'] ?? 0),
                        'status' => $b['status'] ?? 'unknown',
                    ];
                }
            }
        }
        return $items;
    }

    /**
     * Pausa uma publicação no Mercado Livre.
     *
     * @param MlPublication $publication
     * @return array ['success' => bool, 'message' => string]
     */
    public function pausePublication(MlPublication $publication): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            $itemId = $publication->ml_item_id;
            if (!$itemId || str_starts_with($itemId, 'TEMP_')) {
                return ['success' => false, 'message' => 'Publicação ainda não foi publicada no ML'];
            }

            $response = Http::withToken($token->access_token)
                ->put("https://api.mercadolibre.com/items/{$itemId}", [
                    'status' => 'paused',
                ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Erro desconhecido';
                throw new \Exception("Erro ao pausar item no ML: {$errorMessage}");
            }

            $publication->update([
                'status' => 'paused',
                'sync_status' => 'synced',
                'last_sync_at' => now(),
            ]);

            Log::info('Publicação pausada no ML', ['ml_item_id' => $itemId]);

            return ['success' => true, 'message' => 'Publicação pausada com sucesso'];
        } catch (\Exception $e) {
            Log::error('Erro ao pausar publicação no ML', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ativa uma publicação no Mercado Livre.
     *
     * @param MlPublication $publication
     * @return array ['success' => bool, 'message' => string]
     */
    public function activatePublication(MlPublication $publication): array
    {
        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            $itemId = $publication->ml_item_id;
            if (!$itemId || str_starts_with($itemId, 'TEMP_')) {
                return ['success' => false, 'message' => 'Publicação ainda não foi publicada no ML'];
            }

            $response = Http::withToken($token->access_token)
                ->put("https://api.mercadolibre.com/items/{$itemId}", [
                    'status' => 'active',
                ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Erro desconhecido';
                throw new \Exception("Erro ao ativar item no ML: {$errorMessage}");
            }

            $publication->update([
                'status' => 'active',
                'sync_status' => 'synced',
                'last_sync_at' => now(),
            ]);

            Log::info('Publicação ativada no ML', ['ml_item_id' => $itemId]);

            return ['success' => true, 'message' => 'Publicação ativada com sucesso'];
        } catch (\Exception $e) {
            Log::error('Erro ao ativar publicação no ML', [
                'ml_item_id' => $publication->ml_item_id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Encerra (status=closed) o anúncio no Mercado Livre, com o token do dono.
     * Publicação ainda não enviada (sem ID / TEMP_) ou item inexistente no ML
     * conta como sucesso: não há o que encerrar lá.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function closePublication(MlPublication $publication): array
    {
        $itemId = $publication->ml_item_id;
        if (!$itemId || str_starts_with($itemId, 'TEMP_')) {
            return ['success' => true, 'message' => 'Publicação não existe no ML'];
        }

        try {
            $token = $this->getTokenForPublication($publication);
            if (!$token) {
                throw new \Exception('Token do Mercado Livre não encontrado ou expirado');
            }

            $response = Http::withToken($token->access_token)
                ->put("https://api.mercadolibre.com/items/{$itemId}", [
                    'status' => 'closed',
                ]);

            if ($response->status() === 404) {
                return ['success' => true, 'message' => 'Item não encontrado no ML'];
            }

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Erro desconhecido';
                throw new \Exception("Erro ao encerrar item no ML: {$errorMessage}");
            }

            Log::info('Publicação encerrada no ML', ['ml_item_id' => $itemId]);

            return ['success' => true, 'message' => 'Publicação encerrada no Mercado Livre'];
        } catch (\Exception $e) {
            Log::error('Erro ao encerrar publicação no ML', [
                'ml_item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Importa um anúncio do ML para o sistema (cria MlPublication com dados do ML).
     * Tenta ligar a um produto do dono (SKU, GTIN, título). O vínculo é só local:
     * a quantidade do ML não é alterada na importação.
     *
     * @param int $userId
     * @param string $mlItemId
     * @return array ['success' => bool, 'message' => string, 'publication' => MlPublication|null,
     *                'linked_product' => Product|null, 'match' => 'sku'|'gtin'|'title'|null]
     */
    public function createPublicationFromMlItem(int $userId, string $mlItemId, bool $autoLink = true): array
    {
        $existing = MlPublication::where('user_id', $userId)->where('ml_item_id', $mlItemId)->first();
        if ($existing) {
            return ['success' => true, 'message' => 'Já existe no sistema', 'publication' => $existing, 'linked_product' => null, 'match' => null];
        }
        $pub = MlPublication::create([
            'user_id' => $userId,
            'ml_item_id' => $mlItemId,
            'title' => $mlItemId,
            'price' => 0,
            'publication_type' => 'simple',
            'status' => 'active',
            'sync_status' => 'pending',
        ])->fresh(); // carrega os defaults do banco (listing_type, condition...)
        $result = $this->fetchPublicationFromMercadoLivre($pub);
        if ($result['success'] && $result['publication']) {
            $publication = $result['publication'];
            $publication->update(['publication_type' => 'simple']);

            $linked = null;
            $match = null;
            $linkedVariations = 0;
            if ($autoLink && $publication->usesVariationLinks()) {
                // Anúncio com variações: cada variação vai para o seu produto
                // (nunca o anúncio inteiro para um produto só).
                $linkedVariations = $this->autoLinkVariations($publication, $userId)['linked'];
                $match = $linkedVariations > 0 ? 'variations' : null;
                if ($linkedVariations > 0) {
                    Log::info('Anúncio importado: variações ligadas', [
                        'ml_item_id' => $mlItemId,
                        'linked' => $linkedVariations,
                    ]);
                }
            } elseif ($autoLink) {
                [$linked, $match] = $this->findMatchingProduct($userId, $result['item'] ?? []);
                if ($linked) {
                    // Só o vínculo: não recalcula nem envia estoque ao ML agora.
                    $publication->addProduct($linked->id, 1, (float) $linked->price, 0, false);
                    Log::info('Anúncio importado ligado a produto', [
                        'ml_item_id' => $mlItemId,
                        'product_id' => $linked->id,
                        'match' => $match,
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Importado do ML',
                'publication' => $publication->fresh(),
                'linked_product' => $linked,
                'match' => $match,
                'linked_variations' => $linkedVariations,
                'variation_count' => $publication->fresh()->usesVariationLinks() ? $publication->fresh()->mlVariationCount() : 0,
            ];
        }
        // Não deixa a linha provisória (título = ID, preço 0) para trás
        $pub->delete();
        return ['success' => false, 'message' => $result['message'] ?? 'Erro ao importar', 'publication' => null, 'linked_product' => null, 'match' => null];
    }

    /**
     * Produto do dono que corresponde ao item do ML, nesta ordem:
     * SKU (atributo SELLER_SKU ou seller_custom_field) = product_code;
     * GTIN/EAN = barcode; título normalizado igual ao nome.
     * Só liga quando há exatamente um candidato no critério.
     *
     * @return array [Product|null, 'sku'|'gtin'|'title'|null]
     */
    public function findMatchingProduct(int $userId, array $item): array
    {
        if (empty($item)) {
            return [null, null];
        }

        $base = fn () => Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $userId)
            ->where(fn ($q) => $q->whereNull('is_variation_parent')->orWhere('is_variation_parent', false));

        // Atributos do item e, se houver uma variação só, os dela também
        $attrs = $item['attributes'] ?? [];
        $variations = $item['variations'] ?? [];
        if (count($variations) === 1) {
            $attrs = array_merge($attrs, $variations[0]['attributes'] ?? []);
        }
        $attrValues = function (array $ids) use ($attrs): array {
            $out = [];
            foreach ($attrs as $a) {
                if (in_array(strtoupper($a['id'] ?? ''), $ids, true) && filled($a['value_name'] ?? null)) {
                    foreach (preg_split('/[,;\s]+/', (string) $a['value_name']) as $v) {
                        if ($v !== '') {
                            $out[] = $v;
                        }
                    }
                }
            }
            return $out;
        };

        // 1) SKU
        $skus = $attrValues(['SELLER_SKU']);
        foreach ([$item['seller_custom_field'] ?? null, count($variations) === 1 ? ($variations[0]['seller_custom_field'] ?? null) : null] as $scf) {
            if (filled($scf)) {
                $skus[] = (string) $scf;
            }
        }
        $skus = array_values(array_unique(array_map(fn ($v) => mb_strtolower(trim($v)), $skus)));
        if ($skus) {
            $found = $base()->whereIn(DB::raw('LOWER(TRIM(product_code))'), $skus)->get();
            if ($found->count() === 1) {
                return [$found->first(), 'sku'];
            }
        }

        // 2) GTIN / EAN (compara só dígitos, sem zeros à esquerda)
        $norm = fn ($v) => ltrim(preg_replace('/\D/', '', (string) $v), '0');
        $gtins = array_values(array_filter(array_unique(array_map($norm, $attrValues(['GTIN', 'EAN', 'UPC'])))));
        if ($gtins) {
            $found = $base()->whereNotNull('barcode')->where('barcode', '!=', '')->get(['id', 'barcode'])
                ->filter(fn ($p) => in_array($norm($p->barcode), $gtins, true));
            if ($found->count() === 1) {
                return [$base()->find($found->first()->id), 'gtin'];
            }
        }

        // 3) Título normalizado (sem acento/pontuação/caixa, ordem das palavras livre)
        $title = self::normalizeTitle($item['title'] ?? '');
        if ($title !== '') {
            $found = $base()->where('status', 'ativo')->get(['id', 'name'])
                ->filter(fn ($p) => self::normalizeTitle($p->name) === $title);
            if ($found->count() === 1) {
                return [$base()->find($found->first()->id), 'title'];
            }
        }

        return [null, null];
    }

    /**
     * Liga as variações ainda sem produto, nesta ordem:
     * 1) SKU da variação (atributo SELLER_SKU ou seller_custom_field) = product_code;
     * 2) GTIN/EAN da variação = barcode;
     * 3) valor do atributo (ex.: "Nude") = variation_value de um produto de UMA
     *    família de variações (a do produto já ligado, a pedida, ou a que mais casa).
     * Só liga quando há um candidato só, e nunca o mesmo produto em duas variações.
     * O vínculo é local: não envia nada ao ML (stock_confirmed fica falso).
     *
     * @return array ['linked' => int, 'by' => [ml_variation_id => 'sku'|'gtin'|'cor']]
     */
    public function autoLinkVariations(MlPublication $publication, int $userId, ?int $familyRootId = null, bool $useCodes = true): array
    {
        $out = ['linked' => 0, 'by' => []];
        if (!\App\Models\MlPublicationVariation::tableExists()) {
            return $out;
        }
        $rows = $publication->variationLinks()->get();
        if ($rows->isEmpty()) {
            return $out;
        }

        $base = fn () => Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $userId)
            ->where(fn ($q) => $q->whereNull('is_variation_parent')->orWhere('is_variation_parent', false));
        $used = $rows->pluck('product_id')->filter()->map(fn ($v) => (int) $v)->all();
        $link = function ($row, Product $product, string $how) use (&$used, &$out) {
            if (in_array((int) $product->id, $used, true)) {
                return false;
            }
            $row->update(['product_id' => $product->id, 'quantity' => max(1, (int) $row->quantity), 'stock_confirmed' => false, 'link_source' => $how]);
            $used[] = (int) $product->id;
            $out['linked']++;
            $out['by'][(string) $row->ml_variation_id] = $how;
            return true;
        };

        if ($useCodes) {
            $norm = fn ($v) => ltrim(preg_replace('/\D/', '', (string) $v), '0');
            $withBarcode = null;
            foreach ($rows as $row) {
                if ($row->product_id) {
                    continue;
                }
                // 1) SKU
                if (filled($row->seller_sku)) {
                    $found = $base()->where(DB::raw('LOWER(TRIM(product_code))'), mb_strtolower(trim($row->seller_sku)))->get();
                    if ($found->count() === 1 && $link($row, $found->first(), 'sku')) {
                        continue;
                    }
                }
                // 2) GTIN
                $g = $norm($row->gtin);
                if ($g !== '') {
                    $withBarcode ??= $base()->whereNotNull('barcode')->where('barcode', '!=', '')->get(['id', 'barcode']);
                    $found = $withBarcode->filter(fn ($p) => $norm($p->barcode) === $g);
                    if ($found->count() === 1) {
                        $link($row, $base()->find($found->first()->id), 'gtin');
                    }
                }
            }
        }

        // 3) Cor/tamanho dentro de uma família de variações
        $pending = $rows->filter(fn ($r) => !$r->product_id && !empty($r->matchKeys()));
        if ($pending->isEmpty()) {
            return $out;
        }

        $variants = $base()->whereNotNull('parent_id')->whereNotNull('variation_value')->where('variation_value', '!=', '')
            ->get(['id', 'name', 'parent_id', 'variation_value']);
        if ($variants->isEmpty()) {
            return $out;
        }
        $byFamily = $variants->groupBy('parent_id');

        if ($familyRootId === null) {
            // Família do produto já ligado a alguma variação (a mais comum)
            $mappedIds = $rows->pluck('product_id')->filter()->all();
            if ($mappedIds) {
                $familyRootId = Product::withoutGlobalScope('team_visibility')->whereIn('id', $mappedIds)->get(['id', 'parent_id', 'is_variation_parent'])
                    ->map(fn ($p) => $p->parent_id ?? ($p->is_variation_parent ? $p->id : null))
                    ->filter()->countBy()->sortDesc()->keys()->first();
            }
        }

        $matchesIn = function ($members) use ($pending) {
            $res = [];
            foreach ($pending as $row) {
                $keys = $row->matchKeys();
                $c = $members->filter(fn ($p) => in_array(\App\Models\MlPublicationVariation::norm($p->variation_value), $keys, true));
                if ($c->count() === 1) {
                    $res[$row->id] = $c->first()->id;
                }
            }
            return $res;
        };

        if ($familyRootId === null) {
            // A família que mais casa (empate: desempata pelo nome do pai vs título)
            $titleWords = array_filter(explode(' ', self::normalizeTitle($publication->title)), fn ($w) => mb_strlen($w) >= 3);
            $parents = Product::withoutGlobalScope('team_visibility')->whereIn('id', $byFamily->keys())->pluck('name', 'id');
            $scores = [];
            foreach ($byFamily as $root => $members) {
                $m = count($matchesIn($members));
                if ($m === 0) {
                    continue;
                }
                $pw = explode(' ', self::normalizeTitle($parents[$root] ?? ''));
                $scores[$root] = $m * 100 + count(array_intersect($titleWords, $pw));
            }
            if (empty($scores)) {
                return $out;
            }
            arsort($scores);
            $top = array_slice($scores, 0, 2, true);
            if (count($top) === 2 && count(array_unique(array_values($top))) === 1) {
                return $out; // empate: não chuta
            }
            $familyRootId = (int) array_key_first($top);
        }

        $members = $byFamily->get($familyRootId, collect());
        $pairs = $matchesIn($members);
        // Mesmo produto para duas variações = ambíguo: não liga nenhuma das duas
        $dupes = array_keys(array_filter(array_count_values($pairs), fn ($n) => $n > 1));
        foreach ($pending as $row) {
            $pid = $pairs[$row->id] ?? null;
            if ($pid && !in_array($pid, $dupes, true)) {
                $product = $base()->find($pid);
                if ($product) {
                    $link($row, $product, 'cor');
                }
            }
        }

        return $out;
    }

    /**
     * Produtos que parecem ser a variação (ex.: "Nude" no variation_value ou no
     * nome), para sugerir na edição. Família preferida primeiro.
     */
    public function suggestProductsForVariation(int $userId, \App\Models\MlPublicationVariation $row, array $preferFamilies = [], array $excludeIds = [], int $limit = 4)
    {
        $keys = $row->matchKeys();
        if (empty($keys)) {
            return collect();
        }
        $words = collect($keys)->flatMap(fn ($k) => explode(' ', $k))->filter(fn ($w) => mb_strlen($w) >= 2)->unique()->values()->all();

        return Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $userId)
            ->where('status', 'ativo')
            ->where(fn ($q) => $q->whereNull('is_variation_parent')->orWhere('is_variation_parent', false))
            ->whereNotIn('id', $excludeIds)
            ->where(function ($q) use ($words) {
                foreach ($words as $w) {
                    $q->orWhere('variation_value', 'like', "%{$w}%")->orWhere('name', 'like', "%{$w}%");
                }
            })
            ->limit(60)
            ->get(['id', 'name', 'product_code', 'barcode', 'stock_quantity', 'price', 'image', 'tipo', 'parent_id', 'variation_value'])
            ->map(function ($p) use ($keys, $preferFamilies) {
                $vv = \App\Models\MlPublicationVariation::norm($p->variation_value);
                $nm = \App\Models\MlPublicationVariation::norm($p->name);
                $score = 0;
                if ($vv !== '' && in_array($vv, $keys, true)) {
                    $score += 10;
                }
                foreach ($keys as $k) {
                    if ($k !== '' && preg_match('/\b' . preg_quote($k, '/') . '\b/', $nm)) {
                        $score += 4;
                    }
                }
                if ($p->parent_id && in_array((int) $p->parent_id, $preferFamilies, true)) {
                    $score += 5;
                }
                $p->match_score = $score;
                return $p;
            })
            ->filter(fn ($p) => $p->match_score > 0)
            ->sortByDesc('match_score')
            ->take($limit)
            ->values();
    }

    /**
     * "Perfume X 100ml - Original!" e "perfume x original 100 ML" ficam iguais.
     */
    public static function normalizeTitle(?string $text): string
    {
        $t = mb_strtolower(Str::ascii((string) $text));
        $t = preg_replace('/(\d)\s+(ml|g|kg|l|un|cm|mm)\b/', '$1$2', $t);
        $t = preg_replace('/[^a-z0-9]+/', ' ', $t);
        $words = array_filter(explode(' ', $t), fn ($w) => $w !== '' && !in_array($w, ['de', 'da', 'do', 'e', 'com', 'para', 'o', 'a'], true));
        $words = array_values(array_unique($words));
        sort($words);
        return implode(' ', $words);
    }

    /**
     * Produtos do dono parecidos com o título do anúncio (para sugerir no "Ligar a um produto").
     */
    public function suggestProductsForTitle(int $userId, string $title, array $excludeIds = [], int $limit = 6)
    {
        $words = array_filter(explode(' ', self::normalizeTitle($title)), fn ($w) => mb_strlen($w) >= 3);
        if (empty($words)) {
            return collect();
        }

        return Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $userId)
            ->where('status', 'ativo')
            ->whereNotIn('id', $excludeIds)
            ->get(['id', 'name', 'product_code', 'barcode', 'stock_quantity', 'price', 'price_sale', 'image', 'tipo'])
            ->map(function ($p) use ($words) {
                $pw = explode(' ', self::normalizeTitle($p->name));
                $p->match_score = count(array_intersect($words, $pw)) / max(1, count(array_unique(array_merge($words, $pw))));
                return $p;
            })
            ->filter(fn ($p) => $p->match_score > 0)
            ->sortByDesc('match_score')
            ->take($limit)
            ->values();
    }
}
