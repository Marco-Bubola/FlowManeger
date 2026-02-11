<?php

namespace App\Livewire\MercadoLivre;

use App\Jobs\SyncPublicationToMercadoLivre;
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
    public string $warranty = '';
    
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
        
        // Atualizar do ML ao abrir a página (título, preço, etc. alterados no ML passam a aparecer aqui)
        if ($this->publication->ml_item_id) {
            $syncService = app(MlStockSyncService::class);
            $result = $syncService->fetchPublicationFromMercadoLivre($this->publication);
            if ($result['success'] && $result['publication']) {
                $this->publication = $result['publication']->load('products', 'stockLogs');
            }
        }
        
        $this->applyPublicationToForm();
        $this->loadProducts();
    }
    
    /**
     * Aplica os dados da publicação aos campos do formulário.
     */
    protected function applyPublicationToForm(): void
    {
        $p = $this->publication;
        $this->title = $p->title;
        $this->description = $p->description ?? '';
        $this->price = (float) $p->price;
        $this->mlCategoryId = $p->ml_category_id ?? '';
        $this->listingType = $p->listing_type ?? 'gold_special';
        $this->freeShipping = (bool) $p->free_shipping;
        $this->localPickup = (bool) $p->local_pickup;
        $this->condition = $p->condition ?? 'new';
        $this->publicationType = $p->publication_type;
        $this->warranty = (string) ($p->warranty ?? '');
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
            
            // Sincronização automática com ML (em fila, evita conflitos)
            SyncPublicationToMercadoLivre::dispatch($this->publication->fresh())->delay(now()->addSeconds(3));
            
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
            
            SyncPublicationToMercadoLivre::dispatch($this->publication->fresh())->delay(now()->addSeconds(3));
            
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
            
            SyncPublicationToMercadoLivre::dispatch($this->publication->fresh())->delay(now()->addSeconds(3));
            
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
            'mlCategoryId' => 'nullable|string',
        ]);
        
        try {
            $this->publication->update([
                'title' => $this->title,
                'description' => $this->description,
                'price' => $this->price,
                'ml_category_id' => $this->mlCategoryId ?: $this->publication->ml_category_id,
                'listing_type' => $this->listingType,
                'free_shipping' => $this->freeShipping,
                'local_pickup' => $this->localPickup,
                'condition' => $this->condition,
                'warranty' => $this->warranty,
            ]);
            
            $this->publication->refresh();
            
            // Envia alterações para o Mercado Livre (título, preço, descrição, quantidade)
            $syncService = app(MlStockSyncService::class);
            $result = $syncService->updatePublicationToMercadoLivre($this->publication);
            
            if ($result['success']) {
                $this->notifySuccess('Publicação atualizada e sincronizada com o Mercado Livre');
            } else {
                $this->notifyWarning('Salvo localmente, mas falha ao atualizar no ML: ' . $result['message']);
            }
            
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
    
    /**
     * Atualiza os dados da publicação a partir do Mercado Livre (o que mudou no ML aparece aqui).
     */
    public function refreshFromMl(): void
    {
        if (!$this->publication->ml_item_id) {
            $this->notifyError('Esta publicação ainda não tem ID do ML.');
            return;
        }
        $syncService = app(MlStockSyncService::class);
        $result = $syncService->fetchPublicationFromMercadoLivre($this->publication);
        if ($result['success'] && $result['publication']) {
            $this->publication = $result['publication']->load('products', 'stockLogs');
            $this->applyPublicationToForm();
            $this->loadProducts();
            $this->notifySuccess('Dados atualizados do Mercado Livre.');
        } else {
            $this->notifyError($result['message'] ?? 'Erro ao atualizar do ML.');
        }
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
