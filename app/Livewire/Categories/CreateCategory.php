<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use App\Models\Bank;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateCategory extends Component
{
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

    public function mount()
    {
        $this->loadSelectData();
    }

    public function loadSelectData()
    {
        $this->banks = Bank::all();
        $this->clients = Client::all();
        $this->categories = Category::where('user_id', Auth::id())->get();
    }

    public function save()
    {
        $this->validate();

        Category::create([
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
            'user_id' => Auth::id(),
            'type' => $this->type,
        ]);

        $this->dispatch('category-created');
        session()->flash('success', 'Categoria criada com sucesso!');
        
        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.categories.create-category');
    }
}
