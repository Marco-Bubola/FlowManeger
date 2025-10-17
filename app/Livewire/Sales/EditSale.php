<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class EditSale extends Component
{
    public Sale $sale;
    public $client_id = '';
    public $sale_date = '';
    public $tipo_pagamento = 'a_vista';
    public $parcelas = 1;
    public $selectedProducts = [];
    public $clients = [];
    public $products = [];
    public $showOnlySelected = false;
    public $searchTerm = '';

    // Propriedades reativas específicas para o Livewire
    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'sale_date' => 'required|date',
        'tipo_pagamento' => 'required|in:a_vista,parcelado',
        'parcelas' => 'nullable|integer|min:1|max:12',
        'selectedProducts' => 'required|array|min:1',
        'selectedProducts.*.product_id' => 'required|exists:products,id',
        'selectedProducts.*.quantity' => 'required|integer|min:1',
        'selectedProducts.*.price_sale' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'client_id.required' => 'Selecione um cliente.',
        'client_id.exists' => 'Cliente selecionado não é válido.',
        'sale_date.required' => 'Data da venda é obrigatória.',
        'sale_date.date' => 'Data da venda deve ser uma data válida.',
        'tipo_pagamento.required' => 'Tipo de pagamento é obrigatório.',
        'tipo_pagamento.in' => 'Tipo de pagamento deve ser à vista ou parcelado.',
        'parcelas.integer' => 'Número de parcelas deve ser um número inteiro.',
        'parcelas.min' => 'Número de parcelas deve ser pelo menos 1.',
        'parcelas.max' => 'Número de parcelas não pode ser maior que 12.',
        'selectedProducts.required' => 'Adicione pelo menos um produto.',
        'selectedProducts.min' => 'Adicione pelo menos um produto.',
        'selectedProducts.*.product_id.required' => 'Produto é obrigatório.',
        'selectedProducts.*.product_id.exists' => 'Produto selecionado não é válido.',
        'selectedProducts.*.quantity.required' => 'Quantidade é obrigatória.',
        'selectedProducts.*.quantity.integer' => 'Quantidade deve ser um número inteiro.',
        'selectedProducts.*.quantity.min' => 'Quantidade deve ser pelo menos 1.',
        'selectedProducts.*.price_sale.required' => 'Preço de venda é obrigatório.',
        'selectedProducts.*.price_sale.numeric' => 'Preço de venda deve ser um número.',
        'selectedProducts.*.price_sale.min' => 'Preço de venda deve ser maior que 0.',
    ];

    public function mount($id)
    {
        $this->sale = Sale::with('saleItems.product')->findOrFail($id);

        // Carregar dados
        $this->clients = Client::where('user_id', Auth::id())->get();
        $this->products = Product::where('user_id', Auth::id())->get();

        // Definir valores da venda
        $this->client_id = $this->sale->client_id;
        $this->sale_date = $this->sale->sale_date ?? $this->sale->created_at->format('Y-m-d');
        $this->tipo_pagamento = $this->sale->tipo_pagamento ?? 'a_vista';
        $this->parcelas = max(1, $this->sale->parcelas ?? 1); // Garantir que nunca seja 0

        // Carregar produtos selecionados com valores corretos
        $this->selectedProducts = [];
        foreach ($this->sale->saleItems as $item) {
            $this->selectedProducts[] = [
                'product_id' => (string)$item->product_id,
                'quantity' => (int)$item->quantity,
                'price_sale' => (float)$item->price_sale,
            ];
        }

        // Se não há produtos, adicionar um produto vazio
        if (empty($this->selectedProducts)) {
            $this->addProduct();
        }
    }

    public function addProduct()
    {
        $this->selectedProducts[] = [
            'product_id' => '',
            'quantity' => 1,
            'price_sale' => 0.00,
        ];
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
    }

    public function toggleProduct($productId)
    {
        // Verificar se o produto já está selecionado
        $existingIndex = null;
        foreach ($this->selectedProducts as $index => $selectedProduct) {
            if ($selectedProduct['product_id'] == $productId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Se já está selecionado, remover
            $this->removeProduct($existingIndex);
        } else {
            // Se não está selecionado, adicionar
            $product = collect($this->products)->firstWhere('id', $productId);
            if ($product) {
                $this->selectedProducts[] = [
                    'product_id' => (string)$productId,
                    'quantity' => 1,
                    'price_sale' => (float)$product->price_sale,
                ];
            }
        }
    }

    public function updatedTipoPagamento()
    {
        if ($this->tipo_pagamento === 'a_vista') {
            $this->parcelas = 1;
        } elseif ($this->tipo_pagamento === 'parcelado') {
            if (!$this->parcelas || $this->parcelas <= 1) {
                $this->parcelas = 2;
            }
        }
    }

    public function updatedParcelas($value)
    {
        // Garante que as parcelas estejam dentro do limite e nunca sejam 0
        $value = (int)$value;
        if ($value < 1) {
            $this->parcelas = 1;
        } elseif ($value > 12) {
            $this->parcelas = 12;
        } else {
            $this->parcelas = $value;
        }
    }

    public function updatedSelectedProducts($value, $key)
    {
        // Verificar se é uma mudança de produto
        if (str_contains($key, 'product_id')) {
            // Extrair o índice do produto (ex: de "selectedProducts.0.product_id" extrair "0")
            $keyParts = explode('.', $key);
            if (count($keyParts) >= 2) {
                $index = $keyParts[1];

                if ($value && isset($this->selectedProducts[$index])) {
                    $product = Product::find($value);
                    if ($product) {
                        // Atualizar o preço de venda automaticamente
                        $this->selectedProducts[$index]['price_sale'] = $product->price_sale;

                        // Se a quantidade não foi definida, definir como 1
                        if (!isset($this->selectedProducts[$index]['quantity']) || $this->selectedProducts[$index]['quantity'] <= 0) {
                            $this->selectedProducts[$index]['quantity'] = 1;
                        }
                    }
                }
            }
        }

        // Re-calcular o total sempre que houver mudanças
        $this->dispatch('total-updated');
    }

    public function getTotalPrice()
    {
        $total = 0;
        foreach ($this->selectedProducts as $item) {
            if (isset($item['quantity']) && isset($item['price_sale'])) {
                $total += $item['quantity'] * $item['price_sale'];
            }
        }
        return $total;
    }

    public function getSafeParcelas()
    {
        return max(1, (int)$this->parcelas); // Garantir que nunca seja 0 ou negativo
    }

    public function update()
    {
        // Validação customizada para parcelas quando tipo_pagamento é parcelado
        if ($this->tipo_pagamento === 'parcelado') {
            $this->validate([
                'parcelas' => 'required|integer|min:2|max:12'
            ], [
                'parcelas.required' => 'Número de parcelas é obrigatório para pagamento parcelado.',
                'parcelas.min' => 'Número de parcelas deve ser pelo menos 2 para pagamento parcelado.',
                'parcelas.max' => 'Número de parcelas não pode ser maior que 12.',
            ]);
        }

        $this->validate();

        // Operação em transação: calcular diferenças líquidas de estoque para evitar inconsistências
        DB::transaction(function () {
            // Mapear quantidades antigas por product_id
            $oldQuantities = [];
            foreach ($this->sale->saleItems as $oldItem) {
                $oldQuantities[$oldItem->product_id] = ($oldQuantities[$oldItem->product_id] ?? 0) + $oldItem->quantity;
            }

            // Mapear quantidades novas por product_id
            $newQuantities = [];
            foreach ($this->selectedProducts as $item) {
                $newQuantities[$item['product_id']] = ($newQuantities[$item['product_id']] ?? 0) + $item['quantity'];
            }

            // Para cada produto envolvido, calcular diferença (novo - antigo).
            // Se diff > 0, precisamos reduzir estoque; se diff < 0, devemos restaurar estoque.
            $productIds = array_unique(array_merge(array_keys($oldQuantities), array_keys($newQuantities)));

            // Verificar disponibilidade antes de aplicar alterações
            foreach ($productIds as $pid) {
                $oldQ = $oldQuantities[$pid] ?? 0;
                $newQ = $newQuantities[$pid] ?? 0;
                $diff = $newQ - $oldQ;

                if ($diff > 0) {
                    $product = Product::find($pid);
                    if (!$product || $product->stock_quantity < $diff) {
                        // Falha: estoque insuficiente para aplicar incremento
                        throw new \Exception("Estoque insuficiente para o produto: {$product->name}");
                    }
                }
            }

            // Atualizar dados da venda
            $this->sale->update([
                'client_id' => $this->client_id,
                'sale_date' => $this->sale_date,
                'tipo_pagamento' => $this->tipo_pagamento,
                'parcelas' => $this->tipo_pagamento === 'parcelado' ? $this->getSafeParcelas() : 1,
                'total_price' => $this->getTotalPrice(),
            ]);

            // Apagar itens antigos
            $this->sale->saleItems()->delete();

            // Criar os novos itens e aplicar diferenças no estoque
            foreach ($this->selectedProducts as $item) {
                $product = Product::find($item['product_id']);

                SaleItem::create([
                    'sale_id' => $this->sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'price_sale' => $item['price_sale'],
                ]);
            }

            // Aplicar alterações de estoque por produto (novo - antigo)
            foreach ($productIds as $pid) {
                $oldQ = $oldQuantities[$pid] ?? 0;
                $newQ = $newQuantities[$pid] ?? 0;
                $diff = $newQ - $oldQ; // se positivo -> diminuir estoque; se negativo -> aumentar

                $product = Product::find($pid);
                if ($product && $diff !== 0) {
                    $product->stock_quantity -= $diff; // subtrair diff (se diff negativo, soma)
                    $product->save();
                }
            }
        });

        session()->flash('message', 'Venda atualizada com sucesso!');
        return redirect()->route('sales.show', $this->sale->id);
    }

    public function getFilteredProducts()
    {
        $query = collect($this->products);

        // Filtrar por termo de busca
        if ($this->searchTerm) {
            $searchTerm = strtolower($this->searchTerm);
            $query = $query->filter(function($product) use ($searchTerm) {
                return str_contains(strtolower($product->name), $searchTerm) ||
                       str_contains(strtolower($product->product_code), $searchTerm) ||
                       ($product->category && str_contains(strtolower($product->category->name), $searchTerm));
            });
        }

        // Se showOnlySelected estiver ativo, filtrar apenas produtos selecionados
        if ($this->showOnlySelected) {
            $selectedIds = collect($this->selectedProducts)->pluck('product_id')->filter();
            $query = $query->whereIn('id', $selectedIds);
        }

        return $query;
    }

    public function incrementQuantity($index)
    {
        if (isset($this->selectedProducts[$index])) {
            $this->selectedProducts[$index]['quantity'] = ($this->selectedProducts[$index]['quantity'] ?? 0) + 1;
        }
    }

    public function decrementQuantity($index)
    {
        if (isset($this->selectedProducts[$index])) {
            $currentQuantity = $this->selectedProducts[$index]['quantity'] ?? 1;
            if ($currentQuantity > 1) {
                $this->selectedProducts[$index]['quantity'] = $currentQuantity - 1;
            }
        }
    }

    public function render()
    {
        return view('livewire.sales.edit-sale');
    }
}
