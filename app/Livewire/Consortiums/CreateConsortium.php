<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Consortium;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class CreateConsortium extends Component
{
    // Passo atual do wizard
    public $currentStep = 1;

    // Passo 1: Informações Básicas
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    #[Validate('required|numeric|min:0.01')]
    public $monthly_value = '';

    #[Validate('required|integer|min:1|max:120')]
    public $duration_months = '';

    // Passo 2: Configurações
    #[Validate('required|integer|min:2|max:1000')]
    public $max_participants = '';

    #[Validate('required|in:monthly,bimonthly,weekly')]
    public $draw_frequency = 'monthly';

    #[Validate('required|date|after_or_equal:today')]
    public $start_date = '';

    // Computed
    public $total_value = 0;

    // Modal de dicas
    public $showTipsModal = false;

    public function mount()
    {
        // Define data inicial padrão como primeiro dia do próximo mês
        $this->start_date = now()->addMonth()->startOfMonth()->format('Y-m-d');
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

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep($step)
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    private function validateCurrentStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'monthly_value' => 'required|numeric|min:0.01',
                'duration_months' => 'required|integer|min:1|max:120',
            ]);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'max_participants' => 'required|integer|min:2|max:1000',
                'draw_frequency' => 'required|in:monthly,bimonthly,weekly',
                'start_date' => 'required|date|after_or_equal:today',
            ]);
        }
    }

    public function save()
    {
        // Validar todos os campos
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'monthly_value' => 'required|numeric|min:0.01',
            'duration_months' => 'required|integer|min:1|max:120',
            'max_participants' => 'required|integer|min:2|max:1000',
            'draw_frequency' => 'required|in:monthly,bimonthly,weekly',
            'start_date' => 'required|date|after_or_equal:today',
        ]);

        // Calcular valor total
        $validated['total_value'] = $validated['monthly_value'] * $validated['duration_months'] * $validated['max_participants'];
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'active';

        // Criar o consórcio
        $consortium = Consortium::create($validated);

        session()->flash('message', 'Consórcio criado com sucesso!');

        return redirect()->route('consortiums.show', $consortium);
    }

    public function toggleTips()
    {
        $this->showTipsModal = !$this->showTipsModal;
    }

    public function render()
    {
        return view('livewire.consortiums.create-consortium');
    }
}
