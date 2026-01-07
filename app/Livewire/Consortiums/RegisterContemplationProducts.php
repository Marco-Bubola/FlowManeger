<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\ConsortiumContemplation;
use App\Models\Product;

class RegisterContemplationProducts extends Component
{
    public $contemplationId;
    public $redemptionType = 'pending';
    public $redemptionValue = 0;
    public $selectedProducts = [];
    public $showModal = false;

    public function mount(ConsortiumContemplation $contemplation)
    {
        $this->contemplationId = $contemplation->id;
        $this->redemptionType = $contemplation->redemption_type;
        $this->redemptionValue = $contemplation->redemption_value ?? 0;

        if ($contemplation->products) {
            $this->selectedProducts = json_decode($contemplation->products, true) ?? [];
        }
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);

        if ($product && !collect($this->selectedProducts)->contains('id', $productId)) {
            $this->selectedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'value' => $product->value,
            ];

            $this->redemptionValue = collect($this->selectedProducts)->sum('value');
        }
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->redemptionValue = collect($this->selectedProducts)->sum('value');
    }

    public function saveRedemption()
    {
        $this->validate([
            'redemptionType' => 'required|in:cash,products,pending',
        ]);

        $contemplation = ConsortiumContemplation::find($this->contemplationId);

        $data = [
            'redemption_type' => $this->redemptionType,
            'status' => $this->redemptionType === 'pending' ? 'pending' : 'redeemed',
        ];

        if ($this->redemptionType === 'products') {
            $data['products'] = json_encode($this->selectedProducts);
            $data['redemption_value'] = $this->redemptionValue;
        } elseif ($this->redemptionType === 'cash') {
            $data['redemption_value'] = $this->redemptionValue;
            $data['products'] = null;
        }

        $contemplation->update($data);

        session()->flash('success', 'Resgate registrado com sucesso!');
        $this->closeModal();
        $this->dispatch('contemplation-updated');
    }

    public function render()
    {
        $availableProducts = Product::orderBy('name')->get();

        return view('livewire.consortiums.register-contemplation-products', [
            'availableProducts' => $availableProducts,
        ]);
    }
}
