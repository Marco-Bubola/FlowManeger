<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\VendaParcela;
use Illuminate\Support\Facades\DB;

class AddProducts extends Component
{
    public Sale $sale;
    public $products = [];
    public $newProducts = [];
    public $searchTerm = '';
    public $selectedCategory = '';
    public $categories = [];

    public function mount(Sale $sale)
    {
        $this->sale = $sale;
        $this->resetNewProducts();
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = \App\Models\Category::all();
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

    public function addProductRow()
    {
        $this->newProducts[] = [
            'product_id' => '',
            'quantity' => 1,
            'price_sale' => 0
        ];
    }

    public function removeProductRow($index)
    {
        if (count($this->newProducts) > 1) {
            unset($this->newProducts[$index]);
            $this->newProducts = array_values($this->newProducts);
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

    public function updatedNewProducts($value, $key)
    {
        if (str_contains($key, '.product_id')) {
            $index = explode('.', $key)[0];
            $productId = $this->newProducts[$index]['product_id'];

            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $this->newProducts[$index]['price_sale'] = $product->price_sale;
                }
            }
        }
    }

    public function addProducts()
    {
        $this->validate([
            'newProducts.*.product_id' => 'required|exists:products,id',
            'newProducts.*.quantity' => 'required|integer|min:1',
            'newProducts.*.price_sale' => 'required|numeric|min:0'
        ]);

        DB::transaction(function () {
            foreach ($this->newProducts as $productData) {
                if ($productData['product_id']) {
                    // Verificar estoque
                    $product = Product::find($productData['product_id']);
                    if ($product->stock_quantity < $productData['quantity']) {
                        session()->flash('error', "Estoque insuficiente para o produto: {$product->name}");
                        return;
                    }

                    // Verifica se o produto já existe na venda
                    $existingItem = SaleItem::where('sale_id', $this->sale->id)
                        ->where('product_id', $productData['product_id'])
                        ->first();

                    if ($existingItem) {
                        // Atualiza a quantidade se o produto já existe
                        $existingItem->quantity += $productData['quantity'];
                        $existingItem->save();
                    } else {
                        // Cria novo item se não existe
                        SaleItem::create([
                            'sale_id' => $this->sale->id,
                            'product_id' => $productData['product_id'],
                            'quantity' => $productData['quantity'],
                            'price' => $product->price, // Preço de custo
                            'price_sale' => $productData['price_sale']
                        ]);
                    }

                    // Atualiza estoque do produto
                    $product->stock_quantity -= $productData['quantity'];
                    $product->save();
                }
            }

            // Recalcula o total da venda
            $this->sale->refresh();
            $totalPrice = $this->sale->saleItems->sum(function ($item) {
                return $item->quantity * $item->price_sale;
            });
            $this->sale->update(['total_price' => $totalPrice]);

            // Recalcular parcelas se a venda for parcelada
            $this->recalcularParcelas();
        });

        session()->flash('success', 'Produtos adicionados com sucesso!');
        return redirect()->route('sales.show', $this->sale->id);
    }

    /**
     * Recalcula e atualiza as parcelas da venda
     * Protegido contra divisão por zero
     */
    private function recalcularParcelas()
    {
        // Se a venda não for parcelada, não há parcelas para atualizar
        if ($this->sale->tipo_pagamento !== 'parcelado' || $this->sale->parcelas <= 1) {
            return;
        }

        // Buscar parcelas existentes
        $parcelasExistentes = VendaParcela::where('sale_id', $this->sale->id)
            ->orderBy('numero_parcela')
            ->get();

        // Se não há parcelas, não há nada para atualizar
        if ($parcelasExistentes->isEmpty()) {
            return;
        }

        $numeroParcelas = $parcelasExistentes->count();

        // Proteção contra divisão por zero
        if ($numeroParcelas === 0) {
            return;
        }

        $totalVenda = $this->sale->total_price;
        $valorParcela = round($totalVenda / $numeroParcelas, 2);

        // Atualizar apenas os valores das parcelas existentes (manter datas e status)
        foreach ($parcelasExistentes as $parcela) {
            // Não atualizar parcelas já pagas
            if ($parcela->status !== 'paga') {
                $parcela->update([
                    'valor' => $valorParcela
                ]);
            }
        }
    }

    public function getFilteredProducts()
    {
        $query = Product::query();

        // Filtrar apenas produtos do usuário da venda
        $query->where('user_id', $this->sale->user_id);

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('product_code', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        return $query->where('stock_quantity', '>', 0)
                    ->with('category')
                    ->orderBy('name')
                    ->get();
    }

    public function render()
    {
        return view('livewire.sales.add-products', [
            'filteredProducts' => $this->getFilteredProducts()
        ]);
    }
}
