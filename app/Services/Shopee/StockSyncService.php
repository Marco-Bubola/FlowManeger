<?php

namespace App\Services\Shopee;

use App\Models\ShopeeOrder;
use App\Models\ShopeePublication;
use App\Models\Product;
use App\Models\MlStockLog;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * Serviço de sincronização de estoque multi-canal.
 *
 * Ao receber um pedido da Shopee (webhook ou importação), este serviço:
 * 1. Grava/atualiza o pedido (status, valores, itens, tarifas)
 * 2. Baixa o estoque UMA vez por pedido (todos os itens; kit baixa os componentes)
 * 3. Devolve o estoque se o pedido for cancelado
 * 4. Dispara atualização de estoque em TODOS os canais vinculados
 */
class StockSyncService extends ShopeeService
{
    /** Status em que o pedido já está pago e deve baixar estoque. */
    /**
     * Pedidos feitos antes desta data não baixam estoque ao serem importados:
     * a versão antiga nunca baixava e o estoque já foi acertado à mão.
     */
    public const STOCK_SINCE = '2026-10-09 02:00:00';

    public const PAID_STATUSES = [
        'READY_TO_SHIP', 'PROCESSED', 'RETRY_SHIP', 'SHIPPED',
        'TO_CONFIRM_RECEIVE', 'COMPLETED', 'TO_RETURN', 'INVOICE_PENDING',
    ];

    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    // =========================================================================
    // Pedido Shopee completo (get_order_detail)
    // =========================================================================

    /**
     * Grava/atualiza um pedido vindo do get_order_detail e acerta o estoque.
     *
     * @param array      $order     Um item de response.order_list do get_order_detail
     * @param int        $userId    Dono da loja
     * @param string     $shopId    Shop ID
     * @param float|null $feeAmount Tarifas (get_escrow_detail); null = não informado
     */
    public function syncOrder(array $order, int $userId, string $shopId = '', ?float $feeAmount = null): array
    {
        $orderSn = (string) ($order['order_sn'] ?? '');
        if ($orderSn === '') {
            return ['success' => false, 'message' => 'order_sn ausente.'];
        }

        $status = (string) ($order['order_status'] ?? 'UNPAID');
        $items  = $order['item_list'] ?? [];

        $data = [
            'user_id'          => $userId,
            'shop_id'          => $shopId,
            'shopee_item_id'   => isset($items[0]['item_id']) ? (string) $items[0]['item_id'] : null,
            'shopee_model_id'  => !empty($items[0]['model_id']) ? (string) $items[0]['model_id'] : null,
            'buyer_username'   => $order['buyer_username'] ?? null,
            'shipping_address' => $order['recipient_address'] ?? null,
            'order_items'      => $items,
            'total_amount'     => (float) ($order['total_amount'] ?? 0),
            'currency'         => $order['currency'] ?? 'BRL',
            'order_status'     => $status,
            'payment_method'   => isset($order['payment_method']) ? Str::limit((string) $order['payment_method'], 50, '') : null,
            'shipping_carrier' => isset($order['shipping_carrier']) ? Str::limit((string) $order['shipping_carrier'], 100, '') : null,
            'days_to_ship'     => isset($order['days_to_ship']) ? min(255, (int) $order['days_to_ship']) : null,
            'ship_by_date'     => !empty($order['ship_by_date']) ? Carbon::createFromTimestamp((int) $order['ship_by_date']) : null,
            'raw_data'         => $order,
            'shopee_created_at'=> !empty($order['create_time']) ? Carbon::createFromTimestamp((int) $order['create_time']) : now(),
            'shopee_updated_at'=> !empty($order['update_time']) ? Carbon::createFromTimestamp((int) $order['update_time']) : now(),
        ];
        if ($feeAmount !== null) {
            $data['fee_amount'] = round($feeAmount, 2); // não apaga um valor já salvo
        }
        if (!empty($order['package_list'][0]['tracking_number'] ?? null)) {
            $data['tracking_number'] = Str::limit((string) $order['package_list'][0]['tracking_number'], 100, '');
        }

        return $this->applyOrder($orderSn, $userId, $status, $items, $data);
    }

    /**
     * Compatibilidade: venda de um único item (ex.: payload simples).
     */
    public function processShopeeOrder(
        string  $orderSn,
        string  $shopeeItemId,
        ?string $shopeeModelId,
        int     $quantity,
        int     $userId,
        array   $rawData = []
    ): array {
        $items = $rawData['item_list'] ?? [[
            'item_id'                  => $shopeeItemId,
            'model_id'                 => $shopeeModelId ?: 0,
            'model_quantity_purchased' => $quantity,
        ]];

        return $this->syncOrder(array_merge($rawData, [
            'order_sn'     => $orderSn,
            'order_status' => $rawData['order_status'] ?? $rawData['status'] ?? 'READY_TO_SHIP',
            'item_list'    => $items,
        ]), $userId, (string) ($rawData['shop_id'] ?? ''));
    }

    /**
     * Grava o pedido e acerta o estoque com a linha travada (pushes repetidos
     * ou simultâneos da Shopee não baixam duas vezes).
     */
    private function applyOrder(string $orderSn, int $userId, string $status, array $items, array $data): array
    {
        $this->ensureOrderRow($orderSn, $data);
        if (($data['shop_id'] ?? '') === '') {
            unset($data['shop_id']); // não apaga o shop_id já gravado
        }

        $restored = 0;
        $touched = collect();
        $message = "Pedido {$orderSn} atualizado ({$status}).";

        try {
            $order = DB::transaction(function () use ($orderSn, $userId, $status, $items, $data, &$touched, &$message, &$restored) {
                $order = ShopeeOrder::where('shopee_order_sn', $orderSn)->lockForUpdate()->firstOrFail();

                if ((int) $order->user_id !== $userId) {
                    throw new Exception("Pedido {$orderSn} pertence a outro usuário.");
                }

                // Não volta o status de um pedido já cancelado por push atrasado
                if ($order->order_status === 'CANCELLED' && $status !== 'CANCELLED') {
                    unset($data['order_status']);
                }
                $order->fill($data);

                if ($status === 'CANCELLED') {
                    // Só devolve o que ainda não foi devolvido (rolled_back)
                    $logs = MlStockLog::restoreOrder($orderSn, 'shopee_sale', 'Pedido Shopee');
                    $restored = $logs->count();
                    if ($restored > 0) {
                        $touched = Product::withoutGlobalScope('team_visibility')
                            ->whereIn('id', $logs->pluck('product_id')->unique())->get();
                    }
                    $message = "Pedido cancelado: {$restored} item(ns) devolvido(s) ao estoque.";
                    // Marca como tratado: um push atrasado de "pago" não baixa mais
                    $order->stock_processed_at = $order->stock_processed_at ?? now();
                } elseif (in_array($status, self::PAID_STATUSES, true) && !$order->stock_processed_at
                    && $order->shopee_created_at && $order->shopee_created_at->lt(\Carbon\Carbon::parse(self::STOCK_SINCE, 'UTC'))) {
                    // Pedido antigo: só registra, sem mexer no estoque.
                    $order->stock_processed_at = now();
                    $order->sync_status = 'synced';
                    $message = 'Pedido anterior à baixa automática: registrado sem mexer no estoque.';
                } elseif (in_array($status, self::PAID_STATUSES, true) && !$order->stock_processed_at) {
                    [$touched, $missing] = $this->deductOrderItems($items, $userId, $orderSn);
                    $order->stock_processed_at = now();
                    $order->sync_status   = $missing ? 'error' : 'synced';
                    $order->error_message = $missing
                        ? 'Anúncio(s) sem produto vinculado no sistema: ' . implode(', ', $missing)
                        : null;
                    $message = $missing
                        ? 'Pedido salvo; estoque não baixado para item(ns) sem vínculo: ' . implode(', ', $missing)
                        : 'Venda Shopee processada e estoque atualizado.';
                }

                $order->save();
                return $order;
            });
        } catch (Exception $e) {
            Log::error('StockSyncService: erro ao processar pedido Shopee', [
                'order_sn' => $orderSn,
                'error'    => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }

        // Fora da transação: dispara a sincronização dos canais
        if ($touched->isNotEmpty()) {
            try {
                $this->propagateStockUpdate($touched, $userId);
            } catch (\Throwable $e) {
                Log::warning('StockSyncService: falha ao propagar estoque', ['order_sn' => $orderSn, 'error' => $e->getMessage()]);
            }
        }

        return ['success' => true, 'message' => $message, 'order' => $order, 'restored' => $restored];
    }

    /**
     * Cria a linha do pedido se ainda não existir (tolera corrida no unique).
     */
    private function ensureOrderRow(string $orderSn, array $data): void
    {
        if (ShopeeOrder::where('shopee_order_sn', $orderSn)->exists()) {
            return;
        }
        try {
            ShopeeOrder::create(array_merge(['shop_id' => ''], $data, [
                'shopee_order_sn' => $orderSn,
                'order_status'    => $data['order_status'] ?? 'UNPAID',
                'sync_status'     => 'pending',
            ]));
        } catch (QueryException $e) {
            // Outro processo criou ao mesmo tempo: segue com a linha existente
            if (!ShopeeOrder::where('shopee_order_sn', $orderSn)->exists()) {
                throw $e;
            }
        }
    }

    // =========================================================================
    // Cancelamento
    // =========================================================================

    /**
     * Pedido Shopee cancelado: devolve o estoque baixado por ele (uma vez só).
     */
    public function restoreShopeeOrder(string $orderSn, int $userId): int
    {
        $result = $this->applyOrder($orderSn, $userId, 'CANCELLED', [], [
            'user_id'      => $userId,
            'order_status' => 'CANCELLED',
        ]);

        return (int) ($result['restored'] ?? 0);
    }

    // =========================================================================
    // Propagação de estoque para todos os canais
    // =========================================================================

    /**
     * Dado um conjunto de produtos internos que tiveram estoque alterado,
     * dispara jobs de sincronização para cada publicação ativa em todos os canais.
     * Inclui os kits que usam esses produtos como componente.
     *
     * @param \Illuminate\Support\Collection $products
     * @param int $userId
     */
    public function propagateStockUpdate($products, int $userId): void
    {
        $ids = collect($products)->pluck('id')->filter()->unique();
        if ($ids->isEmpty()) {
            return;
        }

        $kitIds = \App\Models\ProdutoComponente::whereIn('componente_produto_id', $ids)->pluck('kit_produto_id');
        $allIds = $ids->merge($kitIds)->unique()->values();

        // ML: a própria publicação já tem a regra de kit
        $mlPublications = \App\Models\MlPublication::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->whereHas('products', fn ($q) => $q->withoutGlobalScope('team_visibility')->whereIn('products.id', $allIds))
            ->where('sync_status', '!=', 'error')
            ->whereNotNull('ml_item_id')
            ->get();

        foreach ($mlPublications as $mlPub) {
            \App\Jobs\SyncPublicationToMercadoLivre::dispatch($mlPub)->onQueue('marketplace');
        }

        $shopeePublications = ShopeePublication::where('user_id', $userId)
            ->whereHas('products', fn ($q) => $q->withoutGlobalScope('team_visibility')->whereIn('products.id', $allIds))
            ->whereNotNull('shopee_item_id')
            ->get();

        foreach ($shopeePublications as $shopeePub) {
            \App\Jobs\SyncPublicationToShopee::dispatch($shopeePub)->onQueue('marketplace');
        }
    }

    // =========================================================================
    // Helpers internos
    // =========================================================================

    /**
     * Baixa o estoque de todos os itens do pedido.
     *
     * @return array [Collection $produtosAlterados, string[] $itensSemVinculo]
     */
    private function deductOrderItems(array $items, int $userId, string $orderSn): array
    {
        $touched = collect();
        $missing = [];
        $transactionId = (string) Str::uuid();

        foreach ($items as $item) {
            $itemId  = (string) ($item['item_id'] ?? '');
            $modelId = (string) ($item['model_id'] ?? '');
            $qty     = (int) ($item['model_quantity_purchased'] ?? $item['quantity'] ?? 1);
            if ($itemId === '' || $qty <= 0) {
                continue;
            }

            $publication = ShopeePublication::where('shopee_item_id', $itemId)
                ->where('user_id', $userId)
                ->first();

            if (!$publication) {
                Log::warning('StockSyncService: publicação Shopee não encontrada', [
                    'shopee_item_id' => $itemId,
                    'order_sn'       => $orderSn,
                ]);
                $missing[] = $itemId;
                continue;
            }

            $products = $publication->linkedProducts();

            // Variação: só o produto mapeado ao model_id (model_id 0 = sem variação)
            if ($modelId !== '' && $modelId !== '0') {
                $byModel = $products->filter(fn ($p) => (string) $p->pivot->shopee_model_id === $modelId);
                if ($byModel->isNotEmpty() || $publication->has_variations) {
                    $products = $byModel;
                }
            }

            if ($products->isEmpty()) {
                $missing[] = $itemId . ($modelId && $modelId !== '0' ? "/{$modelId}" : '');
                continue;
            }

            foreach ($products as $product) {
                $perUnit = max(1, (int) ($product->pivot->quantity ?? 1));
                $total   = $qty * $perUnit;

                // Kit não tem estoque próprio: baixa dos componentes
                $targets = $product->isKit()
                    ? array_map(fn ($c) => [$c[0], $c[1] * $total], ShopeePublication::kitComponents($product))
                    : [[$product, $total]];

                foreach ($targets as [$target, $deduct]) {
                    $target = Product::withoutGlobalScope('team_visibility')->lockForUpdate()->find($target->id);
                    if (!$target) {
                        continue;
                    }
                    $before = (int) $target->stock_quantity;
                    $after  = max(0, $before - $deduct);
                    $target->update(['stock_quantity' => $after]);

                    MlStockLog::create([
                        'product_id'        => $target->id,
                        'ml_publication_id' => null,
                        'operation_type'    => 'shopee_sale',
                        'quantity_before'   => $before,
                        'quantity_after'    => $after,
                        // o que saiu de fato (é o que volta no cancelamento)
                        'quantity_change'   => $after - $before,
                        'source'            => 'shopee',
                        'ml_order_id'       => $orderSn,
                        'notes'             => "Venda Shopee - pedido {$orderSn}"
                            . ($target->id !== $product->id ? " (componente do kit {$product->id})" : ''),
                        'transaction_id'    => $transactionId,
                        'user_id'           => $userId,
                        'created_at'        => now(),
                    ]);

                    $touched->push($target);
                }
                $touched->push($product);
            }
        }

        return [$touched->unique('id')->values(), $missing];
    }
}
