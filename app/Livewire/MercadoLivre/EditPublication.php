<?php

namespace App\Livewire\MercadoLivre;

use App\Models\MlPublication;
use App\Models\MlPublicationVariation;
use App\Models\Product;
use App\Services\MercadoLivre\MlStockSyncService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EditPublication extends Component
{
    use HasNotifications;

    public MlPublication $publication;
    public string $title = '';
    public string $description = '';
    public float $price = 0;
    public string $mlCategoryId = '';
    public string $listingType = 'gold_special';
    public bool $freeShipping = false;
    public bool $localPickup = true;
    public string $condition = 'new';
    public string $publicationType = 'simple';
    public string $warranty = '';
    
    // Produtos do kit
    public array $products = [];
    public bool $showProductSelector = false;
    public string $productSearch = '';

    // Aviso quando não deu para ler o ML ao abrir (offline, token vencido...)
    public string $mlFetchWarning = '';

    // "Manter o do ML": esconde a pergunta de estoque para este par local:ML
    public string $stockPromptDismissedFor = '';

    // Variações do anúncio: qual linha está com a busca aberta
    public ?int $variationPickerFor = null;
    public string $variationSearch = '';
    // "Manter o do ML" nas variações (assinatura das diferenças mostradas)
    public string $variationPromptDismissedFor = '';
    
    public function mount(MlPublication $publication)
    {
        // Verificar se a publicação pertence ao usuário
        if ($publication->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar esta publicação.');
        }
        
        $this->publication = $publication->load('products', 'stockLogs');
        
        // Atualizar do ML ao abrir a página (título, preço, etc. alterados no ML passam a aparecer aqui)
        if ($this->publication->ml_item_id) {
            $syncService = app(MlStockSyncService::class);
            // Falha aqui não vira "erro de sincronização": só um aviso na tela.
            $result = $syncService->fetchPublicationFromMercadoLivre($this->publication, false);
            if ($result['success'] && $result['publication']) {
                $this->publication = $result['publication']->load('products', 'stockLogs');
            } else {
                $this->publication->refresh()->load('products', 'stockLogs');
                $this->mlFetchWarning = 'Não foi possível buscar os dados atuais no Mercado Livre agora'
                    . (!empty($result['message']) ? ' (' . $result['message'] . ')' : '')
                    . '. Mostrando os dados salvos.';
            }
        }
        
        $this->applyPublicationToForm();
        $this->loadProducts();
    }
    
    /**
     * Aplica os dados da publicação aos campos do formulário.
     */
    protected function applyPublicationToForm(): void
    {
        $p = $this->publication;
        $this->title = $p->title;
        $this->description = $p->description ?? '';
        $this->price = (float) $p->price;
        $this->mlCategoryId = $p->ml_category_id ?? '';
        $this->listingType = $p->listing_type ?? 'gold_special';
        $this->freeShipping = (bool) $p->free_shipping;
        $this->localPickup = (bool) $p->local_pickup;
        $this->condition = $p->condition ?? 'new';
        $this->publicationType = $p->publication_type;
        $this->warranty = (string) ($p->warranty ?? '');
    }
    
    /**
     * Carrega produtos da publicação
     */
    protected function loadProducts()
    {
        $this->products = $this->publication->products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code,
                'price' => (float)$product->price,
                // Kit não tem estoque próprio: vale o componente que acaba primeiro
                'stock_quantity' => $product->isKit() ? $product->availableStock() : (int)$product->stock_quantity,
                'is_kit' => $product->isKit(),
                'image_url' => $product->image_url,
                'quantity' => (int)$product->pivot->quantity,
                'unit_cost' => (float)$product->pivot->unit_cost,
            ];
        })->toArray();
    }
    
    /**
     * Produtos disponíveis para busca inline
     */
    public function getSearchableProductsProperty()
    {
        $addedIds = array_column($this->products, 'id');

        // Qualquer produto ativo do dono (inclusive sem estoque e kits):
        // o vínculo é o que importa; o estoque é conferido depois.
        $query = Product::where('user_id', Auth::id())
            ->where('status', 'ativo')
            ->whereNotIn('id', $addedIds);
        
        if (strlen($this->productSearch) >= 2) {
            $term = $this->productSearch;
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('product_code', 'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%");
            });
        }
        
        return $query->orderBy('name')->limit(20)->get();
    }
    
    /**
     * Adiciona produto à publicação
     */
    public function addProduct(int $productId, int $quantity = 1, ?float $unitCost = null)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->user_id !== Auth::id()) {
            $this->notifyError('Produto não encontrado');
            return;
        }
        
        if (collect($this->products)->contains('id', $productId)) {
            $this->notifyWarning('Este produto já está vinculado');
            return;
        }
        
        try {
            // Vínculo local: não mexe no estoque do ML. Se o estoque local for
            // diferente, a página pergunta antes de atualizar o ML.
            $this->publication->addProduct(
                $productId, 
                $quantity, 
                (float) ($unitCost ?? $product->price),
                count($this->products),
                false
            );
            
            $this->reloadPublication();
            $this->showProductSelector = false;
            $this->productSearch = '';
            $this->notifySuccess('Produto vinculado: ' . $product->name);
            
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar produto à publicação', [
                'publication_id' => $this->publication->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao adicionar produto: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove produto da publicação
     */
    public function removeProduct(int $productId)
    {
        try {
            $this->publication->removeProduct($productId, false);
            
            $this->reloadPublication();
            $this->notifySuccess('Produto removido da publicação');
            
        } catch (\Exception $e) {
            Log::error('Erro ao remover produto da publicação', [
                'publication_id' => $this->publication->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao remover produto: ' . $e->getMessage());
        }
    }
    
    /**
     * Atualiza quantidade de um produto
     */
    public function updateProductQuantity(int $productId, int $quantity)
    {
        $quantity = max(1, $quantity);
        
        try {
            $this->publication->updateProductQuantity($productId, $quantity, false);
            
            $this->reloadPublication();
            $this->notifySuccess('Quantidade atualizada');
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar quantidade do produto', [
                'publication_id' => $this->publication->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao atualizar quantidade: ' . $e->getMessage());
        }
    }
    
    /**
     * Recarrega publicação e produtos do banco.
     */
    protected function reloadPublication(): void
    {
        $this->publication = $this->publication->fresh()->load('products', 'stockLogs');
        $this->loadProducts();
    }

    /**
     * Atualiza dados básicos da publicação
     */
    public function updatePublication()
    {
        $titleLocked = $this->publication->hasLockedTitle();
        if ($titleLocked) {
            $this->title = $this->publication->title;
        }

        $this->validate([
            'title' => 'required|min:3|max:255',
            'price' => 'required|numeric|min:0.01',
            'mlCategoryId' => 'nullable|string',
        ]);
        
        try {
            $this->publication->update([
                'title' => $this->title,
                'description' => $this->description,
                'price' => $this->price,
                'ml_category_id' => $this->mlCategoryId ?: $this->publication->ml_category_id,
                'listing_type' => $this->listingType,
                'publication_type' => $this->publicationType,
                'free_shipping' => $this->freeShipping,
                'local_pickup' => $this->localPickup,
                'condition' => $this->condition,
                'warranty' => $this->warranty,
            ]);
            
            $this->publication->refresh();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar publicação', [
                'publication_id' => $this->publication->id,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao salvar publicação: ' . $e->getMessage());
            return;
        }

        // Envia ao ML só o que mudou (título, preço, descrição). Estoque não vai
        // aqui: ele é enviado pelo botão de estoque, com confirmação.
        try {
            $result = app(MlStockSyncService::class)->updatePublicationToMercadoLivre($this->publication);
        } catch (\Throwable $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }

        $this->reloadPublication();
        $this->applyPublicationToForm();

        if ($result['success']) {
            if (!empty($result['title_locked']) && str_contains($result['message'], MlStockSyncService::TITLE_LOCKED_MESSAGE)) {
                $this->notifyWarning('Salvo e enviado ao ML. ' . MlStockSyncService::TITLE_LOCKED_MESSAGE . '.');
            } else {
                $this->notifySuccess('Publicação salva e sincronizada com o Mercado Livre');
            }
        } else {
            $this->notifyWarning('Salvo no sistema, mas o Mercado Livre recusou: ' . $result['message']);
        }
    }
    
    /**
     * Envia ao ML a quantidade calculada pelo estoque local (o dono confirma antes na tela).
     */
    public function syncPublication()
    {
        $byVariation = $this->publication->usesVariationLinks();
        if ($byVariation ? $this->publication->mappedVariations()->isEmpty() : empty($this->products)) {
            $this->notifyWarning($byVariation
                ? 'Ligue um produto a pelo menos uma variação antes de enviar o estoque ao ML.'
                : 'Vincule um produto antes de enviar o estoque ao ML.');
            return;
        }

        try {
            $syncService = app(MlStockSyncService::class);
            // Com variações, enviar = o dono confirmou: as variações ligadas
            // passam a receber o estoque do sistema.
            $result = $syncService->syncQuantityToMercadoLivre($this->publication->fresh(), $byVariation);
            
            $this->reloadPublication();
            if ($result['success'] && !empty($result['data']['variations'])) {
                $n = count($result['data']['variations']);
                $this->notifySuccess("Estoque de {$n} variação(ões) atualizado no ML.");
            } elseif ($result['success'] && !empty($result['data']['skipped'])) {
                $this->notifyWarning($result['message']);
            } elseif ($result['success']) {
                $this->notifySuccess('Estoque do ML atualizado para ' . ($result['data']['quantity'] ?? $this->publication->available_quantity) . ' un.');
            } else {
                $this->notifyWarning('Não foi possível atualizar o estoque no ML: ' . ($result['message'] ?? 'Erro desconhecido'));
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar publicação', [
                'publication_id' => $this->publication->id,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao sincronizar: ' . $e->getMessage());
        }
    }

    /**
     * Mantém a quantidade que está no ML (esconde o aviso de diferença até a próxima mudança).
     */
    public function keepMlQuantity(): void
    {
        $this->stockPromptDismissedFor = $this->publication->calculateAvailableQuantity() . ':' . (int) $this->publication->available_quantity;
    }

    /*
    |--------------------------------------------------------------------------
    | VARIAÇÕES DO ANÚNCIO (cada variação → um produto do estoque)
    |--------------------------------------------------------------------------
    */

    protected function findVariationRow(int $rowId): ?MlPublicationVariation
    {
        return MlPublicationVariation::where('id', $rowId)
            ->where('ml_publication_id', $this->publication->id)
            ->first();
    }

    /** Raízes das famílias de variação dos produtos já ligados (para sugerir). */
    protected function linkedFamilyRoots(): array
    {
        $ids = MlPublicationVariation::where('ml_publication_id', $this->publication->id)->whereNotNull('product_id')->pluck('product_id')
            ->merge(collect($this->products)->pluck('id'))
            ->filter()->unique()->all();
        if (empty($ids)) {
            return [];
        }
        return Product::whereIn('id', $ids)->get(['id', 'parent_id', 'is_variation_parent'])
            ->map(fn ($p) => $p->parent_id ?? ($p->is_variation_parent ? $p->id : null))
            ->filter()->unique()->map(fn ($v) => (int) $v)->values()->all();
    }

    public function openVariationPicker(int $rowId): void
    {
        $this->variationPickerFor = $this->variationPickerFor === $rowId ? null : $rowId;
        $this->variationSearch = '';
    }

    public function closeVariationPicker(): void
    {
        $this->variationPickerFor = null;
        $this->variationSearch = '';
    }

    /** Busca do seletor de uma variação (nome, código ou código de barras). */
    public function getVariationSearchResultsProperty()
    {
        if ($this->variationPickerFor === null) {
            return collect();
        }
        // Produtos já ligados a outras variações deste anúncio não aparecem
        $usedElsewhere = MlPublicationVariation::where('ml_publication_id', $this->publication->id)
            ->where('id', '!=', $this->variationPickerFor)
            ->whereNotNull('product_id')->pluck('product_id')->all();
        $query = Product::where('user_id', Auth::id())
            ->where('status', 'ativo')
            ->whereNotIn('id', $usedElsewhere)
            ->where(fn ($q) => $q->whereNull('is_variation_parent')->orWhere('is_variation_parent', false));
        if (strlen(trim($this->variationSearch)) >= 2) {
            $term = trim($this->variationSearch);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('product_code', 'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%")
                  ->orWhere('variation_value', 'like', "%{$term}%");
            });
        } else {
            // Sem busca: produtos das famílias já ligadas (as outras cores)
            $roots = $this->linkedFamilyRoots();
            if (empty($roots)) {
                return collect();
            }
            $query->whereIn('parent_id', $roots);
        }
        return $query->orderBy('name')->limit(12)->get();
    }

    public function linkVariation(int $rowId, int $productId): void
    {
        $row = $this->findVariationRow($rowId);
        $product = Product::find($productId);
        if (!$row || !$product || $product->user_id !== Auth::id()) {
            $this->notifyError('Produto ou variação não encontrado');
            return;
        }

        // Vínculo local: não mexe no estoque do ML (a página pergunta antes).
        $row->update([
            'product_id' => $product->id,
            'quantity' => max(1, (int) $row->quantity),
            'stock_confirmed' => false,
            'link_source' => 'manual',
        ]);
        $this->closeVariationPicker();
        $this->reloadPublication();
        $this->notifySuccess('Variação ' . $row->shortLabel() . ' ligada a ' . $product->name);
    }

    public function unlinkVariation(int $rowId): void
    {
        $row = $this->findVariationRow($rowId);
        if (!$row) {
            return;
        }
        $row->update(['product_id' => null, 'stock_confirmed' => false, 'link_source' => null]);
        $this->reloadPublication();
        $this->notifySuccess('Variação ' . $row->shortLabel() . ' sem produto');
    }

    public function updateVariationQuantity(int $rowId, $quantity): void
    {
        $row = $this->findVariationRow($rowId);
        if (!$row) {
            return;
        }
        $row->update(['quantity' => max(1, min(999, (int) $quantity))]);
        $this->reloadPublication();
    }

    /**
     * "Auto-ligar pelas cores": SKU / código de barras de cada variação e,
     * depois, o valor (ex.: "Nude") dentro da família do produto já ligado.
     */
    public function autoLinkVariations(): void
    {
        $roots = $this->linkedFamilyRoots();
        $result = app(MlStockSyncService::class)->autoLinkVariations(
            $this->publication->fresh(),
            Auth::id(),
            count($roots) === 1 ? $roots[0] : null
        );
        $this->reloadPublication();

        if ($result['linked'] > 0) {
            $how = collect($result['by'])->countBy()->map(fn ($n, $k) => $n . ' ' . match ($k) {
                'sku' => 'pelo SKU', 'gtin' => 'pelo código de barras', default => 'pela cor',
            })->implode(', ');
            $this->notifySuccess("{$result['linked']} variação(ões) ligada(s) ({$how}). O estoque do ML não foi alterado.");
        } else {
            $this->notifyWarning('Nenhuma variação nova ligada. Ligue uma variação a um produto da família (ex.: o batom Nude) e tente de novo, ou use "Ligar produto".');
        }
    }

    /** Remove os produtos ligados ao anúncio inteiro (anúncio com variações). */
    public function clearWholeListingProducts(): void
    {
        foreach ($this->publication->linkedProducts() as $p) {
            $this->publication->removeProduct($p->id, false);
        }
        $this->reloadPublication();
        $this->notifySuccess('Produtos do anúncio inteiro removidos; valem os produtos de cada variação.');
    }

    public function keepMlVariationQuantities(string $signature): void
    {
        $this->variationPromptDismissedFor = $signature;
    }

    /**
     * Linhas da seção "Variações do anúncio" com produto, estoque e sugestões.
     */
    protected function variationViewData(): array
    {
        $rows = $this->publication->variationLinks()->with('product')->get();
        $roots = $this->linkedFamilyRoots();
        $usedIds = $rows->pluck('product_id')->filter()->all();
        $svc = app(MlStockSyncService::class);

        $items = $rows->map(function ($row) use ($roots, $usedIds, $svc) {
            $product = $row->product_id ? $row->product : null;
            return [
                'row' => $row,
                'product' => $product,
                'local' => $product ? MlPublication::variationAvailableQuantity($row) : null,
                'stock' => $product ? MlPublication::productAvailableStock($product) : null,
                'suggestions' => $product ? collect() : $svc->suggestProductsForVariation(Auth::id(), $row, $roots, $usedIds, 3),
            ];
        });

        $diffs = $items->filter(fn ($i) => $i['product'] && $i['local'] !== (int) $i['row']->ml_available_quantity)->values();
        $signature = $diffs->map(fn ($i) => $i['row']->id . ':' . $i['local'] . ':' . $i['row']->ml_available_quantity)->implode('|');

        return [
            'variationItems' => $items,
            'variationDiffs' => $diffs,
            'variationSignature' => $signature,
            'variationLinkedCount' => $items->filter(fn ($i) => $i['product'])->count(),
        ];
    }

    /**
     * Pausa publicação
     */
    public function pausePublication()
    {
        try {
            $result = app(MlStockSyncService::class)->pausePublication($this->publication);
            if ($result['success']) {
                $this->publication->refresh();
                $this->notifySuccess('Publicação pausada');
            } else {
                $this->notifyError('Erro ao pausar: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->notifyError('Erro ao pausar publicação');
        }
    }
    
    /**
     * Ativa publicação
     */
    public function activatePublication()
    {
        try {
            $result = app(MlStockSyncService::class)->activatePublication($this->publication);
            if ($result['success']) {
                $this->publication->refresh();
                $this->notifySuccess('Publicação ativada');
            } else {
                $this->notifyError('Erro ao ativar: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->notifyError('Erro ao ativar publicação');
        }
    }
    
    /**
     * Deleta publicação
     */
    public function deletePublication()
    {
        try {
            // Encerra no ML antes; só remove localmente se deu certo
            $result = app(MlStockSyncService::class)->closePublication($this->publication);
            if (!$result['success']) {
                $this->notifyError('Erro ao encerrar no Mercado Livre: ' . $result['message']);
                return;
            }

            $this->publication->delete();
            $this->notifySuccess('Publicação deletada');
            
            return redirect()->route('mercadolivre.publications');
            
        } catch (\Exception $e) {
            Log::error('Erro ao deletar publicação', [
                'publication_id' => $this->publication->id,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao deletar publicação');
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
     * Atualiza os dados da publicação a partir do Mercado Livre (o que mudou no ML aparece aqui).
     */
    public function refreshFromMl(): void
    {
        if (!$this->publication->ml_item_id) {
            $this->notifyError('Esta publicação ainda não tem ID do ML.');
            return;
        }
        $syncService = app(MlStockSyncService::class);
        $result = $syncService->fetchPublicationFromMercadoLivre($this->publication);
        if ($result['success'] && $result['publication']) {
            $this->publication = $result['publication']->load('products', 'stockLogs');
            $this->applyPublicationToForm();
            $this->loadProducts();
            $this->mlFetchWarning = '';
            $this->notifySuccess('Dados atualizados do Mercado Livre.');
        } else {
            $this->notifyError($result['message'] ?? 'Erro ao atualizar do ML.');
        }
    }
    
    public function render()
    {
        $availableQuantity = $this->publication->calculateAvailableQuantity();
        $stockLogs = $this->publication->stockLogs()
            ->with('product')
            ->latest()
            ->take(10)
            ->get();
        
        $mlQuantity = (int) $this->publication->available_quantity;
        $hasMlItem = $this->publication->ml_item_id && !str_starts_with($this->publication->ml_item_id, 'TEMP_');
        $showStockPrompt = $hasMlItem
            && !empty($this->products)
            && $mlQuantity !== $availableQuantity
            && $this->stockPromptDismissedFor !== $availableQuantity . ':' . $mlQuantity;

        $byVariation = $this->publication->usesVariationLinks();
        $variationData = $byVariation ? $this->variationViewData() : [
            'variationItems' => collect(), 'variationDiffs' => collect(), 'variationSignature' => '', 'variationLinkedCount' => 0,
        ];
        if ($byVariation) {
            // Com variações a pergunta é por variação (abaixo)
            $showStockPrompt = false;
        }
        $showVariationPrompt = $byVariation && $hasMlItem
            && $variationData['variationDiffs']->isNotEmpty()
            && $this->variationPromptDismissedFor !== $variationData['variationSignature'];

        return view('livewire.mercadolivre.edit-publication', $variationData + [
            'byVariation' => $byVariation,
            'showVariationPrompt' => $showVariationPrompt,
            'availableQuantity' => $availableQuantity,
            'mlQuantity' => $mlQuantity,
            'showStockPrompt' => $showStockPrompt,
            'titleLocked' => $this->publication->hasLockedTitle(),
            'mlVariations' => $this->publication->ml_variations ?? [],
            'stockLogs' => $stockLogs,
            'mlAttributes' => $this->publication->ml_attributes ?? [],
            'pictures' => $this->publication->pictures ?? [],
        ])->layout('components.layouts.app');
    }
}
