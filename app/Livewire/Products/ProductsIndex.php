<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    // public int $perPage = 18; // Removido para evitar duplicidade

    // Modal de exclusão
    public ?Product $deletingProduct = null;
    public bool $showDeleteModal = false;

    public $perPage = 18;
    public $page = 1;

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
    ];

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
        $this->resetPage();
    }

    public function confirmDelete(Product $product)
    {
        $this->deletingProduct = $product;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->deletingProduct) {
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

        return $query->paginate($this->perPage);
    }

    public function getCategoriesProperty()
    {
        return Category::where('user_id', Auth::id())->get();
    }

    public function render()
    {
        return view('livewire.products.products-index', [
            'products' => $this->products,
            'categories' => $this->categories,
        ]);
    }
}
