<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditSale extends Component
{
    public Sale $sale;
    public $client_id;
    public $tipo_pagamento;
    public $parcelas;
    public $selectedProducts = [];
    public $clients = [];
    public $products = [];

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'tipo_pagamento' => 'required|in:a_vista,parcelado',
        'parcelas' => 'nullable|integer|min:1',
        'selectedProducts' => 'required|array|min:1',
        'selectedProducts.*.product_id' => 'required|exists:products,id',
        'selectedProducts.*.quantity' => 'required|integer|min:1',
        'selectedProducts.*.price_sale' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'client_id.required' => 'Selecione um cliente.',
        'selectedProducts.required' => 'Adicione pelo menos um produto.',
        'selectedProducts.*.product_id.required' => 'Produto é obrigatório.',
        'selectedProducts.*.quantity.required' => 'Quantidade é obrigatória.',
        'selectedProducts.*.quantity.min' => 'Quantidade deve ser pelo menos 1.',
        'selectedProducts.*.price_sale.required' => 'Preço de venda é obrigatório.',
        'selectedProducts.*.price_sale.min' => 'Preço de venda deve ser maior que 0.',
    ];

    public function mount($id)
    {
        $this->sale = Sale::with('saleItems.product')->findOrFail($id);
        $this->client_id = $this->sale->client_id;
        $this->tipo_pagamento = $this->sale->tipo_pagamento;
        $this->parcelas = $this->sale->parcelas;

        // Carregar produtos selecionados
        $this->selectedProducts = $this->sale->saleItems->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price_sale' => $item->price_sale,
            ];
        })->toArray();

        $this->clients = Client::where('user_id', Auth::id())->get();
        $this->products = Product::where('user_id', Auth::id())->get();
    }

    public function addProduct()
    {
        $this->selectedProducts[] = [
            'product_id' => '',
            'quantity' => 1,
            'price_sale' => 0,
        ];
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
    }

    public function updatedSelectedProducts($value, $key)
    {
        if (str_contains($key, 'product_id')) {
            $index = explode('.', $key)[0];
            if ($value) {
                $product = Product::find($value);
                if ($product) {
                    $this->selectedProducts[$index]['price_sale'] = $product->price_sale;
                }
            }
        }
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

    public function update()
    {
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
                session()->flash('error', "Estoque insuficiente para o produto: {$product->name}");
                return;
            }
        }

        // Atualizar dados da venda
        $this->sale->update([
            'client_id' => $this->client_id,
            'tipo_pagamento' => $this->tipo_pagamento,
            'parcelas' => $this->tipo_pagamento === 'parcelado' ? $this->parcelas : 1,
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

    public function render()
    {
        return view('livewire.sales.edit-sale');
    }
}
