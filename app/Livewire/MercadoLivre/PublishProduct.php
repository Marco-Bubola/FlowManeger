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
    public string $warranty = ''; // Garantia do produto
    public string $productCondition = 'new'; // Condição: new ou used
    
    // Dados do produto do catálogo selecionado
    public array $catalogProductData = [];
    public array $catalogPictures = [];
    public array $catalogAttributes = [];
    public string $catalogProductName = '';
    public string $catalogDescription = '';
    
    // Preço e quantidade editáveis
    public string $publishPrice = '';
    public int $publishQuantity = 1;
    
    // Imagens selecionadas para publicação
    public array $selectedPictures = [];
    public bool $useCatalogPictures = true;
    
    // Controle de vinculação ao catálogo
    public bool $linkToCatalog = true; // TRUE = vincula ao catálogo | FALSE = apenas copia dados
    
    // Múltiplos produtos (kit ou publicação composta)
    public array $selectedProducts = [];
    public bool $showProductSelector = false;
    public string $publicationType = 'simple';
    
    // Listeners do Livewire
    protected $listeners = [
        'product-added' => 'addProductToList',
        'product-removed' => 'onProductRemoved',
    ];

    public function mount(Product $product)
    {
        // Verificar se o produto pertence ao usuário
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para publicar este produto.');
        }

        $this->product = $product->load('category');
        
        // Inicializar condição e garantia
        $this->productCondition = $product->condition ?? 'new';
        $this->warranty = ''; // Pode adicionar campo no produto depois
        
        // Inicializar quantidade com dados do produto
        $this->publishQuantity = max(1, (int)($product->stock_quantity ?? 1));
        
        // Adicionar imagem do produto como selecionada por padrão
        if ($product->image && $product->image !== 'product-placeholder.png') {
            $this->selectedPictures = [$product->image_url];
        }
        
        // Inicializar produto principal na lista
        $this->selectedProducts = [[
            'id' => $product->id,
            'name' => $product->name,
            'product_code' => $product->product_code,
            'price_sale' => (float)($product->price_sale ?? $product->price),
            'stock_quantity' => (int)$product->stock_quantity,
            'image_url' => $product->image_url,
            'quantity' => 1,
            'unit_cost' => (float)($product->price_sale ?? $product->price),
        ]];
        
        // Atualizar preço baseado nos produtos
        $this->updatePublishPrice();
        
        // Buscar sugestão automática de categorias ao carregar
        $this->predictCategory();
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
     * Buscar produto no catálogo do ML pelo código de barras
     */
    public function searchCatalog()
    {
        if (empty($this->product->barcode)) {
            $this->notifyWarning('Produto não possui código de barras cadastrado');
            return;
        }

        try {
            $productService = new ProductService();
            $result = $productService->searchCatalogByBarcode($this->product->barcode, Auth::id());

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
                'barcode' => $this->product->barcode
            ]);
            $this->notifyError('Erro ao buscar no catálogo: ' . $e->getMessage());
        }
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
                $this->catalogProductName = $info['name'] ?? '';
                $this->catalogDescription = $info['short_description']['content'] ?? ($info['short_description'] ?? '');
                
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
                        $this->catalogAttributes[] = [
                            'id' => $attr['id'] ?? '',
                            'name' => $attr['name'] ?? $attr['id'] ?? '',
                            'value_name' => $attr['value_name'] ?? ($attr['values'][0]['name'] ?? ''),
                        ];
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
                
                // Auto-preencher atributos obrigatórios com dados do produto
                foreach ($this->mlCategoryAttributes as $attr) {
                    $attrId = $attr['id'];
                    if (empty($this->selectedAttributes[$attrId])) {
                        if ($attrId === 'BRAND' && !empty($this->product->brand)) {
                            $this->selectedAttributes[$attrId] = $this->product->brand;
                        } elseif ($attrId === 'MODEL') {
                            $this->selectedAttributes[$attrId] = $this->product->name;
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
    
    /**
     * Publica o produto no Mercado Livre (agora com suporte a múltiplos produtos)
     */
    public function publishProduct()
    {
        if (!$this->product) {
            $this->notifyError('Produto não selecionado');
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
            // Definir descrição: priorizar catálogo se disponível
            $description = $this->product->description ?? '';
            if ($this->catalogProductId && !empty($this->catalogDescription)) {
                $description = $this->catalogDescription;
            }
            
            Log::info('PublishProduct: Preparando descrição', [
                'has_catalog_description' => !empty($this->catalogDescription),
                'description_length' => mb_strlen($description),
                'description_preview' => mb_substr($description, 0, 100)
            ]);
            
            // Criar publicação no novo sistema
            $publication = \App\Models\MlPublication::create([
                'ml_item_id' => 'MLB' . rand(100000000, 999999999), // Temporário até chamar API ML
                'ml_category_id' => $this->mlCategoryId,
                'title' => mb_substr($this->product->name, 0, 60),
                'description' => $description,
                'price' => $price,
                'publication_type' => count($this->selectedProducts) > 1 ? 'kit' : 'simple',
                'listing_type' => $this->listingType,
                'free_shipping' => $this->freeShipping,
                'local_pickup' => $this->localPickup,
                'condition' => $this->productCondition,
                'warranty' => $this->warranty,
                'status' => 'active',
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
                'family_name' => mb_substr($this->product->name, 0, 60),
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
                foreach ($this->catalogAttributes as $attr) {
                    if (!empty($attr['id']) && !empty($attr['value_name'])) {
                        $attributes[$attr['id']] = $attr['value_name'];
                    }
                }
                $publishData['attributes'] = $attributes;
                
                Log::info('Enviando atributos do catálogo', [
                    'attributes_count' => count($attributes),
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
                $this->product,
                $publishData,
                Auth::id()
            );

            if ($result['success']) {
                // Atualizar ml_item_id real da API
                if (!empty($result['ml_item_id'])) {
                    $publication->update(['ml_item_id' => $result['ml_item_id']]);
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
                'product_id' => $this->product->id,
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
            // Usar imagem do produto local
            $this->selectedPictures = [];
            if ($this->product && $this->product->image && $this->product->image !== 'product-placeholder.png') {
                $this->selectedPictures = [$this->product->image_url];
            }
        }
    }
    
    /**
     * Remove o produto do catálogo selecionado
     */
    public function clearCatalogProduct()
    {
        $this->catalogProductId = '';
        $this->catalogProductData = [];
        $this->catalogPictures = [];
        $this->catalogAttributes = [];
        $this->catalogProductName = '';
        $this->catalogDescription = '';
        $this->useCatalogPictures = false;
        $this->selectedPictures = [];
        
        if ($this->product && $this->product->image && $this->product->image !== 'product-placeholder.png') {
            $this->selectedPictures = [$this->product->image_url];
        }
    }

    public function render()
    {
        return view('livewire.mercadolivre.publish-product');
    }
}
