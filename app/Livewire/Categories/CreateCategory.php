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
        'tipo' => 'nullable|string|in:,gasto,receita,ambos,pix,ted,doc',
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
        'type' => 'required|in:product,transaction,transfer',
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
        // Definir valores padrão com base no tipo
        $this->setDefaultValues();

        // Inicializar campo tipo como null para produtos/transações
        if ($this->type !== 'transfer') {
            $this->tipo = null;
        }
    }

    public function loadSelectData()
    {
        $this->banks = Bank::all();
        $this->clients = Client::all();
        $this->categories = Category::where('user_id', Auth::id())->get();
    }

    public function setDefaultValues()
    {
        // Definir ícone padrão baseado no tipo
        if ($this->type === 'transfer') {
            $this->icone = 'fas fa-exchange-alt';
            $this->hexcolor_category = '#8b5cf6'; // Roxo para transferências
        } elseif ($this->type === 'transaction') {
            $this->icone = 'fas fa-dollar-sign';
            $this->hexcolor_category = '#10b981'; // Verde para transações
        } else {
            $this->icone = 'fas fa-box';
            $this->hexcolor_category = '#6366f1'; // Azul para produtos
        }
    }

    public function updatedType($value)
    {
        // Quando o tipo muda, ajustar configurações padrão
        $this->setDefaultValues();

        // Limpar campos específicos quando muda o tipo
        if ($value !== 'transfer') {
            $this->tipo = null;
            $this->id_bank = null;
            $this->id_clients = null;
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
        // Limpar campo 'tipo' para categorias que não são transferência
        if ($this->type !== 'transfer') {
            $this->tipo = null; // Usar null em vez de string vazia
        }

        $this->validate();

        // Validações específicas para transferências
        if ($this->type === 'transfer') {
            if (empty($this->tipo)) {
                $this->addError('tipo', 'Para categorias de transferência, selecione o tipo específico (PIX, TED ou DOC).');
                return;
            }
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

        // Só adicionar 'tipo' se for transferência e não estiver vazio
        if ($this->type === 'transfer' && !empty($this->tipo)) {
            $categoryData['tipo'] = $this->tipo;
        } else {
            $categoryData['tipo'] = null;
        }

        // Criar a categoria
        $category = Category::create($categoryData);

        $this->dispatch('category-created', ['category' => $category]);

        // Mensagem de sucesso personalizada baseada no tipo
        $typeLabels = [
            'product' => 'de produto',
            'transaction' => 'de transação',
            'transfer' => 'de transferência'
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
