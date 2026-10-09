<?php

namespace App\Livewire\MercadoLivre;

use App\Services\MercadoLivre\MlStockSyncService;
use App\Models\MlPublication;
use App\Models\Product;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PublicationsList extends Component
{
    use WithPagination, HasNotifications;
    
    public string $search = '';
    public string $statusFilter = 'all';
    public string $typeFilter = 'all';
    public string $syncFilter = 'all';
    public string $viewMode = 'cards'; // cards ou table
    
    // Sincronização automática
    // Desligado: sincronizar no mount fazia 2 chamadas HTTP por publicação a
    // cada carregamento da página. Use o botão "Sincronizar" (syncAllPublications).
    public bool $autoSyncEnabled = false;
    public bool $isSyncing = false;
    public int $syncedCount = 0;
    public int $totalToSync = 0;
    public array $syncErrors = [];

    // Modal "Ligar a um produto" (depois de importar sem achar o produto)
    public ?int $linkPublicationId = null;
    public string $linkSearch = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'typeFilter' => ['except' => 'all'],
        'syncFilter' => ['except' => 'all'],
        'viewMode' => ['except' => 'cards'],
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatingTypeFilter()
    {
        $this->resetPage();
    }
    
    public function updatingSyncFilter()
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->typeFilter = 'all';
        $this->syncFilter = 'all';
        $this->resetPage();
    }

    /**
     * Executar sincronização automática ao montar o componente
     */
    public function mount()
    {
        if ($this->autoSyncEnabled) {
            $this->syncAllPublications();
        }
    }
    
    /**
     * Sincroniza todas as publicações automaticamente
     */
    public function syncAllPublications()
    {
        try {
            $this->isSyncing = true;
            $this->syncedCount = 0;
            $this->syncErrors = [];
            
            // Buscar todas as publicações do usuário com ml_item_id válido
            $publications = MlPublication::where('user_id', Auth::id())
                ->whereNotNull('ml_item_id')
                ->where('ml_item_id', 'NOT LIKE', 'TEMP_%')
                ->get();
            
            $this->totalToSync = $publications->count();
            
            if ($this->totalToSync === 0) {
                $this->isSyncing = false;
                return;
            }
            
            $syncService = app(MlStockSyncService::class);
            
            foreach ($publications as $publication) {
                try {
                    $result = $syncService->fetchPublicationFromMercadoLivre($publication);
                    
                    if ($result['success']) {
                        $this->syncedCount++;
                    } else {
                        $this->syncErrors[] = [
                            'id' => $publication->id,
                            'title' => $publication->title,
                            'error' => $result['message'],
                        ];
                    }
                } catch (\Exception $e) {
                    $this->syncErrors[] = [
                        'id' => $publication->id,
                        'title' => $publication->title,
                        'error' => $e->getMessage(),
                    ];
                }
            }
            
            $this->isSyncing = false;
            \Illuminate\Support\Facades\Cache::forget('ml_only_on_ml_' . Auth::id());
            
            // Notificar resultado
            if ($this->syncedCount > 0) {
                $message = "✅ {$this->syncedCount} de {$this->totalToSync} publicações sincronizadas";
                if (count($this->syncErrors) > 0) {
                    $message .= " (" . count($this->syncErrors) . " com erro)";
                }
                $this->dispatch('sync-completed', ['message' => $message]);
            }
            
            if (count($this->syncErrors) > 0 && $this->syncedCount === 0) {
                $this->dispatch('sync-failed', ['errors' => $this->syncErrors]);
            }
            
        } catch (\Exception $e) {
            $this->isSyncing = false;
            \Log::error('Erro na sincronização automática', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function getPublications()
    {
        $query = MlPublication::query()
            ->with(['products.category', 'user'])
            ->where('user_id', Auth::id());
        
        // Filtro de busca
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('ml_item_id', 'like', "%{$this->search}%");
            });
        }
        
        // Filtro de status
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        
        // Filtro de tipo
        if ($this->typeFilter !== 'all') {
            $query->where('publication_type', $this->typeFilter);
        }
        
        // Filtro de sincronização
        if ($this->syncFilter !== 'all') {
            $query->where('sync_status', $this->syncFilter);
        }
        
        // Ordenação: Prontos para publicar primeiro, depois publicados, depois pendentes
        return $query
            ->orderByRaw("
                CASE 
                    WHEN sync_status = 'synced' AND status = 'active' THEN 1
                    WHEN ml_item_id IS NOT NULL AND ml_item_id != '' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
    
    /**
     * Publicações que existem no ML mas ainda não foram importadas para o sistema.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOnlyOnMlItems(): \Illuminate\Support\Collection
    {
        try {
            $userId = Auth::id();
            $syncService = app(MlStockSyncService::class);
            // render() roda a cada interação: guarda a lista do ML por alguns minutos
            $result = \Illuminate\Support\Facades\Cache::remember(
                'ml_only_on_ml_' . $userId,
                now()->addMinutes(5),
                fn () => $syncService->fetchUserItemIdsFromMl($userId)
            );
            
            if (!$result['success'] || empty($result['item_ids'])) {
                return collect();
            }
            
            $ourIds = MlPublication::where('user_id', $userId)->whereNotNull('ml_item_id')->pluck('ml_item_id')->flip();
            $onlyIds = array_values(array_filter($result['item_ids'], fn ($id) => !$ourIds->has($id)));
            
            if (empty($onlyIds)) {
                return collect();
            }
            
            $summaries = $syncService->fetchItemsSummaryFromMl($onlyIds, $userId);
            return collect($summaries);
        } catch (\Exception $e) {
            // Log do erro mas não interrompe a página
            \Log::warning('Erro ao buscar itens do ML não importados', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    public function render()
    {
        $publications = $this->getPublications();
        $onlyOnMlItems = $this->getOnlyOnMlItems();
        
        $stats = [
            'total' => MlPublication::where('user_id', Auth::id())->count(),
            'active' => MlPublication::where('user_id', Auth::id())->where('status', 'active')->count(),
            'kits' => MlPublication::where('user_id', Auth::id())->where('publication_type', 'kit')->count(),
            'errors' => MlPublication::where('user_id', Auth::id())->where('sync_status', 'error')->count(),
            'only_on_ml' => $onlyOnMlItems->count(),
        ];
        
        return view('livewire.mercadolivre.publications-list', [
            'publications' => $publications,
            'onlyOnMlItems' => $onlyOnMlItems,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Importa um anúncio do ML para o sistema. Tenta ligar sozinho a um produto
     * (SKU, GTIN ou título); se não achar, abre "Ligar a um produto".
     */
    public function importFromMl(string $mlItemId): void
    {
        $syncService = app(MlStockSyncService::class);
        $result = $syncService->createPublicationFromMlItem(Auth::id(), $mlItemId);
        if (!$result['success']) {
            $this->notifyError($result['message']);
            return;
        }

        \Illuminate\Support\Facades\Cache::forget('ml_only_on_ml_' . Auth::id());
        $publication = $result['publication'];

        if (!empty($result['linked_product'])) {
            $how = match ($result['match']) {
                'sku' => 'pelo SKU',
                'gtin' => 'pelo código de barras',
                'title' => 'pelo nome',
                default => '',
            };
            $this->notifySuccess("Importado e ligado ao produto \"{$result['linked_product']->name}\" {$how}. O estoque do ML não foi alterado.");
            return;
        }

        if ($publication && $publication->products()->count() === 0) {
            $this->openLinkModal($publication->id);
            $this->notifySuccess('Anúncio importado. Agora escolha o produto do seu estoque.');
            return;
        }

        $this->notifySuccess($result['message']);
    }

    public function openLinkModal(int $publicationId): void
    {
        $pub = MlPublication::where('id', $publicationId)->where('user_id', Auth::id())->first();
        if (!$pub) {
            $this->notifyError('Publicação não encontrada');
            return;
        }
        $this->linkPublicationId = $pub->id;
        $this->linkSearch = '';
    }

    public function closeLinkModal(): void
    {
        $this->linkPublicationId = null;
        $this->linkSearch = '';
    }

    /**
     * Publicação aberta no modal de vínculo.
     */
    public function getLinkPublicationProperty(): ?MlPublication
    {
        if (!$this->linkPublicationId) {
            return null;
        }
        return MlPublication::with('products')->where('id', $this->linkPublicationId)->where('user_id', Auth::id())->first();
    }

    /**
     * Busca (nome, código, código de barras) ou, sem busca, sugestões pelo título do anúncio.
     */
    public function getLinkCandidatesProperty()
    {
        $pub = $this->linkPublication;
        if (!$pub) {
            return collect();
        }
        $exclude = $pub->products->pluck('id')->all();
        $term = trim($this->linkSearch);

        if (mb_strlen($term) >= 2) {
            return Product::where('user_id', Auth::id())
                ->where('status', 'ativo')
                ->whereNotIn('id', $exclude)
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('product_code', 'like', "%{$term}%")
                      ->orWhere('barcode', 'like', "%{$term}%");
                })
                ->orderBy('name')
                ->limit(12)
                ->get();
        }

        return app(MlStockSyncService::class)->suggestProductsForTitle(Auth::id(), (string) $pub->title, $exclude);
    }

    /**
     * Liga a publicação ao produto escolhido. Só o vínculo: o estoque do ML
     * não muda aqui (a página de edição pergunta antes de enviar).
     */
    public function linkProduct(int $productId): void
    {
        $pub = $this->linkPublication;
        $product = Product::where('id', $productId)->where('user_id', Auth::id())->first();
        if (!$pub || !$product) {
            $this->notifyError('Produto ou publicação não encontrado');
            return;
        }

        try {
            if (!$pub->products->contains('id', $product->id)) {
                $pub->addProduct($product->id, 1, (float) $product->price, $pub->products->count(), false);
            }
            $this->closeLinkModal();
            $this->notifySuccess("Ligado ao produto \"{$product->name}\". O estoque do ML não foi alterado — confira em Editar.");
        } catch (\Throwable $e) {
            \Log::error('Erro ao ligar produto à publicação', ['publication_id' => $pub->id, 'product_id' => $productId, 'error' => $e->getMessage()]);
            $this->notifyError('Erro ao ligar produto: ' . $e->getMessage());
        }
    }
    
    public function syncPublication($publicationId)
    {
        try {
            $publication = MlPublication::where('id', $publicationId)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            
            // Verificar se tem ml_item_id
            if (!$publication->ml_item_id || str_starts_with($publication->ml_item_id, 'TEMP_')) {
                $this->notifyError('Esta publicação ainda não foi publicada no Mercado Livre');
                return;
            }
            
            // Sincronizar dados do ML
            $syncService = app(MlStockSyncService::class);
            $result = $syncService->fetchPublicationFromMercadoLivre($publication);
            
            if ($result['success']) {
                $this->notifySuccess('Publicação sincronizada com sucesso!');
            } else {
                $this->notifyError('Erro ao sincronizar: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->notifyError('Erro ao sincronizar publicação: ' . $e->getMessage());
        }
    }
    
    public function pausePublication($publicationId)
    {
        try {
            $publication = MlPublication::where('id', $publicationId)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            
            // Verificar se tem ml_item_id
            if (!$publication->ml_item_id || str_starts_with($publication->ml_item_id, 'TEMP_')) {
                $this->notifyError('Esta publicação ainda não foi publicada no Mercado Livre');
                return;
            }
            
            // Pausar no ML
            $syncService = app(MlStockSyncService::class);
            $result = $syncService->pausePublication($publication);
            
            if ($result['success']) {
                $this->notifySuccess('Publicação pausada com sucesso!');
            } else {
                $this->notifyError('Erro ao pausar: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->notifyError('Erro ao pausar publicação: ' . $e->getMessage());
        }
    }
    
    public function activatePublication($publicationId)
    {
        try {
            $publication = MlPublication::where('id', $publicationId)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            
            // Verificar se tem ml_item_id
            if (!$publication->ml_item_id || str_starts_with($publication->ml_item_id, 'TEMP_')) {
                $this->notifyError('Esta publicação ainda não foi publicada no Mercado Livre');
                return;
            }
            
            // Ativar no ML
            $syncService = app(MlStockSyncService::class);
            $result = $syncService->activatePublication($publication);
            
            if ($result['success']) {
                $this->notifySuccess('Publicação ativada com sucesso!');
            } else {
                $this->notifyError('Erro ao ativar: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->notifyError('Erro ao ativar publicação: ' . $e->getMessage());
        }
    }
    
    public function deletePublication($publicationId)
    {
        try {
            $publication = MlPublication::where('id', $publicationId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Encerra no ML antes; só remove localmente se deu certo
            $result = app(MlStockSyncService::class)->closePublication($publication);
            if (!$result['success']) {
                $this->notifyError('Erro ao encerrar no Mercado Livre: ' . $result['message']);
                return;
            }

            $publication->delete();
            
            $this->notifySuccess('Publicação excluída com sucesso!');
        } catch (\Exception $e) {
            $this->notifyError('Erro ao excluir publicação: ' . $e->getMessage());
        }
    }
}
