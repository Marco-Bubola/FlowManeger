<?php

namespace App\Livewire\Cashbook;

use App\Models\Cashbook;
use App\Models\Category;
use App\Models\Type;
use App\Models\Segment;
use App\Models\Client;
use App\Models\Cofrinho;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditCashbook extends Component
{
    use WithFileUploads;

    public Cashbook $cashbook;
    
    // Propriedades do formulário
    public $value = '';
    public $description = '';
    public $date = '';
    public $is_pending = false;
    public $attachment = null;
    public $category_id = '';
    public $type_id = '';
    public $client_id = '';
    public $note = '';
    public $segment_id = '';
    public $cofrinho_id = '';

    // Dados para select
    public $categories = [];
    public $types = [];
    public $segments = [];
    public $clients = [];
    public $cofrinhos = [];

    // Propriedades para validação
    protected $rules = [
        'value' => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:255',
        'date' => 'required|date',
        'is_pending' => 'boolean',
        'attachment' => 'nullable|file|max:2048',
        'category_id' => 'required|exists:category,id_category',
        'type_id' => 'required|exists:type,id_type',
        'client_id' => 'nullable|exists:clients,id',
        'note' => 'nullable|string|max:255',
        'segment_id' => 'nullable|exists:segment,id',
        'cofrinho_id' => 'nullable|exists:cofrinhos,id',
    ];

    protected $messages = [
        'value.required' => 'O valor é obrigatório.',
        'value.numeric' => 'O valor deve ser um número.',
        'value.min' => 'O valor deve ser maior que zero.',
        'date.required' => 'A data é obrigatória.',
        'date.date' => 'A data deve ser uma data válida.',
        'category_id.required' => 'A categoria é obrigatória.',
        'category_id.exists' => 'A categoria selecionada não existe.',
        'type_id.required' => 'O tipo é obrigatório.',
        'type_id.exists' => 'O tipo selecionado não existe.',
        'client_id.exists' => 'O cliente selecionado não existe.',
        'segment_id.exists' => 'O segmento selecionado não existe.',
        'cofrinho_id.exists' => 'O cofrinho selecionado não existe.',
        'attachment.file' => 'O anexo deve ser um arquivo.',
        'attachment.max' => 'O anexo deve ter no máximo 2MB.',
    ];

    public function mount(Cashbook $cashbook)
    {
        $this->cashbook = $cashbook;
        
        // Preencher formulário com dados existentes
        $this->value = $cashbook->value;
        $this->description = $cashbook->description;
        $this->date = $cashbook->date;
        $this->is_pending = $cashbook->is_pending;
        $this->category_id = $cashbook->category_id;
        $this->type_id = $cashbook->type_id;
        $this->client_id = $cashbook->client_id;
        $this->note = $cashbook->note;
        $this->segment_id = $cashbook->segment_id;
        $this->cofrinho_id = $cashbook->cofrinho_id;
        
        $this->loadData();
    }

    public function loadData()
    {
        $this->categories = Category::where('user_id', Auth::id())
            ->where('type', 'transaction')
            ->orderBy('name')
            ->get();

        $this->types = Type::orderBy('desc_type')->get();

        $this->segments = Segment::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $this->clients = Client::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $this->cofrinhos = Cofrinho::where('user_id', Auth::id())
            ->orderBy('nome')
            ->get();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'value' => $this->value,
            'description' => $this->description,
            'date' => $this->date,
            'is_pending' => $this->is_pending,
            'category_id' => $this->category_id,
            'type_id' => $this->type_id,
            'client_id' => $this->client_id ?: null,
            'note' => $this->note,
            'segment_id' => $this->segment_id ?: null,
            'cofrinho_id' => $this->cofrinho_id ?: null,
            'edit_datetime' => now(),
        ];

        if ($this->attachment) {
            // Deletar anexo antigo se existir
            if ($this->cashbook->attachment) {
                Storage::disk('public')->delete($this->cashbook->attachment);
            }
            $data['attachment'] = $this->attachment->store('attachments', 'public');
        }

        $this->cashbook->update($data);

        $this->dispatch('transaction-updated');
        session()->flash('success', 'Transação atualizada com sucesso!');
        
        $this->redirect(route('cashbook.index'));
    }

    public function cancel()
    {
        $this->redirect(route('cashbook.index'));
    }

    public function render()
    {
        return view('livewire.cashbook.edit-cashbook');
    }
}
