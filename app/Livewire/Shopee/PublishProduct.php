<?php

namespace App\Livewire\Shopee;

use App\Models\Product;
use App\Models\ShopeePublication;
use App\Models\ShopeeToken;
use App\Services\Shopee\ProductService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Componente de publicação de produto na Shopee.
 *
 * Segue o mesmo padrão de steps do PublishProduct (ML), mas com os
 * campos obrigatórios da Shopee: peso, dimensões, DTS.
 *
 * Steps:
 * 1 — Selecionar produto(s)
 * 2 — Configurar categoria e atributos Shopee
 * 3 — Configurar preço, estoque e logística
 * 4 — Revisar e publicar
 */
class PublishProduct extends Component
{
    use HasNotifications;

    // -------------------------------------------------------------------------
    // Estado de steps
    // -------------------------------------------------------------------------

    public int $currentStep = 1;

    // -------------------------------------------------------------------------
    // Step 1 — Seleção de produtos
    // -------------------------------------------------------------------------

    public array  $selectedProducts  = [];
    public string $searchTerm        = '';
    public string $selectedCategory  = '';

    // -------------------------------------------------------------------------
    // Step 2 — Categoria e atributos Shopee
    // -------------------------------------------------------------------------

    public string $shopeeCategoryId       = '';
    public array  $shopeeCategories       = [];
    public array  $categoryAttributes     = [];
    public array  $selectedAttributes     = [];
    public string $categorySearch         = '';

    // -------------------------------------------------------------------------
    // Step 3 — Preço, estoque e logística
    // -------------------------------------------------------------------------

    public string $title            = '';
    public string $description      = '';
    public string $publishPrice     = '';
    public int    $publishQuantity  = 1;
    public string $productCondition = 'NEW';

    // Logística — obrigatória na Shopee
    public string $weightGrams  = '';     // string para permitir input vazio
    public string $lengthCm     = '';
    public string $widthCm      = '';
    public string $heightCm     = '';
    public int    $daysToShip   = 3;

    // Variações
    public bool  $hasVariations = false;

    // -------------------------------------------------------------------------
    // Estado interno
    // -------------------------------------------------------------------------

    public bool   $isConnected  = false;
    public bool   $isPublishing = false;
    public ?int   $productId    = null;

    // -------------------------------------------------------------------------
    // Mount
    // -------------------------------------------------------------------------

    public function mount(?Product $product = null): void
    {
        // Verificar conexão Shopee
        $this->isConnected = ShopeeToken::getActiveForUser(Auth::id()) !== null;

        if (!$this->isConnected) {
            $this->notifyError('Sua loja Shopee não está conectada. Configure nas <a href="' . route('shopee.settings') . '">configurações</a>.');
        }

        if ($product) {
            if ($product->user_id !== Auth::id()) {
                abort(403);
            }
            $this->productId = $product->id;
            $this->addProductToSelection($product);
        }
    }

    // -------------------------------------------------------------------------
    // Navegação de Steps
    // -------------------------------------------------------------------------

    public function nextStep(): void
    {
        if ($this->currentStep === 1 && empty($this->selectedProducts)) {
            $this->notifyError('Selecione pelo menos um produto para continuar.');
            return;
        }

        if ($this->currentStep === 2 && empty(trim($this->shopeeCategoryId))) {
            $this->notifyError('Selecione a categoria do produto na Shopee.');
            return;
        }

        if ($this->currentStep === 3) {
            if (empty(trim($this->publishPrice)) || (float) $this->publishPrice <= 0) {
                $this->notifyError('Informe um preço válido.');
                return;
            }
            if (empty(trim($this->weightGrams)) || (int) $this->weightGrams <= 0) {
                $this->notifyError('O peso do produto é obrigatório para publicação na Shopee.');
                return;
            }
        }

        $this->currentStep = min(4, $this->currentStep + 1);

        // Carregar categorias ao entrar no step 2
        if ($this->currentStep === 2 && empty($this->shopeeCategories)) {
            $this->loadCategories();
        }
    }

    public function previousStep(): void
    {
        $this->currentStep = max(1, $this->currentStep - 1);
    }

    // -------------------------------------------------------------------------
    // Ações de produto
    // -------------------------------------------------------------------------

    public function selectProduct(int $productId): void
    {
        if (isset($this->selectedProducts[$productId])) {
            unset($this->selectedProducts[$productId]);
            return;
        }

        $product = Product::where('id', $productId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$product) {
            return;
        }

        $this->addProductToSelection($product);
    }

    private function addProductToSelection(Product $product): void
    {
        $this->selectedProducts[$product->id] = [
            'id'          => $product->id,
            'name'        => $product->name,
            'price'       => $product->price_sale ?? $product->price,
            'stock'       => $product->stock_quantity,
            'image'       => $product->image_url,
            'weight'      => $product->weight_grams,
            'quantity'    => 1,
            'unit_cost'   => $product->price,
        ];

        // Pré-preencher dados logísticos do produto
        if ($product->weight_grams && empty($this->weightGrams)) {
            $this->weightGrams = (string) $product->weight_grams;
        }
        if ($product->length_cm && empty($this->lengthCm)) {
            $this->lengthCm = (string) $product->length_cm;
        }
        if ($product->width_cm && empty($this->widthCm)) {
            $this->widthCm = (string) $product->width_cm;
        }
        if ($product->height_cm && empty($this->heightCm)) {
            $this->heightCm = (string) $product->height_cm;
        }

        if (empty($this->title)) {
            $this->title = $product->name;
        }
        if (empty($this->publishPrice)) {
            $this->publishPrice = (string) ($product->price_sale ?? $product->price);
        }
    }

    // -------------------------------------------------------------------------
    // Categorias Shopee
    // -------------------------------------------------------------------------

    public function loadCategories(): void
    {
        try {
            $service = app(ProductService::class);
            $this->shopeeCategories = $service->getCategories(Auth::id());
        } catch (\Exception $e) {
            $this->notifyError('Erro ao carregar categorias: ' . $e->getMessage());
            $this->shopeeCategories = [];
        }
    }

    public function updatedShopeeCategoryId(): void
    {
        if (!$this->shopeeCategoryId) {
            return;
        }
        try {
            $service = app(ProductService::class);
            $this->categoryAttributes = $service->getCategoryAttributes(
                Auth::id(),
                (int) $this->shopeeCategoryId
            );
        } catch (\Exception $e) {
            $this->categoryAttributes = [];
        }
    }

    // -------------------------------------------------------------------------
    // Publicação
    // -------------------------------------------------------------------------

    public function publish(): void
    {
        if (!$this->isConnected) {
            $this->notifyError('Shopee não está conectada.');
            return;
        }

        $this->isPublishing = true;

        DB::beginTransaction();
        try {
            // Criar publicação interna
            $publication = ShopeePublication::create([
                'user_id'             => Auth::id(),
                'shop_id'             => ShopeeToken::getActiveForUser(Auth::id())?->shop_id ?? '',
                'shopee_category_id'  => $this->shopeeCategoryId,
                'title'               => $this->title,
                'description'         => $this->description,
                'price'               => (float) $this->publishPrice,
                'available_quantity'  => $this->publishQuantity,
                'condition'           => $this->productCondition,
                'weight_grams'        => (int) $this->weightGrams,
                'length_cm'           => $this->lengthCm ? (float) $this->lengthCm : null,
                'width_cm'            => $this->widthCm  ? (float) $this->widthCm  : null,
                'height_cm'           => $this->heightCm ? (float) $this->heightCm : null,
                'days_to_ship'        => $this->daysToShip,
                'has_variations'      => $this->hasVariations,
                'status'              => 'draft',
                'sync_status'         => 'pending',
                'shopee_attributes'   => array_values($this->selectedAttributes),
            ]);

            // Vincular produtos
            foreach ($this->selectedProducts as $pid => $data) {
                $publication->products()->attach($pid, [
                    'quantity'   => $data['quantity'] ?? 1,
                    'unit_cost'  => $data['unit_cost'] ?? 0,
                    'sort_order' => 0,
                ]);
            }

            DB::commit();

            // Publicar na Shopee via serviço
            $service = app(ProductService::class);
            $result  = $service->createListing($publication->fresh()->load('products'), Auth::id());

            if ($result['success']) {
                $this->notifySuccess('Produto publicado com sucesso na Shopee! Item ID: ' . $result['shopee_item_id']);
                return $this->redirect(route('shopee.publications'), navigate: true);
            } else {
                $this->notifyError('Publicação salva, mas houve um erro na Shopee: ' . $result['message']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->notifyError('Erro ao publicar: ' . $e->getMessage());
        } finally {
            $this->isPublishing = false;
        }
    }

    // -------------------------------------------------------------------------
    // Computed helpers
    // -------------------------------------------------------------------------

    public function getProductsListProperty()
    {
        return Product::where('user_id', Auth::id())
            ->where('status', 'ativo')
            ->when($this->searchTerm, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('product_code', 'like', '%' . $this->searchTerm . '%');
            }))
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->orderBy('name')
            ->paginate(12);
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.shopee.publish-product', [
            'productsList' => $this->productsList,
        ]);
    }
}
