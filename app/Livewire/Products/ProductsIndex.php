<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ProductsIndex extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';
    public string $category = '';
    public string $status_filtro = '';
    public string $tipo = '';
    public string $preco_min = '';
    public string $preco_max = '';
    public string $estoque = '';
    public string $estoque_valor = '';
    public string $data_inicio = '';
    public string $data_fim = '';
    public string $ordem = '';
    public bool $sem_imagem = false;
    public bool $fullHdLayout = false;
    public bool $ultraLayout = false;
    public int $perPage = 18;
    public int $page = 1;

    // Modal de exclusão
    public ?Product $deletingProduct = null;
    public bool $showDeleteModal = false;

    public function mount()
    {
        // Debug: forçar para testar
        // $this->showDeleteModal = true;

        // Define o padrão de itens por página para lojas específicas
        $this->queryString['perPage']['except'] = $this->defaultPerPage();

        if (!request()->query('perPage')) {
            $this->perPage = $this->defaultPerPage();
        }

        $this->alignPerPageToOptions();
    }

    private function isUltraWind(): bool
    {
        $clientName = env('CLIENT_NAME', config('app.client_name', ''));
        return strtolower(trim($clientName)) === 'ultra wind';
    }

    private function usesUltraMultipliers(): bool
    {
        // Permite ativar multiplicadores "ultra" a partir do cliente (largura)
        // ou pela variável de ambiente para compatibilidade.
        return $this->ultraLayout || $this->isUltraWind();
    }

    private function defaultPerPage(): int
    {
        return $this->isUltraWind() ? 32 : 18;
    }

    private function alignPerPageToOptions(): void
    {
        $allowed = $this->getPerPageOptions();
        $current = (int) $this->perPage;

        if (!in_array($current, $allowed, true)) {
            $this->perPage = $allowed[array_key_first($allowed)];
        }
    }

    // Seleção em massa
    public array $selectedProducts = [];
    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status_filtro' => ['except' => ''],
        'tipo' => ['except' => ''],
        'preco_min' => ['except' => ''],
        'preco_max' => ['except' => ''],
        'estoque' => ['except' => ''],
        'estoque_valor' => ['except' => ''],
        'data_inicio' => ['except' => ''],
        'data_fim' => ['except' => ''],
        'ordem' => ['except' => ''],
        'perPage' => ['except' => 18],
        'page' => ['except' => 1],
        'ultraLayout' => ['except' => false],
    ];

    public function updatingPerPage($value): void
    {
        $target = (int) $value;
        $allowed = $this->getPerPageOptions();

        if (!in_array($target, $allowed, true)) {
            $this->perPage = $allowed[array_key_first($allowed)];
            session()->flash('info', 'Use um dos tamanhos sugeridos para esta visualização.');
        }

        $this->resetPage();
    }

    public function updatedFullHdLayout(bool $isFullHd): void
    {
        if ($this->isUltraWind()) {
            return;
        }

        // Ajusta o valor 'except' usado no queryString para perPage baseado
        // no layout Full HD. Não forçamos resetPage aqui porque esse método
        // pode ser chamado repetidamente durante re-renders do Livewire
        // (por exemplo quando o Alpine reaplica o valor) e isso causava
        // o comportamento em que a paginação voltava para a primeira página.
        $this->queryString['perPage']['except'] = $isFullHd ? 25 : 18;
        $this->alignPerPageToOptions();
    }

    public function updatedUltraLayout(bool $isUltra): void
    {
        // Alinha as opções de perPage quando o cliente informa ultra layout.
        // Não resetamos a página para evitar comportamento de retornar
        // à página 1 quando Alpine reaplica a propriedade.
        $this->alignPerPageToOptions();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingStatusFiltro()
    {
        $this->resetPage();
    }

    public function updatingTipo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->category = '';
        $this->status_filtro = '';
        $this->tipo = '';
        $this->preco_min = '';
        $this->preco_max = '';
        $this->estoque = '';
        $this->estoque_valor = '';
        $this->data_inicio = '';
        $this->data_fim = '';
        $this->ordem = '';
        $this->sem_imagem = false;
        $this->resetPage();
    }

    public function setQuickFilter($filter)
    {
        // Limpar filtros anteriores
        $this->clearFilters();

        switch ($filter) {
            case 'baixo-estoque':
                $this->estoque = 'abaixo';
                $this->estoque_valor = '10'; // Considera baixo estoque produtos com menos de 10 unidades
                break;

            case 'sem-imagem':
                // Filtrar produtos sem imagem personalizada (que usam a imagem padrão)
                $this->sem_imagem = true;
                break;

            case 'novos':
                $this->data_inicio = now()->subDays(7)->format('Y-m-d'); // Produtos dos últimos 7 dias
                $this->ordem = 'recentes';
                break;

            case 'kits':
                $this->tipo = 'kit';
                break;

            case 'preco-zero':
                $this->preco_max = '0';
                break;
        }

        $this->resetPage();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedProducts = $this->products->pluck('id')->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function updatedSelectedProducts()
    {
        $this->selectAll = count($this->selectedProducts) === $this->products->count();
    }

    public function deleteSelected()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Nenhum produto selecionado.');
            return;
        }

        $products = Product::whereIn('id', $this->selectedProducts)->get();

        foreach ($products as $product) {
            // Verificar se a imagem do produto não é a imagem padrão
            if ($product->image && $product->image !== 'product-placeholder.png') {
                // Excluir a imagem do produto
                Storage::delete('public/products/' . $product->image);
            }

            $product->delete();
        }

        $this->selectedProducts = [];
        $this->selectAll = false;

        session()->flash('success', count($products) . ' produtos excluídos com sucesso!');
    }

    public function exportSelected()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Nenhum produto selecionado.');
            return;
        }

        // Aqui você pode implementar a lógica de exportação
        session()->flash('info', 'Funcionalidade de exportação em desenvolvimento.');
    }

    public function confirmDelete($productId)
    {
        $this->deletingProduct = Product::find($productId);
        $this->showDeleteModal = true;
    }

    public function confirmDeleteSelected()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('warning', 'Nenhum produto selecionado para exclusão.');
            return;
        }

        // Aqui poderíamos usar um modal diferente para seleção múltipla
        // Por enquanto vamos usar o mesmo modal
        $this->deletingProduct = Product::find($this->selectedProducts[0]); // Primeiro produto para mostrar no modal
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        // Se há produtos selecionados, excluir em massa
        if (!empty($this->selectedProducts)) {
            $deletedCount = 0;

            foreach ($this->selectedProducts as $productId) {
                $product = Product::find($productId);
                if ($product) {
                    // Verificar se a imagem do produto não é a imagem padrão
                    if ($product->image && $product->image !== 'product-placeholder.png') {
                        // Excluir a imagem do produto
                        Storage::delete('public/products/' . $product->image);
                    }

                    // Excluir o produto
                    $product->delete();
                    $deletedCount++;
                }
            }

            session()->flash('success', "{$deletedCount} produto(s) excluído(s) com sucesso!");
            $this->selectedProducts = [];
            $this->selectAll = false;
        }
        // Se há apenas um produto para deletar
        elseif ($this->deletingProduct) {
            // Verificar se a imagem do produto não é a imagem padrão
            if ($this->deletingProduct->image && $this->deletingProduct->image !== 'product-placeholder.png') {
                // Excluir a imagem do produto
                Storage::delete('public/products/' . $this->deletingProduct->image);
            }

            // Excluir o produto
            $this->deletingProduct->delete();

            session()->flash('success', 'Produto excluído com sucesso!');
        }

        $this->showDeleteModal = false;
        $this->deletingProduct = null;
    }

    /**
     * Gera as opções de paginação com base no cliente.
     * Ultra Wind: múltiplos de 8. Padrão: múltiplos de 6.
     */
    public function getPerPageOptions(): array
    {
        if ($this->usesUltraMultipliers()) {
            // Múltiplos de 8 para grids mais largos
            return [32, 40, 48, 56, 64, 80, 96];
        }

        if ($this->fullHdLayout) {
            // Layout pensado para monitores 1920px (5 cards por linha)
            return [25, 30, 35, 40, 45, 50];
        }

        // Múltiplos de 6 (padrão)
        return [12, 18, 24, 30, 36, 42, 48];
    }

    #[On('product-created')]
    #[On('product-updated')]
    #[On('kit-created')]
    #[On('kit-updated')]
    public function refreshList()
    {
        // Força o refresh dos dados
        $this->render();
    }

    public function getProductsProperty()
    {
        $userId = Auth::id();

        // Inicializa a consulta para produtos, filtrando pelo user_id
        $query = Product::where('user_id', $userId);

        // Filtro de pesquisa por nome ou código
        if (!empty($this->search)) {
            $searchTerm = str_replace('.', '', $this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere(DB::raw("REPLACE(product_code, '.', '')"), 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtro por categoria
        if (!empty($this->category)) {
            $query->where('category_id', $this->category);
        }

        // Filtro de status
        $statusValidos = ['ativo', 'inativo', 'descontinuado'];
        if (!empty($this->status_filtro) && in_array($this->status_filtro, $statusValidos)) {
            $query->where('status', $this->status_filtro);
        }

        // Filtro por tipo (simples ou kit)
        if (!empty($this->tipo) && in_array($this->tipo, ['simples', 'kit'])) {
            $query->where('tipo', $this->tipo);
        }

        // Filtro por faixa de preço
        if (!empty($this->preco_min) && is_numeric($this->preco_min)) {
            $query->where('price_sale', '>=', $this->preco_min);
        }
        if (!empty($this->preco_max) && is_numeric($this->preco_max)) {
            $query->where('price_sale', '<=', $this->preco_max);
        }

        // Filtro por estoque
        if (!empty($this->estoque)) {
            if ($this->estoque === 'zerado') {
                $query->where('stock_quantity', 0);
            } elseif ($this->estoque === 'abaixo' && is_numeric($this->estoque_valor)) {
                $query->where('stock_quantity', '<', $this->estoque_valor);
            }
        }

        // Filtro por data de criação
        if (!empty($this->data_inicio)) {
            $query->whereDate('created_at', '>=', $this->data_inicio);
        }
        if (!empty($this->data_fim)) {
            $query->whereDate('created_at', '<=', $this->data_fim);
        }

        // Filtro para produtos sem imagem personalizada
        if ($this->sem_imagem) {
            $query->where(function ($q) {
                $q->whereNull('image')
                  ->orWhere('image', '')
                  ->orWhere('image', 'product-placeholder.png');
            });
        }

        // Ordenação
        if (!empty($this->ordem)) {
            switch ($this->ordem) {
                case 'recentes':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'antigas':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'az':
                    $query->orderBy('name', 'asc');
                    break;
                case 'za':
                    $query->orderBy('name', 'desc');
                    break;
            }
        } else {
            // Ordenar produtos fora de estoque para o final se nenhuma ordenação for aplicada
            $query->orderByRaw('stock_quantity > 0 DESC');
        }

        $products = $query->paginate($this->perPage);

        // Garante que os links de paginação apontem para a rota pública (evita herdar
        // o caminho interno do Livewire como `/livewire/update`) e preserva os filtros
        // atuais, especialmente `perPage`, para que a seleção de itens por página não seja perdida.
        try {
            $appends = [
                'search' => $this->search,
                'category' => $this->category,
                'status_filtro' => $this->status_filtro,
                'tipo' => $this->tipo,
                'preco_min' => $this->preco_min,
                'preco_max' => $this->preco_max,
                'estoque' => $this->estoque,
                'estoque_valor' => $this->estoque_valor,
                'data_inicio' => $this->data_inicio,
                'data_fim' => $this->data_fim,
                'ordem' => $this->ordem,
                'perPage' => $this->perPage,
                'sem_imagem' => $this->sem_imagem ? 1 : 0,
            ];

            // Remover chaves vazias para URLs mais limpas
            $appends = array_filter($appends, function ($v) {
                return $v !== '' && $v !== null;
            });

            $products->appends($appends);
            $products->setPath(route('products.index'));
        } catch (\Exception $e) {
            Log::warning('Falha ao ajustar path do paginator: ' . $e->getMessage());
        }

        return $products;
    }

    public function getCategoriesProperty()
    {
        return Category::where('user_id', Auth::id())
                      ->where('type', 'product')
                      ->where('is_active', 1)
                      ->orderBy('name')
                      ->get();
    }

    public function render()
    {
        return view('livewire.products.products-index', [
            'products' => $this->products,
            'categories' => $this->categories,
            'perPageOptions' => $this->getPerPageOptions(),
        ]);
    }
}
