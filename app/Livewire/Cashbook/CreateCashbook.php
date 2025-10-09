<?php

namespace App\Livewire\Cashbook;

use App\Models\Cashbook;
use App\Models\Category;
use App\Models\Type;
use App\Models\Segment;
use App\Models\Client;
use App\Models\Cofrinho;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class CreateCashbook extends Component
{
    use WithFileUploads;

    public $value = '';
    public $description = '';
    public $date = '';
    public $currentStep = 1;
    public $is_pending = false;
    public $attachment = null;
    public $category_id = '';
    public $type_id = '';
    public $client_id = '';
    public $note = '';
    public $segment_id = '';
    public $cofrinho_id = '';

    public function mount(): void
    {
        $this->date = Carbon::now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate([
            'value' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
            'is_pending' => 'required|boolean',
            'attachment' => 'nullable|file|max:2048',
            'category_id' => 'required|exists:category,id_category',
            'type_id' => 'required|exists:type,id_type',
            'client_id' => 'nullable|exists:clients,id',
            'note' => 'nullable|string|max:255',
            'segment_id' => 'nullable|exists:segment,id',
            'cofrinho_id' => 'nullable|exists:cofrinhos,id',
        ], [
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
        ]);

        $data = [
            'user_id' => Auth::id(),
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
            'inc_datetime' => now(),
        ];

        if ($this->attachment) {
            $data['attachment'] = $this->attachment->store('attachments', 'public');
        }

        Cashbook::create($data);

        session()->flash('success', 'Transação criada com sucesso!');

        $this->dispatch('transaction-created');

        $this->redirect(route('cashbook.index'));
    }

    public function nextStep()
    {
        // Validação leve e manual para não travar o botão por validações pesadas
        $this->resetErrorBag();
        $hasError = false;

        if (empty($this->value) || !is_numeric($this->value) || (float)$this->value <= 0) {
            $this->addError('value', 'Informe um valor válido maior que zero.');
            $hasError = true;
        }

        if (empty($this->date)) {
            $this->addError('date', 'A data é obrigatória.');
            $hasError = true;
        }

        if (empty($this->category_id)) {
            $this->addError('category_id', 'A categoria é obrigatória.');
            $hasError = true;
        }

        if (empty($this->type_id)) {
            $this->addError('type_id', 'O tipo é obrigatório.');
            $hasError = true;
        }

        if (! $hasError) {
            $this->currentStep = min(2, $this->currentStep + 1);
        }
    }

    public function previousStep()
    {
        $this->currentStep = max(1, $this->currentStep - 1);
    }

    public function cancel()
    {
        $this->redirect(route('cashbook.index'));
    }

    public function render()
    {
        $categories = Category::where('user_id', Auth::id())
            ->where('type', 'transaction')
            ->orderBy('name')
            ->get();

        $types = Type::orderBy('desc_type')->get();

        $segments = Segment::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $clients = Client::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $cofrinhos = Cofrinho::where('user_id', Auth::id())
            ->orderBy('nome')
            ->get();

        return view('livewire.cashbook.create-cashbook', compact('categories', 'types', 'segments', 'clients', 'cofrinhos'));
    }
}
