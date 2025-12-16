<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\On;

class ExportProductCard extends Component
{
    public $product;
    public $showModal = false;
    public $exportType = 'complete'; // 'complete' ou 'public'
    public $selectedProducts = [];

    #[On('openExportModal')]
    public function openExportModal($productId)
    {
        $this->product = Product::with('category')->findOrFail($productId);
        $this->showModal = true;
    }

    public function openBulkExportModal($productIds)
    {
        $this->selectedProducts = Product::with('category')->whereIn('id', $productIds)->get();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->product = null;
        $this->selectedProducts = [];
        $this->exportType = 'complete';
    }

    public function render()
    {
        return view('livewire.products.export-product-card');
    }
}
