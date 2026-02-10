<?php

namespace App\Livewire\MercadoLivre;

use App\Models\Product;
use App\Models\MlPublication;
use Livewire\Component;
use Livewire\Attributes\On;

class ProductPublicationsModal extends Component
{
    public bool $showModal = false;
    public ?Product $product = null;
    public $publications = [];

    #[On('openPublicationsModal')]
    public function openModal($productId)
    {
        $this->product = Product::find($productId);
        
        if ($this->product) {
            // Buscar todas as publicações que contém este produto
            $this->publications = MlPublication::whereHas('products', function($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->with(['products' => function($query) use ($productId) {
                $query->where('product_id', $productId);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
            
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->product = null;
        $this->publications = [];
    }

    public function render()
    {
        return view('livewire.mercadolivre.product-publications-modal');
    }
}
