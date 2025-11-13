<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use App\Models\Bank;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class CategoriesIndex extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';
    public string $typeFilter = '';
    public string $statusFilter = '';
    public int $perPage = 18;
    public string $sortBy = 'name';

    // Configurações de visualização
    public string $viewMode = 'grid'; // 'grid' ou 'list'
    public bool $showFilters = false; // Para controlar a visibilidade dos filtros
    public string $activeTab = 'products'; // 'products', 'transactions', 'all', 'tips'

    // Modal de exclusão
    public ?Category $deletingCategory = null;
    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 18],
        'sortBy' => ['except' => 'name'],
        'viewMode' => ['except' => 'grid'],
        'activeTab' => ['except' => 'products'],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->resetPage();
    }

    #[On('category-created')]
    #[On('category-updated')]
    #[On('category-deleted')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    // Filtros rápidos
    public function quickFilter(string $filter): void
    {
        $this->resetPage();

        switch ($filter) {
            case 'recent':
                $this->sortBy = 'created_at';
                break;
            case 'active':
                $this->statusFilter = '1';
                break;
            case 'products':
                $this->typeFilter = 'product';
                break;
            case 'transactions':
                $this->typeFilter = 'transaction';
                break;
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->sortBy = 'name';
        $this->resetPage();
    }

    // Ações para mostrar mais categorias
    public function showAllProducts(): void
    {
        $this->typeFilter = 'product';
        $this->resetPage();
    }

    public function showAllTransactions(): void
    {
        $this->typeFilter = 'transaction';
        $this->resetPage();
    }

    // Exportar categorias
    public function exportCategories(): void
    {
        // Aqui você pode implementar a lógica de exportação
        session()->flash('success', 'Funcionalidade de exportação será implementada em breve!');
    }

    public function confirmDelete(Category $category): void
    {
        $this->deletingCategory = $category;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingCategory = null;
        $this->showDeleteModal = false;
    }

    public function deleteCategory(): void
    {
        if ($this->deletingCategory) {
            $this->deletingCategory->delete();
            $this->dispatch('category-deleted');
            $this->cancelDelete();
            session()->flash('success', 'Categoria excluída com sucesso!');
        }
    }

    // Funcionalidades de favoritas
    public function toggleFavorite(int $categoryId): void
    {
        $category = Category::where('id_category', $categoryId)->first();

        if ($category) {
            // Como não temos o campo is_favorite na base de dados ainda,
            // vamos simular a funcionalidade por enquanto
            session()->flash('success', 'Funcionalidade de favoritas será implementada em breve!');
        }
    }

    // Funcionalidades de compartilhamento
    public function shareCategory(int $categoryId): void
    {
        $category = Category::where('id_category', $categoryId)->first();

        if ($category) {
            // Aqui você pode implementar a lógica de compartilhamento
            session()->flash('success', 'Link de compartilhamento gerado! Funcionalidade será implementada em breve.');
        }
    }

    // Controle de filtros
    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    // Métodos para reordenação drag-and-drop
    public function updateProductOrder($orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Category::where('id_category', $id['value'])
                ->where('type', 'product')
                ->update(['sort_order' => $index + 1]);
        }

        session()->flash('success', 'Ordem das categorias de produtos atualizada!');
    }

    public function updateTransactionOrder($orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Category::where('id_category', $id['value'])
                ->where('type', 'transaction')
                ->update(['sort_order' => $index + 1]);
        }

        session()->flash('success', 'Ordem das categorias de transações atualizada!');
    }

    // Controle de abas
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // Métodos para criar categorias específicas
    public function createProductCategory()
    {
        // Redirecionar para criação de categoria de produto
        return redirect()->route('categories.create', ['type' => 'product']);
    }

    public function createTransactionCategory()
    {
        // Redirecionar para criação de categoria de transação
        return redirect()->route('categories.create', ['type' => 'transaction']);
    }

    public function render()
    {
        // Filtrar apenas categorias do usuário logado
        $query = Category::where('user_id', Auth::id());

        // Aplicar filtros de busca
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Aplicar filtro de tipo: se o usuário escolheu explicitamente um typeFilter, respeitar.
        // Caso contrário, aplicar o filtro baseado na aba ativa (products/transactions),
        // e para a aba 'all' não aplicar filtro por tipo.
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        } else {
            if ($this->activeTab === 'products') {
                $query->where('type', 'product');
            } elseif ($this->activeTab === 'transactions') {
                $query->where('type', 'transaction');
            }
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        // Aplicar ordenação
        switch ($this->sortBy) {
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'created_at':
                $query->orderBy('created_at', 'desc');
                break;
            case 'created_at_desc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'type':
                $query->orderBy('type')->orderBy('name');
                break;
            default:
                $query->orderBy('name');
                break;
        }

        $categories = $query->paginate($this->perPage);

        // Separar categorias por tipo para os resumos (apenas do usuário logado)
        $productCategories = Category::where('type', 'product')
            ->where('user_id', Auth::id())
            ->get();
        $transactionCategories = Category::where('type', 'transaction')
            ->where('user_id', Auth::id())
            ->get();

        // Criar paginação independente para categorias de produtos e transações
        $paginatedProductCategories = Category::where('type', 'product')
            ->where('user_id', Auth::id())
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('name')
            ->paginate(6, ['*'], 'product_page');

        $paginatedTransactionCategories = Category::where('type', 'transaction')
            ->where('user_id', Auth::id())
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('name')
            ->paginate(6, ['*'], 'transaction_page');

        return view('livewire.categories.categories-index', [
            'categories' => $categories,
            'productCategories' => $productCategories,
            'transactionCategories' => $transactionCategories,
            'paginatedProductCategories' => $paginatedProductCategories,
            'paginatedTransactionCategories' => $paginatedTransactionCategories,
        ]);
    }
}
