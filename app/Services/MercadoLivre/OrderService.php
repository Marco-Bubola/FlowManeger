<?php

namespace App\Services\MercadoLivre;

use App\Models\MercadoLivreOrder;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\MlPublication;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service para gerenciar pedidos do Mercado Livre
 * 
 * Funcionalidades:
 * - Buscar pedidos do ML
 * - Importar pedidos para o sistema
 * - Sincronizar status de envio
 * - Converter pedidos ML em vendas internas
 */
class OrderService extends MercadoLivreService
{
    /**
     * Obtém o token ativo do usuário logado (para chamadas à API ML).
     *
     * @return \App\Models\MercadoLivreToken|null
     */
    protected function getToken(): ?\App\Models\MercadoLivreToken
    {
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }
        $authService = new AuthService();
        return $authService->getActiveToken($userId);
    }

    /**
     * Buscar pedidos do Mercado Livre
     * 
     * @param array $filters Filtros (status, date_from, date_to, limit)
     * @return array
     */
    public function getOrders(array $filters = []): array
    {
        try {
            $token = $this->getToken();
            if (!$token || !$token->ml_user_id) {
                return [
                    'success' => false,
                    'message' => 'Você precisa conectar sua conta do Mercado Livre para listar pedidos.',
                    'orders' => [],
                ];
            }

            $params = [];
            
            // Filtro de vendedor (seller) = ID do usuário no ML
            $params['seller'] = $token->ml_user_id;
            
            // Filtro de status
            if (!empty($filters['status'])) {
                $params['order.status'] = $filters['status'];
            }
            
            // Filtro de data inicial
            if (!empty($filters['date_from'])) {
                // Datas da tela são do horário de Brasília (dia inteiro).
                $params['order.date_created.from'] = Carbon::parse($filters['date_from'], 'America/Sao_Paulo')->startOfDay()->toIso8601String();
            }
            
            // Filtro de data final
            if (!empty($filters['date_to'])) {
                $params['order.date_created.to'] = Carbon::parse($filters['date_to'], 'America/Sao_Paulo')->endOfDay()->toIso8601String();
            }
            
            // Limite de resultados
            // O ML aceita no máximo 51 por página.
            $params['limit'] = min(50, max(1, (int) ($filters['limit'] ?? 50)));
            $params['offset'] = max(0, (int) ($filters['offset'] ?? 0));
            
            // Ordenação
            $params['sort'] = 'date_desc';
            
            $response = $this->makeRequest('GET', '/orders/search', $params, $token->access_token, Auth::id());
            
            return [
                'success' => true,
                'orders' => $response['results'] ?? [],
                'paging' => $response['paging'] ?? [],
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar pedidos do ML', [
                'error' => $e->getMessage(),
                'filters' => $filters,
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao buscar pedidos: ' . $e->getMessage(),
                'orders' => [],
            ];
        }
    }
    
    /**
     * Buscar detalhes de um pedido específico
     * 
     * @param string $mlOrderId ID do pedido no ML
     * @return array|null
     */
    public function getOrderDetails(string $mlOrderId): ?array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return null;
            }
            $response = $this->makeRequest('GET', "/orders/{$mlOrderId}", [], $token->access_token, Auth::id());

            // O pedido traz só o id do envio: busca status e endereço.
            $shippingId = $response['shipping']['id'] ?? null;
            if ($shippingId) {
                try {
                    $shipment = $this->makeRequest('GET', "/shipments/{$shippingId}", [], $token->access_token, Auth::id());
                    $response['shipping'] = array_merge($shipment, $response['shipping'] ?? []);
                } catch (\Exception $e) {
                    Log::warning('Envio do pedido ML não encontrado', ['shipping_id' => $shippingId, 'error' => $e->getMessage()]);
                }
            }

            return $response;
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes do pedido ML', [
                'ml_order_id' => $mlOrderId,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }
    
    /**
     * Importar pedido do ML para o sistema como venda
     * 
     * @param string $mlOrderId ID do pedido no ML
     * @return array
     */
    public function importOrder(string $mlOrderId): array
    {
        try {
            // Já virou venda antes?
            $existingOrder = MercadoLivreOrder::where('ml_order_id', $mlOrderId)
                ->whereNotNull('imported_to_sale_id')
                ->first();

            if ($existingOrder && Sale::whereKey($existingOrder->imported_to_sale_id)->exists()) {
                return [
                    'success' => false,
                    'message' => 'Pedido já foi importado anteriormente (venda #' . $existingOrder->imported_to_sale_id . ')',
                    'order' => $existingOrder,
                ];
            }

            $orderData = $this->getOrderDetails($mlOrderId);

            if (!$orderData) {
                return [
                    'success' => false,
                    'message' => 'Não foi possível buscar os dados do pedido no ML',
                ];
            }

            if (($orderData['status'] ?? '') === 'cancelled') {
                return [
                    'success' => false,
                    'message' => 'Pedido cancelado no Mercado Livre, não vira venda.',
                ];
            }

            $sale = DB::transaction(function () use ($orderData, $mlOrderId) {
                $client = $this->getOrCreateClient($orderData['buyer'] ?? []);
                $sale = $this->createSaleFromOrder($orderData, $client);

                $this->recordOrder($orderData, $sale->id, $sale->saleItems()->value('product_id'));

                return $sale;
            });

            Log::info('Pedido ML importado com sucesso', [
                'ml_order_id' => $mlOrderId,
                'sale_id' => $sale->id,
            ]);

            return [
                'success' => true,
                'message' => 'Pedido importado como venda #' . $sale->id . '!',
                'order' => MercadoLivreOrder::where('ml_order_id', $mlOrderId)->first(),
                'sale' => $sale,
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao importar pedido do ML', [
                'ml_order_id' => $mlOrderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao importar pedido: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Grava/atualiza o pedido do ML na tabela local (valor, comprador, status).
     * Mantém a ligação com a venda se já existir.
     */
    public function recordOrder(array $orderData, ?int $saleId = null, ?int $productId = null): MercadoLivreOrder
    {
        $item = $orderData['order_items'][0] ?? [];
        $buyer = $orderData['buyer'] ?? [];
        $payment = $orderData['payments'][0] ?? [];
        $shipping = $orderData['shipping'] ?? [];

        $attrs = [
            'ml_item_id' => $item['item']['id'] ?? null,
            'buyer_id' => $buyer['id'] ?? null,
            'buyer_nickname' => $buyer['nickname'] ?? null,
            'buyer_email' => $buyer['email'] ?? null,
            'buyer_phone' => $buyer['phone']['number'] ?? null,
            'quantity' => (int) collect($orderData['order_items'] ?? [])->sum('quantity'),
            'unit_price' => $item['unit_price'] ?? 0,
            'total_amount' => $orderData['total_amount'] ?? 0,
            'currency_id' => $orderData['currency_id'] ?? 'BRL',
            'order_status' => $orderData['status'] ?? null,
            'payment_status' => $payment['status'] ?? null,
            'payment_method' => $payment['payment_method_id'] ?? null,
            'payment_type' => $payment['payment_type'] ?? null,
            'shipping_id' => $shipping['id'] ?? null,
            'tracking_number' => $shipping['tracking_number'] ?? null,
            'shipping_cost' => $shipping['shipping_option']['cost'] ?? ($shipping['lead_time']['cost'] ?? null),
            'date_created' => !empty($orderData['date_created']) ? Carbon::parse($orderData['date_created']) : now(),
            'date_closed' => !empty($orderData['date_closed']) ? Carbon::parse($orderData['date_closed']) : null,
            'date_last_updated' => !empty($orderData['last_updated']) ? Carbon::parse($orderData['last_updated']) : now(),
            'sync_status' => 'processed',
        ];
        if ($saleId) {
            $attrs['imported_to_sale_id'] = $saleId;
        }
        if ($productId) {
            $attrs['product_id'] = $productId;
        }

        $order = MercadoLivreOrder::updateOrCreate(['ml_order_id' => (string) $orderData['id']], $attrs);

        // Tarifa de venda do ML (sale_fee é por unidade). Só grava quando veio no pedido.
        $fees = collect($orderData['order_items'] ?? [])->filter(fn ($i) => isset($i['sale_fee']) && is_numeric($i['sale_fee']));
        if ($fees->isNotEmpty() && \Illuminate\Support\Facades\Schema::hasColumn('mercadolivre_orders', 'fee_amount')) {
            $order->forceFill([
                'fee_amount' => round($fees->sum(fn ($i) => (float) $i['sale_fee'] * max(1, (int) ($i['quantity'] ?? 1))), 2),
            ])->save();
        }

        return $order;
    }

    /**
     * Criar ou buscar cliente baseado nos dados do comprador ML
     */
    protected function getOrCreateClient(array $buyerData): Client
    {
        $email = trim((string) ($buyerData['email'] ?? ''));
        $phone = trim((string) ($buyerData['phone']['number'] ?? ''));
        $fullName = trim(($buyerData['first_name'] ?? '') . ' ' . ($buyerData['last_name'] ?? ''));
        $name = $fullName !== '' ? $fullName : ($buyerData['nickname'] ?? 'Cliente ML');

        // Só procura por dados que existem (sem e-mail e telefone, a busca
        // antiga pegava qualquer cliente).
        $client = null;
        if ($email !== '') {
            $client = Client::where('user_id', Auth::id())->where('email', $email)->first();
        }
        if (!$client && $phone !== '') {
            $client = Client::where('user_id', Auth::id())->where('phone', $phone)->first();
        }
        if (!$client) {
            $client = Client::where('user_id', Auth::id())->where('name', $name)->first();
        }

        return $client ?? Client::create([
            'user_id' => Auth::id(),
            'name' => mb_substr($name, 0, 100),
            'email' => $email !== '' ? $email : null,
            'phone' => $phone !== '' ? $phone : null,
        ]);
    }

    /**
     * Criar venda no sistema baseada no pedido ML.
     *
     * O estoque segue o mesmo caminho do webhook (uma baixa por pedido,
     * registrada no histórico do ML): se o webhook já baixou, não baixa de novo.
     */
    protected function createSaleFromOrder(array $orderData, Client $client): Sale
    {
        $paid = in_array($orderData['status'] ?? '', ['paid', 'confirmed'], true);
        $total = (float) ($orderData['total_amount'] ?? 0);
        $date = !empty($orderData['date_created']) ? Carbon::parse($orderData['date_created'])->setTimezone(config('app.timezone')) : now();
        $method = $this->getPaymentMethodFromML($orderData['payments'] ?? []);

        $sale = Sale::create([
            'user_id' => Auth::id(),
            'client_id' => $client->id,
            'total_price' => $total,
            'amount_paid' => $paid ? $total : 0,
            'status' => $paid ? 'pago' : 'pendente',
            'payment_method' => $method,
            'tipo_pagamento' => 'a_vista',
            'parcelas' => 1,
            'source' => 'mercadolivre',
        ]);

        $sale->timestamps = false;
        $sale->created_at = $date;
        $sale->updated_at = $date;
        $sale->save();
        $sale->timestamps = true;

        $mlOrderId = (string) $orderData['id'];
        $stockService = app(MlStockSyncService::class);

        foreach ($orderData['order_items'] ?? [] as $item) {
            $mlItemId = (string) ($item['item']['id'] ?? '');
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $unit = (float) ($item['unit_price'] ?? 0);

            $publication = MlPublication::where('ml_item_id', $mlItemId)
                ->where('user_id', Auth::id())
                ->first();
            $variationId = $item['item']['variation_id'] ?? null;
            $variation = $publication ? $publication->mappedVariation($variationId) : null;

            // Variação ligada a um produto: a venda é desse produto só.
            if ($variation) {
                $product = $variation->product;
                $perPub = $variation->perSale();
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty * $perPub,
                    'price' => $product->price,
                    'price_sale' => round($unit / $perPub, 2),
                ]);

                $result = $stockService->processMercadoLivreSale($mlOrderId, $mlItemId, $qty, $variationId);
                if (!($result['success'] ?? false)) {
                    Log::warning('Importar pedido ML: baixa pela variação falhou, baixando direto', [
                        'ml_order_id' => $mlOrderId,
                        'error' => $result['message'] ?? null,
                    ]);
                    $product->adjustStock(-$qty * $perPub);
                }
                continue;
            }

            $pubProducts = $publication
                ? $publication->products()->withoutGlobalScope('team_visibility')->get()
                : collect();

            if ($pubProducts->isNotEmpty()) {
                // Publicação com 1 ou mais produtos: divide o valor pelo preço de cada um.
                $weights = $pubProducts->map(fn ($p) => max(0.01, (float) $p->price_sale) * max(1, (int) $p->pivot->quantity));
                $sum = $weights->sum();
                foreach ($pubProducts->values() as $i => $product) {
                    $perPub = max(1, (int) $product->pivot->quantity);
                    $share = $unit * ($weights[$i] / $sum);
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty * $perPub,
                        'price' => $product->price,
                        'price_sale' => round($share / $perPub, 2),
                    ]);
                }

                $result = $stockService->processMercadoLivreSale($mlOrderId, $mlItemId, $qty, $variationId);
                if (!($result['success'] ?? false)) {
                    Log::warning('Importar pedido ML: baixa pela publicação falhou, baixando direto', [
                        'ml_order_id' => $mlOrderId,
                        'error' => $result['message'] ?? null,
                    ]);
                    foreach ($pubProducts as $product) {
                        $product->update(['stock_quantity' => max(0, (int) $product->stock_quantity - $qty * max(1, (int) $product->pivot->quantity))]);
                    }
                }
                continue;
            }

            $product = $this->findOrCreateProduct($item);
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $product->price,
                'price_sale' => $unit,
            ]);
            $product->update(['stock_quantity' => max(0, (int) $product->stock_quantity - $qty)]);
        }

        // O estoque já saiu acima; marca para não baixar de novo na quitação.
        $sale->forceFill(['stock_applied' => true])->save();

        if ($paid) {
            SalePayment::create([
                'sale_id' => $sale->id,
                'amount_paid' => $total,
                'payment_method' => $method,
                'payment_date' => $date->format('Y-m-d'),
            ]);
        }

        return $sale;
    }

    /**
     * Encontrar ou criar produto baseado no item do pedido
     */
    protected function findOrCreateProduct(array $itemData): Product
    {
        $mlItemId = (string) ($itemData['item']['id'] ?? '');

        if (Schema::hasTable('mercadolivre_products')) {
            $mlProduct = \App\Models\MercadoLivreProduct::where('ml_item_id', $mlItemId)->first();
            $linked = $mlProduct ? Product::where('user_id', Auth::id())->find($mlProduct->product_id) : null;
            if ($linked) {
                return $linked;
            }
        }

        $code = 'ML-' . $mlItemId;
        $existing = Product::where('user_id', Auth::id())->where('product_code', $code)->first();
        if ($existing) {
            return $existing;
        }

        // Produto sem ligação: cria um registro simples na categoria mais usada.
        $categoryId = Product::where('user_id', Auth::id())
            ->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->value('category_id');

        if (!$categoryId) {
            throw new \RuntimeException('O anúncio "' . ($itemData['item']['title'] ?? $mlItemId) . '" não está ligado a nenhum produto. Ligue a publicação a um produto e tente de novo.');
        }

        return Product::create([
            'user_id' => Auth::id(),
            'category_id' => $categoryId,
            'name' => $itemData['item']['title'] ?? $code,
            'product_code' => $code,
            'price' => (float) ($itemData['unit_price'] ?? 0),
            'price_sale' => (float) ($itemData['unit_price'] ?? 0),
            'stock_quantity' => 0,
            'description' => 'Produto importado do Mercado Livre',
        ]);
    }
    
    /**
     * Mapear método de pagamento do ML
     * 
     * @param array $payments Pagamentos
     * @return string
     */
    protected function getPaymentMethodFromML(array $payments): string
    {
        if (empty($payments)) {
            return 'mercadopago';
        }
        
        $payment = $payments[0];
        
        return match($payment['payment_type'] ?? 'other') {
            'credit_card' => 'cartao_credito',
            'debit_card' => 'cartao_debito',
            'ticket' => 'boleto',
            'bank_transfer' => 'transferencia',
            default => 'mercadopago',
        };
    }
    
    /**
     * Sincronizar pedidos recentes
     * 
     * @param string|null $dateFrom Data inicial (padrão: últimas 24h)
     * @return array
     */
    public function syncOrders(?string $dateFrom = null): array
    {
        try {
            $dateFrom = $dateFrom ?? Carbon::now()->subDay()->toIso8601String();
            
            $orders = $this->getOrders([
                'date_from' => $dateFrom,
                'limit' => 50,
            ]);
            
            if (!$orders['success']) {
                return $orders;
            }
            
            $imported = 0;
            $skipped = 0;
            $errors = [];
            
            foreach ($orders['orders'] as $orderData) {
                $result = $this->importOrder($orderData['id']);
                
                if ($result['success']) {
                    $imported++;
                } else {
                    if (str_contains($result['message'], 'já foi importado')) {
                        $skipped++;
                    } else {
                        $errors[] = [
                            'order_id' => $orderData['id'],
                            'error' => $result['message'],
                        ];
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => "Sincronização concluída: {$imported} importados, {$skipped} já existentes",
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar pedidos', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao sincronizar pedidos: ' . $e->getMessage(),
            ];
        }
    }
}
