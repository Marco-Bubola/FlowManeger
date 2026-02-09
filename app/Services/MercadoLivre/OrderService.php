<?php

namespace App\Services\MercadoLivre;

use App\Models\MercadoLivre\MercadoLivreOrder;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
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
     * Buscar pedidos do Mercado Livre
     * 
     * @param array $filters Filtros (status, date_from, date_to, limit)
     * @return array
     */
    public function getOrders(array $filters = []): array
    {
        try {
            $params = [];
            
            // Filtro de vendedor (seller)
            $params['seller'] = $this->getUserId();
            
            // Filtro de status
            if (!empty($filters['status'])) {
                $params['order.status'] = $filters['status'];
            }
            
            // Filtro de data inicial
            if (!empty($filters['date_from'])) {
                $params['order.date_created.from'] = Carbon::parse($filters['date_from'])->toIso8601String();
            }
            
            // Filtro de data final
            if (!empty($filters['date_to'])) {
                $params['order.date_created.to'] = Carbon::parse($filters['date_to'])->toIso8601String();
            }
            
            // Limite de resultados
            $params['limit'] = $filters['limit'] ?? 50;
            $params['offset'] = $filters['offset'] ?? 0;
            
            // Ordenação
            $params['sort'] = 'date_desc';
            
            $response = $this->makeRequest('GET', '/orders/search', $params);
            
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
            $response = $this->makeRequest('GET', "/orders/{$mlOrderId}");
            
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
            // Verificar se já foi importado
            $existingOrder = MercadoLivreOrder::where('ml_order_id', $mlOrderId)
                ->where('user_id', Auth::id())
                ->first();
            
            if ($existingOrder) {
                return [
                    'success' => false,
                    'message' => 'Pedido já foi importado anteriormente',
                    'order' => $existingOrder,
                ];
            }
            
            // Buscar detalhes do pedido no ML
            $orderData = $this->getOrderDetails($mlOrderId);
            
            if (!$orderData) {
                return [
                    'success' => false,
                    'message' => 'Não foi possível buscar os dados do pedido no ML',
                ];
            }
            
            DB::beginTransaction();
            
            try {
                // Criar ou buscar cliente
                $client = $this->getOrCreateClient($orderData['buyer']);
                
                // Criar venda no sistema
                $sale = $this->createSaleFromOrder($orderData, $client);
                
                // Registrar pedido ML
                $mlOrder = MercadoLivreOrder::create([
                    'user_id' => Auth::id(),
                    'sale_id' => $sale->id,
                    'ml_order_id' => $mlOrderId,
                    'status' => $orderData['status'],
                    'status_detail' => $orderData['status_detail'] ?? null,
                    'buyer' => json_encode($orderData['buyer']),
                    'shipping' => json_encode($orderData['shipping'] ?? []),
                    'payments' => json_encode($orderData['payments'] ?? []),
                    'total_amount' => $orderData['total_amount'],
                    'paid_amount' => $orderData['paid_amount'] ?? 0,
                    'currency_id' => $orderData['currency_id'],
                    'date_created' => Carbon::parse($orderData['date_created']),
                    'date_closed' => !empty($orderData['date_closed']) ? Carbon::parse($orderData['date_closed']) : null,
                    'last_updated' => !empty($orderData['last_updated']) ? Carbon::parse($orderData['last_updated']) : null,
                    'raw_data' => json_encode($orderData),
                ]);
                
                DB::commit();
                
                Log::info('Pedido ML importado com sucesso', [
                    'ml_order_id' => $mlOrderId,
                    'sale_id' => $sale->id,
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Pedido importado com sucesso!',
                    'order' => $mlOrder,
                    'sale' => $sale,
                ];
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
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
     * Criar ou buscar cliente baseado nos dados do comprador ML
     * 
     * @param array $buyerData Dados do comprador
     * @return Client
     */
    protected function getOrCreateClient(array $buyerData): Client
    {
        // Buscar cliente existente por email ou telefone
        $client = Client::where('user_id', Auth::id())
            ->where(function($query) use ($buyerData) {
                if (!empty($buyerData['email'])) {
                    $query->where('email', $buyerData['email']);
                }
                if (!empty($buyerData['phone']['number'])) {
                    $query->orWhere('phone', $buyerData['phone']['number']);
                }
            })
            ->first();
        
        if ($client) {
            return $client;
        }
        
        // Criar novo cliente
        $client = Client::create([
            'user_id' => Auth::id(),
            'name' => $buyerData['nickname'] ?? 'Cliente ML',
            'email' => $buyerData['email'] ?? null,
            'phone' => $buyerData['phone']['number'] ?? null,
            'cpf' => null, // ML não fornece CPF diretamente
            'notes' => 'Cliente importado do Mercado Livre - ID: ' . $buyerData['id'],
        ]);
        
        return $client;
    }
    
    /**
     * Criar venda no sistema baseada no pedido ML
     * 
     * @param array $orderData Dados do pedido ML
     * @param Client $client Cliente
     * @return Sale
     */
    protected function createSaleFromOrder(array $orderData, Client $client): Sale
    {
        $sale = Sale::create([
            'user_id' => Auth::id(),
            'client_id' => $client->id,
            'sale_date' => Carbon::parse($orderData['date_created']),
            'total_amount' => $orderData['total_amount'],
            'discount' => 0,
            'final_amount' => $orderData['paid_amount'] ?? $orderData['total_amount'],
            'payment_method' => $this->getPaymentMethodFromML($orderData['payments'] ?? []),
            'status' => $this->mapMLStatusToSaleStatus($orderData['status']),
            'notes' => 'Pedido importado do Mercado Livre - ID: ' . $orderData['id'],
        ]);
        
        // Adicionar itens da venda
        foreach ($orderData['order_items'] as $item) {
            $product = $this->findOrCreateProduct($item);
            
            $sale->items()->create([
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
            
            // Atualizar estoque
            if ($product && $orderData['status'] === 'paid') {
                $product->decrement('stock_quantity', $item['quantity']);
            }
        }
        
        return $sale;
    }
    
    /**
     * Encontrar ou criar produto baseado no item do pedido
     * 
     * @param array $itemData Dados do item
     * @return Product
     */
    protected function findOrCreateProduct(array $itemData): Product
    {
        // Buscar produto existente pelo ML Item ID
        $mlProduct = \App\Models\MercadoLivre\MercadoLivreProduct::where('ml_item_id', $itemData['item']['id'])
            ->where('user_id', Auth::id())
            ->first();
        
        if ($mlProduct && $mlProduct->product) {
            return $mlProduct->product;
        }
        
        // Criar produto genérico
        $product = Product::create([
            'user_id' => Auth::id(),
            'name' => $itemData['item']['title'],
            'product_code' => 'ML-' . $itemData['item']['id'],
            'price' => $itemData['unit_price'],
            'price_sale' => $itemData['unit_price'],
            'stock_quantity' => 0,
            'description' => 'Produto importado do Mercado Livre',
        ]);
        
        return $product;
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
     * Mapear status do ML para status de venda
     * 
     * @param string $mlStatus Status do ML
     * @return string
     */
    protected function mapMLStatusToSaleStatus(string $mlStatus): string
    {
        return match($mlStatus) {
            'paid' => 'completed',
            'confirmed' => 'completed',
            'pending' => 'pending',
            'cancelled' => 'cancelled',
            default => 'pending',
        };
    }
    
    /**
     * Atualizar status de envio no ML
     * 
     * @param string $mlOrderId ID do pedido
     * @param string $status Novo status
     * @param array $trackingData Dados de rastreamento
     * @return array
     */
    public function updateShippingStatus(string $mlOrderId, string $status, array $trackingData = []): array
    {
        try {
            $data = [
                'status' => $status,
            ];
            
            if (!empty($trackingData)) {
                $data['tracking_number'] = $trackingData['tracking_number'] ?? null;
                $data['tracking_method'] = $trackingData['tracking_method'] ?? null;
            }
            
            $response = $this->makeRequest('PUT', "/orders/{$mlOrderId}/shipments", $data);
            
            // Atualizar no banco de dados
            $mlOrder = MercadoLivreOrder::where('ml_order_id', $mlOrderId)
                ->where('user_id', Auth::id())
                ->first();
            
            if ($mlOrder) {
                $shipping = json_decode($mlOrder->shipping, true) ?? [];
                $shipping['status'] = $status;
                $shipping['tracking'] = $trackingData;
                $mlOrder->shipping = json_encode($shipping);
                $mlOrder->save();
            }
            
            return [
                'success' => true,
                'message' => 'Status de envio atualizado com sucesso',
                'response' => $response,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status de envio', [
                'ml_order_id' => $mlOrderId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage(),
            ];
        }
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
                'limit' => 100,
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
