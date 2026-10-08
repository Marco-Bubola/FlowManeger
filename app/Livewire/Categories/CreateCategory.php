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
    public ?string $tipo = null; // Permitir null
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
        'tipo' => 'nullable|string|in:gasto,receita,ambos',
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

    protected $messages = [
        'name.required' => 'O nome da categoria é obrigatório.',
        'name.max' => 'O nome da categoria não pode ter mais de 100 caracteres.',
        'type.required' => 'Selecione um tipo de categoria.',
        'type.in' => 'Tipo de categoria inválido.',
        'limite_orcamento.numeric' => 'O limite de orçamento deve ser um número válido.',
        'is_active.required' => 'Defina o status da categoria.',
    ];

    public function mount()
    {
        $this->loadSelectData();

        // A lista abre "Nova categoria de transação" com ?type=transaction
        $requested = request()->query('type');
        if (in_array($requested, ['product', 'transaction'], true)) {
            $this->type = $requested;
        }

        $this->setDefaultValues();
    }

    public function loadSelectData()
    {
        $this->banks = Bank::where('user_id', Auth::id())->get();
        $this->clients = Client::where('user_id', Auth::id())->get();
        $this->categories = Category::where('user_id', Auth::id())->get();
    }

    public function setDefaultValues()
    {
        // Ícone, cor e tipo de lançamento padrão de acordo com o tipo da categoria
        if ($this->type === 'transaction') {
            $this->icone = 'fas fa-dollar-sign';
            $this->hexcolor_category = '#10b981';
            $this->tipo = $this->tipo ?: 'gasto';
        } else {
            $this->icone = 'fas fa-box';
            $this->hexcolor_category = '#6366f1';
            $this->tipo = null;
        }
    }

    public function updatedType($value)
    {
        $this->setDefaultValues();

        if ($value !== 'transaction') {
            $this->id_bank = null;
            $this->id_clients = null;
            $this->limite_orcamento = null;
        }
    }

    public function updatedHexcolorCategory($value)
    {
        // Validar formato hexadecimal
        if (!preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            $this->hexcolor_category = '#6366f1';
        }
    }

    public function save()
    {
        // Produto não tem tipo de lançamento; transação é despesa, receita ou as duas
        $this->tipo = $this->type === 'transaction' ? ($this->tipo ?: 'ambos') : null;

        $this->validate();

        if ($this->parent_id && !Category::where('user_id', Auth::id())->where('id_category', $this->parent_id)->exists()) {
            $this->parent_id = null;
        }

        // Preparar dados para criação, removendo campos vazios/nulos desnecessários
        $categoryData = [
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'desc_category' => $this->desc_category ?: null,
            'hexcolor_category' => $this->hexcolor_category,
            'icone' => $this->icone,
            'descricao_detalhada' => $this->descricao_detalhada ?: null,
            'limite_orcamento' => $this->limite_orcamento,
            'compartilhavel' => $this->compartilhavel,
            'tags' => $this->tags ?: null,
            'regras_auto_categorizacao' => $this->regras_auto_categorizacao ?: null,
            'id_bank' => $this->id_bank,
            'id_clients' => $this->id_clients,
            'id_produtos_clientes' => $this->id_produtos_clientes,
            'historico_alteracoes' => $this->historico_alteracoes ?: null,
            'is_active' => $this->is_active,
            'description' => $this->description ?: null,
            'user_id' => Auth::id(),
            'type' => $this->type,
        ];

        $categoryData['tipo'] = $this->tipo;

        // Criar a categoria
        $category = Category::create($categoryData);

        $this->dispatch('category-created', ['category' => $category]);

        // Mensagem de sucesso personalizada baseada no tipo
        $typeLabels = [
            'product' => 'de produto',
            'transaction' => 'de transação',
        ];

        $message = 'Categoria ' . ($typeLabels[$this->type] ?? '') . ' "' . $this->name . '" criada com sucesso!';
        session()->flash('success', $message);

        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.categories.create-category-fluid');
    }
}
