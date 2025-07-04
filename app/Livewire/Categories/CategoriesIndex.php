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

    // Modal de exclusão
    public ?Category $deletingCategory = null;
    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 18],
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

    public function render()
    {
        $query = Category::where('user_id', Auth::id());

        // Aplicar filtros
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('desc_category', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        $categories = $query->orderBy('name')->paginate($this->perPage);

        // Separar categorias por tipo
        $productCategories = Category::where('user_id', Auth::id())
            ->where('is_active', 1)
            ->where('type', 'product')
            ->get();

        $transactionCategories = Category::where('user_id', Auth::id())
            ->where('is_active', 1)
            ->where('type', 'transaction')
            ->get();

        return view('livewire.categories.categories-index', [
            'categories' => $categories,
            'productCategories' => $productCategories,
            'transactionCategories' => $transactionCategories,
        ]);
    }
}
