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

    public function mount(Product $product)
    {
        // Verificar se o produto pertence ao usuário
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para publicar este produto.');
        }

        $this->product = $product->load('category');
        
        // Inicializar preço e quantidade com dados do produto
        $this->publishPrice = number_format((float)($product->price_sale ?? $product->price), 2, '.', '');
        $this->publishQuantity = max(1, (int)($product->stock_quantity ?? 1));
        
        // Adicionar imagem do produto como selecionada por padrão
        if ($product->image && $product->image !== 'product-placeholder.png') {
            $this->selectedPictures = [$product->image_url];
        }
        
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
     * Publica o produto no Mercado Livre
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
        
        // Validar quantidade
        if ($this->publishQuantity < 1) {
            $this->notifyError('A quantidade deve ser pelo menos 1.');
            return;
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
            $productService = new ProductService();
            
            // Preparar dados de publicação
            $publishData = [
                'listing_type' => $this->listingType,
                'free_shipping' => $this->freeShipping,
                'local_pickup' => $this->localPickup,
                'family_name' => mb_substr($this->product->name, 0, 60),
                'price' => $price,
                'quantity' => $this->publishQuantity,
            ];
            
            // category_id é SEMPRE obrigatório pela API do ML
            $publishData['category_id'] = $this->mlCategoryId;
            
            // Se selecionou produto do catálogo, incluir catalog_product_id
            if ($this->catalogProductId) {
                $publishData['catalog_product_id'] = $this->catalogProductId;
            } else {
                $publishData['attributes'] = $this->selectedAttributes;
            }
            
            // Usar fotos do catálogo se selecionado (URLs do mlstatic.com)
            if ($this->useCatalogPictures && !empty($this->selectedPictures)) {
                $publishData['pictures'] = array_map(fn($url) => ['source' => $url], $this->selectedPictures);
            }
            
            $result = $productService->publishProduct(
                $this->product,
                $publishData,
                Auth::id()
            );

            if ($result['success']) {
                $this->notifySuccess('Produto publicado no Mercado Livre com sucesso!');
                return redirect()->route('mercadolivre.products');
            } else {
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
        return view('livewire.mercadolivre.publish-product')
            ->layout('components.layouts.app');
    }
}
