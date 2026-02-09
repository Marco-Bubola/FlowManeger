<?php

namespace App\Http\Controllers\MercadoLivre;

use App\Http\Controllers\Controller;
use App\Livewire\MercadoLivre\ProductIntegration;

class ProductIntegrationController extends Controller
{
    public function index()
    {
        return view('livewire.mercadolivre.product-integration');
    }
}
