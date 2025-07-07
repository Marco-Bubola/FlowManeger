<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditPrices extends Component
{
    public $sale;
    public $saleItems = [];

    public function mount($saleId)
    {
        $this->sale = Sale::where('id', $saleId)
            ->where('user_id', Auth::id())
            ->with(['saleItems.product', 'client'])
            ->firstOrFail();

        $this->loadSaleItems();
    }

    public function loadSaleItems()
    {
        $this->saleItems = [];
        
        foreach ($this->sale->saleItems as $item) {
            $this->saleItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price_sale' => $item->price_sale,
                'original_price' => $item->product->price_sale,
                'subtotal' => $item->quantity * $item->price_sale,
            ];
        }
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            $this->addError("saleItems.{$index}.quantity", 'A quantidade deve ser maior que zero.');
            return;
        }

        $this->saleItems[$index]['quantity'] = $quantity;
        $this->saleItems[$index]['subtotal'] = $quantity * $this->saleItems[$index]['price_sale'];
        $this->resetErrorBag("saleItems.{$index}.quantity");
    }

    public function updatePrice($index, $price)
    {
        if ($price <= 0) {
            $this->addError("saleItems.{$index}.price_sale", 'O preço deve ser maior que zero.');
            return;
        }

        $this->saleItems[$index]['price_sale'] = $price;
        $this->saleItems[$index]['subtotal'] = $this->saleItems[$index]['quantity'] * $price;
        $this->resetErrorBag("saleItems.{$index}.price_sale");
    }

    public function removeSaleItem($index)
    {
        if (count($this->saleItems) <= 1) {
            $this->addError('general', 'Não é possível remover o último item da venda.');
            return;
        }

        $itemId = $this->saleItems[$index]['id'];
        
        DB::transaction(function () use ($itemId, $index) {
            SaleItem::where('id', $itemId)->delete();
            unset($this->saleItems[$index]);
            $this->saleItems = array_values($this->saleItems);
        });

        $this->dispatch('success', 'Produto removido da venda com sucesso!');
    }

    public function savePrices()
    {
        $this->validate([
            'saleItems.*.quantity' => 'required|integer|min:1',
            'saleItems.*.price_sale' => 'required|numeric|min:0.01',
        ], [
            'saleItems.*.quantity.required' => 'A quantidade é obrigatória.',
            'saleItems.*.quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'saleItems.*.quantity.min' => 'A quantidade deve ser maior que zero.',
            'saleItems.*.price_sale.required' => 'O preço é obrigatório.',
            'saleItems.*.price_sale.numeric' => 'O preço deve ser um número válido.',
            'saleItems.*.price_sale.min' => 'O preço deve ser maior que zero.',
        ]);

        try {
            DB::transaction(function () {
                foreach ($this->saleItems as $item) {
                    SaleItem::where('id', $item['id'])->update([
                        'quantity' => $item['quantity'],
                        'price_sale' => $item['price_sale'],
                    ]);
                }

                // Recalcular o total da venda
                $this->sale->calculateTotal();
            });

            $this->dispatch('success', 'Preços atualizados com sucesso!');
            return redirect()->route('sales.show', $this->sale->id);

        } catch (\Exception $e) {
            $this->dispatch('error', 'Erro ao atualizar preços: ' . $e->getMessage());
        }
    }

    public function getTotalProperty()
    {
        return array_sum(array_column($this->saleItems, 'subtotal'));
    }

    public function render()
    {
        return view('livewire.sales.edit-prices');
    }
}
