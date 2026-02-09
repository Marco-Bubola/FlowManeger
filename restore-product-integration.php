<?php
// Script para restaurar ProductIntegration.php que está com 0 bytes no disco
$content = <<<'PHPCODE'
<?php

namespace App\Livewire\MercadoLivre;

use App\Models\Product;
use App\Models\Category;
use App\Models\MercadoLivre\MercadoLivreProduct;
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
        $this->reset(['mlCategoryId', 'mlCategories', 'mlCategoryAttributes', 'selectedAttributes']);
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
            $result = $productService->predictCategory($this->selectedProduct->name);

            if ($result['success'] && !empty($result['predictions'])) {
                $this->mlCategories = $result['predictions'];
                
                // Seleciona automaticamente a primeira sugestão
                if (isset($this->mlCategories[0]['id'])) {
                    $this->mlCategoryId = $this->mlCategories[0]['id'];
                    $this->loadCategoryAttributes();
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao prever categoria ML', [
                'product_id' => $this->selectedProduct->id,
                'error' => $e->getMessage()
            ]);
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
     * Sincroniza um produto já publicado
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

            // Sincroniza preço e estoque
            $priceResult = $productService->updatePrice($mlProduct, $product->price_sale);
            $stockResult = $productService->updateStock($mlProduct, $product->stock_quantity);

            if ($priceResult['success'] && $stockResult['success']) {
                $this->notifySuccess('Produto sincronizado com sucesso!');
                $this->dispatch('productSynced');
            } else {
                $this->notifyError('Erro ao sincronizar produto');
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
                $this->dispatch('productActivated');
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
PHPCODE;

$filePath = __DIR__ . '/app/Livewire/MercadoLivre/ProductIntegration.php';

// Backup
if (file_exists($filePath) && filesize($filePath) > 0) {
    copy($filePath, $filePath . '.bak');
}

// Salvar
$bytes = file_put_contents($filePath, $content);

echo "Arquivo salvo: {$bytes} bytes\n";
echo "Verificação: " . filesize($filePath) . " bytes no disco\n";

// Testar
require 'vendor/autoload.php';
echo "Class exists: " . (class_exists('App\Livewire\MercadoLivre\ProductIntegration') ? 'YES' : 'NO') . "\n";
