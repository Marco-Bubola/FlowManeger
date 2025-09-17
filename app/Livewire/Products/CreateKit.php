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

class CreateKit extends Component
{
    use WithFileUploads, HasNotifications;

    public int $currentStep = 1;

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

    public function mount()
    {
        // Inicializa como array vazio - os produtos serão adicionados via toggleProduct
        $this->selectedProducts = [];

        // Inicializa os totais calculados
        $this->calculateTotals();
    }

    /**
     * Avança para a próxima etapa
     */
    public function nextStep()
    {
        // Validação específica por etapa
        if ($this->currentStep == 1) {
            $this->validate([
                'name' => 'required|max:255',
                'category_id' => 'required|exists:category,id_category',
                'product_code' => 'required',
            ]);
        } elseif ($this->currentStep == 2) {
            // Valida se pelo menos um produto foi selecionado
            if (empty($this->selectedProducts)) {
                $this->addError('selectedProducts', 'Selecione pelo menos um produto para o kit.');
                return;
            }
        }

        $this->currentStep = min($this->currentStep + 1, 3);
    }    /**
     * Atualiza os totais quando custos adicionais mudam
     */
    public function updatedAdditionalCosts()
    {
        $this->calculateTotals();
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
    }    /**
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
    }    /**
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
     * Atualiza o preço de um produto selecionado
     */
    public function updateProductPrice($productId, $price)
    {
        foreach ($this->selectedProducts as &$product) {
            if ($product['id'] == $productId) {
                $product['salePrice'] = (float)str_replace(',', '.', $price);
                break;
            }
        }

        $this->calculateTotals();
    }

    /**
     * Usa o preço sugerido como preço de venda real
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

    /**
     * Retorna para a etapa anterior
     */
    public function previousStep()
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'category_id' => 'required|exists:category,id_category',
            'product_code' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
            'selectedProducts' => 'required|array|min:1',
            'additional_costs' => 'nullable|string',
            'additional_costs_description' => 'nullable|string|max:255',
        ];
    }

    public function store()
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

            // Validação do formulário
            $this->validate();

            // Verifica se pelo menos um produto foi selecionado
            if (empty($this->selectedProducts)) {
                $this->addError('selectedProducts', 'Selecione pelo menos um produto para o kit.');
                $this->currentStep = 2; // Volta para o step de seleção de produtos
                return;
            }

            // Upload da imagem se fornecida
            $imageName = null;
            if ($this->image) {
                // Garante que o diretório existe
                if (!Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }

                $imageName = $this->image->store('products', 'public');
                $imageName = basename($imageName); // Remove o path, deixa apenas o nome do arquivo
            }

            // Verifica se já existe um produto com o mesmo código
            $existingProduct = Product::where('product_code', $this->product_code)
                                    ->where('user_id', Auth::id())
                                    ->first();

            $kit = null;

            if ($existingProduct) {
                // Verifica se os preços são iguais
                $pricesMatch = (
                    abs($existingProduct->price - $price) < 0.01 &&
                    abs($existingProduct->price_sale - $price_sale) < 0.01
                );

                if ($pricesMatch) {
                    // Preços iguais: aumenta a quantidade (para kits, vamos manter quantidade = 1 e atualizar outros dados)
                    $existingProduct->update([
                        'name' => $this->name,
                        'description' => $this->description,
                        'category_id' => (int)$this->category_id,
                        'image' => $imageName ?: $existingProduct->image,
                        'custos_adicionais' => $this->convertBrazilianToFloat($this->additional_costs),
                    ]);

                    $kit = $existingProduct;

                    // Remove componentes antigos e adiciona os novos
                    ProdutoComponente::where('kit_produto_id', $kit->id)->delete();
                } else {
                    // Preços diferentes: cria novo produto com código similar
                    $newProductCode = $this->generateUniqueProductCode($this->product_code);

                    $kit = Product::create([
                        'name' => $this->name,
                        'description' => $this->description,
                        'price' => $price,
                        'price_sale' => $price_sale,
                        'stock_quantity' => 0,
                        'category_id' => (int)$this->category_id,
                        'user_id' => Auth::id(),
                        'product_code' => $newProductCode,
                        'image' => $imageName,
                        'status' => 'ativo',
                        'tipo' => 'kit',
                        'custos_adicionais' => $this->convertBrazilianToFloat($this->additional_costs),
                    ]);
                }
            } else {
                // Não existe produto com esse código: cria normalmente
                $kit = Product::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'price' => $price,
                    'price_sale' => $price_sale,
                    'stock_quantity' => 0,
                    'category_id' => (int)$this->category_id,
                    'user_id' => Auth::id(),
                    'product_code' => $this->product_code,
                    'image' => $imageName,
                    'status' => 'ativo',
                    'tipo' => 'kit',
                    'custos_adicionais' => $this->convertBrazilianToFloat($this->additional_costs),
                ]);
            }

            // Salva os componentes do kit
            foreach ($this->selectedProducts as $productData) {
                if (isset($productData['id']) && isset($productData['quantity']) && $productData['quantity'] > 0) {
                    ProdutoComponente::create([
                        'kit_produto_id' => $kit->id,
                        'componente_produto_id' => $productData['id'],
                        'quantidade' => $productData['quantity'],
                        'preco_custo_unitario' => $productData['price'] ?? 0,
                        'preco_venda_unitario' => $productData['salePrice'] ?? 0,
                    ]);
                }
            }

            // Emite evento para atualizar a lista
            $this->dispatch('kit-created');

            // Determina a mensagem de sucesso baseada no que aconteceu
            if ($existingProduct) {
                $pricesMatch = (
                    abs($existingProduct->price - $price) < 0.01 &&
                    abs($existingProduct->price_sale - $price_sale) < 0.01
                );

                if ($pricesMatch) {
                    $this->notifySuccess('Kit "' . $this->name . '" atualizado com sucesso! (Mesmo código e preços)');
                } else {
                    $this->notifySuccess('Kit "' . $this->name . '" criado com código "' . $kit->product_code . '" (Preços diferentes do produto existente)');
                }
            } else {
                $this->notifySuccess('Kit "' . $this->name . '" criado com sucesso!');
            }

            // Redireciona para a lista
            return redirect()->route('products.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = $field . ': ' . implode(', ', $messages);
            }

            $this->notifyError('Erro de validação: ' . implode(' | ', $errorMessages));
            return;

        } catch (\Exception $e) {
            logger('Erro ao criar kit:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->notifyError('Erro inesperado ao criar o kit: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Gera um código de produto único baseado no código original
     */
    private function generateUniqueProductCode($baseCode)
    {
        $counter = 1;
        $newCode = $baseCode;

        // Adiciona sufixo até encontrar um código único
        while (Product::where('product_code', $newCode)->where('user_id', Auth::id())->exists()) {
            $counter++;
            $newCode = $baseCode . '-V' . $counter;
        }

        return $newCode;
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
        return Category::where('user_id', Auth::id())->get();
    }

    /**
     * Mapeia ícones de categorias
     */
    public function getCategoryIcon($iconeName)
    {
        $iconMapping = [
            'electronics' => 'bi-laptop',
            'clothing' => 'bi-shirt',
            'books' => 'bi-book',
            'home' => 'bi-house',
            'sports' => 'bi-trophy',
            'toys' => 'bi-balloon',
            'automotive' => 'bi-car-front',
            'beauty' => 'bi-palette',
            'food' => 'bi-egg-fried',
            'health' => 'bi-heart-pulse',
            'office' => 'bi-briefcase',
            'garden' => 'bi-flower1',
            'pet' => 'bi-heart',
            'music' => 'bi-music-note',
            'phone' => 'bi-phone',
            'computer' => 'bi-laptop',
            'tablet' => 'bi-tablet',
            'camera' => 'bi-camera',
            'headphone' => 'bi-headphones',
            'watch' => 'bi-smartwatch',
            'tv' => 'bi-tv',
            'game' => 'bi-controller',
            'kitchen' => 'bi-cup-hot',
            'furniture' => 'bi-house-door',
            'tool' => 'bi-tools',
            'bag' => 'bi-bag',
            'shoe' => 'bi-shoe-prints',
            'jewelry' => 'bi-gem',
            'default' => 'bi-grid-3x3-gap-fill'
        ];

        return $iconMapping[$iconeName] ?? $iconMapping['default'];
    }

    /**
     * Calcula o preço de venda sugerido com margem de 5%
     */
    public function getSuggestedSalePriceProperty()
    {
        // Garante que temos um valor calculado antes de aplicar a margem
        $baseSalePrice = $this->calculated_sale_price ?? 0;
        return $baseSalePrice * 1.05;
    }    public function getSelectedCategoryNameProperty()
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

    public function render()
    {
        return view('livewire.products.create-kit', [
            'availableProducts' => $this->availableProducts,
            'filteredProducts' => $this->filteredProducts,
            'categories' => $this->categories,
        ]);
    }
}
