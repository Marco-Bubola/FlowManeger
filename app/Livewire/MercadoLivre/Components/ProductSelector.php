<?php

namespace App\Livewire\MercadoLivre\Components;

use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProductSelector extends Component
{
    public array $selectedProducts = [];
    public string $searchTerm = '';
    public array $searchResults = [];
    public bool $showResults = false;
    
    // Produtos já adicionados com suas quantidades
    public array $products = [];
    
    public function mount(array $initialProducts = [])
    {
        $this->products = $initialProducts;
        
        // Carregar produtos automaticamente ao abrir
        $this->loadInitialProducts();
    }
    
    /**
     * Carrega produtos iniciais (apenas elegíveis para ML)
     */
    public function loadInitialProducts()
    {
        $addedIds = array_column($this->products, 'id');
        
        $this->searchResults = Product::where('user_id', Auth::id())
            ->where('status', 'ativo')
            ->whereNotIn('id', $addedIds)
            // Filtros para ML: estoque, preço, imagem
            ->where('stock_quantity', '>', 0)
            ->where('price', '>', 0)
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->whereNotNull('image')
            ->where('image', '!=', 'product-placeholder.png')
            ->whereNotNull('condition')
            ->limit(30)
            ->orderBy('name')
            ->get()
            ->filter(function($product) {
                // Validação adicional: nome com mínimo 3 caracteres
                return !empty($product->name) && strlen($product->name) >= 3;
            })
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'price' => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'image_url' => $product->image_url,
                ];
            })
            ->values()
            ->toArray();
        
        $this->showResults = true;
    }
    
    /**
     * Busca produtos por nome ou código
     */
    public function searchProducts()
    {
        // Se vazio, carregar produtos iniciais
        if (strlen($this->searchTerm) < 2) {
            $this->loadInitialProducts();
            return;
        }
        
        $addedIds = array_column($this->products, 'id');
        
        $this->searchResults = Product::where('user_id', Auth::id())
            ->where('status', 'ativo')
            ->where(function($query) {
                $query->where('name', 'like', "%{$this->searchTerm}%")
                      ->orWhere('product_code', 'like', "%{$this->searchTerm}%")
                      ->orWhere('barcode', 'like', "%{$this->searchTerm}%");
            })
            ->whereNotIn('id', $addedIds)
            // Filtros para ML: apenas produtos elegíveis
            ->where('stock_quantity', '>', 0)
            ->where('price', '>', 0)
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->whereNotNull('image')
            ->where('image', '!=', 'product-placeholder.png')
            ->whereNotNull('condition')
            ->limit(30)
            ->get()
            ->filter(function($product) {
                return !empty($product->name) && strlen($product->name) >= 3;
            })
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'price' => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'image_url' => $product->image_url,
                ];
            })
            ->values()
            ->toArray();
        
        $this->showResults = !empty($this->searchResults);
    }
    
    /**
     * Adiciona produto à seleção
     */
    public function addProduct(int $productId)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->user_id !== Auth::id()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Produto não encontrado'
            ]);
            return;
        }
        
        // Verificar se já está adicionado
        foreach ($this->products as $p) {
            if ($p['id'] == $productId) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'Produto já adicionado'
                ]);
                return;
            }
        }
        
        $this->products[] = [
            'id' => $product->id,
            'name' => $product->name,
            'product_code' => $product->product_code,
            'price' => (float)$product->price,
            'stock_quantity' => (int)$product->stock_quantity,
            'image_url' => $product->image_url,
            'quantity' => 1, // Quantidade padrão no kit
            'unit_cost' => (float)$product->price,
        ];
        
        // Limpar busca
        $this->searchTerm = '';
        $this->searchResults = [];
        $this->showResults = false;
        
        // Emitir evento para componente pai
        $this->dispatch('product-added', $product->id);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Produto adicionado ao kit'
        ]);
    }
    
    /**
     * Remove produto da seleção
     */
    public function removeProduct(int $productId)
    {
        $this->products = array_values(array_filter($this->products, function($p) use ($productId) {
            return $p['id'] != $productId;
        }));
        
        $this->dispatch('product-removed', $productId);
        
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Produto removido'
        ]);
    }
    
    /**
     * Atualiza quantidade de um produto no kit
     */
    public function updateQuantity(int $productId, int $quantity)
    {
        $quantity = max(1, $quantity); // Mínimo 1
        
        foreach ($this->products as &$product) {
            if ($product['id'] == $productId) {
                $product['quantity'] = $quantity;
                break;
            }
        }
        
        $this->dispatch('product-quantity-updated', $productId, $quantity);
    }
    
    /**
     * Retorna os produtos selecionados
     */
    public function getSelectedProducts()
    {
        return $this->products;
    }
    
    /**
     * Calcula quantidade disponível baseado no estoque dos produtos
     */
    public function getAvailableQuantity()
    {
        if (empty($this->products)) {
            return 0;
        }
        
        $minQuantity = PHP_INT_MAX;
        
        foreach ($this->products as $product) {
            $available = floor($product['stock_quantity'] / $product['quantity']);
            $minQuantity = min($minQuantity, $available);
        }
        
        return $minQuantity == PHP_INT_MAX ? 0 : (int)$minQuantity;
    }
    
    /**
     * Fecha dropdown de resultados
     */
    public function closeResults()
    {
        $this->showResults = false;
    }
    
    public function render()
    {
        return view('livewire.mercadolivre.components.product-selector');
    }
}
