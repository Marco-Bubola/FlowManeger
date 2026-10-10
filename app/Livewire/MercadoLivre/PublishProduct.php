<?php

namespace App\Livewire\MercadoLivre;

use App\Models\Product;
use App\Services\MercadoLivre\ProductService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class PublishProduct extends Component
{
    use HasNotifications;
    use WithFileUploads;

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
    public int $productLimit = 48;

    // Outras cores da mesma família publicadas junto (um anúncio por cor com o
    // mesmo family_name: o ML agrupa as cores numa página só).
    public array $extraColors = [];

    // Passo 3: o resto do formulário do ML
    public string $customTitle = '';          // título editável (vazio = automático)
    public array $mlAllAttributes = [];       // todos os atributos da categoria (ficha técnica)
    public bool $showOptionalAttributes = false;
    public string $sellerSku = '';
    public $newPhotos = [];                   // fotos enviadas aqui (upload)
    public array $uploadedPictures = [];      // URLs das fotos enviadas
    public string $packageWeight = '';        // gramas
    public string $packageLength = '';        // cm
    public string $packageWidth = '';
    public string $packageHeight = '';
    public string $warrantyType = 'seller';   // seller | factory | none
    public string $warrantyTime = '90';
    public string $warrantyUnit = 'dias';
    public string $manufacturingDays = '';    // prazo extra para postar (opcional)
    
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

    public function mount($product = null)
    {
        // Sem tipo no parâmetro: com "?Product" o Livewire tentava buscar um produto
        // vazio na rota /publish/create e a página dava 404.
        if ($product !== null && !$product instanceof Product) {
            $product = Product::findOrFail($product);
        }

        if ($product) {
            if ((int) $product->user_id !== (int) Auth::id()) {
                abort(403, 'Você não tem permissão para publicar este produto.');
            }
            $this->product = $product->load('category');
            $this->productCondition = $product->condition ?? 'new';
            $this->publishQuantity = max(1, (int)($product->stock_quantity ?? 1));
            
            // Pré-preencher descrição com a do produto (catálogo sobrescreverá se selecionado)
            if (!empty($product->description)) {
                $this->catalogDescription = $product->description;
            }
            
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
            if ($step === 3) {
                $this->prepareStep3();
            }
        }
    }

    public function nextStep()
    {
        if ($this->currentStep === 1 && $this->hasSelectedProducts()) {
            $this->currentStep = 2;
            $this->product = Product::find($this->selectedProducts[0]['id']);
            // Pré-preencher descrição com a do produto se ainda não tiver
            if (empty($this->catalogDescription) && !empty($this->product?->description)) {
                $this->catalogDescription = $this->product->description;
            }
            $this->predictCategory();
            $this->searchCatalog();
        } elseif ($this->currentStep === 2) {
            $this->currentStep = 3;
            $this->prepareStep3();
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

    protected function productQuery()
    {
        return Product::where('user_id', Auth::id())
            ->when($this->searchTerm, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchTerm}%")
                    ->orWhere('product_code', 'like', "%{$this->searchTerm}%")
                    ->orWhere('barcode', 'like', "%{$this->searchTerm}%");
            }))
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory));
    }

    /** Produtos prontos para o ML (com estoque, foto, preço e código de barras). */
    public function getReadyProductsProperty()
    {
        return $this->productQuery()
            ->with('category')
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get()
            ->filter(fn($p) => $p->isReadyForMercadoLivre()['ready'])
            ->values();
    }

    public function getFilteredProductsProperty()
    {
        return $this->readyProducts->take($this->productLimit);
    }

    /** Por que os outros produtos ficaram de fora (ex.: 8 sem código de barras). */
    public function getNotReadySummaryProperty(): array
    {
        $reasons = [];
        $this->productQuery()->get()->each(function ($p) use (&$reasons) {
            foreach ($p->isReadyForMercadoLivre()['errors'] as $err) {
                $label = match (true) {
                    str_contains($err, 'estoque') => 'sem estoque',
                    str_contains($err, 'Imagem') => 'sem foto',
                    str_contains(mb_strtolower($err), 'barras') || str_contains($err, 'EAN') || str_contains($err, 'GTIN') => 'sem código de barras',
                    str_contains($err, 'Preço') => 'sem preço',
                    default => 'com dados faltando',
                };
                $reasons[$label] = ($reasons[$label] ?? 0) + 1;
                break; // conta cada produto uma vez, pelo primeiro motivo
            }
        });
        arsort($reasons);
        return $reasons;
    }

    public function showMoreProducts(): void
    {
        $this->productLimit += 48;
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
            $this->product = !empty($this->selectedProducts) ? Product::find($this->selectedProducts[0]['id']) : null;
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

        $this->extraColors = [];
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
        return \App\Models\Category::where('user_id', Auth::id())->orderBy('name')->get();
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
        $this->customTitle = '';
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
                $this->mlAllAttributes = array_values(array_filter($result['attributes'] ?? [], fn ($a) => is_array($a) && !empty($a['id'])));
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
     * Atributos obrigatórios da categoria que o usuário precisa preencher
     * (os que o ProductService já completa sozinho ficam de fora).
     */
    public function manualRequiredAttributes(): array
    {
        $autoFilled = ['GTIN', 'EMPTY_GTIN_REASON', 'SALE_FORMAT', 'UNITS_PER_PACK', 'NAME'];

        return array_values(array_filter($this->mlCategoryAttributes, function ($attr) use ($autoFilled) {
            $tags = $attr['tags'] ?? [];
            $readOnly = is_array($tags) && (in_array('read_only', $tags, true) || !empty($tags['read_only']));
            return !empty($attr['id']) && !in_array($attr['id'], $autoFilled, true) && !$readOnly;
        }));
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
        
        if (!$product || (int) $product->user_id !== (int) Auth::id()) {
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
        return round($totalPrice / (1 - $this->listingFeeRate() - 0.05), 2);
    }

    /**
     * Taxa de venda estimada do ML no Brasil (varia por categoria):
     * Clássico ~14% e Premium ~19%. Abaixo de R$ 79 o ML ainda cobra um
     * custo fixo por unidade.
     */
    public function listingFeeRate(?string $type = null): float
    {
        return ($type ?? $this->listingType) === 'gold_pro' ? 0.19 : 0.14;
    }

    public function fixedFee(float $price): float
    {
        return match (true) {
            $price <= 0, $price >= 79 => 0.0,
            $price < 29 => 6.25,
            $price < 50 => 6.50,
            default => 6.75,
        };
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
     * Publica o produto no Mercado Livre (um produto, kit com vários produtos,
     * ou várias cores da mesma família, cada cor no seu anúncio).
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

        if (empty($this->mlCategoryId)) {
            $this->notifyError('Escolha a categoria do Mercado Livre.');
            return;
        }

        $price = (float) $this->publishPrice;
        if ($price <= 0) {
            $this->notifyError('Informe um preço válido para o anúncio.');
            return;
        }

        // A quantidade do anúncio sempre segue o estoque (e é sincronizada depois).
        $this->publishQuantity = $this->getAvailableQuantity();
        if ($this->publishQuantity < 1) {
            $this->notifyError('Sem estoque para publicar.');
            return;
        }

        // Atributos obrigatórios só quando não está usando o produto do catálogo
        if (!$this->catalogProductId) {
            foreach ($this->manualRequiredAttributes() as $attr) {
                $val = $this->selectedAttributes[$attr['id']] ?? null;
                if ($val === null || trim((string) $val) === '') {
                    $this->notifyError("Preencha o campo '{$attr['name']}'.");
                    return;
                }
            }
        }

        $titleText = trim($this->getFinalTitle());
        if (mb_strlen($titleText) < 5) {
            $this->notifyError('Escreva um título com pelo menos 5 letras.');
            return;
        }
        if (empty($this->selectedPictures)) {
            $this->notifyError('Escolha pelo menos uma foto para o anúncio.');
            return;
        }

        try {
            $title = mb_substr($titleText, 0, 60);
            $description = trim((string) $this->catalogDescription) !== ''
                ? $this->catalogDescription
                : (string) ($mainProduct->description ?? '');

            $publishData = [
                'listing_type' => $this->listingType,
                'free_shipping' => $this->freeShipping,
                'local_pickup' => $this->localPickup,
                'family_name' => $title,
                'price' => $price,
                'category_id' => $this->mlCategoryId,
                'description' => $description,
                'condition' => $this->productCondition,
                'warranty' => $this->warranty,
            ];

            if ($this->catalogProductId && $this->linkToCatalog) {
                $publishData['catalog_product_id'] = $this->catalogProductId;
            }
            $publishData['sale_terms'] = $this->buildSaleTerms();

            // Enviar atributos: do catálogo ou preenchidos manualmente
            if (!empty($this->catalogAttributes)) {
                // Tem atributos do catálogo — enviar com filtro de atributos problemáticos
                // Mesmo no modo vinculado (catalog_product_id), o ML exige atributos required
                // como UNIT_VOLUME e PERFUME_NAME na categoria MLB6284.
                $attributes = [];
                
                // Atributos que NUNCA devem ser enviados manualmente
                // (causam invalid.item.attribute.values — o ML os herda/calcula sozinho)
                $ignoredAttrs = [
                    'MANUAL_TITLE',
                    'HAIR_TYPES',
                    'HAIR_CARE_TYPES',
                    'PERFUME_TYPE',
                    'FRAGRANCE_TYPE',
                    'WRIST_WATCH_TYPE',
                    'CLOTHING_TYPE',
                    'SHOE_TYPE',
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

                        // Se tem value_id, enviar MESMO se value_name estiver vazio
                        // O ML aceita value_id sozinho — e atributos como UNIT_VOLUME, PERFUME_NAME
                        // podem ter value_id mas value_name vazio no catálogo.
                        if (!empty($attr['value_id'])) {
                            $valueName = $attr['value_name'];

                            // Tentar resolver value_name via lista de values
                            if (empty($valueName) && !empty($attr['values'])) {
                                foreach ($attr['values'] as $value) {
                                    if ($value['id'] === $attr['value_id']) {
                                        $valueName = $value['name'];
                                        break;
                                    }
                                }
                            }

                            // Filtrar value_name com vírgula (múltiplos valores concatenados)
                            if (!empty($valueName) && str_contains($valueName, ',')) {
                                Log::warning('Atributo ignorado - value_name contém vírgula', [
                                    'attr_id' => $attr['id'],
                                    'value_name' => $valueName,
                                ]);
                                continue;
                            }

                            // Enviar com value_id; incluir value_name se disponível
                            $payload = ['id' => $attr['id'], 'value_id' => $attr['value_id']];
                            if (!empty($valueName)) {
                                $payload['value_name'] = $valueName;
                            }
                            $attributes[$attr['id']] = $payload;
                            Log::info('Atributo do catálogo incluído', ['id' => $attr['id'], 'value_id' => $attr['value_id'], 'value_name' => $valueName]);
                        } elseif (!empty($attr['value_name'])) {
                            // Atributos do tipo texto (sem value_id) — filtrar vírgulas
                            if (str_contains($attr['value_name'], ',')) {
                                continue;
                            }
                            $attributes[$attr['id']] = [
                                'id' => $attr['id'],
                                'value_name' => $attr['value_name'],
                            ];
                        }
                    }

                    Log::info('Enviando atributos do catálogo', [
                        'mode' => $this->linkToCatalog ? 'vinculado' : 'cópia',
                        'attributes_count' => count($attributes),
                        'attr_ids' => array_keys($attributes),
                    ]);

                $publishData['attributes'] = array_values($attributes);
            } elseif (!empty($this->selectedAttributes)) {
                // Atributos preenchidos manualmente
                $publishData['attributes'] = $this->selectedAttributes;
            }
            
            if (!empty($this->selectedPictures)) {
                $publishData['pictures'] = array_map(fn($url) => ['source' => $url], $this->selectedPictures);
            }

            $publishData['attributes'] = $this->mergeAttributes(
                $publishData['attributes'] ?? [],
                $this->extraAttributes(count($this->selectedProducts) > 1 ? '' : $this->sellerSku)
            );

            $colorIds = $this->selectedExtraColorIds();
            if (!empty($colorIds)) {
                $publishData['attributes'] = $this->withColor($publishData['attributes'] ?? [], $mainProduct);
                // Com várias cores o nome não pode citar uma cor só (ex.: "Vermelho"):
                // todas usam o nome da família, que é o que o ML agrupa.
                $title = $this->familyTitle();
                $publishData['family_name'] = $title;
            }

            $main = $this->createPublication($mainProduct, $this->selectedProducts, $publishData, $title, $description, $price);
            if (!$main['success']) {
                $this->notifyError($main['error'] ?? 'Erro ao publicar produto');
                return;
            }
            $this->saveDimensionsToProduct($mainProduct);

            // Outras cores: um anúncio por cor, mesmo nome de família, foto e código de barras próprios.
            $failed = [];
            foreach ($colorIds as $colorId) {
                $color = Product::where('user_id', Auth::id())->find($colorId);
                if (!$color) {
                    continue;
                }
                $data = $publishData;
                unset($data['catalog_product_id']);
                $data['attributes'] = $this->withColor(
                    array_filter($data['attributes'] ?? [], function ($a, $k) {
                        $id = is_array($a) ? ($a['id'] ?? $k) : $k;
                        return !in_array($id, ['GTIN', 'EMPTY_GTIN_REASON', 'COLOR', 'MODEL', 'SELLER_SKU'], true);
                    }, ARRAY_FILTER_USE_BOTH),
                    $color
                );
                $colorPics = array_slice($color->load('images')->all_images, 0, 10);
                $data['pictures'] = $colorPics
                    ? array_map(fn ($u) => ['source' => $u], $colorPics)
                    : ($data['pictures'] ?? []);
                $data['attributes'] = $this->mergeAttributes($data['attributes'], array_filter([
                    $color->product_code ? ['id' => 'SELLER_SKU', 'value_name' => (string) $color->product_code] : null,
                ]));

                $row = [[
                    'id' => $color->id,
                    'quantity' => 1,
                    'unit_cost' => (float) ($color->price_sale ?? $color->price),
                ]];
                $res = $this->createPublication($color, $row, $data, $title, $description, $price);
                if (!$res['success']) {
                    $failed[] = ($color->variation_value ?: $color->name) . ': ' . ($res['error'] ?? 'erro');
                }
            }

            $total = 1 + count($colorIds) - count($failed);
            if (!empty($failed)) {
                $this->notifyWarning("{$total} anúncio(s) criado(s). Não foi possível publicar: " . implode('; ', $failed));
            } else {
                $this->notifySuccess($total > 1 ? "{$total} anúncios criados (um por cor)!" : 'Anúncio criado no Mercado Livre!');
            }
            return redirect()->route('mercadolivre.publications');
        } catch (\Exception $e) {
            Log::error('Erro ao publicar produto no ML', ['error' => $e->getMessage()]);
            $this->notifyError('Erro ao publicar: ' . $e->getMessage());
        }
    }

    /**
     * Cria a publicação local, envia ao ML e liga os produtos. Se o ML recusar,
     * a publicação local é apagada (não fica anúncio "TEMP_" sobrando).
     */
    protected function createPublication(Product $mainProduct, array $products, array $publishData, string $title, string $description, float $price): array
    {
        $publication = \App\Models\MlPublication::create([
            'ml_item_id' => 'TEMP_' . uniqid(),
            'ml_category_id' => $publishData['category_id'],
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'publication_type' => count($products) > 1 ? 'kit' : 'simple',
            'listing_type' => $this->listingType,
            'free_shipping' => $this->freeShipping,
            'local_pickup' => $this->localPickup,
            'condition' => $this->productCondition,
            'warranty' => $this->warranty,
            'status' => 'pending',
            'user_id' => Auth::id(),
        ]);

        try {
            foreach ($products as $prod) {
                $publication->addProduct($prod['id'], (int) ($prod['quantity'] ?? 1), $prod['unit_cost'] ?? null);
            }
            $publishData['quantity'] = $publication->calculateAvailableQuantity();

            $result = (new ProductService())->publishProduct($mainProduct, $publishData, Auth::id());
        } catch (\Throwable $e) {
            $publication->delete();
            throw $e;
        }

        if (empty($result['success'])) {
            $publication->delete();
            return ['success' => false, 'error' => $result['error'] ?? 'Erro ao publicar produto'];
        }

        $mlItemId = $result['ml_item_id']
            ?? $result['ml_response']['id']
            ?? $result['ml_product']->ml_item_id
            ?? null;
        $mlPermalink = $result['ml_permalink']
            ?? $result['ml_response']['permalink']
            ?? $result['ml_product']->ml_permalink
            ?? null;

        if (!$mlItemId) {
            Log::error('ML Item ID não retornado pela API', ['result' => $result]);
            return ['success' => true, 'ml_item_id' => null];
        }

        $mlResponse = is_array($result['ml_response'] ?? null) ? $result['ml_response'] : [];
        $publication->update(array_merge([
            'ml_item_id' => $mlItemId,
            'ml_permalink' => $mlPermalink,
            'status' => 'active',
            // Acabou de ser criado com esta quantidade: já está em dia
            'sync_status' => 'synced',
            'last_sync_at' => now(),
            'error_message' => null,
            // Publicado com family_name (User Products): o ML não aceita
            // mudar o título depois; a edição mostra o título travado.
            'ml_family_name' => $mlResponse['family_name'] ?? $title,
        ], !empty($mlResponse['id']) ? array_filter(
            \App\Services\MercadoLivre\MlStockSyncService::itemMeta($mlResponse),
            fn ($v) => $v !== null
        ) : []));

        // Liga todos os produtos (kit) ao anúncio em mercadolivre_products
        foreach ($products as $prod) {
            $exists = \App\Models\MercadoLivreProduct::where('product_id', $prod['id'])
                ->where('ml_item_id', $mlItemId)
                ->exists();
            if (!$exists) {
                \App\Models\MercadoLivreProduct::create([
                    'product_id' => $prod['id'],
                    'ml_item_id' => $mlItemId,
                    'ml_permalink' => $mlPermalink,
                    'ml_category_id' => $publishData['category_id'],
                    'listing_type' => $this->listingType,
                    'status' => 'active',
                    'ml_price' => $price,
                    'ml_quantity' => $publication->calculateAvailableQuantity(),
                    'ml_attributes' => !empty($this->catalogAttributes) ? $this->catalogAttributes : [],
                    'sync_status' => 'synced',
                    'last_sync_at' => now(),
                ]);
            }
        }

        Log::info('Publicação criada com sucesso no ML', [
            'ml_item_id' => $mlItemId,
            'publication_id' => $publication->id,
            'products_linked' => count($products),
        ]);

        return ['success' => true, 'ml_item_id' => $mlItemId];
    }

    /** Coloca (ou troca) o atributo COLOR pela cor da variação do produto. */
    protected function withColor(array $attributes, Product $product): array
    {
        $color = trim((string) ($product->variation_value ?? ''));
        if ($color === '') {
            return $attributes;
        }
        $out = [];
        foreach ($attributes as $k => $a) {
            $id = is_array($a) ? ($a['id'] ?? $k) : $k;
            if ($id === 'COLOR') {
                continue;
            }
            $out[] = is_array($a) ? $a : ['id' => $k, 'value_name' => (string) $a];
        }
        $out[] = ['id' => 'COLOR', 'value_name' => $color];
        return $out;
    }

    /**
     * Outras cores (mesma família de variação) que podem ser publicadas junto.
     * Só para anúncio de um produto (kit não entra).
     */
    public function getFamilyOptionsProperty(): array
    {
        if (count($this->selectedProducts) !== 1) {
            return [];
        }
        $main = Product::find($this->selectedProducts[0]['id']);
        if (!$main || !$main->hasVariations()) {
            return [];
        }

        return $main->family()->get()
            ->reject(fn ($p) => $p->id === $main->id || $p->is_variation_parent)
            ->map(function ($p) {
                $check = $p->isReadyForMercadoLivre();
                $published = \App\Models\MercadoLivreProduct::where('product_id', $p->id)->exists();
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'color' => $p->variation_value ?: $p->name,
                    'stock' => (int) $p->stock_quantity,
                    'image_url' => $p->image_url,
                    'ready' => $check['ready'],
                    'reason' => $check['errors'][0] ?? null,
                    'published' => $published,
                ];
            })
            ->values()
            ->all();
    }

    /** Nome comum das cores (o nome do produto pai da variação). */
    public function familyTitle(): string
    {
        $main = !empty($this->selectedProducts) ? Product::find($this->selectedProducts[0]['id']) : null;
        $root = $main?->parent ?? $main;
        $name = trim((string) ($root?->name ?: $this->getFinalTitle()));
        return mb_substr(mb_convert_case($name, MB_CASE_TITLE, 'UTF-8') === $name ? $name : ucfirst($name), 0, 60);
    }

    protected function selectedExtraColorIds(): array
    {
        $allowed = collect($this->familyOptions)->where('ready', true)->pluck('id')->all();
        return array_values(array_intersect(array_map('intval', $this->extraColors), $allowed));
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
        $this->customTitle = '';
        
        $firstProduct = !empty($this->selectedProducts) ? Product::find($this->selectedProducts[0]['id']) : null;
        if ($firstProduct && $firstProduct->image && $firstProduct->image !== 'product-placeholder.png') {
            $this->selectedPictures = [$firstProduct->image_url];
        }
    }

    public function getFinalTitle(): string
    {
        if (trim($this->customTitle) !== '') {
            return trim($this->customTitle);
        }

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

    // ---------------------------------------------------------------
    // Passo 3: título, fotos, ficha técnica, embalagem e garantia
    // ---------------------------------------------------------------

    /** Preenche o que dá para preencher sozinho ao chegar no passo 3. */
    protected function prepareStep3(): void
    {
        $main = $this->product ?? (!empty($this->selectedProducts) ? Product::find($this->selectedProducts[0]['id']) : null);
        if (trim($this->customTitle) === '') {
            $auto = $this->getFinalTitle();
            $this->customTitle = mb_substr(mb_strtoupper(mb_substr($auto, 0, 1)) . mb_substr($auto, 1), 0, 60);
        }
        if ($main) {
            if ($this->sellerSku === '') {
                $this->sellerSku = (string) ($main->product_code ?? '');
            }
            foreach (['packageWeight' => 'weight_grams', 'packageLength' => 'length_cm', 'packageWidth' => 'width_cm', 'packageHeight' => 'height_cm'] as $prop => $col) {
                if ($this->{$prop} === '' && !empty($main->{$col}) && (float) $main->{$col} > 0) {
                    $this->{$prop} = rtrim(rtrim(number_format((float) $main->{$col}, 2, '.', ''), '0'), '.');
                }
            }
        }
        if (empty($this->selectedPictures) || (count($this->selectedPictures) <= 1 && empty($this->catalogPictures))) {
            $this->selectedPictures = array_slice(array_values(array_unique(array_merge($this->selectedPictures, $this->pictureOptions()))), 0, 10);
        }
        if (empty($this->mlAllAttributes) && $this->mlCategoryId && !$this->catalogProductId) {
            $this->loadCategoryAttributes();
        }
    }

    /** Todas as fotos que podem ir no anúncio: catálogo, produtos (com galeria) e enviadas. */
    public function pictureOptions(): array
    {
        $urls = [];
        foreach ($this->catalogPictures as $pic) {
            $urls[] = $pic['secure_url'] ?: ($pic['url'] ?? '');
        }
        foreach ($this->selectedProducts as $sp) {
            $prod = Product::with('images')->find($sp['id']);
            if ($prod) {
                foreach ($prod->all_images as $u) {
                    $urls[] = $u;
                }
                if ($prod->image && $prod->image !== 'product-placeholder.png') {
                    $urls[] = $prod->image_url;
                }
            }
        }
        foreach ($this->uploadedPictures as $u) {
            $urls[] = $u;
        }
        return array_values(array_unique(array_filter($urls)));
    }

    public function togglePicture(string $url): void
    {
        $i = array_search($url, $this->selectedPictures, true);
        if ($i !== false) {
            array_splice($this->selectedPictures, $i, 1);
            return;
        }
        if (count($this->selectedPictures) >= 10) {
            $this->notifyWarning('O Mercado Livre aceita até 10 fotos.');
            return;
        }
        $this->selectedPictures[] = $url;
    }

    public function makeMainPicture(string $url): void
    {
        $rest = array_values(array_filter($this->selectedPictures, fn ($u) => $u !== $url));
        $this->selectedPictures = array_merge([$url], $rest);
    }

    public function updatedNewPhotos(): void
    {
        $this->validate(['newPhotos.*' => 'image|max:8192'], [
            'newPhotos.*.image' => 'Envie só imagens (JPG, PNG ou WEBP).',
            'newPhotos.*.max' => 'Cada foto pode ter até 8 MB.',
        ]);
        foreach ((array) $this->newPhotos as $file) {
            $path = $file->store('products/ml', 'public');
            $url = asset('storage/' . $path);
            $this->uploadedPictures[] = $url;
            if (count($this->selectedPictures) < 10) {
                $this->selectedPictures[] = $url;
            }
        }
        $this->newPhotos = [];
    }

    /** Atributos opcionais úteis da categoria (melhoram a busca e a qualidade do anúncio). */
    public function optionalAttributes(): array
    {
        $skip = ['GTIN', 'EMPTY_GTIN_REASON', 'SALE_FORMAT', 'UNITS_PER_PACK', 'NAME', 'SELLER_SKU', 'ITEM_CONDITION',
            'SELLER_PACKAGE_HEIGHT', 'SELLER_PACKAGE_WIDTH', 'SELLER_PACKAGE_LENGTH', 'SELLER_PACKAGE_WEIGHT', 'PACKAGE_HEIGHT', 'PACKAGE_WIDTH', 'PACKAGE_LENGTH', 'PACKAGE_WEIGHT'];
        $required = array_column($this->manualRequiredAttributes(), 'id');

        return array_values(array_filter($this->mlAllAttributes, function ($a) use ($skip, $required) {
            $tags = $a['tags'] ?? [];
            $has = fn ($t) => is_array($tags) && (in_array($t, $tags, true) || !empty($tags[$t]));
            if (in_array($a['id'], $skip, true) || in_array($a['id'], $required, true)) {
                return false;
            }
            if ($has('hidden') || $has('read_only') || $has('fixed') || $has('others') || $has('variation_attribute')) {
                return false;
            }
            return in_array($a['value_type'] ?? 'string', ['string', 'list', 'number', 'number_unit', 'boolean'], true);
        }));
    }

    /** Monta sale_terms (garantia e prazo para postar). */
    protected function buildSaleTerms(): array
    {
        $terms = [];
        $types = ['seller' => 'Garantia do vendedor', 'factory' => 'Garantia de fábrica', 'none' => 'Sem garantia'];
        $terms[] = ['id' => 'WARRANTY_TYPE', 'value_name' => $types[$this->warrantyType] ?? 'Garantia do vendedor'];
        if ($this->warrantyType !== 'none' && (int) $this->warrantyTime > 0) {
            $terms[] = ['id' => 'WARRANTY_TIME', 'value_name' => (int) $this->warrantyTime . ' ' . ($this->warrantyUnit === 'meses' ? 'meses' : 'dias')];
        }
        if ((int) $this->manufacturingDays > 0) {
            $terms[] = ['id' => 'MANUFACTURING_TIME', 'value_name' => (int) $this->manufacturingDays . ' dias'];
        }
        return $terms;
    }

    /** SKU, embalagem e atributos opcionais preenchidos, no formato do ML. */
    protected function extraAttributes(string $sku): array
    {
        $out = [];
        if (trim($sku) !== '') {
            $out[] = ['id' => 'SELLER_SKU', 'value_name' => trim($sku)];
        }
        $num = fn ($v) => (float) str_replace(',', '.', (string) $v);
        if ($num($this->packageHeight) > 0) $out[] = ['id' => 'SELLER_PACKAGE_HEIGHT', 'value_name' => $num($this->packageHeight) . ' cm'];
        if ($num($this->packageWidth) > 0) $out[] = ['id' => 'SELLER_PACKAGE_WIDTH', 'value_name' => $num($this->packageWidth) . ' cm'];
        if ($num($this->packageLength) > 0) $out[] = ['id' => 'SELLER_PACKAGE_LENGTH', 'value_name' => $num($this->packageLength) . ' cm'];
        if ($num($this->packageWeight) > 0) $out[] = ['id' => 'SELLER_PACKAGE_WEIGHT', 'value_name' => (int) round($num($this->packageWeight)) . ' g'];

        if (!$this->catalogProductId) {
            foreach ($this->optionalAttributes() as $attr) {
                $val = $this->selectedAttributes[$attr['id']] ?? null;
                if ($val !== null && trim((string) $val) !== '') {
                    $out[] = ['id' => $attr['id'], 'value_name' => trim((string) $val)];
                }
            }
        }
        return $out;
    }

    /** Junta atributos sem repetir id (os da direita ganham). */
    protected function mergeAttributes(array $base, array $extra): array
    {
        $byId = [];
        foreach ($base as $k => $a) {
            $id = is_array($a) ? ($a['id'] ?? $k) : $k;
            $byId[$id] = is_array($a) ? $a : ['id' => $id, 'value_name' => (string) $a];
        }
        foreach ($extra as $a) {
            $byId[$a['id']] = $a;
        }
        return array_values($byId);
    }

    /** Guarda as medidas no produto quando ele ainda não tinha. */
    protected function saveDimensionsToProduct(Product $product): void
    {
        $num = fn ($v) => (float) str_replace(',', '.', (string) $v);
        $data = [];
        foreach (['weight_grams' => $this->packageWeight, 'length_cm' => $this->packageLength, 'width_cm' => $this->packageWidth, 'height_cm' => $this->packageHeight] as $col => $v) {
            if ($num($v) > 0 && (empty($product->{$col}) || (float) $product->{$col} <= 0)) {
                $data[$col] = $col === 'weight_grams' ? (int) round($num($v)) : $num($v);
            }
        }
        if ($data) {
            $product->forceFill($data)->saveQuietly();
        }
    }

    public function render()
    {
        return view('livewire.mercadolivre.publish-product')
            ->layout('components.layouts.app');
    }
}
