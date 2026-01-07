<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Consortium;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class EditConsortium extends Component
{
    public Consortium $consortium;

    // Campos editáveis
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    #[Validate('required|numeric|min:0.01')]
    public $monthly_value = '';

    #[Validate('required|integer|min:1|max:120')]
    public $duration_months = '';

    #[Validate('required|integer|min:2|max:1000')]
    public $max_participants = '';

    #[Validate('required|in:monthly,bimonthly,weekly')]
    public $draw_frequency = 'monthly';

    #[Validate('required|date')]
    public $start_date = '';

    #[Validate('required|in:active,completed,cancelled')]
    public $status = 'active';

    // Computed
    public $total_value = 0;
    public $hasDraws = false;
    public $hasParticipants = false;

    public function mount(Consortium $consortium)
    {
        // Verificar se o usuário tem permissão
        if ($consortium->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $this->consortium = $consortium;

        // Preencher os campos
        $this->name = $consortium->name;
        $this->description = $consortium->description;
        $this->monthly_value = $consortium->monthly_value;
        $this->duration_months = $consortium->duration_months;
        $this->max_participants = $consortium->max_participants;
        $this->draw_frequency = $consortium->draw_frequency;
        $this->start_date = $consortium->start_date?->format('Y-m-d') ?? '';
        $this->status = $consortium->status;

        // Verificar se tem sorteios ou participantes (limita edição)
        $this->hasDraws = $consortium->draws()->count() > 0;
        $this->hasParticipants = $consortium->participants()->count() > 0;

        $this->calculateTotalValue();
    }

    public function updatedMonthlyValue()
    {
        $this->calculateTotalValue();
    }

    public function updatedDurationMonths()
    {
        $this->calculateTotalValue();
    }

    public function updatedMaxParticipants()
    {
        $this->calculateTotalValue();
    }

    private function calculateTotalValue()
    {
        if ($this->monthly_value && $this->duration_months && $this->max_participants) {
            $this->total_value = $this->monthly_value * $this->duration_months * $this->max_participants;
        }
    }

    public function update()
    {
        // Validar todos os campos
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'monthly_value' => 'required|numeric|min:0.01',
            'duration_months' => 'required|integer|min:1|max:120',
            'max_participants' => 'required|integer|min:2|max:1000',
            'draw_frequency' => 'required|in:monthly,bimonthly,weekly',
            'start_date' => 'required|date',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        // Validações especiais
        if ($this->hasParticipants) {
            // Se já tem participantes, não pode reduzir max_participants abaixo do atual
            $currentCount = $this->consortium->participants()->count();
            if ($validated['max_participants'] < $currentCount) {
                $this->addError('max_participants', "Não é possível reduzir para menos de {$currentCount} participantes (já cadastrados).");
                return;
            }
        }

        // Calcular valor total
        $validated['total_value'] = $validated['monthly_value'] * $validated['duration_months'] * $validated['max_participants'];

        // Atualizar o consórcio
        $this->consortium->update($validated);

        session()->flash('message', 'Consórcio atualizado com sucesso!');

        return redirect()->route('consortiums.show', $this->consortium);
    }

    public function render()
    {
        return view('livewire.consortiums.edit-consortium');
    }
}
