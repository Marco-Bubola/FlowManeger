<?php

namespace App\Http\Controllers\MercadoLivre;

use App\Http\Controllers\Controller;
use App\Services\MercadoLivre\ProductService;
use App\Services\MercadoLivre\SyncService;
use App\Models\MercadoLivreProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controller REST para gerenciar produtos no Mercado Livre
 */
class ProductController extends Controller
{
    protected ProductService $productService;
    protected SyncService $syncService;
    
    public function __construct(
        ProductService $productService,
        SyncService $syncService
    ) {
        $this->productService = $productService;
        $this->syncService = $syncService;
    }

    /**
     * Produto do usuário logado (ou null se não existir / não for dele).
     */
    protected function findOwnedProduct(int $id): ?Product
    {
        return Product::where('id', $id)->where('user_id', Auth::id())->first();
    }

    /**
     * Resolve produto + anúncio legado (mercadolivre_products) do usuário.
     * Retorna [MercadoLivreProduct|null, JsonResponse|null].
     */
    protected function resolveMlProduct(int $id): array
    {
        $product = $this->findOwnedProduct($id);
        if (!$product) {
            return [null, $this->fail('Produto não encontrado', 404)];
        }

        $mlProduct = $product->mercadoLivreProduct;
        if (!$mlProduct || !$mlProduct->ml_item_id) {
            return [null, $this->fail('Produto não está publicado no ML', 404)];
        }

        return [$mlProduct, null];
    }

    protected function fail(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message] + $extra, $status);
    }

    /**
     * Resposta padrão para os métodos do ProductService (retornam 'error' em falha).
     */
    protected function respond(array $result, string $okMessage, string $errMessage): JsonResponse
    {
        if (!empty($result['success'])) {
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? $okMessage,
                'data' => $result,
            ], 200);
        }

        return $this->fail($result['error'] ?? $result['message'] ?? $errMessage, 400);
    }

    protected function handleException(string $label, int $id, \Throwable $e): JsonResponse
    {
        Log::error("Erro ao {$label} produto", [
            'product_id' => $id,
            'error' => $e->getMessage(),
        ]);

        return $this->fail("Erro ao {$label} produto: " . $e->getMessage(), 500);
    }
    
    /**
     * Publicar produto no Mercado Livre
     * 
     * POST /mercadolivre/api/products/{id}/publish
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|string',
                'listing_type' => 'required|string|in:gold_special,gold_pro,gold,silver,bronze,free',
                'condition' => 'required|string|in:new,used',
                'warranty' => 'nullable|string',
                'attributes' => 'nullable|array',
                'price' => 'nullable|numeric|min:0.01',
                'quantity' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'free_shipping' => 'nullable|boolean',
                'local_pickup' => 'nullable|boolean',
            ]);
            
            if ($validator->fails()) {
                return $this->fail('Dados inválidos', 422, ['errors' => $validator->errors()]);
            }
            
            $product = $this->findOwnedProduct($id);
            if (!$product) {
                return $this->fail('Produto não encontrado', 404);
            }

            if ($product->mercadoLivreProduct && $product->mercadoLivreProduct->ml_item_id
                && $product->mercadoLivreProduct->status !== 'closed') {
                return $this->fail('Produto já está publicado no ML', 409);
            }
            
            $publishData = array_filter(
                $request->only([
                    'category_id', 'listing_type', 'condition', 'warranty', 'attributes',
                    'price', 'quantity', 'description', 'free_shipping', 'local_pickup',
                ]),
                fn ($v) => $v !== null
            );

            $result = $this->productService->publishProduct($product, $publishData, Auth::id());

            return $this->respond($result, 'Produto publicado com sucesso', 'Erro ao publicar produto');
        } catch (\Throwable $e) {
            return $this->handleException('publicar', $id, $e);
        }
    }
    
    /**
     * Sincronizar produto com Mercado Livre
     * 
     * POST /mercadolivre/api/products/{id}/sync
     */
    public function sync(int $id): JsonResponse
    {
        try {
            [$mlProduct, $error] = $this->resolveMlProduct($id);
            if ($error) {
                return $error;
            }

            // ProductService::syncProduct usa o token do dono do produto
            $result = $this->productService->syncProduct($mlProduct);

            return $this->respond($result, 'Produto sincronizado', 'Erro ao sincronizar produto');
        } catch (\Throwable $e) {
            return $this->handleException('sincronizar', $id, $e);
        }
    }
    
    /**
     * Pausar produto no Mercado Livre
     * 
     * POST /mercadolivre/api/products/{id}/pause
     */
    public function pause(int $id): JsonResponse
    {
        try {
            [$mlProduct, $error] = $this->resolveMlProduct($id);
            if ($error) {
                return $error;
            }

            return $this->respond($this->productService->pauseProduct($mlProduct), 'Produto pausado com sucesso', 'Erro ao pausar produto');
        } catch (\Throwable $e) {
            return $this->handleException('pausar', $id, $e);
        }
    }
    
    /**
     * Ativar produto no Mercado Livre
     * 
     * POST /mercadolivre/api/products/{id}/activate
     */
    public function activate(int $id): JsonResponse
    {
        try {
            [$mlProduct, $error] = $this->resolveMlProduct($id);
            if ($error) {
                return $error;
            }

            return $this->respond($this->productService->activateProduct($mlProduct), 'Produto ativado com sucesso', 'Erro ao ativar produto');
        } catch (\Throwable $e) {
            return $this->handleException('ativar', $id, $e);
        }
    }
    
    /**
     * Encerrar anúncio do produto no Mercado Livre (status closed)
     * 
     * DELETE /mercadolivre/api/products/{id}
     */
    public function delete(int $id): JsonResponse
    {
        try {
            [$mlProduct, $error] = $this->resolveMlProduct($id);
            if ($error) {
                return $error;
            }

            return $this->respond($this->productService->closeProduct($mlProduct), 'Produto removido do ML com sucesso', 'Erro ao remover produto');
        } catch (\Throwable $e) {
            return $this->handleException('deletar', $id, $e);
        }
    }
    
    /**
     * Atualizar estoque de produto no ML
     * 
     * POST /mercadolivre/api/products/{id}/update-stock
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:0',
            ]);
            
            if ($validator->fails()) {
                return $this->fail('Quantidade inválida', 422, ['errors' => $validator->errors()]);
            }
            
            [$mlProduct, $error] = $this->resolveMlProduct($id);
            if ($error) {
                return $error;
            }

            $result = $this->productService->updateStock($mlProduct, (int) $request->input('quantity'));

            return $this->respond($result, 'Estoque atualizado com sucesso', 'Erro ao atualizar estoque');
        } catch (\Throwable $e) {
            return $this->handleException('atualizar estoque do', $id, $e);
        }
    }
    
    /**
     * Atualizar preço de produto no ML
     * 
     * POST /mercadolivre/api/products/{id}/update-price
     */
    public function updatePrice(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'price' => 'required|numeric|min:0.01',
            ]);
            
            if ($validator->fails()) {
                return $this->fail('Preço inválido', 422, ['errors' => $validator->errors()]);
            }
            
            [$mlProduct, $error] = $this->resolveMlProduct($id);
            if ($error) {
                return $error;
            }

            $result = $this->productService->updatePrice($mlProduct, (float) $request->input('price'));

            return $this->respond($result, 'Preço atualizado com sucesso', 'Erro ao atualizar preço');
        } catch (\Throwable $e) {
            return $this->handleException('atualizar preço do', $id, $e);
        }
    }
    
    /**
     * Listar produtos do usuário publicados no ML
     * 
     * GET /mercadolivre/api/products
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $products = Product::where('user_id', Auth::id())
                ->whereHas('mercadoLivreProduct')
                ->with('mercadoLivreProduct')
                ->when($request->input('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('product_code', 'like', "%{$search}%");
                    });
                })
                ->when($request->input('status'), function ($query, $status) {
                    $query->whereHas('mercadoLivreProduct', function ($q) use ($status) {
                        $q->where('status', $status);
                    });
                })
                ->paginate(min(100, max(1, (int) $request->input('per_page', 20))));
            
            return response()->json([
                'success' => true,
                'data' => $products,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Erro ao listar produtos', [
                'error' => $e->getMessage(),
            ]);

            return $this->fail('Erro ao listar produtos: ' . $e->getMessage(), 500);
        }
    }
}
