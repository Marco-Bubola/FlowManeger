<?php

namespace App\Livewire\MercadoLivre;

use App\Models\Product;
use App\Services\MercadoLivre\ProductService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PublishProduct extends Component
{
    use HasNotifications;

    public ?Product $product = null;
    public int $currentStep = 1;
    public string $mlCategoryId = '';
    public array $mlCategories = [];
    public array $mlCategoryAttributes = [];
    public array $selectedAttributes = [];
    public string $categorySearch = '';
    public string $listingType = 'gold_special';
    public bool $freeShipping = false;
    public bool $localPickup = true;
    public ?string $catalogProductId = null;
    public array $catalogResults = [];
    public string $warranty = '';
    public string $productCondition = 'new';
    
    public array $catalogProductData = [];
    public array $catalogPictures = [];
    public array $catalogAttributes = [];
    public string $catalogProductName = '';
    public string $catalogDescription = '';
    public ?float $catalogPrice = null;
    
    public string $publishPrice = '';
    public int $publishQuantity = 1;
    public array $selectedPictures = [];
    public bool $useCatalogPictures = true;
    public bool $linkToCatalog = true;
    
    public array $selectedProducts = [];
    public bool $showProductSelector = false;
    public string $publicationType = 'simple';
    
    // Step 1: filtros de produtos
    public string $searchTerm = '';
    public string $selectedCategory = '';
    
    protected $listeners = [
        'product-added' => 'addProductToList',
        'product-removed' => 'onProductRemoved',
    ];

    public function updatedSelectedProducts($value, $key)
    {
        $keyStr = (string) $key;
        if (str_contains($keyStr, 'price_sale')) {
            $parts = explode('.', $keyStr);
            if (isset($parts[0]) && is_numeric($parts[0]) && isset($this->selectedProducts[(int)$parts[0]])) {
                $this->selectedProducts[(int)$parts[0]]['unit_cost'] = (float) ($value ?? 0);
            }
        }
        if (str_contains($keyStr, 'price_sale') || str_contains($keyStr, 'unit_cost') || str_contains($keyStr, 'quantity')) {
            $this->updatePublishPrice();
        }
    }

    public function mount(?Product $product = null)
    {
        if ($product) {
            if ($product->user_id !== Auth::id()) {
                abort(403, 'Você não tem permissão para publicar este produto.');
            }
            $this->product = $product->load('category');
            $this->productCondition = $product->condition ?? 'new';
            $this->publishQuantity = max(1, (int)($product->stock_quantity ?? 1));
            
            if ($product->image && $product->image !== 'product-placeholder.png') {
                $this->selectedPictures = [$product->image_url];
            }
            
            $this->selectedProducts = [[
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code,
                'barcode' => $product->barcode ?? '',
                'price_sale' => (float)($product->price_sale ?? $product->price),
                'stock_quantity' => (int)$product->stock_quantity,
                'image_url' => $product->image_url,
                'quantity' => 1,
                'unit_cost' => (float)($product->price_sale ?? $product->price),
            ]];
            
            $this->updatePublishPrice();
            $this->currentStep = 1;
        } else {
            $this->selectedProducts = [];
            $this->currentStep = 1;
        }
    }

    public function goToStep(int $step)
    {
        if ($step >= 1 && $step <= 3) {
            $this->currentStep = $step;
            if ($step >= 2 && !empty($this->selectedProducts) && !$this->product) {
                $this->product = Product::find($this->selectedProducts[0]['id']);
                $this->predictCategory();
                if ($step === 2) {
                    $this->searchCatalog();
                }
            }
        }
    }

    public function nextStep()
    {
        if ($this->currentStep === 1 && $this->hasSelectedProducts()) {
            $this->currentStep = 2;
            $this->product = Product::find($this->selectedProducts[0]['id']);
            $this->predictCategory();
            $this->searchCatalog();
        } elseif ($this->currentStep === 2) {
            $this->currentStep = 3;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function hasSelectedProducts(): bool
    {
        return !empty($this->selectedProducts);
    }

    public function getFilteredProductsProperty()
    {
        $query = Product::where('user_id', Auth::id())
            ->with('category')
            ->when($this->searchTerm, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchTerm}%")
                    ->orWhere('product_code', 'like', "%{$this->searchTerm}%")
                    ->orWhere('barcode', 'like', "%{$this->searchTerm}%");
            }))
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory));

        $products = $query->get();
        return $products->filter(fn($p) => $p->isReadyForMercadoLivre()['ready']);
    }

    public function toggleProduct(int $productId)
    {
        $product = Product::find($productId);
        if (!$product || $product->user_id !== Auth::id()) {
            return;
        }
        $validation = $product->isReadyForMercadoLivre();
        if (!$validation['ready']) {
            $this->notifyWarning('Produto não está pronto para publicação: ' . implode(', ', $validation['errors']));
            return;
        }

        $idx = null;
        foreach ($this->selectedProducts as $i => $p) {
            if ($p['id'] == $productId) {
                $idx = $i;
                break;
            }
        }

        if ($idx !== null) {
            array_splice($this->selectedProducts, $idx, 1);
            $this->selectedProducts = array_values($this->selectedProducts);
            $this->product = $this->selectedProducts[0] ? Product::find($this->selectedProducts[0]['id']) : null;
        } else {
            $this->selectedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code,
                'barcode' => $product->barcode ?? '',
                'price_sale' => (float)($product->price_sale ?? $product->price),
                'stock_quantity' => (int)$product->stock_quantity,
                'image_url' => $product->image_url,
                'quantity' => 1,
                'unit_cost' => (float)($product->price_sale ?? $product->price),
            ];
            $this->product = $product;
        }

        $this->updatePublishPrice();
        if (!empty($this->selectedProducts)) {
            $this->publishQuantity = count($this->selectedProducts) > 1
                ? $this->getAvailableQuantity()
                : max(1, (int)($this->product->stock_quantity ?? 1));
        }
    }

    public function isProductSelected(int $productId): bool
    {
        foreach ($this->selectedProducts as $p) {
            if ($p['id'] == $productId) {
                return true;
            }
        }
        return false;
    }

    public function getCategoriesProperty()
    {
        return \App\Models\Category::all();
    }

    /**
     * Prediz a categoria do ML baseado no título do produto
     */
    public function predictCategory()
    {
        if (!$this->product) {
            return;
        }

        try {
            $productService = new ProductService();
            $result = $productService->predictCategory($this->product->name, 'MLB', Auth::id());

            if ($result['success'] && !empty($result['predictions'])) {
                $this->mlCategories = $result['predictions'];
                
                // Seleciona automaticamente a primeira sugestão se houver
                if (isset($this->mlCategories[0]['id'])) {
                    $this->mlCategoryId = $this->mlCategories[0]['id'];
                    $this->loadCategoryAttributes();
                }
                
                $this->notifySuccess(count($this->mlCategories) . ' categorias encontradas!', 3000);
            } else {
                // Se não encontrou, carrega categorias principais
                $this->loadMainCategories();
            }
        } catch (\Exception $e) {
            Log::error('Erro ao prever categoria ML', [
                'product_id' => $this->product->id,
                'error' => $e->getMessage()
            ]);
            // Se falhar, carrega categorias principais
            $this->loadMainCategories();
        }
    }
    
    /**
     * Carrega categorias principais do Mercado Livre
     */
    public function loadMainCategories()
    {
        try {
            $productService = new ProductService();
            $result = $productService->getCategories('MLB', Auth::id());
            
            if ($result['success'] && !empty($result['categories'])) {
                $this->mlCategories = array_map(function($cat) {
                    return [
                        'id' => $cat['id'],
                        'name' => $cat['name']
                    ];
                }, array_slice($result['categories'], 0, 20)); // Primeiras 20 categorias
                
                $this->notifyInfo('Categorias principais carregadas. Use a busca para filtrar.', 4000);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao carregar categorias principais ML', [
                'error' => $e->getMessage()
            ]);
            $this->notifyWarning('Use a busca para encontrar categorias', 4000);
        }
    }

    /**
     * Busca categorias manualmente por termo
     */
    public function searchMLCategories(string $search)
    {
        if (empty(trim($search))) {
            $this->mlCategories = [];
            return;
        }

        try {
            $productService = new ProductService();
            $result = $productService->searchCategories($search, 'MLB', Auth::id());

            if ($result['success'] && !empty($result['categories'])) {
                $this->mlCategories = array_map(function($cat) {
                    return [
                        'id' => $cat['id'],
                        'name' => $cat['name']
                    ];
                }, $result['categories']);
                
                $this->notifyInfo(count($this->mlCategories) . ' categorias encontradas', 3000);
            } else {
                $this->mlCategories = [];
                $this->notifyWarning('Nenhuma categoria encontrada', 3000);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao buscar categorias ML', [
                'search' => $search,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao buscar categorias');
        }
    }

    /**
     * Buscar produto no catálogo do ML pelo código de barras dos produtos selecionados
     */
    public function searchCatalog()
    {
        $barcode = $this->getFirstSelectedProductBarcode();
        if (empty($barcode)) {
            $this->catalogResults = [];
            $this->notifyWarning('Nenhum produto selecionado com código de barras');
            return;
        }

        try {
            $productService = new ProductService();
            $result = $productService->searchCatalogByBarcode($barcode, Auth::id());

            if ($result['success'] && !empty($result['results'])) {
                $this->catalogResults = $result['results'];
                $this->notifySuccess(count($result['results']) . ' produto(s) encontrado(s) no catálogo ML');
            } else {
                $this->notifyWarning('Nenhum produto encontrado no catálogo ML com esse código de barras');
                $this->catalogResults = [];
            }
        } catch (\Exception $e) {
            Log::error('Erro ao buscar no catálogo ML', [
                'error' => $e->getMessage(),
                'barcode' => $barcode
            ]);
            $this->notifyError('Erro ao buscar no catálogo: ' . $e->getMessage());
        }
    }

    private function getFirstSelectedProductBarcode(): ?string
    {
        foreach ($this->selectedProducts as $p) {
            $prod = Product::find($p['id'] ?? 0);
            if ($prod && !empty($prod->barcode)) {
                return $prod->barcode;
            }
        }
        return null;
    }

    /**
     * Selecionar produto do catálogo
     */
    public function selectCatalogProduct(string $productId, string $domainId = '')
    {
        $this->catalogProductId = $productId;
        
        // Tentar obter a categoria e dados completos do produto do catálogo via API /products/{id}
        try {
            $productService = new ProductService();
            $result = $productService->getCategoryFromCatalogProduct($productId, Auth::id());
            
            if ($result['success'] && !empty($result['category_id'])) {
                $this->mlCategoryId = $result['category_id'];
                Log::info('Categoria extraída do produto do catálogo', [
                    'product_id' => $productId,
                    'category_id' => $this->mlCategoryId
                ]);
            } else {
                Log::warning('Categoria não encontrada no catálogo - será necessário seleção manual', [
                    'product_id' => $productId,
                ]);
            }
            
            // Extrair dados completos do produto do catálogo
            if ($result['success'] && !empty($result['product_info'])) {
                $info = $result['product_info'];
                $this->catalogProductData = $info;
                $this->catalogProductName = $info['name'] ?? $info['title'] ?? '';
                
                $shortDesc = $info['short_description'] ?? null;
                $this->catalogDescription = $this->extractCatalogDescription($shortDesc);
                
                $this->catalogPrice = $this->extractCatalogPrice($info);
                
                // Extrair fotos do catálogo
                $this->catalogPictures = [];
                if (!empty($info['pictures'])) {
                    foreach ($info['pictures'] as $pic) {
                        $this->catalogPictures[] = [
                            'url' => $pic['url'] ?? '',
                            'secure_url' => $pic['secure_url'] ?? ($pic['url'] ?? ''),
                            'size' => $pic['size'] ?? '',
                            'max_size' => $pic['max_size'] ?? '',
                        ];
                    }
                }
                
                // Extrair atributos do catálogo
                $this->catalogAttributes = [];
                if (!empty($info['attributes'])) {
                    foreach ($info['attributes'] as $attr) {
                        // Extrair value_id e value_name
                        $valueId = $attr['value_id'] ?? ($attr['values'][0]['id'] ?? null);
                        $valueName = $attr['value_name'] ?? ($attr['values'][0]['name'] ?? '');
                        
                        // Se temos value_id mas não temos value_name, buscar na lista de values
                        if (!empty($valueId) && empty($valueName) && !empty($attr['values'])) {
                            foreach ($attr['values'] as $value) {
                                if ($value['id'] === $valueId) {
                                    $valueName = $value['name'];
                                    break;
                                }
                            }
                        }
                        
                        $this->catalogAttributes[] = [
                            'id' => $attr['id'] ?? '',
                            'name' => $attr['name'] ?? $attr['id'] ?? '',
                            'value_id' => $valueId,
                            'value_name' => $valueName,
                            'values' => $attr['values'] ?? [], // Lista de valores possíveis para edição
                            'value_type' => $attr['value_type'] ?? 'string',
                        ];
                        
                        // Log detalhado para debug
                        Log::info('Atributo extraído do catálogo', [
                            'id' => $attr['id'] ?? '',
                            'name' => $attr['name'] ?? '',
                            'value_id' => $valueId,
                            'value_name' => $valueName,
                            'values_count' => !empty($attr['values']) ? count($attr['values']) : 0,
                        ]);
                    }
                }
                
                // Se tiver fotos do catálogo, pré-selecionar para uso
                if (!empty($this->catalogPictures)) {
                    $this->useCatalogPictures = true;
                    $this->selectedPictures = array_map(fn($p) => $p['secure_url'] ?: $p['url'], $this->catalogPictures);
                }
                
                Log::info('Dados do catálogo carregados', [
                    'product_id' => $productId,
                    'name' => $this->catalogProductName,
                    'pictures' => count($this->catalogPictures),
                    'attributes' => count($this->catalogAttributes),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Não foi possível extrair categoria do catálogo - selecione manualmente', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
        }
        
        if (!empty($this->mlCategoryId)) {
            $this->notifySuccess('Produto do catálogo selecionado com categoria automática!');
        } else {
            $this->notifyWarning('Produto selecionado, mas selecione a categoria manualmente abaixo.');
        }
        
        // Limpar campos de atributos já que serão preenchidos automaticamente pelo catálogo
        $this->mlCategoryAttributes = [];
        $this->selectedAttributes = [];
        
        if ($this->catalogPrice !== null && $this->catalogPrice > 0) {
            $this->publishPrice = number_format($this->catalogPrice, 2, '.', '');
        }
    }

    private function extractCatalogDescription($shortDesc): string
    {
        if (empty($shortDesc)) {
            return '';
        }
        if (is_string($shortDesc)) {
            return $shortDesc;
        }
        if (is_array($shortDesc)) {
            return $shortDesc['content'] ?? $shortDesc['plain_text'] ?? $shortDesc['text'] ?? '';
        }
        return '';
    }

    private function extractCatalogPrice(array $info): ?float
    {
        if (isset($info['price']) && is_numeric($info['price']) && $info['price'] > 0) {
            return (float) $info['price'];
        }
        $buyBox = $info['buy_box'] ?? null;
        if (is_array($buyBox) && isset($buyBox['price']) && is_numeric($buyBox['price']) && $buyBox['price'] > 0) {
            return (float) $buyBox['price'];
        }
        if (!empty($buyBox['price_info']['amount']) && is_numeric($buyBox['price_info']['amount'])) {
            return (float) $buyBox['price_info']['amount'];
        }
        return null;
    }

    /**
     * Carrega os atributos obrigatórios da categoria selecionada
     */
    public function loadCategoryAttributes()
    {
        if (empty($this->mlCategoryId)) {
            return;
        }

        try {
            $productService = new ProductService();
            $result = $productService->getCategoryAttributes($this->mlCategoryId, Auth::id());

            if ($result['success']) {
                // Filtra apenas atributos obrigatórios verificando em 'tags'
                // Tags pode ser array ['required'] OU objeto {'required': true, 'catalog_required': true}
                $this->mlCategoryAttributes = array_filter($result['attributes'] ?? [], function ($attr) {
                    $tags = $attr['tags'] ?? [];
                    
                    // Se tags é um array indexado (ex: ['required', 'catalog_required'])
                    if (is_array($tags) && !empty($tags) && isset($tags[0])) {
                        return in_array('required', $tags);
                    }
                    
                    // Se tags é um objeto/array associativo (ex: {'required': true, 'catalog_required': true})
                    if (is_array($tags) && !empty($tags)) {
                        return !empty($tags['required']);
                    }
                    
                    return false;
                });
                
                // Log temporário para debug
                Log::info('Atributos carregados no componente', [
                    'category_id' => $this->mlCategoryId,
                    'total_attributes' => count($result['attributes'] ?? []),
                    'required_attributes' => count($this->mlCategoryAttributes),
                    'sample' => array_slice($this->mlCategoryAttributes, 0, 2)
                ]);
                
                $mainProd = $this->product ?? (!empty($this->selectedProducts) ? Product::find($this->selectedProducts[0]['id']) : null);
                foreach ($this->mlCategoryAttributes as $attr) {
                    $attrId = $attr['id'];
                    if (empty($this->selectedAttributes[$attrId]) && $mainProd) {
                        if ($attrId === 'BRAND' && !empty($mainProd->brand)) {
                            $this->selectedAttributes[$attrId] = $mainProd->brand;
                        } elseif ($attrId === 'MODEL') {
                            $this->selectedAttributes[$attrId] = $mainProd->name;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao carregar atributos da categoria ML', [
                'category_id' => $this->mlCategoryId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Atualiza a categoria selecionada
     */
    public function updatedMlCategoryId()
    {
        $this->loadCategoryAttributes();
    }

    /**
     * Watcher para busca de categorias
     */
    public function updatedCategorySearch()
    {
        if (strlen(trim($this->categorySearch)) >= 3) {
            $this->searchMLCategories($this->categorySearch);
        } elseif (empty(trim($this->categorySearch))) {
            $this->mlCategories = [];
        }
    }

    /**
     * Sincroniza value_name quando value_id muda
     */
    public function updatedCatalogAttributes($value, $key)
    {
        // Formato do $key: "0.value_id" onde 0 é o índice
        if (str_ends_with($key, '.value_id')) {
            $index = (int) explode('.', $key)[0];
            
            if (isset($this->catalogAttributes[$index])) {
                $attr = &$this->catalogAttributes[$index];
                $newValueId = $attr['value_id'];
                
                // Buscar o value_name correspondente na lista de values
                if (!empty($newValueId) && !empty($attr['values'])) {
                    foreach ($attr['values'] as $value) {
                        if ($value['id'] === $newValueId) {
                            $attr['value_name'] = $value['name'];
                            Log::info('Atributo sincronizado', [
                                'attr_id' => $attr['id'],
                                'value_id' => $newValueId,
                                'value_name' => $value['name'],
                            ]);
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Toggle seletor de produtos
     */
    public function toggleProductSelector()
    {
        $this->showProductSelector = !$this->showProductSelector;
    }
    
    /**
     * Listener: produto adicionado pelo ProductSelector
     */
    public function addProductToList($productId)
    {
        // Busca o produto
        $product = Product::find($productId);
        
        if (!$product) {
            $this->notifyError('Produto não encontrado.');
            return;
        }
        
        // Verifica se já está na lista
        foreach ($this->selectedProducts as $selected) {
            if ($selected['id'] == $productId) {
                $this->notifyWarning('Produto já está na lista.');
                return;
            }
        }
        
        // Adiciona à lista
        $this->selectedProducts[] = [
            'id' => $product->id,
            'name' => $product->name,
            'product_code' => $product->product_code,
            'price_sale' => (float)($product->price_sale ?? $product->price),
            'stock_quantity' => (int)$product->stock_quantity,
            'image_url' => $product->image_url,
            'quantity' => 1,
            'unit_cost' => (float)($product->price_sale ?? $product->price),
        ];
        
        // Atualizar preço do anúscio
        $this->updatePublishPrice();
        
        $this->notifySuccess('Produto adicionado com sucesso.');
    }
    
    /**
     * Remove um produto da lista de selecionados
     */
    public function removeProduct($index)
    {
        // Não permite remover se só tiver 1 produto (o principal)
        if (count($this->selectedProducts) <= 1) {
            $this->notifyWarning('É necessário manter ao menos um produto na publicação.');
            return;
        }
        
        // Remove o produto pelo índice
        array_splice($this->selectedProducts, $index, 1);
        
        // Reindexar array
        $this->selectedProducts = array_values($this->selectedProducts);
        
        // Atualizar preço do anúscio
        $this->updatePublishPrice();
        
        $this->notifySuccess('Produto removido com sucesso.');
    }
    
    /**
     * Listener: produto removido pelo ProductSelector
     */
    public function onProductRemoved($productId)
    {
        // Remove da lista pelo ID
        $this->selectedProducts = array_values(array_filter($this->selectedProducts, function($p) use ($productId) {
            return $p['id'] != $productId;
        }));
    }
    
    /**
     * Calcula preço total dos produtos selecionados
     */
    public function getTotalProductsPrice()
    {
        if (empty($this->selectedProducts)) {
            return 0;
        }
        
        $total = 0;
        foreach ($this->selectedProducts as $product) {
            $total += ($product['price_sale'] ?? $product['unit_cost']) * $product['quantity'];
        }
        
        return $total;
    }
    
    /**
     * Calcula quantidade disponível baseado nos produtos selecionados
     */
    public function getAvailableQuantity()
    {
        if (empty($this->selectedProducts)) {
            return 0;
        }
        
        $minQuantity = PHP_INT_MAX;
        
        foreach ($this->selectedProducts as $product) {
            $available = floor($product['stock_quantity'] / $product['quantity']);
            $minQuantity = min($minQuantity, $available);
        }
        
        return $minQuantity == PHP_INT_MAX ? 0 : (int)$minQuantity;
    }
    
    /**
     * Atualiza o preço do anúscio baseado nos produtos
     */
    public function updatePublishPrice()
    {
        $totalPrice = $this->getTotalProductsPrice();
        if ($totalPrice > 0) {
            $this->publishPrice = number_format($totalPrice, 2, '.', '');
        }
    }

    public function applySuggestedPrice()
    {
        $suggested = $this->getSuggestedPrice();
        if ($suggested > 0) {
            $this->publishPrice = number_format($suggested, 2, '.', '');
            $this->notifySuccess('Preço sugerido aplicado!');
        }
    }

    public function getSuggestedPrice(): float
    {
        $totalPrice = $this->getTotalProductsPrice();
        if ($totalPrice <= 0) {
            return 0;
        }
        $mlFee = match($this->listingType) {
            'gold_special' => 0.16,
            'gold_pro' => 0.17,
            'gold' => 0.13,
            default => 0.11,
        };
        return round($totalPrice / (1 - $mlFee - 0.05), 2);
    }

    public function getCatalogResultPrice(array $item): ?float
    {
        if (isset($item['price']) && is_numeric($item['price']) && $item['price'] > 0) {
            return (float) $item['price'];
        }
        $buyBox = $item['buy_box'] ?? null;
        if (is_array($buyBox) && isset($buyBox['price']) && is_numeric($buyBox['price'])) {
            return (float) $buyBox['price'];
        }
        return null;
    }

    public function getCatalogResultImage(array $item): ?string
    {
        if (!empty($item['thumbnail'])) return $item['thumbnail'];
        if (!empty($item['picture'])) return $item['picture'];
        if (!empty($item['pictures'][0])) {
            $p = $item['pictures'][0];
            return $p['secure_url'] ?? $p['url'] ?? null;
        }
        return null;
    }
    
    /**
     * Publica o produto no Mercado Livre (agora com suporte a múltiplos produtos)
     */
    public function publishProduct()
    {
        if (empty($this->selectedProducts)) {
            $this->notifyError('Selecione ao menos um produto');
            return;
        }
        $mainProduct = $this->product ?? Product::find($this->selectedProducts[0]['id']);
        if (!$mainProduct) {
            $this->notifyError('Produto não encontrado');
            return;
        }

        // Validações
        if (empty($this->mlCategoryId)) {
            $this->notifyError('Selecione uma categoria do Mercado Livre. É obrigatório mesmo usando produto do catálogo.');
            return;
        }
        
        // Validar preço
        $price = (float)$this->publishPrice;
        if ($price <= 0) {
            $this->notifyError('Informe um preço válido para o anúncio.');
            return;
        }
        
        // Quando há múltiplos produtos, usar available_quantity calculado
        if (count($this->selectedProducts) > 1) {
            $this->publishQuantity = $this->getAvailableQuantity();
            if ($this->publishQuantity < 1) {
                $this->notifyError('Estoque insuficiente para criar publicação com múltiplos produtos.');
                return;
            }
        } else {
            // Validar quantidade para produto único
            if ($this->publishQuantity < 1) {
                $this->notifyError('A quantidade deve ser pelo menos 1.');
                return;
            }
        }

        // Validar atributos obrigatórios apenas se NÃO estiver usando product_id do catálogo
        if (!$this->catalogProductId) {
            foreach ($this->mlCategoryAttributes as $attr) {
                if (!isset($this->selectedAttributes[$attr['id']])) {
                    $this->notifyError("O campo '{$attr['name']}' é obrigatório");
                    return;
                }
            }
        }

        try {
            $title = mb_substr($mainProduct->name, 0, 60);
            if ($this->catalogProductId && !empty($this->catalogProductName)) {
                $title = mb_substr($this->catalogProductName, 0, 60);
            }
            
            $description = $mainProduct->description ?? '';
            if ($this->catalogProductId && !empty($this->catalogDescription)) {
                $description = $this->catalogDescription;
            }
            
            Log::info('PublishProduct: Preparando título e descrição', [
                'title' => $title,
                'has_catalog_description' => !empty($this->catalogDescription),
                'description_length' => mb_strlen($description),
            ]);
            
            $publication = \App\Models\MlPublication::create([
                'ml_item_id' => 'TEMP_' . uniqid(), // Temporário, será atualizado com o ID real do ML
                'ml_category_id' => $this->mlCategoryId,
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'publication_type' => count($this->selectedProducts) > 1 ? 'kit' : 'simple',
                'listing_type' => $this->listingType,
                'free_shipping' => $this->freeShipping,
                'local_pickup' => $this->localPickup,
                'condition' => $this->productCondition,
                'warranty' => $this->warranty,
                'status' => 'pending', // Pendente até confirmar criação no ML
                'user_id' => Auth::id(),
            ]);
            
            // Adicionar produtos à publicação
            foreach ($this->selectedProducts as $prod) {
                $publication->addProduct(
                    $prod['id'],
                    $prod['quantity'],
                    $prod['unit_cost']
                );
            }
            
            // Atualizar available_quantity
            $publication->syncQuantityToMl();
            
            // Publicar no ML via API (legacy ProductService)
            $productService = new ProductService();
            
            $publishData = [
                'listing_type' => $this->listingType,
                'free_shipping' => $this->freeShipping,
                'local_pickup' => $this->localPickup,
                'family_name' => $title,
                'price' => $price,
                'quantity' => $publication->calculateAvailableQuantity(),
                'category_id' => $this->mlCategoryId,
                'description' => $description, // Incluir descrição (catálogo ou produto)
            ];
            
            // Verificar se deve vincular ao catálogo ou apenas copiar dados
            if ($this->catalogProductId && $this->linkToCatalog) {
                // MODO 1: Vincular ao catálogo (envia catalog_product_id)
                $publishData['catalog_product_id'] = $this->catalogProductId;
                
                Log::info('Publicação VINCULADA ao catálogo', [
                    'catalog_product_id' => $this->catalogProductId,
                    'link_mode' => 'catalog_linked'
                ]);
            } elseif ($this->catalogProductId && !$this->linkToCatalog) {
                // MODO 2: Copiar dados do catálogo mas criar publicação independente
                Log::info('Publicação INDEPENDENTE (dados copiados do catálogo)', [
                    'catalog_product_id' => $this->catalogProductId,
                    'link_mode' => 'catalog_copied'
                ]);
            }
            
            // Enviar atributos: do catálogo ou preenchidos manualmente
            if (!empty($this->catalogAttributes)) {
                // Tem atributos do catálogo - usar eles
                $attributes = [];
                // Atributos problemáticos que causam erro de validação no ML API
                $ignoredAttrs = [
                    'MANUAL_TITLE',
                    'HAIR_TYPES',           // Causa erro: valores múltiplos
                    'HAIR_CARE_TYPES',      // Causa erro: validação de value_id
                ];
                
                foreach ($this->catalogAttributes as $attr) {
                    // Pular atributos que devem ser ignorados
                    if (empty($attr['id']) || in_array($attr['id'], $ignoredAttrs)) {
                        continue;
                    }
                    
                    // Pular atributos com valores inválidos/nulos
                    if (empty($attr['value_id']) && empty($attr['value_name'])) {
                        continue;
                    }
                    
                    // Se tem value_id, DEVE ter value_name também
                    if (!empty($attr['value_id'])) {
                        $valueName = $attr['value_name'];
                        
                        // Se value_name está vazio/null, buscar na lista de values
                        if (empty($valueName) && !empty($attr['values'])) {
                            foreach ($attr['values'] as $value) {
                                if ($value['id'] === $attr['value_id']) {
                                    $valueName = $value['name'];
                                    break;
                                }
                            }
                        }
                        
                        // CORREÇÃO: Filtrar atributos com value_name contendo vírgula (múltiplos valores)
                        // Esses causam erro "invalid.item.attribute.values" no ML API
                        if (!empty($valueName) && str_contains($valueName, ',')) {
                            Log::warning('Atributo ignorado - value_name contém vírgula (múltiplos valores)', [
                                'attr_id' => $attr['id'],
                                'attr_name' => $attr['name'],
                                'value_id' => $attr['value_id'],
                                'value_name' => $valueName,
                            ]);
                            continue;
                        }
                        
                        // Só adicionar se conseguiu o value_name
                        if (!empty($valueName)) {
                            $attributes[$attr['id']] = [
                                'id' => $attr['id'],
                                'value_id' => $attr['value_id'],
                                'value_name' => $valueName,
                            ];
                            
                            Log::info('Atributo preparado para envio', [
                                'attr_id' => $attr['id'],
                                'value_id' => $attr['value_id'],
                                'value_name' => $valueName,
                            ]);
                        } else {
                            Log::warning('Atributo ignorado - value_name não encontrado', [
                                'attr_id' => $attr['id'],
                                'attr_name' => $attr['name'],
                                'value_id' => $attr['value_id'],
                                'values_count' => !empty($attr['values']) ? count($attr['values']) : 0,
                            ]);
                        }
                    } elseif (!empty($attr['value_name'])) {
                        // Atributos do tipo texto (sem value_id)
                        // CORREÇÃO: Também filtrar atributos texto com vírgula
                        if (str_contains($attr['value_name'], ',')) {
                            Log::warning('Atributo texto ignorado - contém vírgula', [
                                'attr_id' => $attr['id'],
                                'value_name' => $attr['value_name'],
                            ]);
                            continue;
                        }
                        $attributes[$attr['id']] = [
                            'id' => $attr['id'],
                            'value_name' => $attr['value_name'],
                        ];
                    }
                }
                $publishData['attributes'] = array_values($attributes);
                
                Log::info('Enviando atributos do catálogo', [
                    'attributes_count' => count($attributes),
                    'attributes' => $attributes,
                    'link_to_catalog' => $this->linkToCatalog
                ]);
            } elseif (!empty($this->selectedAttributes)) {
                // Atributos preenchidos manualmente
                $publishData['attributes'] = $this->selectedAttributes;
            }
            
            if ($this->useCatalogPictures && !empty($this->selectedPictures)) {
                $publishData['pictures'] = array_map(fn($url) => ['source' => $url], $this->selectedPictures);
            }
            
            $result = $productService->publishProduct(
                $mainProduct,
                $publishData,
                Auth::id()
            );

            if ($result['success']) {
                // CRÍTICO: Atualizar com o ml_item_id REAL retornado pelo Mercado Livre
                $mlItemId = $result['ml_item_id'] 
                    ?? $result['ml_response']['id'] 
                    ?? $result['ml_product']->ml_item_id 
                    ?? null;
                $mlPermalink = $result['ml_permalink'] 
                    ?? $result['ml_response']['permalink'] 
                    ?? $result['ml_product']->ml_permalink 
                    ?? null;
                
                if ($mlItemId) {
                    $publication->update([
                        'ml_item_id' => $mlItemId,
                        'ml_permalink' => $mlPermalink,
                        'status' => 'active',
                    ]);
                    
                    // CORREÇÃO: Vincular TODOS os produtos selecionados em mercadolivre_products
                    // Quando há múltiplos produtos (kit/combo), o ProductService cria apenas para o primeiro
                    // Precisamos criar/atualizar para TODOS os produtos do kit
                    foreach ($this->selectedProducts as $prod) {
                        $productId = $prod['id'];
                        
                        // Verificar se já existe registro para este produto
                        $mlProduct = \App\Models\MercadoLivreProduct::where('product_id', $productId)
                            ->where('ml_item_id', $mlItemId)
                            ->first();
                        
                        if (!$mlProduct) {
                            // Criar novo registro em mercadolivre_products
                            \App\Models\MercadoLivreProduct::create([
                                'product_id' => $productId,
                                'ml_item_id' => $mlItemId,
                                'ml_permalink' => $mlPermalink,
                                'ml_category_id' => $this->mlCategoryId,
                                'listing_type' => $this->listingType,
                                'status' => 'active',
                                'ml_price' => $this->publishPrice,
                                'ml_quantity' => $publication->calculateAvailableQuantity(),
                                'ml_attributes' => !empty($this->catalogAttributes) ? $this->catalogAttributes : [],
                                'sync_status' => 'synced',
                                'last_sync_at' => now(),
                            ]);
                            
                            Log::info('Produto vinculado ao ml_item_id', [
                                'product_id' => $productId,
                                'ml_item_id' => $mlItemId,
                            ]);
                        }
                    }
                    
                    Log::info('Publicação criada com sucesso no ML', [
                        'ml_item_id' => $mlItemId,
                        'publication_id' => $publication->id,
                        'permalink' => $mlPermalink,
                        'products_linked' => count($this->selectedProducts),
                    ]);
                } else {
                    Log::error('ML Item ID não retornado pela API', ['result' => $result]);
                }
                
                $this->notifySuccess('Publicação criada com ' . count($this->selectedProducts) . ' produto(s)!');
                return redirect()->route('mercadolivre.publications');
            } else {
                // Deletar publicação se API falhar
                $publication->delete();
                $this->notifyError($result['error'] ?? 'Erro ao publicar produto');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao publicar produto no ML', [
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao publicar: ' . $e->getMessage());
        }
    }

    /**
     * Alterna entre usar fotos do catálogo ou do produto local
     */
    public function toggleCatalogPictures()
    {
        $this->useCatalogPictures = !$this->useCatalogPictures;
        
        if ($this->useCatalogPictures && !empty($this->catalogPictures)) {
            $this->selectedPictures = array_map(fn($p) => $p['secure_url'] ?: $p['url'], $this->catalogPictures);
        } else {
            $this->selectedPictures = [];
            $firstProduct = !empty($this->selectedProducts) ? Product::find($this->selectedProducts[0]['id']) : null;
            if ($firstProduct && $firstProduct->image && $firstProduct->image !== 'product-placeholder.png') {
                $this->selectedPictures = [$firstProduct->image_url];
            }
        }
    }
    
    public function clearCatalogProduct()
    {
        $this->catalogProductId = '';
        $this->catalogProductData = [];
        $this->catalogPictures = [];
        $this->catalogAttributes = [];
        $this->catalogProductName = '';
        $this->catalogDescription = '';
        $this->catalogPrice = null;
        $this->useCatalogPictures = false;
        $this->selectedPictures = [];
        
        $firstProduct = !empty($this->selectedProducts) ? Product::find($this->selectedProducts[0]['id']) : null;
        if ($firstProduct && $firstProduct->image && $firstProduct->image !== 'product-placeholder.png') {
            $this->selectedPictures = [$firstProduct->image_url];
        }
    }

    public function getFinalTitle(): string
    {
        // Se tem produto do catálogo selecionado, usar título do catálogo
        if (!empty($this->catalogProductName)) {
            return $this->catalogProductName;
        }
        
        // Caso contrário, usar título do produto original
        if (!empty($this->selectedProducts)) {
            return $this->selectedProducts[0]['name'] ?? 'Produto sem título';
        }
        
        return 'Produto sem título';
    }

    public function render()
    {
        return view('livewire.mercadolivre.publish-product')
            ->layout('components.layouts.app');
    }
}
