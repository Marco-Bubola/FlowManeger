<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProdutoComponente;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditKit extends Component
{
    use WithFileUploads, HasNotifications;

    public Product $kit;

    // Propriedades do formulário
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $price_sale = '';
    public string $category_id = '';
    public string $product_code = '';
    public $image;
    public array $selectedProducts = [];

    // Propriedades para custos adicionais
    public string $additional_costs = '0';
    public string $additional_costs_description = '';

    // Propriedades para preços calculados
    public float $calculated_cost_price = 0;
    public float $calculated_sale_price = 0;
    public string $real_sale_price = '';

    // Propriedades para filtros e busca
    public string $searchTerm = '';
    public string $selectedCategory = '';

    // Propriedades para steps
    public int $currentStep = 1;

    protected $rules = [
        'name' => 'required|string|max:255',
        'product_code' => 'required|string|max:100',
        'category_id' => 'required|exists:category,id_category',
        'description' => 'nullable|string|max:1000',
        'image' => 'nullable|image|max:2048', // 2MB max
        'additional_costs' => 'nullable|string',
        'additional_costs_description' => 'nullable|string|max:500',
        'real_sale_price' => 'nullable|string',
    ];

    protected $messages = [
        'name.required' => 'O nome do kit é obrigatório.',
        'name.max' => 'O nome do kit não pode ter mais de 255 caracteres.',
        'product_code.required' => 'O código do produto é obrigatório.',
        'product_code.max' => 'O código do produto não pode ter mais de 100 caracteres.',
        'category_id.required' => 'A categoria é obrigatória.',
        'category_id.exists' => 'A categoria selecionada não existe.',
        'description.max' => 'A descrição não pode ter mais de 1000 caracteres.',
        'image.image' => 'O arquivo deve ser uma imagem.',
        'image.max' => 'A imagem não pode ser maior que 2MB.',
        'additional_costs_description.max' => 'A descrição dos custos não pode ter mais de 500 caracteres.',
    ];

    public function mount(Product $kit)
    {
        if ($kit->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($kit->tipo !== 'kit') {
            abort(404, 'Product is not a kit.');
        }

        $this->kit = $kit;

        // Carregar dados do kit
        $this->name = $kit->name;
        $this->description = $kit->description ?? '';
        $this->category_id = (string) $kit->category_id;
        $this->product_code = $kit->product_code;
        $this->additional_costs = number_format($kit->additional_costs ?? 0, 2, ',', '.');
        $this->additional_costs_description = $kit->additional_costs_description ?? '';
        $this->price = number_format($kit->price, 2, ',', '.');
        $this->price_sale = number_format($kit->price_sale, 2, ',', '.');
        $this->real_sale_price = number_format($kit->price_sale, 2, ',', '.');

        // Carregar componentes do kit
        $this->loadKitComponents();

        // Calcular totais
        $this->calculateTotals();
    }

    private function loadKitComponents()
    {
        $kitComponents = ProdutoComponente::where('kit_produto_id', $this->kit->id)
            ->with('componente')
            ->get();

        foreach ($kitComponents as $component) {
            $product = $component->componente;
            if ($product) {
                $this->selectedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'image' => $product->image,
                    'stock_quantity' => $product->stock_quantity,
                    'quantity' => $component->quantidade,
                    'price' => $product->price,
                    'salePrice' => $product->price_sale,
                    'product' => $product
                ];
            }
        }
    }

    /**
     * Calcula os totais do kit baseado nos produtos selecionados e custos adicionais
     */
    public function calculateTotals()
    {
        $productsTotal = collect($this->selectedProducts)->sum(function ($product) {
            return ($product['price'] ?? 0) * ($product['quantity'] ?? 1);
        });

        $productsSaleTotal = collect($this->selectedProducts)->sum(function ($product) {
            return ($product['salePrice'] ?? 0) * ($product['quantity'] ?? 1);
        });

        $additionalCosts = $this->convertBrazilianToFloat($this->additional_costs);

        $this->calculated_cost_price = $productsTotal + $additionalCosts;
        $this->calculated_sale_price = $productsSaleTotal + $additionalCosts;

        // Atualizar os campos de preço para usar os valores calculados
        $this->price = number_format($this->calculated_cost_price, 2, ',', '.');
        $this->price_sale = number_format($this->calculated_sale_price, 2, ',', '.');

        // Disparar evento JavaScript para atualizar a interface
        $this->dispatch('totals-updated', [
            'productsTotal' => $productsTotal,
            'additionalCosts' => $additionalCosts,
            'kitTotal' => $this->calculated_cost_price
        ]);
    }

    /**
     * Alterna a seleção de um produto
     */
    public function toggleProduct($productId)
    {
        $existingIndex = array_search($productId, array_column($this->selectedProducts, 'id'));

        if ($existingIndex !== false) {
            // Remove o produto se já estiver selecionado
            array_splice($this->selectedProducts, $existingIndex, 1);
        } else {
            // Adiciona o produto se não estiver selecionado
            $product = $this->availableProducts->firstWhere('id', $productId);
            if ($product) {
                $this->selectedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'image' => $product->image,
                    'stock_quantity' => $product->stock_quantity,
                    'quantity' => 1,
                    'price' => $product->price,
                    'salePrice' => $product->price_sale,
                    'product' => $product
                ];
            }
        }

        $this->calculateTotals();
    }

    /**
     * Remove um produto da seleção
     */
    public function removeProduct($productId)
    {
        $this->selectedProducts = array_filter($this->selectedProducts, function($product) use ($productId) {
            return $product['id'] != $productId;
        });

        // Reindexar o array
        $this->selectedProducts = array_values($this->selectedProducts);

        $this->calculateTotals();
    }

    /**
     * Atualiza a quantidade de um produto selecionado
     */
    public function updateProductQuantity($productId, $quantity)
    {
        foreach ($this->selectedProducts as &$product) {
            if ($product['id'] == $productId) {
                $product['quantity'] = max(1, (int)$quantity);
                break;
            }
        }

        $this->calculateTotals();
    }

    /**
     * Usa o preço sugerido como preço real
     */
    public function usesSuggestedPrice()
    {
        $suggestedPrice = $this->suggestedSalePrice ?? 0;
        if ($suggestedPrice > 0) {
            $this->real_sale_price = number_format($suggestedPrice, 2, ',', '.');
        } else {
            $this->notifyWarning('Não há preço sugerido disponível. Selecione produtos primeiro.');
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:category,id_category',
            'product_code' => 'required|max:100|unique:products,product_code,' . $this->kit->id,
            'additional_costs' => 'nullable|string',
            'additional_costs_description' => 'nullable|string',
            'real_sale_price' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function update()
    {
        try {
            // Calcular totais antes de salvar
            $this->calculateTotals();

            // Usar os preços calculados
            $price = $this->calculated_cost_price;

            // Usa o preço de venda real se foi definido, senão usa o calculado
            if (!empty($this->real_sale_price)) {
                // Converte formato brasileiro (1.905,66) para formato decimal (1905.66)
                $price_sale = $this->convertBrazilianToFloat($this->real_sale_price);
            } else {
                $price_sale = $this->calculated_sale_price;
            }

            // Validação dinâmica para product_code único (excluindo o produto atual)
            $this->validate([
                'name' => 'required|string|max:255',
                'product_code' => 'required|string|max:100|unique:products,product_code,' . $this->kit->id . ',id,user_id,' . Auth::id(),
                'category_id' => 'required|exists:category,id_category',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|max:2048',
                'additional_costs' => 'nullable|string',
                'additional_costs_description' => 'nullable|string|max:500',
                'real_sale_price' => 'nullable|string',
            ]);

            // Verifica se pelo menos um produto foi selecionado
            if (empty($this->selectedProducts)) {
                $this->addError('selectedProducts', 'Selecione pelo menos um produto para o kit.');
                return;
            }

            // Upload da imagem se fornecida
            $imageName = $this->kit->image; // Mantém a imagem atual por padrão
            if ($this->image) {
                // Remove a imagem antiga se existir
                if ($this->kit->image && Storage::disk('public')->exists('products/' . $this->kit->image)) {
                    Storage::disk('public')->delete('products/' . $this->kit->image);
                }

                // Garante que o diretório existe
                if (!Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }

                $imageName = $this->image->store('products', 'public');
                $imageName = basename($imageName); // Remove o path, deixa apenas o nome do arquivo
            }

            // Atualiza o kit
            $this->kit->update([
                'name' => $this->name,
                'description' => $this->description,
                'price' => $price,
                'price_sale' => $price_sale,
                'category_id' => (int)$this->category_id,
                'product_code' => $this->product_code,
                'image' => $imageName,
                'custos_adicionais' => $this->convertBrazilianToFloat($this->additional_costs),
                'descricao_custos_adicionais' => $this->additional_costs_description,
            ]);

            // Remove componentes antigos e adiciona os novos
            ProdutoComponente::where('kit_produto_id', $this->kit->id)->delete();

            // Salva os componentes do kit
            foreach ($this->selectedProducts as $productData) {
                if (isset($productData['id']) && isset($productData['quantity']) && $productData['quantity'] > 0) {
                    ProdutoComponente::create([
                        'kit_produto_id' => $this->kit->id,
                        'componente_produto_id' => $productData['id'],
                        'quantidade' => $productData['quantity'],
                        'preco_custo_unitario' => $productData['price'] ?? 0,
                        'preco_venda_unitario' => $productData['salePrice'] ?? 0,
                    ]);
                }
            }

            // Emite evento para atualizar a lista
            $this->dispatch('kit-updated');

            $this->notifySuccess('Kit atualizado com sucesso!');

            // Redireciona para a lista
            return redirect()->route('products.index');

        } catch (\Exception $e) {
            $this->notifyError('Erro ao atualizar kit: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::debug("Erro ao atualizar kit:", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return;
        }
    }

    public function nextStep()
    {
        // Validações específicas por step
        if ($this->currentStep == 1) {
            $this->validate([
                'name' => 'required|string|max:255',
                'product_code' => 'required|string|max:100|unique:products,product_code,' . $this->kit->id . ',id,user_id,' . Auth::id(),
                'category_id' => 'required|exists:category,id_category',
                'description' => 'nullable|string|max:1000',
            ]);
        } elseif ($this->currentStep == 2) {
            if (empty($this->selectedProducts)) {
                $this->addError('selectedProducts', 'Selecione pelo menos um produto para o kit.');
                return;
            }
        }

        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function store()
    {
        return $this->update();
    }

    /**
     * Converte valor em formato brasileiro (1.905,66) para float (1905.66)
     */
    private function convertBrazilianToFloat($value)
    {
        // Remove espaços em branco
        $value = trim($value);

        // Se está vazio, retorna 0
        if (empty($value)) {
            return 0.00;
        }

        // Remove caracteres não numéricos exceto vírgula e ponto
        $value = preg_replace('/[^\d,.]/', '', $value);

        // Se contém tanto vírgula quanto ponto
        if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
            // Formato brasileiro: 1.905,66 -> remove pontos e troca vírgula por ponto
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        // Se contém apenas vírgula (ex: 1905,66)
        elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }
        // Se contém apenas ponto, mantém como está (já está no formato correto)

        return (float) $value;
    }

    /**
     * Calcula o preço de venda sugerido com margem de 5%
     */
    public function getSuggestedSalePriceProperty()
    {
        // Garante que temos um valor calculado antes de aplicar a margem
        $baseSalePrice = $this->calculated_sale_price ?? 0;
        return $baseSalePrice * 1.05;
    }

    public function getSelectedCategoryNameProperty()
    {
        if ($this->category_id) {
            $category = Category::find($this->category_id);
            return $category ? $category->name : 'Escolha uma categoria...';
        }
        return 'Escolha uma categoria...';
    }

    public function getSelectedCategoryIconProperty()
    {
        if ($this->category_id) {
            $category = Category::find($this->category_id);
            return $category ? $this->getCategoryIcon($category->icone) : 'bi-grid-3x3-gap-fill';
        }
        return 'bi-grid-3x3-gap-fill';
    }

    private function getCategoryIcon($iconName)
    {
        $iconMap = [
            'perfume' => 'bi-flower1',
            'cosmetico' => 'bi-palette',
            'higiene' => 'bi-droplet',
            'alimentacao' => 'bi-cup-straw',
            'casa' => 'bi-house',
            'vestuario' => 'bi-person',
            'eletronico' => 'bi-phone',
            'livro' => 'bi-book',
            'esporte' => 'bi-bicycle',
            'brinquedo' => 'bi-balloon',
        ];

        return $iconMap[$iconName] ?? 'bi-grid-3x3-gap-fill';
    }

    public function getAvailableProductsProperty()
    {
        return Product::where('user_id', Auth::id())
            ->where('tipo', 'simples')
            ->where('status', 'ativo')
            ->get();
    }

    public function getFilteredProductsProperty()
    {
        $query = Product::where('user_id', Auth::id())
            ->where('tipo', 'simples')
            ->where('status', 'ativo');

        // Filtro por categoria
        if (!empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        // Filtro por termo de busca
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('product_code', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->get();
    }

    public function getCategoriesProperty()
    {
        return Category::where('user_id', Auth::id())
            ->where('type', 'product')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.products.edit-kit', [
            'availableProducts' => $this->availableProducts,
            'filteredProducts' => $this->filteredProducts,
            'categories' => $this->categories,
        ]);
    }
}
