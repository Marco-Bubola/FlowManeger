<?php

namespace App\Http\Controllers\MercadoLivre;

use App\Http\Controllers\Controller;
use App\Services\MercadoLivre\ProductService;
use App\Services\MercadoLivre\SyncService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
     * Publicar produto no Mercado Livre
     * 
     * POST /mercadolivre/products/{id}/publish
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
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
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ], 404);
            }
            
            // Publicar produto
            $result = $this->productService->publishProduct(
                $product,
                $request->input('category_id'),
                $request->input('listing_type'),
                $request->input('condition'),
                $request->input('warranty'),
                $request->input('attributes', [])
            );
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produto publicado com sucesso',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erro ao publicar produto',
                    'errors' => $result['errors'] ?? [],
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao publicar produto', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao publicar produto: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Sincronizar produto com Mercado Livre
     * 
     * POST /mercadolivre/products/{id}/sync
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function sync(int $id): JsonResponse
    {
        try {
            $result = $this->syncService->syncProduct($id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar produto', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar produto: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Pausar produto no Mercado Livre
     * 
     * POST /mercadolivre/products/{id}/pause
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function pause(int $id): JsonResponse
    {
        try {
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ], 404);
            }
            
            $result = $this->productService->pauseProduct($product);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produto pausado com sucesso',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erro ao pausar produto',
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao pausar produto', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao pausar produto: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Ativar produto no Mercado Livre
     * 
     * POST /mercadolivre/products/{id}/activate
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ], 404);
            }
            
            $result = $this->productService->activateProduct($product);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produto ativado com sucesso',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erro ao ativar produto',
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao ativar produto', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao ativar produto: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Deletar produto do Mercado Livre (fechar anúncio)
     * 
     * DELETE /mercadolivre/products/{id}
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        try {
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ], 404);
            }
            
            $result = $this->productService->deleteProduct($product);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produto removido do ML com sucesso',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erro ao remover produto',
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao deletar produto', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar produto: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualizar estoque de produto no ML
     * 
     * POST /mercadolivre/products/{id}/update-stock
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantidade inválida',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ], 404);
            }
            
            $mlProduct = $product->mercadoLivreProduct;
            
            if (!$mlProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não está publicado no ML',
                ], 404);
            }
            
            $result = $this->productService->updateStock(
                $mlProduct->ml_item_id,
                $request->input('quantity')
            );
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estoque atualizado com sucesso',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erro ao atualizar estoque',
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar estoque', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar estoque: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualizar preço de produto no ML
     * 
     * POST /mercadolivre/products/{id}/update-price
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updatePrice(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'price' => 'required|numeric|min:0.01',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preço inválido',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado',
                ], 404);
            }
            
            $mlProduct = $product->mercadoLivreProduct;
            
            if (!$mlProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não está publicado no ML',
                ], 404);
            }
            
            $result = $this->productService->updatePrice(
                $mlProduct->ml_item_id,
                $request->input('price')
            );
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Preço atualizado com sucesso',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erro ao atualizar preço',
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar preço', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar preço: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Listar produtos publicados no ML
     * 
     * GET /mercadolivre/products
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $products = Product::whereHas('mercadoLivreProduct')
                ->with('mercadoLivreProduct')
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                })
                ->when($request->input('status'), function ($query, $status) {
                    $query->whereHas('mercadoLivreProduct', function ($q) use ($status) {
                        $q->where('status', $status);
                    });
                })
                ->paginate($request->input('per_page', 20));
            
            return response()->json([
                'success' => true,
                'data' => $products,
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao listar produtos', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar produtos: ' . $e->getMessage(),
            ], 500);
        }
    }
}
