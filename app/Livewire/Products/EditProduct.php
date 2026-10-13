<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Services\MercadoLivre\ProductService;
use App\Services\Products\VariationService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProduct extends Component
{
    use WithFileUploads, HasNotifications, Concerns\ImportsFromMlCatalog;

    public Product $product;
    public int $currentStep = 1;

    // Propriedades do formulário
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $price_sale = '';
    public string $price_original = ''; // R$ TABELA, riscado nas promoções (opcional)
    public string $stock_quantity = '';
    public string $category_id = '';
    public string $product_code = '';
    public $image;
    public string $status = 'ativo';
    
    // Campos Mercado Livre
    public string $barcode = '';
    public string $brand = '';
    public string $model = '';
    public string $warranty_months = '3';
    public string $condition = 'new';

    public bool $showTipsModal = false;

    // ─── Variações (produto-pai + variantes) ─────────────────────────────────
    public bool $showVariationsPanel = false;
    public string $variationAttribute = 'Variação';   // ex.: Cor, Tamanho, Voltagem
    public string $newVariantValue = '';              // ex.: Azul, M, 220V
    public string $newVariantName = '';               // nome do novo produto-variante
    public string $newVariantPrice = '';
    public string $newVariantPriceSale = '';
    public string $newVariantStock = '0';
    public string $linkVariantId = '';                // produto existente p/ vincular
    public string $linkVariantValue = '';             // valor da variação ao vincular
    public string $linkSearch = '';                   // busca por código/nome
    public string $linkSelectedLabel = '';            // rótulo do produto selecionado

    // Galeria de imagens adicionais
    public array $extraImages = [];


    public function mount(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->product = $product;

        // Preenche os campos com os dados do produto
        $this->name = $product->name ?? '';
        $this->description = $product->description ?? '';

        // Os preços já vêm formatados como decimal(2) graças ao cast do Model
        // Convertemos para float e depois para string no formato americano
        $price = (float)($product->price ?? 0);
        $price_sale = (float)($product->price_sale ?? 0);

        // Formato americano para o componente currency-input
        $this->price = number_format($price, 2, '.', '');
        $this->price_sale = number_format($price_sale, 2, '.', '');
        $this->price_original = $product->price_original !== null ? number_format((float) $product->price_original, 2, '.', '') : '';

        $this->stock_quantity = (string)($product->stock_quantity ?? 0);
        $this->category_id = (string)($product->category_id ?? '');
        $this->product_code = $product->product_code ?? '';
        $this->status = $product->status ?? 'ativo';
        
        // Campos Mercado Livre
        $this->barcode = $product->barcode ?? '';
        $this->brand = $product->brand ?? '';
        $this->model = $product->model ?? '';
        $this->warranty_months = $product->warranty_months ? (string)$product->warranty_months : '3';
        $this->condition = $product->condition ?? 'new';

        // Atributo de variação herdado (se já for pai)
        if ($product->variation_attribute) {
            $this->variationAttribute = $product->variation_attribute;
        }

        $this->specs = collect($product->specs ?? [])
            ->map(fn ($s) => ['n' => (string) ($s['n'] ?? ''), 'v' => (string) ($s['v'] ?? '')])
            ->values()->all();

        // Carrega galeria extra
        $this->loadExtraImages();

        // Variações já abertas quando o produto tem grupo ou há outros com o mesmo código
        $this->showVariationsPanel = $product->parent_id || $product->is_variation_parent || $this->sameCodeCount > 0;

        // Não preenchemos $this->image para evitar conflitos
        // A imagem atual será mostrada através de $product->image
    }

    /**
     * Avança para a próxima etapa
     */
    public function nextStep()
    {
        $this->currentStep = min($this->currentStep + 1, 2);
    }

    /**
     * Retorna para a etapa anterior
     */
    public function previousStep()
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function toggleTips()
    {
        $this->showTipsModal = !$this->showTipsModal;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable|max:5000',
            'specs' => 'array|max:60',
            'specs.*.n' => 'nullable|string|max:80',
            'specs.*.v' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'price_sale' => 'required|numeric',
            'price_original' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer',
            'category_id' => 'required|exists:category,id_category',
            'product_code' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
            'barcode' => 'nullable|max:15',
            'brand' => 'nullable|max:100',
            'model' => 'nullable|max:100',
            'warranty_months' => 'nullable|integer|min:0|max:120',
            'condition' => 'nullable|in:new,used,refurbished',
            // Removido o status obrigatório pois ele é definido automaticamente
        ];
    }

    public function update()
    {
        try {
            // Conversão correta dos valores monetários do formato brasileiro para americano
            $price = str_replace(',', '.', $this->price);
            $price_sale = str_replace(',', '.', $this->price_sale);

            // Convertendo para float para garantir formato correto
            $price = (float)$price;
            $price_sale = (float)$price_sale;

            // Atualização temporária das propriedades para validação
            $this->price = (string)$price;
            $this->price_sale = (string)$price_sale;
            $price_original = trim((string) $this->price_original) === '' ? null : (float) str_replace(',', '.', $this->price_original);
            $this->price_original = $price_original === null ? '' : (string) $price_original;

            // Validação do formulário
            // Sanitizar o nome no backend (remover '/', '(', ')', '-' e normalizar espaços)
            $this->name = preg_replace('/\s+/', ' ', trim(preg_replace('/[\/()\-]/', '', $this->name)));

            $validated = $this->validate();

            // Atualizar imagem se necessário
            $imageName = $this->product->image;
            if ($this->image) {
                try {
                    // Criar diretório de produtos se não existir
                    if (!Storage::disk('public')->exists('products')) {
                        Storage::disk('public')->makeDirectory('products');
                    }

                    // Remove a imagem anterior se existir (exceto imagem padrão)
                    if ($this->product->image && $this->product->image !== 'product-placeholder.png' && Storage::disk('public')->exists('products/' . $this->product->image)) {
                        Storage::disk('public')->delete('products/' . $this->product->image);
                    }

                    // Salva a nova imagem usando o disco public
                    $imagePath = $this->image->store('products', 'public');
                    $imageName = basename($imagePath);

                    // Log para debug
                    logger('Imagem salva:', [
                        'imagePath' => $imagePath,
                        'imageName' => $imageName,
                        'full_path' => storage_path('app/public/' . $imagePath),
                        'exists' => file_exists(storage_path('app/public/' . $imagePath)),
                        'directory_exists' => is_dir(storage_path('app/public/products'))
                    ]);

                } catch (\Exception $e) {
                    logger('Erro ao salvar imagem:', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);

                    $this->notifyError('Erro ao salvar a imagem: ' . $e->getMessage());
                    return;
                }
            }

            // Atualizar o produto
            $updateResult = $this->product->update([
                'name' => $this->name,
                'description' => $this->description,
                'specs' => $this->cleanSpecs() ?: null,
                'price' => $price,
                'price_sale' => $price_sale,
                'price_original' => $price_original ?: null,
                'stock_quantity' => (int)$this->stock_quantity,
                'category_id' => (int)$this->category_id,
                'product_code' => $this->product_code,
                'image' => $imageName,
                'status' => 'ativo',
                'tipo' => 'simples',
                'custos_adicionais' => 0,
                // Campos Mercado Livre
                'barcode' => $this->barcode ?: null,
                'brand' => $this->brand ?: null,
                'model' => $this->model ?: null,
                'warranty_months' => $this->warranty_months ?: 3,
                'condition' => $this->condition ?: 'new',
            ]);

            if (!$updateResult) {
                throw new \Exception('Falha ao atualizar o produto no banco de dados.');
            }

            // O que veio do ML vai também para as outras variações
            $family = $this->propagateMlToFamily($this->product);

            // Emite evento para atualizar a lista
            $this->dispatch('product-updated');

            // Notifica sucesso (exibe o nome já sanitizado)
            $this->notifySuccess('Produto "' . $this->name . '" atualizado com sucesso!' . ($family ? " Descrição e ficha também nas {$family} outras variações." : ''));

            // Redireciona diretamente
            return redirect()->route('products.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Exibe os erros específicos
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = $field . ': ' . implode(', ', $messages);
            }

            $this->notifyError('Erros de validação: ' . implode(' | ', $errorMessages));

        } catch (\Exception $e) {
            logger('Erro ao atualizar produto:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'product_id' => $this->product->id
            ]);

            $this->notifyError('Erro inesperado ao atualizar o produto: ' . $e->getMessage());
        }
    }

    public function updatedImage()
    {
        // Validação básica da imagem quando é selecionada
        $this->validateOnly('image');

        // Debug para entender o que está acontecendo
        if ($this->image) {
            logger('Imagem selecionada:', [
                'original_name' => $this->image->getClientOriginalName(),
                'size' => $this->image->getSize(),
                'mime' => $this->image->getMimeType(),
                'temporary_url_available' => method_exists($this->image, 'temporaryUrl')
            ]);
        }

        // Este método é chamado automaticamente quando $this->image é atualizado
        // Força a re-renderização do componente
        $this->dispatch('image-updated');
    }

    public function getHasNewImageProperty()
    {
        return $this->image && is_object($this->image) && method_exists($this->image, 'temporaryUrl');
    }

    public function getCategoriesProperty()
    {
        return Category::where('user_id', Auth::id())
                      ->where('type', 'product')
                      ->where('is_active', 1)
                      ->orderBy('name')
                      ->get();
    }

    public function getCategoryIcon($iconeName)
    {
        $iconMapping = [
            'laptop' => 'bi-laptop',
            'phone' => 'bi-phone',
            'tablet' => 'bi-tablet',
            'headphones' => 'bi-headphones',
            'keyboard' => 'bi-keyboard',
            'mouse' => 'bi-mouse',
            'monitor' => 'bi-display',
            'camera' => 'bi-camera',
            'speaker' => 'bi-speaker',
            'printer' => 'bi-printer',
            'router' => 'bi-router',
            'hdd' => 'bi-hdd',
            'cpu' => 'bi-cpu',
            'gpu' => 'bi-gpu-card',
            'memory' => 'bi-memory',
            'motherboard' => 'bi-motherboard',
            'power' => 'bi-lightning',
            'cooler' => 'bi-fan',
            'case' => 'bi-pc-display',
            'cable' => 'bi-usb-c',
            'chair' => 'bi-chair',
            'desk' => 'bi-table',
            'tools' => 'bi-tools',
            'bag' => 'bi-bag',
            'book' => 'bi-book',
            'game' => 'bi-controller',
            'watch' => 'bi-smartwatch',
            'car' => 'bi-car-front',
            'house' => 'bi-house',
            'clothing' => 'bi-shirt',
            'food' => 'bi-egg-fried',
            'drink' => 'bi-cup-straw',
            'sport' => 'bi-trophy',
            'music' => 'bi-music-note',
            'health' => 'bi-heart-pulse',
            'beauty' => 'bi-palette',
            'clean' => 'bi-droplet',
            'pet' => 'bi-heart',
            'baby' => 'bi-balloon',
            'garden' => 'bi-flower1',
            'default' => 'bi-grid-3x3-gap-fill'
        ];

        return $iconMapping[$iconeName] ?? $iconMapping['default'];
    }

    public function getSelectedCategoryNameProperty()
    {
        if ($this->category_id) {
            // Buscar pela chave primária correta da categoria
            $category = Category::where('id_category', $this->category_id)->first();
            return $category ? $category->name : 'Escolha uma categoria...';
        }
        return 'Escolha uma categoria...';
    }

    public function getSelectedCategoryIconProperty()
    {
        if ($this->category_id) {
            // Buscar pela chave primária correta da categoria
            $category = Category::where('id_category', $this->category_id)->first();
            return $category ? $this->getCategoryIcon($category->icone) : 'bi-grid-3x3-gap-fill';
        }
        return 'bi-grid-3x3-gap-fill';
    }

    /**
     * Retorna a URL completa da imagem existente do produto
     */
    public function getExistingImageUrlProperty()
    {
        if ($this->product->image && $this->product->image !== 'product-placeholder.png') {
            return asset('storage/products/' . $this->product->image);
        }
        return null;
    }

    // ─── Galeria de imagens adicionais ────────────────────────────────────────

    public function loadExtraImages(): void
    {
        $this->extraImages = $this->product->images()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($img) => [
                'id'         => $img->id,
                'url'        => $img->url,
                'filename'   => $img->filename,
                'source'     => $img->source,
                'sort_order' => $img->sort_order,
            ])
            ->toArray();
    }

    public function deleteExtraImage(int $imageId): void
    {
        $img = ProductImage::where('id', $imageId)
            ->where('product_id', $this->product->id)
            ->first();

        if (!$img) {
            return;
        }

        if ($img->source !== 'mercadolivre' && $img->filename) {
            Storage::disk('public')->delete('products/' . $img->filename);
        }

        $img->delete();
        $this->loadExtraImages();
        $this->notifySuccess('Imagem removida.');
    }

    // ─── Variações ────────────────────────────────────────────────────────────

    /**
     * Variantes já vinculadas a este produto (quando ele é pai).
     */
    // ─── Importar do ML: onde ficam fotos e capa ────────────────────────────

    protected function mlTargetProduct(): ?Product
    {
        return $this->product;
    }

    protected function mlAfterApply(): void
    {
        $this->loadExtraImages();
    }

    public function getVariantsProperty()
    {
        // Numa variação, mostra o resto do grupo (principal + irmãs).
        if ($this->product->parent_id) {
            return $this->product->family()->where('id', '!=', $this->product->id)->get();
        }

        return $this->product->variants()->orderBy('variation_sort')->get();
    }

    /** Código sem ponto, espaço ou traço ("77.123" e "77123" são o mesmo). */
    private static function normCode(?string $code): string
    {
        return preg_replace('/[^0-9a-z]/', '', mb_strtolower((string) $code));
    }

    private const NORM_CODE_SQL = "REPLACE(REPLACE(REPLACE(LOWER(product_code), '.', ''), ' ', ''), '-', '')";

    /** Produtos que podem entrar no grupo: mesmo dono, simples, soltos e não o próprio. */
    private function linkableQuery()
    {
        return Product::where('user_id', Auth::id())
            ->where('id', '!=', $this->product->id)
            ->whereNull('parent_id')
            ->where('is_variation_parent', false)
            ->where('tipo', 'simples');
    }

    /**
     * Sugestões para vincular como variação, já carregadas (sem digitar):
     * primeiro os de MESMO CÓDIGO (ignorando pontos), depois os de mesmo nome.
     * Com busca, filtra por nome ou código mantendo essa ordem.
     */
    public function getLinkSearchResultsProperty()
    {
        $term = trim($this->linkSearch);
        $code = self::normCode($this->product->product_code);
        $name = $this->product->name;

        $query = $this->linkableQuery();

        if ($term !== '') {
            $codeTerm = self::normCode($term);
            $query->where(function ($q) use ($term, $codeTerm) {
                $q->where('name', 'like', "%{$term}%");
                if ($codeTerm !== '') {
                    $q->orWhereRaw(self::NORM_CODE_SQL . ' like ?', ["%{$codeTerm}%"]);
                }
            });
        } else {
            // Sem busca: só os prováveis (mesmo código ou nome que começa igual)
            $first = collect(preg_split('/\s+/', trim($name)))->first(fn ($w) => mb_strlen($w) >= 3);
            $query->where(function ($q) use ($code, $first) {
                $q->whereRaw(self::NORM_CODE_SQL . ' = ?', [$code]);
                if ($first) {
                    $q->orWhere('name', 'like', $first . '%');
                }
            });
        }

        $rows = $query
            ->orderByRaw('CASE WHEN ' . self::NORM_CODE_SQL . ' = ? THEN 0 WHEN name = ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END', [$code, $name, $name . '%'])
            ->orderBy('name')
            ->limit($term !== '' ? 24 : 12)
            ->get(['id', 'name', 'product_code', 'image', 'stock_quantity', 'price', 'price_sale', 'category_id']);

        foreach ($rows as $row) {
            $row->match = $code !== '' && self::normCode($row->product_code) === $code
                ? 'code'
                : (mb_strtolower(trim($row->name)) === mb_strtolower(trim($name)) ? 'name' : null);
        }

        return $rows;
    }

    /** Quantos produtos soltos têm o mesmo código deste (prováveis variações). */
    public function getSameCodeCountProperty(): int
    {
        $code = self::normCode($this->product->product_code);
        if ($code === '' || $this->product->parent_id) {
            return 0;
        }

        return $this->linkableQuery()->whereRaw(self::NORM_CODE_SQL . ' = ?', [$code])->count();
    }

    /**
     * Indica se a lista atual está em "modo sugestão" (sem busca digitada).
     */
    public function getHasLinkSearchProperty(): bool
    {
        return trim($this->linkSearch) !== '';
    }

    public function selectLinkTarget(int $id): void
    {
        $product = Product::where('user_id', Auth::id())->find($id);
        if (!$product) {
            $this->notifyError('Produto não encontrado.');
            return;
        }
        $this->linkVariantId = (string) $id;
        $this->linkSelectedLabel = $product->name . ' (' . $product->product_code . ')';
        $this->linkSearch = '';
    }

    public function clearLinkTarget(): void
    {
        $this->linkVariantId = '';
        $this->linkSelectedLabel = '';
        $this->linkSearch = '';
    }

    public function toggleVariationsPanel(): void
    {
        $this->showVariationsPanel = !$this->showVariationsPanel;
    }

    /**
     * Cria um novo produto e o anexa como variação deste.
     */
    public function addVariation(VariationService $variations): void
    {
        $this->validate([
            'newVariantName'  => 'required|string|max:255',
            'newVariantValue' => 'required|string|max:120',
        ], [], [
            'newVariantName'  => 'nome da variação',
            'newVariantValue' => 'valor da variação',
        ]);

        try {
            $variations->createVariant($this->product, [
                'name'           => preg_replace('/\s+/', ' ', trim($this->newVariantName)),
                'price'          => (float) str_replace(',', '.', $this->newVariantPrice ?: '0'),
                'price_sale'     => (float) str_replace(',', '.', $this->newVariantPriceSale ?: '0'),
                'stock_quantity' => (int) ($this->newVariantStock ?: 0),
                'category_id'    => $this->product->category_id,
            ], trim($this->newVariantValue));

            // Garante que o atributo escolhido fica registrado no pai
            if ($this->variationAttribute && $this->product->variation_attribute !== $this->variationAttribute) {
                $this->product->update(['variation_attribute' => $this->variationAttribute]);
            }

            $this->product->refresh();
            $this->reset(['newVariantName', 'newVariantValue', 'newVariantPrice', 'newVariantPriceSale', 'newVariantStock']);
            $this->dispatch('product-updated');
            $this->notifySuccess('Variação criada com sucesso!');
        } catch (\Throwable $e) {
            $this->notifyError('Erro ao criar variação: ' . $e->getMessage());
        }
    }

    /**
     * Vincula um produto já existente como variação deste.
     */
    public function linkVariation(VariationService $variations): void
    {
        $this->validate(['linkVariantId' => 'required'], [], ['linkVariantId' => 'produto']);

        try {
            $child = Product::where('user_id', Auth::id())->findOrFail((int) $this->linkVariantId);

            $variations->link($this->product, $child);

            $this->product->refresh();
            $this->reset(['linkVariantId', 'linkVariantValue', 'linkSearch', 'linkSelectedLabel']);
            $this->dispatch('product-updated');
            $this->notifySuccess('Produto vinculado como variação!');
        } catch (\Throwable $e) {
            $this->notifyError('Erro ao vincular variação: ' . $e->getMessage());
        }
    }

    /** Vincula com um clique (cards de sugestão). */
    public function linkOne(int $id, VariationService $variations): void
    {
        $this->linkVariantId = (string) $id;
        $this->linkVariation($variations);
    }

    /** Vincula de uma vez todos os produtos soltos com o mesmo código. */
    public function linkAllSameCode(VariationService $variations): void
    {
        $code = self::normCode($this->product->product_code);
        if ($code === '' || $this->product->parent_id) {
            return;
        }
        $children = $this->linkableQuery()->whereRaw(self::NORM_CODE_SQL . ' = ?', [$code])->orderBy('price_sale')->get();
        try {
            foreach ($children as $child) {
                $variations->link($this->product, $child);
            }
            $this->product->refresh();
            $this->dispatch('product-updated');
            $this->notifySuccess($children->count() === 1 ? '1 produto vinculado como variação.' : $children->count() . ' produtos vinculados como variações.');
        } catch (\Throwable $e) {
            $this->notifyError('Erro ao vincular variações: ' . $e->getMessage());
        }
    }

    /**
     * Desanexa uma variante (volta a ser produto standalone).
     */
    public function detachVariation(int $variantId, VariationService $variations): void
    {
        try {
            $variant = Product::where('user_id', Auth::id())
                ->where('parent_id', $this->product->id)
                ->findOrFail($variantId);

            $variations->detach($variant);
            $this->product->refresh();
            $this->dispatch('product-updated');
            $this->notifySuccess('Variação desvinculada.');
        } catch (\Throwable $e) {
            $this->notifyError('Erro ao desvincular: ' . $e->getMessage());
        }
    }

    /** Numa variação: tira ela do grupo (volta a ser produto independente). */
    public function detachSelf(VariationService $variations): void
    {
        if (!$this->product->parent_id) {
            return;
        }
        $variations->detach($this->product);
        $this->product->refresh();
        $this->dispatch('product-updated');
        $this->notifySuccess('Este produto saiu do grupo de variações.');
    }

    public function render()
    {
        return view('livewire.products.edit-product', [
            'categories' => $this->categories,
            'selectedCategoryName' => $this->selectedCategoryName,
            'selectedCategoryIcon' => $this->selectedCategoryIcon,
            'variants' => $this->variants,
            'linkSearchResults' => $this->linkSearchResults,
            'sameCodeCount' => $this->sameCodeCount,
        ]);
    }
}
