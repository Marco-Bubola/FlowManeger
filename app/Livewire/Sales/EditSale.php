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

class EditSale extends Component
{
    public Sale $sale;
    public $client_id = '';
    public $tipo_pagamento = 'a_vista';
    public $parcelas = 1;
    public $selectedProducts = [];
    public $clients = [];
    public $products = [];
    public $showOnlySelected = false;

    // Propriedades reativas específicas para o Livewire
    protected $rules = [
        'client_id' => 'required|exists:clients,id',
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

        // Restaurar estoque dos produtos antigos
        foreach ($this->sale->saleItems as $oldItem) {
            $product = Product::find($oldItem->product_id);
            if ($product) {
                $product->stock_quantity += $oldItem->quantity;
                $product->save();
            }
        }

        // Verificar estoque dos novos produtos
        foreach ($this->selectedProducts as $item) {
            $product = Product::find($item['product_id']);
            if ($product->stock_quantity < $item['quantity']) {
                // Restaurar o estoque que já foi devolvido
                foreach ($this->sale->saleItems as $oldItem) {
                    $oldProduct = Product::find($oldItem->product_id);
                    if ($oldProduct) {
                        $oldProduct->stock_quantity -= $oldItem->quantity;
                        $oldProduct->save();
                    }
                }
                session()->flash('error', "Estoque insuficiente para o produto: {$product->name}. Disponível: {$product->stock_quantity}, Solicitado: {$item['quantity']}");
                return;
            }
        }

        // Atualizar dados da venda
        $this->sale->update([
            'client_id' => $this->client_id,
            'tipo_pagamento' => $this->tipo_pagamento,
            'parcelas' => $this->tipo_pagamento === 'parcelado' ? $this->getSafeParcelas() : 1,
            'total_price' => $this->getTotalPrice(),
        ]);

        // Deletar itens antigos
        $this->sale->saleItems()->delete();

        // Criar novos itens
        foreach ($this->selectedProducts as $item) {
            $product = Product::find($item['product_id']);

            SaleItem::create([
                'sale_id' => $this->sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'price_sale' => $item['price_sale'],
            ]);

            // Atualizar estoque
            $product->stock_quantity -= $item['quantity'];
            $product->save();
        }

        session()->flash('message', 'Venda atualizada com sucesso!');
        return redirect()->route('sales.show', $this->sale->id);
    }

    public function getFilteredProducts()
    {
        $query = collect($this->products);
        
        // Se showOnlySelected estiver ativo, filtrar apenas produtos selecionados
        if ($this->showOnlySelected) {
            $selectedIds = collect($this->selectedProducts)->pluck('product_id')->filter();
            $query = $query->whereIn('id', $selectedIds);
        }
        
        return $query;
    }

    public function render()
    {
        return view('livewire.sales.edit-sale');
    }
}
