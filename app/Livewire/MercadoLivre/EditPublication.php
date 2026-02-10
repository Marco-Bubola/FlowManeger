<?php

namespace App\Livewire\MercadoLivre;

use App\Models\MlPublication;
use App\Models\Product;
use App\Services\MercadoLivre\MlStockSyncService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EditPublication extends Component
{
    use HasNotifications;

    public MlPublication $publication;
    public string $title = '';
    public string $description = '';
    public float $price = 0;
    public string $mlCategoryId = '';
    public string $listingType = 'gold_special';
    public bool $freeShipping = false;
    public bool $localPickup = true;
    public string $condition = 'new';
    public string $publicationType = 'simple';
    
    // Produtos do kit
    public array $products = [];
    public bool $showProductSelector = false;
    
    public function mount(MlPublication $publication)
    {
        // Verificar se a publicação pertence ao usuário
        if ($publication->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar esta publicação.');
        }
        
        $this->publication = $publication->load('products', 'stockLogs');
        
        // Carregar dados da publicação
        $this->title = $publication->title;
        $this->description = $publication->description ?? '';
        $this->price = (float)$publication->price;
        $this->mlCategoryId = $publication->ml_category_id ?? '';
        $this->listingType = $publication->listing_type ?? 'gold_special';
        $this->freeShipping = (bool)$publication->free_shipping;
        $this->localPickup = (bool)$publication->local_pickup;
        $this->condition = $publication->condition ?? 'new';
        $this->publicationType = $publication->publication_type;
        
        // Carregar produtos
        $this->loadProducts();
    }
    
    /**
     * Carrega produtos da publicação
     */
    protected function loadProducts()
    {
        $this->products = $this->publication->products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code,
                'price' => (float)$product->price,
                'stock_quantity' => (int)$product->stock_quantity,
                'image_url' => $product->image_url,
                'quantity' => (int)$product->pivot->quantity,
                'unit_cost' => (float)$product->pivot->unit_cost,
            ];
        })->toArray();
    }
    
    /**
     * Adiciona produto à publicação
     */
    public function addProduct(int $productId, int $quantity = 1, ?float $unitCost = null)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->user_id !== Auth::id()) {
            $this->notifyError('Produto não encontrado');
            return;
        }
        
        try {
            $this->publication->addProduct(
                $productId, 
                $quantity, 
                $unitCost ?? $product->price
            );
            
            $this->loadProducts();
            $this->notifySuccess('Produto adicionado à publicação');
            
            // Forçar sync após adicionar produto
            $this->syncPublication();
            
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar produto à publicação', [
                'publication_id' => $this->publication->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao adicionar produto: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove produto da publicação
     */
    public function removeProduct(int $productId)
    {
        try {
            $this->publication->removeProduct($productId);
            
            $this->loadProducts();
            $this->notifySuccess('Produto removido da publicação');
            
            // Forçar sync após remover produto
            $this->syncPublication();
            
        } catch (\Exception $e) {
            Log::error('Erro ao remover produto da publicação', [
                'publication_id' => $this->publication->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao remover produto: ' . $e->getMessage());
        }
    }
    
    /**
     * Atualiza quantidade de um produto
     */
    public function updateProductQuantity(int $productId, int $quantity)
    {
        $quantity = max(1, $quantity);
        
        try {
            $this->publication->updateProductQuantity($productId, $quantity);
            
            $this->loadProducts();
            $this->notifySuccess('Quantidade atualizada');
            
            // Forçar sync após atualizar quantidade
            $this->syncPublication();
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar quantidade do produto', [
                'publication_id' => $this->publication->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao atualizar quantidade: ' . $e->getMessage());
        }
    }
    
    /**
     * Atualiza dados básicos da publicação
     */
    public function updatePublication()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'price' => 'required|numeric|min:0.01',
            'mlCategoryId' => 'required',
        ]);
        
        try {
            $this->publication->update([
                'title' => $this->title,
                'description' => $this->description,
                'price' => $this->price,
                'ml_category_id' => $this->mlCategoryId,
                'listing_type' => $this->listingType,
                'free_shipping' => $this->freeShipping,
                'local_pickup' => $this->localPickup,
                'condition' => $this->condition,
            ]);
            
            $this->notifySuccess('Publicação atualizada com sucesso');
            
            // Forçar sync após atualizar
            $this->syncPublication();
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar publicação', [
                'publication_id' => $this->publication->id,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao atualizar publicação: ' . $e->getMessage());
        }
    }
    
    /**
     * Sincroniza publicação com Mercado Livre
     */
    public function syncPublication()
    {
        try {
            $syncService = new MlStockSyncService();
            $result = $syncService->syncQuantityToMercadoLivre($this->publication);
            
            if ($result['success']) {
                $this->publication->refresh();
                $this->notifySuccess('Sincronizado com Mercado Livre');
            } else {
                $this->notifyWarning('Erro ao sincronizar: ' . ($result['message'] ?? 'Erro desconhecido'));
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar publicação', [
                'publication_id' => $this->publication->id,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao sincronizar: ' . $e->getMessage());
        }
    }
    
    /**
     * Pausa publicação
     */
    public function pausePublication()
    {
        try {
            $this->publication->update(['status' => 'paused']);
            $this->notifySuccess('Publicação pausada');
            
            // TODO: Chamar API ML para pausar
            
        } catch (\Exception $e) {
            $this->notifyError('Erro ao pausar publicação');
        }
    }
    
    /**
     * Ativa publicação
     */
    public function activatePublication()
    {
        try {
            $this->publication->update(['status' => 'active']);
            $this->notifySuccess('Publicação ativada');
            
            // TODO: Chamar API ML para ativar
            
        } catch (\Exception $e) {
            $this->notifyError('Erro ao ativar publicação');
        }
    }
    
    /**
     * Deleta publicação
     */
    public function deletePublication()
    {
        try {
            // TODO: Chamar API ML para deletar antes
            
            $this->publication->delete();
            $this->notifySuccess('Publicação deletada');
            
            return redirect()->route('mercadolivre.publications');
            
        } catch (\Exception $e) {
            Log::error('Erro ao deletar publicação', [
                'publication_id' => $this->publication->id,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao deletar publicação');
        }
    }
    
    /**
     * Toggle seletor de produtos
     */
    public function toggleProductSelector()
    {
        $this->showProductSelector = !$this->showProductSelector;
    }
    
    public function render()
    {
        $availableQuantity = $this->publication->calculateAvailableQuantity();
        $stockLogs = $this->publication->stockLogs()
            ->with('product')
            ->latest()
            ->take(10)
            ->get();
        
        return view('livewire.mercadolivre.edit-publication', [
            'availableQuantity' => $availableQuantity,
            'stockLogs' => $stockLogs,
        ])->layout('components.layouts.app');
    }
}
