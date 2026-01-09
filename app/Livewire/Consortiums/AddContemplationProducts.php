<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\ConsortiumContemplation;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class AddContemplationProducts extends Component
{
    public ConsortiumContemplation $contemplation;
    public $products = [];
    public $newProducts = [];
    public $searchTerm = '';
    public $selectedCategory = '';
    public $categories = [];
    public $maxValue = 0;
    public $isEditing = false;
    public $oldProducts = [];

    public function mount(ConsortiumContemplation $contemplation)
    {
        $this->contemplation = $contemplation;

        // Calcular valor máximo disponível (valor total do consórcio)
        $participant = $contemplation->participant;
        $consortium = $participant->consortium;
        $this->maxValue = $consortium->monthly_value * $consortium->duration_months;

        // Se já existem produtos, carregar para edição
        if ($contemplation->products && count($contemplation->products) > 0) {
            $this->isEditing = true;
            $this->oldProducts = $contemplation->products;
            $this->loadExistingProducts();
        } else {
            $this->resetNewProducts();
        }

        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = Category::all();
    }

    public function resetNewProducts()
    {
        $this->newProducts = [
            [
                'product_id' => '',
                'quantity' => 1,
                'price_sale' => 0
            ]
        ];
    }

    public function loadExistingProducts()
    {
        $this->newProducts = [];
        foreach ($this->oldProducts as $product) {
            $this->newProducts[] = [
                'product_id' => $product['product_id'] ?? '',
                'quantity' => $product['quantity'] ?? 1,
                'price_sale' => $product['price'] ?? 0
            ];
        }
    }

    public function toggleProduct($productId)
    {
        $existingIndex = null;

        // Procurar se o produto já está selecionado
        foreach ($this->newProducts as $index => $item) {
            if ($item['product_id'] == $productId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Se já está selecionado, remove
            $this->removeProductRow($existingIndex);
        } else {
            // Se não está selecionado, adiciona
            $product = Product::find($productId);
            if ($product) {
                // Remover validação de valor máximo - permitir exceder

                // Procurar por uma linha vazia
                $emptyIndex = null;
                foreach ($this->newProducts as $index => $item) {
                    if (empty($item['product_id'])) {
                        $emptyIndex = $index;
                        break;
                    }
                }

                if ($emptyIndex !== null) {
                    // Usar linha vazia existente
                    $this->newProducts[$emptyIndex] = [
                        'product_id' => $productId,
                        'quantity' => 1,
                        'price_sale' => $product->price_sale
                    ];
                } else {
                    // Adicionar nova linha
                    $this->newProducts[] = [
                        'product_id' => $productId,
                        'quantity' => 1,
                        'price_sale' => $product->price_sale
                    ];
                }
            }
        }
    }

    public function removeProductRow($index)
    {
        if (count($this->newProducts) > 1) {
            unset($this->newProducts[$index]);
            $this->newProducts = array_values($this->newProducts);
        } else {
            // Se é o último, apenas limpa
            $this->newProducts[$index] = [
                'product_id' => '',
                'quantity' => 1,
                'price_sale' => 0
            ];
        }
    }

    public function isProductSelected($productId)
    {
        foreach ($this->newProducts as $item) {
            if ($item['product_id'] == $productId) {
                return true;
            }
        }
        return false;
    }

    public function hasSelectedProducts()
    {
        foreach ($this->newProducts as $item) {
            if (!empty($item['product_id'])) {
                return true;
            }
        }
        return false;
    }

    public function getTotalPrice()
    {
        $total = 0;
        foreach ($this->newProducts as $item) {
            if (!empty($item['product_id']) && $item['quantity'] && $item['price_sale']) {
                $total += $item['quantity'] * $item['price_sale'];
            }
        }
        return $total;
    }

    public function getRemainingValue()
    {
        return $this->maxValue - $this->getTotalPrice();
    }
    public function getExcessValue()
    {
        $total = $this->getTotalPrice();
        return $total > $this->maxValue ? $total - $this->maxValue : 0;
    }
    public function getFilteredProductsProperty()
    {
        $query = Product::query()->where('stock_quantity', '>', 0);

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('product_code', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        return $query->with('category')->orderBy('name')->get();
    }

    public function updatedNewProducts($value, $key)
    {
        if (str_contains($key, '.quantity') || str_contains($key, '.price_sale')) {
            // Verificar se não ultrapassa o valor máximo
            $total = $this->getTotalPrice();
            if ($total > $this->maxValue) {
                session()->flash('error', 'O valor total dos produtos não pode ultrapassar R$ ' . number_format($this->maxValue, 2, ',', '.'));

                // Reverter a última alteração
                $index = explode('.', $key)[0];
                if (str_contains($key, '.quantity')) {
                    $this->newProducts[$index]['quantity'] = 1;
                }
            }
        }
    }

    public function save()
    {
        // Validar que há produtos selecionados
        if (!$this->hasSelectedProducts()) {
            session()->flash('error', 'Selecione pelo menos um produto.');
            return;
        }

        // Calcular total e valor excedente
        $total = $this->getTotalPrice();
        $excessValue = $this->getExcessValue();

        try {
            DB::beginTransaction();

            // Se está editando, reverter estoque dos produtos antigos
            if ($this->isEditing && !empty($this->oldProducts)) {
                foreach ($this->oldProducts as $oldProduct) {
                    $product = Product::find($oldProduct['product_id'] ?? 0);
                    if ($product) {
                        // Devolver ao estoque
                        $product->increment('stock_quantity', $oldProduct['quantity'] ?? 0);
                    }
                }
            }

            // Preparar produtos para salvar e atualizar estoque
            $productsData = [];
            foreach ($this->newProducts as $item) {
                if (!empty($item['product_id']) && $item['quantity'] > 0) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        // Verificar se há estoque suficiente
                        if ($product->stock_quantity < $item['quantity']) {
                            throw new \Exception("Estoque insuficiente para o produto {$product->name}. Disponível: {$product->stock_quantity}");
                        }

                        $productsData[] = [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $item['quantity'],
                            'price' => $item['price_sale'],
                        ];

                        // Decrementar estoque
                        $product->decrement('stock_quantity', $item['quantity']);
                    }
                }
            }

            // Atualizar contemplação
            $this->contemplation->update([
                'redemption_type' => 'products',
                'redemption_value' => $total,
                'products' => $productsData,
                'status' => 'redeemed',
            ]);

            DB::commit();

            session()->flash('success', 'Produtos registrados com sucesso!');

            // Redirecionar de volta para a página de detalhes
            return redirect()->route('consortiums.show', $this->contemplation->participant->consortium);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao registrar produtos: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.consortiums.add-contemplation-products', [
            'filteredProducts' => $this->filteredProducts,
        ]);
    }
}
