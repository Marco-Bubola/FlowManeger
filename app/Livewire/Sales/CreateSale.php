<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\VendaParcela;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CreateSale extends Component
{
    public int $currentStep = 1;
    public $client_id = '';
    public $sale_date;
    public $tipo_pagamento = 'a_vista';
    public $parcelas = 1;
    public $products = []; // Array de produtos adicionados
    public $clients = [];
    public $availableProducts = [];
    public $selectedProducts = []; // Array com IDs dos produtos selecionados
    public $searchProduct = ''; // Campo de pesquisa de produtos
    public $showOnlySelected = false; // Filtro para mostrar apenas produtos selecionados

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'sale_date' => 'required|date',
        'tipo_pagamento' => 'required|in:a_vista,parcelado',
        'parcelas' => 'nullable|integer|min:1',
        'products' => 'required|array|min:1',
        'products.*.product_id' => 'required|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1',
        'products.*.unit_price' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'client_id.required' => 'Selecione um cliente.',
        'sale_date.required' => 'Data da venda é obrigatória.',
        'products.required' => 'Adicione pelo menos um produto.',
        'products.*.product_id.required' => 'Produto é obrigatório.',
        'products.*.quantity.required' => 'Quantidade é obrigatória.',
        'products.*.quantity.min' => 'Quantidade deve ser pelo menos 1.',
        'products.*.unit_price.required' => 'Preço unitário é obrigatório.',
        'products.*.unit_price.min' => 'Preço unitário deve ser maior que 0.',
    ];

    public function mount()
    {
        $this->clients = Client::where('user_id', Auth::id())->get();
        $this->availableProducts = Product::where('user_id', Auth::id())
                                        ->where('stock_quantity', '>', 0)
                                        ->get();
        $this->sale_date = now()->format('Y-m-d');
    }

    public function updatedProducts($value, $key)
    {
        // Quando um produto é selecionado, auto-preenche o preço
        if (str_contains($key, '.product_id')) {
            $index = explode('.', $key)[0];
            if ($value) {
                $product = Product::find($value);
                if ($product) {
                    $this->products[$index]['unit_price'] = $product->price_sale;
                }
            }
        }
    }

    public function getSelectedProduct($productId)
    {
        return Product::find($productId);
    }

    public function getTotalPrice()
    {
        $total = 0;
        foreach ($this->products as $item) {
            if (isset($item['product_id'], $item['quantity'], $item['unit_price'])
                && $item['product_id'] && $item['quantity'] && $item['unit_price']) {
                $total += $item['quantity'] * $item['unit_price'];
            }
        }
        return $total;
    }

    public function getTotalAmount()
    {
        return $this->getTotalPrice();
    }

    public function save()
    {
        try {
            // Debug: Log dos dados recebidos
            Log::info('Tentativa de criar venda', [
                'client_id' => $this->client_id,
                'products' => $this->products,
                'tipo_pagamento' => $this->tipo_pagamento,
                'sale_date' => $this->sale_date
            ]);

            // Verificar se há produtos selecionados
            if (empty($this->products)) {
                session()->flash('error', 'Adicione pelo menos um produto à venda.');
                return;
            }

            // Verificar se o cliente foi selecionado
            if (empty($this->client_id)) {
                session()->flash('error', 'Selecione um cliente para a venda.');
                return;
            }

            $this->validate();

            // Verificar estoque
            foreach ($this->products as $item) {
                $product = Product::find($item['product_id']);
            if ($product->stock_quantity < $item['quantity']) {
                session()->flash('error', "Estoque insuficiente para o produto: {$product->name}");
                return;
            }
        }

        $totalPrice = $this->getTotalPrice();

        // Criar a venda
        $sale = Sale::create([
            'client_id' => $this->client_id,
            'user_id' => Auth::id(),
            'status' => 'pendente',
            'total_price' => $totalPrice,
            'tipo_pagamento' => $this->tipo_pagamento,
            'parcelas' => $this->tipo_pagamento === 'parcelado' ? $this->parcelas : 1,
        ]);

        // Criar os itens da venda
        foreach ($this->products as $item) {
            $product = Product::find($item['product_id']);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'price_sale' => $item['unit_price'],
            ]);

            // Atualizar estoque
            $product->stock_quantity -= $item['quantity'];
            $product->save();
        }

        // Gerar parcelas se for parcelado
        if ($sale->tipo_pagamento === 'parcelado' && $sale->parcelas > 1) {
            $this->gerarParcelasVenda($sale);
        }

        session()->flash('message', 'Venda criada com sucesso!');
        return redirect()->route('sales.index');

        } catch (\Exception $e) {
            Log::error('Erro ao criar venda', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Erro ao criar venda: ' . $e->getMessage());
        }
    }

    private function gerarParcelasVenda($sale)
    {
        $valorParcela = round($sale->total_price / $sale->parcelas, 2);
        $dataPrimeira = now();

        for ($i = 1; $i <= $sale->parcelas; $i++) {
            $dataVencimento = $dataPrimeira->copy()->addMonths($i - 1);
            VendaParcela::create([
                'sale_id' => $sale->id,
                'numero_parcela' => $i,
                'valor' => $valorParcela,
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'status' => 'pendente',
            ]);
        }
    }

    public function toggleProduct($productId)
    {
        if (in_array($productId, $this->selectedProducts)) {
            // Remove produto da seleção
            $this->selectedProducts = array_filter($this->selectedProducts, function($id) use ($productId) {
                return $id != $productId;
            });

            // Remove do array de produtos
            $this->products = array_filter($this->products, function($product) use ($productId) {
                return $product['product_id'] != $productId;
            });
            $this->products = array_values($this->products);
        } else {
            // Adiciona produto à seleção
            $this->selectedProducts[] = $productId;

            // Adiciona ao array de produtos
            $product = Product::find($productId);
            if ($product) {
                $this->products[] = [
                    'product_id' => $productId,
                    'quantity' => 1,
                    'unit_price' => $product->price_sale,
                ];
            }
        }
    }

    public function getFilteredProducts()
    {
        $query = collect($this->availableProducts);

        // Filtro por pesquisa
        if ($this->searchProduct) {
            $query = $query->filter(function($product) {
                return str_contains(strtolower($product->name), strtolower($this->searchProduct)) ||
                       str_contains(strtolower($product->product_code), strtolower($this->searchProduct));
            });
        }

        // Filtro para mostrar apenas selecionados
        if ($this->showOnlySelected) {
            $query = $query->filter(function($product) {
                return in_array($product->id, $this->selectedProducts);
            });
        }

        return $query;
    }

    public function updateProductQuantity($productId, $quantity)
    {
        foreach ($this->products as $index => $product) {
            if ($product['product_id'] == $productId) {
                $this->products[$index]['quantity'] = max(1, intval($quantity));
                break;
            }
        }
    }

    public function updateProductPrice($productId, $price)
    {
        foreach ($this->products as $index => $product) {
            if ($product['product_id'] == $productId) {
                $this->products[$index]['unit_price'] = max(0, floatval($price));
                break;
            }
        }
    }

    public function getProductQuantity($productId)
    {
        foreach ($this->products as $product) {
            if ($product['product_id'] == $productId) {
                return $product['quantity'];
            }
        }
        return 1;
    }

    public function getProductPrice($productId)
    {
        foreach ($this->products as $product) {
            if ($product['product_id'] == $productId) {
                return $product['unit_price'];
            }
        }
        return 0;
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

    public function render()
    {
        return view('livewire.sales.create-sale');
    }
}
