<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\VendaParcela;
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

        session()->flash('success', 'Produto removido da venda com sucesso!');
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
                $this->sale->refresh();

                // Recalcular parcelas se a venda for parcelada
                $this->recalcularParcelas();
            });

            session()->flash('success', 'Preços atualizados com sucesso!');
            return redirect()->route('sales.show', $this->sale->id);

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao atualizar preços: ' . $e->getMessage());
        }
    }

    /**
     * Recalcula e atualiza as parcelas da venda
     * Protegido contra divisão por zero e modificação de parcelas pagas
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

        // Contar apenas parcelas pendentes para recalcular
        $parcelasPendentes = $parcelasExistentes->where('status', '!=', 'paga');
        $numeroParcelas = $parcelasPendentes->count();

        // Proteção contra divisão por zero
        if ($numeroParcelas === 0) {
            return; // Todas as parcelas já foram pagas
        }

        $totalVenda = $this->sale->total_price;

        // Calcular quanto já foi pago
        $totalPago = $parcelasExistentes->where('status', 'paga')->sum('valor');

        // Valor restante a ser dividido entre parcelas pendentes
        $valorRestante = $totalVenda - $totalPago;
        $valorParcela = round($valorRestante / $numeroParcelas, 2);

        // Atualizar apenas as parcelas pendentes
        foreach ($parcelasExistentes as $parcela) {
            // IMPORTANTE: Não modificar parcelas pagas
            if ($parcela->status !== 'paga') {
                $parcela->update([
                    'valor' => $valorParcela
                ]);
            }
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
