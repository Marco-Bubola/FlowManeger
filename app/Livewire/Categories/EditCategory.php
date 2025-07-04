<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use App\Models\Bank;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditCategory extends Component
{
    public Category $category;
    
    // Propriedades do formulário
    public ?int $parent_id = null;
    public string $name = '';
    public string $desc_category = '';
    public string $hexcolor_category = '#6366f1';
    public string $icone = 'fas fa-tag';
    public string $descricao_detalhada = '';
    public string $tipo = '';
    public ?float $limite_orcamento = null;
    public bool $compartilhavel = false;
    public string $tags = '';
    public string $regras_auto_categorizacao = '';
    public ?int $id_bank = null;
    public ?int $id_clients = null;
    public ?int $id_produtos_clientes = null;
    public string $historico_alteracoes = '';
    public int $is_active = 1;
    public string $description = '';
    public string $type = 'product';

    // Dados para os selects
    public $banks = [];
    public $clients = [];
    public $categories = [];

    protected $rules = [
        'name' => 'required|string|max:100',
        'desc_category' => 'nullable|string|max:100',
        'hexcolor_category' => 'nullable|string|max:45',
        'icone' => 'nullable|string|max:100',
        'descricao_detalhada' => 'nullable|string',
        'tipo' => 'nullable|in:gasto,receita,ambos',
        'limite_orcamento' => 'nullable|numeric',
        'compartilhavel' => 'nullable|boolean',
        'tags' => 'nullable|string|max:255',
        'regras_auto_categorizacao' => 'nullable|string',
        'id_bank' => 'nullable|integer',
        'id_clients' => 'nullable|integer',
        'id_produtos_clientes' => 'nullable|integer',
        'historico_alteracoes' => 'nullable|string',
        'is_active' => 'required|integer|in:0,1',
        'description' => 'nullable|string',
        'type' => 'required|in:product,transaction',
    ];

    public function mount(Category $category)
    {
        $this->category = $category;
        $this->loadCategoryData();
        $this->loadSelectData();
    }

    public function loadCategoryData()
    {
        $this->parent_id = $this->category->parent_id;
        $this->name = $this->category->name;
        $this->desc_category = $this->category->desc_category ?? '';
        $this->hexcolor_category = $this->category->hexcolor_category ?? '#6366f1';
        $this->icone = $this->category->icone ?? 'fas fa-tag';
        $this->descricao_detalhada = $this->category->descricao_detalhada ?? '';
        $this->tipo = $this->category->tipo ?? '';
        $this->limite_orcamento = $this->category->limite_orcamento;
        $this->compartilhavel = $this->category->compartilhavel ?? false;
        $this->tags = $this->category->tags ?? '';
        $this->regras_auto_categorizacao = $this->category->regras_auto_categorizacao ?? '';
        $this->id_bank = $this->category->id_bank;
        $this->id_clients = $this->category->id_clients;
        $this->id_produtos_clientes = $this->category->id_produtos_clientes;
        $this->historico_alteracoes = $this->category->historico_alteracoes ?? '';
        $this->is_active = $this->category->is_active;
        $this->description = $this->category->description ?? '';
        $this->type = $this->category->type;
    }

    public function loadSelectData()
    {
        $this->banks = Bank::all();
        $this->clients = Client::all();
        $this->categories = Category::where('user_id', Auth::id())
            ->where('id_category', '!=', $this->category->id_category)
            ->get();
    }

    public function save()
    {
        $this->validate();

        $this->category->update([
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'desc_category' => $this->desc_category,
            'hexcolor_category' => $this->hexcolor_category,
            'icone' => $this->icone,
            'descricao_detalhada' => $this->descricao_detalhada,
            'tipo' => $this->tipo,
            'limite_orcamento' => $this->limite_orcamento,
            'compartilhavel' => $this->compartilhavel,
            'tags' => $this->tags,
            'regras_auto_categorizacao' => $this->regras_auto_categorizacao,
            'id_bank' => $this->id_bank,
            'id_clients' => $this->id_clients,
            'id_produtos_clientes' => $this->id_produtos_clientes,
            'historico_alteracoes' => $this->historico_alteracoes,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'type' => $this->type,
        ]);

        $this->dispatch('category-updated');
        session()->flash('success', 'Categoria atualizada com sucesso!');
        
        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.categories.edit-category');
    }
}
