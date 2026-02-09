<?php

namespace App\Services\MercadoLivre;

use App\Models\Product;
use App\Models\MercadoLivre\MercadoLivreProduct;
use App\Models\MercadoLivre\MercadoLivreSync;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service para sincronização automática entre sistema e Mercado Livre
 * 
 * Sincroniza:
 * - Estoque
 * - Preços
 * - Status de produtos
 * - Pedidos
 */
class SyncService extends MercadoLivreService
{
    protected ProductService $productService;
    protected OrderService $orderService;
    
    public function __construct()
    {
        parent::__construct();
        $this->productService = new ProductService();
        $this->orderService = new OrderService();
    }
    
    /**
     * Sincronizar todos os produtos publicados
     * 
     * @return array
     */
    public function syncAllProducts(): array
    {
        try {
            $stats = [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'errors' => [],
            ];
            
            // Buscar produtos publicados no ML
            $mlProducts = MercadoLivreProduct::where('status', '!=', 'closed')
                ->whereNotNull('ml_item_id')
                ->get();
            
            $stats['total'] = $mlProducts->count();
            
            foreach ($mlProducts as $mlProduct) {
                try {
                    $result = $this->syncProduct($mlProduct->product_id);
                    
                    if ($result['success']) {
                        $stats['success']++;
                    } else {
                        $stats['failed']++;
                        $stats['errors'][] = [
                            'product_id' => $mlProduct->product_id,
                            'error' => $result['message'] ?? 'Erro desconhecido',
                        ];
                    }
                    
                    // Rate limiting: aguardar 500ms entre requests
                    usleep(500000);
                    
                } catch (\Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = [
                        'product_id' => $mlProduct->product_id,
                        'error' => $e->getMessage(),
                    ];
                    
                    Log::error('Erro ao sincronizar produto', [
                        'product_id' => $mlProduct->product_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Registrar sincronização
            $this->logSync('products', 'full', $stats);
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar todos os produtos', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao sincronizar produtos: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Sincronizar um produto específico
     * 
     * @param int $productId
     * @return array
     */
    public function syncProduct(int $productId): array
    {
        try {
            $product = Product::find($productId);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ];
            }
            
            $mlProduct = $product->mercadoLivreProduct;
            
            if (!$mlProduct || !$mlProduct->ml_item_id) {
                return [
                    'success' => false,
                    'message' => 'Produto não está publicado no ML',
                ];
            }
            
            // Buscar dados atuais no ML
            $mlData = $this->makeRequest('GET', "/items/{$mlProduct->ml_item_id}");
            
            if (!$mlData) {
                return [
                    'success' => false,
                    'message' => 'Erro ao buscar produto no ML',
                ];
            }
            
            $updates = [];
            
            // Verificar se precisa atualizar estoque
            if ($mlData['available_quantity'] != $product->stock_quantity) {
                $updates['available_quantity'] = $product->stock_quantity;
            }
            
            // Verificar se precisa atualizar preço
            if ($mlData['price'] != $product->sale_price) {
                $updates['price'] = $product->sale_price;
            }
            
            // Se não há atualizações, retornar
            if (empty($updates)) {
                return [
                    'success' => true,
                    'message' => 'Produto já está sincronizado',
                    'changes' => 0,
                ];
            }
            
            // Atualizar no ML
            $result = $this->makeRequest('PUT', "/items/{$mlProduct->ml_item_id}", $updates);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar produto no ML',
                ];
            }
            
            // Atualizar registro local
            $mlProduct->update([
                'price' => $result['price'],
                'available_quantity' => $result['available_quantity'],
                'sold_quantity' => $result['sold_quantity'],
                'last_updated' => Carbon::now(),
            ]);
            
            // Registrar sincronização
            $this->logSync('product', 'update', [
                'product_id' => $productId,
                'changes' => $updates,
            ]);
            
            return [
                'success' => true,
                'message' => 'Produto sincronizado com sucesso',
                'changes' => count($updates),
                'updates' => $updates,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar produto', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao sincronizar produto: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Sincronizar apenas estoque de um produto
     * 
     * @param int $productId
     * @return array
     */
    public function syncProductStock(int $productId): array
    {
        try {
            $product = Product::find($productId);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ];
            }
            
            $mlProduct = $product->mercadoLivreProduct;
            
            if (!$mlProduct || !$mlProduct->ml_item_id) {
                return [
                    'success' => false,
                    'message' => 'Produto não está publicado no ML',
                ];
            }
            
            // Atualizar estoque no ML
            $result = $this->makeRequest('PUT', "/items/{$mlProduct->ml_item_id}", [
                'available_quantity' => $product->stock_quantity,
            ]);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar estoque no ML',
                ];
            }
            
            // Atualizar registro local
            $mlProduct->update([
                'available_quantity' => $result['available_quantity'],
                'last_updated' => Carbon::now(),
            ]);
            
            return [
                'success' => true,
                'message' => 'Estoque sincronizado com sucesso',
                'stock' => $result['available_quantity'],
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar estoque', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao sincronizar estoque: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Sincronizar apenas preço de um produto
     * 
     * @param int $productId
     * @return array
     */
    public function syncProductPrice(int $productId): array
    {
        try {
            $product = Product::find($productId);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ];
            }
            
            $mlProduct = $product->mercadoLivreProduct;
            
            if (!$mlProduct || !$mlProduct->ml_item_id) {
                return [
                    'success' => false,
                    'message' => 'Produto não está publicado no ML',
                ];
            }
            
            // Atualizar preço no ML
            $result = $this->makeRequest('PUT', "/items/{$mlProduct->ml_item_id}", [
                'price' => $product->sale_price,
            ]);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar preço no ML',
                ];
            }
            
            // Atualizar registro local
            $mlProduct->update([
                'price' => $result['price'],
                'last_updated' => Carbon::now(),
            ]);
            
            return [
                'success' => true,
                'message' => 'Preço sincronizado com sucesso',
                'price' => $result['price'],
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar preço', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao sincronizar preço: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Sincronizar pedidos do Mercado Livre
     * 
     * @param string|null $dateFrom Data inicial (formato Y-m-d)
     * @return array
     */
    public function syncOrders(?string $dateFrom = null): array
    {
        try {
            $result = $this->orderService->syncOrders($dateFrom);
            
            // Registrar sincronização
            $this->logSync('orders', 'import', $result);
            
            return $result;
            
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
    
    /**
     * Sincronização completa (produtos + pedidos)
     * 
     * @return array
     */
    public function fullSync(): array
    {
        try {
            $results = [
                'timestamp' => Carbon::now()->toDateTimeString(),
                'products' => [],
                'orders' => [],
            ];
            
            // Sincronizar produtos
            Log::info('Iniciando sincronização completa - Produtos');
            $results['products'] = $this->syncAllProducts();
            
            // Sincronizar pedidos das últimas 24 horas
            Log::info('Iniciando sincronização completa - Pedidos');
            $results['orders'] = $this->syncOrders();
            
            // Registrar sincronização completa
            $this->logSync('full', 'complete', $results);
            
            return [
                'success' => true,
                'message' => 'Sincronização completa realizada',
                'results' => $results,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro na sincronização completa', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro na sincronização completa: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Importar produtos do ML para o sistema
     * (Fluxo reverso: ML -> Sistema)
     * 
     * @return array
     */
    public function importProductsFromML(): array
    {
        try {
            $stats = [
                'total' => 0,
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => [],
            ];
            
            // Buscar produtos do vendedor no ML
            $mlItems = $this->productService->searchSellerItems();
            
            if (!$mlItems || !isset($mlItems['results'])) {
                return [
                    'success' => false,
                    'message' => 'Erro ao buscar produtos no ML',
                ];
            }
            
            $stats['total'] = count($mlItems['results']);
            
            foreach ($mlItems['results'] as $item) {
                try {
                    $mlItemId = $item['id'];
                    
                    // Verificar se já existe
                    $existing = MercadoLivreProduct::where('ml_item_id', $mlItemId)->first();
                    
                    if ($existing) {
                        $stats['skipped']++;
                        continue;
                    }
                    
                    // Buscar detalhes completos
                    $itemDetails = $this->makeRequest('GET', "/items/{$mlItemId}");
                    
                    if (!$itemDetails) {
                        $stats['errors'][] = [
                            'ml_item_id' => $mlItemId,
                            'error' => 'Erro ao buscar detalhes',
                        ];
                        continue;
                    }
                    
                    DB::beginTransaction();
                    
                    // Criar produto no sistema
                    $product = Product::create([
                        'name' => $itemDetails['title'],
                        'description' => strip_tags($itemDetails['descriptions'][0]['plain_text'] ?? $itemDetails['title']),
                        'code' => 'ML-' . $mlItemId,
                        'sale_price' => $itemDetails['price'],
                        'purchase_price' => $itemDetails['price'] * 0.7, // Estimar 30% de margem
                        'stock_quantity' => $itemDetails['available_quantity'],
                        'category_id' => 1, // Categoria padrão - ajustar depois
                    ]);
                    
                    // Criar vínculo com ML
                    MercadoLivreProduct::create([
                        'product_id' => $product->id,
                        'ml_item_id' => $mlItemId,
                        'ml_category_id' => $itemDetails['category_id'],
                        'listing_type_id' => $itemDetails['listing_type_id'],
                        'title' => $itemDetails['title'],
                        'description' => $itemDetails['descriptions'][0]['plain_text'] ?? null,
                        'price' => $itemDetails['price'],
                        'available_quantity' => $itemDetails['available_quantity'],
                        'sold_quantity' => $itemDetails['sold_quantity'],
                        'status' => $itemDetails['status'],
                        'permalink' => $itemDetails['permalink'],
                        'thumbnail' => $itemDetails['thumbnail'],
                        'pictures' => json_encode($itemDetails['pictures'] ?? []),
                        'attributes' => json_encode($itemDetails['attributes'] ?? []),
                    ]);
                    
                    DB::commit();
                    $stats['imported']++;
                    
                    // Rate limiting
                    usleep(500000);
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $stats['errors'][] = [
                        'ml_item_id' => $item['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                    
                    Log::error('Erro ao importar produto do ML', [
                        'item' => $item,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Registrar importação
            $this->logSync('products', 'import_from_ml', $stats);
            
            return [
                'success' => true,
                'stats' => $stats,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao importar produtos do ML', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao importar produtos: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Registrar sincronização no banco
     * 
     * @param string $type
     * @param string $action
     * @param array $result
     * @return void
     */
    protected function logSync(string $type, string $action, array $result): void
    {
        try {
            MercadoLivreSync::create([
                'type' => $type,
                'action' => $action,
                'status' => $result['success'] ?? true ? 'success' : 'failed',
                'result' => json_encode($result),
                'synced_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar sincronização', [
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Obter histórico de sincronizações
     * 
     * @param int $limit
     * @return array
     */
    public function getSyncHistory(int $limit = 50): array
    {
        try {
            $syncs = MercadoLivreSync::orderBy('synced_at', 'desc')
                ->limit($limit)
                ->get();
            
            return [
                'success' => true,
                'syncs' => $syncs,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao buscar histórico: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Limpar logs de sincronização antigos
     * 
     * @param int $days
     * @return int
     */
    public function cleanupOldSyncs(int $days = 90): int
    {
        try {
            $date = Carbon::now()->subDays($days);
            
            $deleted = MercadoLivreSync::where('synced_at', '<', $date)->delete();
            
            Log::info("Logs de sincronização limpos: {$deleted} registros");
            
            return $deleted;
            
        } catch (\Exception $e) {
            Log::error('Erro ao limpar logs de sincronização', [
                'error' => $e->getMessage(),
            ]);
            
            return 0;
        }
    }
}
