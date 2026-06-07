<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\VendaParcela;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddProducts extends Component
{
    public Sale $sale;
    public $products = [];
    public $newProducts = [];
    public $searchTerm = '';
    public $selectedCategory = '';
    public $categories = [];
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $stockFilter = 'all'; // all | low | in_stock | kits

    public function mount(Sale $sale)
    {
        if ((int) $sale->user_id !== (int) Auth::id()) {
            abort(403, 'Você só pode adicionar produtos em suas próprias vendas.');
        }

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

    public function getSelectedQuantity($productId)
    {
        foreach ($this->newProducts as $item) {
            if ($item['product_id'] == $productId) {
                return $item['quantity'];
            }
        }
        return 0;
    }

    public function clearAllProducts()
    {
        $this->resetNewProducts();
    }

    public function clearFilters()
    {
        $this->searchTerm = '';
        $this->selectedCategory = '';
        $this->stockFilter = 'all';
        $this->sortBy = 'name';
        $this->sortDirection = 'asc';
    }

    public function getActiveFiltersCount(): int
    {
        $n = 0;
        if (!empty($this->selectedCategory)) $n++;
        if ($this->stockFilter !== 'all') $n++;
        if ($this->sortBy !== 'name' || $this->sortDirection !== 'asc') $n++;
        return $n;
    }

    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
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

    /**
     * Itens selecionados com detalhes completos (independente do filtro atual).
     * Usado pelo modal de confirmação e pelo carrinho mobile.
     */
    public function getSelectedItemsProperty()
    {
        $ids = collect($this->newProducts)->pluck('product_id')->filter()->unique();
        if ($ids->isEmpty()) {
            return collect();
        }

        $products = Product::whereIn('id', $ids)->get()->keyBy('id');

        return collect($this->newProducts)
            ->filter(fn ($row) => !empty($row['product_id']) && $products->has($row['product_id']))
            ->map(function ($row) use ($products) {
                $p = $products[$row['product_id']];
                $qty = (int) $row['quantity'];
                $price = (float) $row['price_sale'];
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->product_code,
                    'image' => $p->image,
                    'is_kit' => ($p->tipo ?? 'simples') === 'kit',
                    'stock' => (int) $p->stock_quantity,
                    'quantity' => $qty,
                    'price_sale' => $price,
                    'subtotal' => $qty * $price,
                ];
            })
            ->values();
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

        // Linhas válidas (com produto selecionado)
        $rows = array_values(array_filter($this->newProducts, fn ($p) => !empty($p['product_id'])));
        if (empty($rows)) {
            session()->flash('error', 'Selecione ao menos um produto.');
            return;
        }

        // ========= 1ª PASSAGEM: VALIDAÇÃO (antes de qualquer alteração) =========
        // Produtos simples checam o próprio estoque; kits checam o estoque de cada
        // COMPONENTE e informam exatamente qual componente está faltando.
        foreach ($rows as $productData) {
            $product = Product::find($productData['product_id']);
            if (!$product) {
                session()->flash('error', 'Produto não encontrado.');
                return;
            }

            $qty = (int) $productData['quantity'];

            if (($product->tipo ?? 'simples') === 'kit') {
                $componentes = $product->componentes()->get();
                if ($componentes->isEmpty()) {
                    session()->flash('error', "O kit '{$product->name}' não possui componentes definidos.");
                    return;
                }

                foreach ($componentes as $pc) {
                    $componentProduct = $pc->componente()->first();
                    if (!$componentProduct) {
                        session()->flash('error', "Componente do kit '{$product->name}' não encontrado (ID: {$pc->componente_produto_id}).");
                        return;
                    }
                    $requiredQty = (int) ($pc->quantidade ?? 0) * $qty;
                    if ($componentProduct->stock_quantity < $requiredQty) {
                        session()->flash('error', "Estoque insuficiente para o componente '{$componentProduct->name}' do kit '{$product->name}'. Necessário: {$requiredQty}, Disponível: {$componentProduct->stock_quantity}.");
                        return;
                    }
                }
            } else {
                if ($product->stock_quantity < $qty) {
                    session()->flash('error', "Estoque insuficiente para o produto '{$product->name}'. Necessário: {$qty}, Disponível: {$product->stock_quantity}.");
                    return;
                }
            }
        }

        // ========= 2ª PASSAGEM: PERSISTÊNCIA (atômica) =========
        DB::transaction(function () use ($rows) {
            foreach ($rows as $productData) {
                $product = Product::find($productData['product_id']);
                $qty = (int) $productData['quantity'];

                // Cria ou incrementa o item da venda
                $existingItem = SaleItem::where('sale_id', $this->sale->id)
                    ->where('product_id', $productData['product_id'])
                    ->first();

                if ($existingItem) {
                    $existingItem->quantity += $qty;
                    $existingItem->save();
                } else {
                    SaleItem::create([
                        'sale_id' => $this->sale->id,
                        'product_id' => $productData['product_id'],
                        'quantity' => $qty,
                        'price' => $product->price, // Preço de custo
                        'price_sale' => $productData['price_sale'],
                    ]);
                }

                // Atualiza estoque: simples desconta o próprio; kit desconta componentes
                if (($product->tipo ?? 'simples') === 'kit') {
                    foreach ($product->componentes()->get() as $pc) {
                        $componentProduct = $pc->componente()->first();
                        if (!$componentProduct) {
                            continue;
                        }
                        $requiredQty = (int) ($pc->quantidade ?? 0) * $qty;
                        $componentProduct->stock_quantity = max(0, $componentProduct->stock_quantity - $requiredQty);
                        $componentProduct->save();
                    }
                } else {
                    $product->stock_quantity = max(0, $product->stock_quantity - $qty);
                    $product->save();
                }
            }

            // Recalcula o total da venda
            $this->sale->refresh();
            $totalPrice = $this->sale->saleItems->sum(fn ($item) => $item->quantity * $item->price_sale);
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

        // Kits são salvos com stock_quantity = 0 e o estoque real é composto pelos componentes.
        // Por isso, não podemos excluir todos os kits pelo filtro de estoque.
        $query->where(function ($q) {
            $q->where('stock_quantity', '>', 0)
              ->orWhere('tipo', 'kit');
        });

        // Filtro de estoque (modal)
        match ($this->stockFilter) {
            'low'      => $query->where('tipo', '!=', 'kit')->where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0),
            'in_stock' => $query->where('tipo', '!=', 'kit')->where('stock_quantity', '>', 5),
            'kits'     => $query->where('tipo', 'kit'),
            default    => null,
        };

        return $query->with('category')
                    ->orderBy($this->sortBy, $this->sortDirection)
                    ->get();
    }

    public function render()
    {
        return view('livewire.sales.add-products', [
            'filteredProducts' => $this->getFilteredProducts()
        ]);
    }
}
