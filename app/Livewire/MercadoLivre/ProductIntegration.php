<?php

namespace App\Livewire\MercadoLivre;

use App\Models\Product;
use App\Models\Category;
use App\Models\MercadoLivreProduct;
use App\Services\MercadoLivre\ProductService;
use App\Services\MercadoLivre\AuthService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ProductIntegration extends Component
{
    use WithPagination, HasNotifications;

    // Filtros
    public string $search = '';
    public string $statusFilter = 'all'; // all, published, unpublished
    public string $categoryFilter = 'all';

    // Modal de publicação
    public bool $showPublishModal = false;
    public ?Product $selectedProduct = null;
    public string $mlCategoryId = '';
    public array $mlCategories = [];
    public array $mlCategoryAttributes = [];
    public array $selectedAttributes = [];
    public string $categorySearch = '';
    public string $listingType = 'gold_special';
    public bool $freeShipping = false;
    public bool $localPickup = true;

    // Estados
    public bool $isConnected = false;
    public bool $loading = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'categoryFilter' => ['except' => 'all'],
    ];

    /**
     * Método invocável necessário para uso direto em rotas
     */
    public function __invoke()
    {
        return $this->render();
    }

    public function mount()
    {
        Log::info('ProductIntegration: mount() iniciado', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name
        ]);
        
        $this->checkConnection();
        
        Log::info('ProductIntegration: mount() concluído', [
            'isConnected' => $this->isConnected
        ]);
    }

    /**
     * Verifica se está conectado ao ML
     */
    public function checkConnection()
    {
        $authService = new AuthService();
        $this->isConnected = $authService->isConnected(Auth::id());
    }

    /**
     * Abre o modal para publicar um produto
     */
    public function openPublishModal(int $productId)
    {
        $this->selectedProduct = Product::with('category')->findOrFail($productId);
        $this->mlCategoryId = '';
        $this->mlCategories = [];
        $this->mlCategoryAttributes = [];
        $this->selectedAttributes = [];
        $this->categorySearch = '';
        $this->showPublishModal = true;

        // Busca sugestão de categoria baseada no título
        $this->predictCategory();
    }

    /**
     * Fecha o modal de publicação
     */
    public function closePublishModal()
    {
        $this->showPublishModal = false;
        $this->selectedProduct = null;
        $this->reset(['mlCategoryId', 'mlCategories', 'mlCategoryAttributes', 'selectedAttributes', 'categorySearch']);
    }

    /**
     * Prediz a categoria do ML baseado no título do produto
     */
    public function predictCategory()
    {
        if (!$this->selectedProduct) {
            return;
        }

        try {
            $productService = new ProductService();
            $result = $productService->predictCategory($this->selectedProduct->name, 'MLB', Auth::id());

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
                'product_id' => $this->selectedProduct->id,
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
     * Carrega os atributos obrigatórios da categoria selecionada
     */
    public function loadCategoryAttributes()
    {
        if (empty($this->mlCategoryId)) {
            return;
        }

        try {
            $productService = new ProductService();
            $result = $productService->getCategoryAttributes($this->mlCategoryId);

            if ($result['success']) {
                // Filtra apenas atributos obrigatórios
                $this->mlCategoryAttributes = array_filter($result['attributes'] ?? [], function ($attr) {
                    return $attr['tags']['required'] ?? false;
                });
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
        if (!$this->selectedProduct) {
            $this->notifyError('Produto não selecionado');
            return;
        }

        // Validações
        if (empty($this->mlCategoryId)) {
            $this->notifyError('Selecione uma categoria do Mercado Livre');
            return;
        }

        // Valida atributos obrigatórios
        foreach ($this->mlCategoryAttributes as $attr) {
            if (empty($this->selectedAttributes[$attr['id']])) {
                $this->notifyError("Campo obrigatório: {$attr['name']}");
                return;
            }
        }

        $this->loading = true;

        try {
            $productService = new ProductService();

            // Monta os dados adicionais
            $mlData = [
                'category_id' => $this->mlCategoryId,
                'listing_type' => $this->listingType,
                'attributes' => [],
                'shipping' => [
                    'mode' => 'me2',
                    'free_shipping' => $this->freeShipping,
                    'local_pick_up' => $this->localPickup,
                ],
            ];

            // Adiciona atributos selecionados
            foreach ($this->selectedAttributes as $attrId => $attrValue) {
                if (!empty($attrValue)) {
                    $mlData['attributes'][] = [
                        'id' => $attrId,
                        'value_name' => $attrValue,
                    ];
                }
            }

            // Publica o produto
            $result = $productService->createProduct($this->selectedProduct, $mlData);

            if ($result['success']) {
                $this->notifySuccess('Produto publicado no Mercado Livre com sucesso!');
                $this->closePublishModal();
                $this->dispatch('productPublished');
            } else {
                $this->notifyError($result['error'] ?? 'Erro ao publicar produto');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao publicar produto no ML', [
                'product_id' => $this->selectedProduct->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->notifyError('Erro ao publicar: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Sincroniza um produto já publicado (verifica status real no ML)
     */
    public function syncProduct(int $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $mlProduct = $product->mercadoLivreProduct;

            if (!$mlProduct) {
                $this->notifyError('Produto não está publicado no Mercado Livre');
                return;
            }

            $productService = new ProductService();
            $result = $productService->syncProduct($mlProduct);

            if ($result['success']) {
                if (!empty($result['status_changed'])) {
                    $newStatus = $result['new_status'] ?? 'desconhecido';
                    if ($newStatus === 'closed') {
                        $this->notifyWarning('O anúncio foi encerrado/excluído no Mercado Livre. Status atualizado.');
                    } else {
                        $this->notifyInfo("Status atualizado: {$result['old_status']} → {$newStatus}");
                    }
                } else {
                    $this->notifySuccess('Produto sincronizado com sucesso!');
                }
            } else {
                $this->notifyError($result['error'] ?? 'Erro ao sincronizar produto');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar produto', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro: ' . $e->getMessage());
        }
    }

    /**
     * Pausa um anúncio no ML
     */
    public function pauseProduct(int $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $mlProduct = $product->mercadoLivreProduct;

            if (!$mlProduct) {
                $this->notifyError('Produto não está publicado no Mercado Livre');
                return;
            }

            $productService = new ProductService();
            $result = $productService->pauseProduct($mlProduct);

            if ($result['success']) {
                $this->notifySuccess('Anúncio pausado com sucesso!');
                $this->dispatch('productPaused');
            } else {
                $this->notifyError($result['error'] ?? 'Erro ao pausar anúncio');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao pausar produto', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            $this->notifyError('Erro: ' . $e->getMessage());
        }
    }

    /**
     * Reativa um anúncio pausado
     */
    public function activateProduct(int $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $mlProduct = $product->mercadoLivreProduct;

            if (!$mlProduct) {
                $this->notifyError('Produto não está publicado no Mercado Livre');
                return;
            }

            $productService = new ProductService();
            $result = $productService->activateProduct($mlProduct);

            if ($result['success']) {
                $this->notifySuccess('Anúncio reativado com sucesso!');
            } else {
                $this->notifyError($result['error'] ?? 'Erro ao reativar anúncio');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao reativar produto', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            $this->notifyError('Erro: ' . $e->getMessage());
        }
    }

    /**
     * Encerra (fecha) um anúncio no ML permanentemente
     */
    public function closeProduct(int $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $mlProduct = $product->mercadoLivreProduct;

            if (!$mlProduct) {
                $this->notifyError('Produto não está publicado no Mercado Livre');
                return;
            }

            $productService = new ProductService();
            $result = $productService->closeProduct($mlProduct);

            if ($result['success']) {
                $mlProduct->update(['status' => 'closed']);
                $this->notifySuccess('Anúncio encerrado no Mercado Livre com sucesso!');
            } else {
                $this->notifyError($result['error'] ?? 'Erro ao encerrar anúncio');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao encerrar produto', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro: ' . $e->getMessage());
        }
    }

    /**
     * Remove o registro local da publicação (sem afetar o ML)
     * Permite republicar o produto novamente
     */
    public function deleteLocalRecord(int $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $mlProduct = $product->mercadoLivreProduct;

            if (!$mlProduct) {
                $this->notifyWarning('Produto não possui registro de publicação');
                return;
            }

            // Primeiro sincronizar para ver o status real no ML
            $productService = new ProductService();
            $syncResult = $productService->syncProduct($mlProduct);
            
            // Se o produto ainda está ativo no ML, avisar
            if ($syncResult['success'] && isset($syncResult['new_status']) && $syncResult['new_status'] === 'active') {
                $this->notifyError('O anúncio ainda está ATIVO no Mercado Livre. Encerre-o primeiro antes de excluir o registro local.');
                return;
            }

            $mlItemId = $mlProduct->ml_item_id;
            $mlProduct->delete();

            Log::info('Registro local de publicação ML excluído', [
                'product_id' => $productId,
                'ml_item_id' => $mlItemId,
            ]);

            $this->notifySuccess('Registro de publicação removido. Agora você pode republicar este produto.');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir registro local', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro: ' . $e->getMessage());
        }
    }

    /**
     * Verifica o status real do anúncio no ML e atualiza localmente
     */
    public function checkMLStatus(int $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $mlProduct = $product->mercadoLivreProduct;

            if (!$mlProduct) {
                $this->notifyError('Produto não possui registro de publicação');
                return;
            }

            $productService = new ProductService();
            $result = $productService->syncProduct($mlProduct);

            if ($result['success']) {
                $mlProduct->refresh();
                $status = $mlProduct->status;
                
                $statusLabels = [
                    'active' => 'Ativo',
                    'paused' => 'Pausado',
                    'closed' => 'Encerrado',
                    'under_review' => 'Em revisão',
                    'inactive' => 'Inativo',
                ];
                
                $label = $statusLabels[$status] ?? $status;
                
                if ($status === 'closed') {
                    $this->notifyWarning("Status no ML: {$label}. O anúncio foi encerrado/excluído. Você pode remover o registro local e republicar.");
                } elseif ($status === 'active') {
                    $this->notifySuccess("Status no ML: {$label}. O anúncio está funcionando normalmente.");
                } elseif ($status === 'paused') {
                    $this->notifyInfo("Status no ML: {$label}. O anúncio está pausado.");
                } else {
                    $this->notifyInfo("Status no ML: {$label}.");
                }
            } else {
                $this->notifyError('Não foi possível verificar o status: ' . ($result['error'] ?? 'Erro desconhecido'));
            }

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status ML', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro: ' . $e->getMessage());
        }
    }

    /**
     * Renderiza o componente
     */
    public function render()
    {
        Log::info('ProductIntegration: render() iniciado', [
            'user_id' => Auth::id(),
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'categoryFilter' => $this->categoryFilter
        ]);
        
        $query = Product::with(['category', 'mercadoLivreProduct'])
            ->where('user_id', Auth::id())
            ->where('stock_quantity', '>', 0); // Apenas produtos EM ESTOQUE

        // Filtro de busca
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('product_code', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro de status de publicação
        if ($this->statusFilter === 'published') {
            $query->has('mercadoLivreProduct');
        } elseif ($this->statusFilter === 'unpublished') {
            $query->doesntHave('mercadoLivreProduct');
        }

        // Filtro de categoria
        if ($this->categoryFilter !== 'all') {
            $query->where('category_id', $this->categoryFilter);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        // Busca categorias para o filtro
        $categories = Category::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        Log::info('ProductIntegration: render() concluído', [
            'products_count' => $products->count(),
            'total_products' => $products->total(),
            'categories_count' => $categories->count()
        ]);

        return view('livewire.mercadolivre.product-integration', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}