<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Consortium;
use App\Models\Client;
use App\Models\ConsortiumParticipant;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class AddParticipant extends Component
{
    public Consortium $consortium;
    public $showModal = false;

    #[Validate('required|exists:clients,id')]
    public $client_id = '';

    #[Validate('required|date')]
    public $entry_date = '';

    public $notes = '';

    public function mount(Consortium $consortium)
    {
        $this->consortium = $consortium;
        $this->entry_date = now()->format('Y-m-d');
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->entry_date = now()->format('Y-m-d');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['client_id', 'notes']);
        $this->entry_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function addParticipant()
    {
        // Validar se pode adicionar mais participantes
        if (!$this->consortium->canAddParticipants()) {
            session()->flash('error', 'Consórcio já atingiu o número máximo de participantes.');
            return;
        }

        $this->validate();

        // Verificar se o cliente já participa deste consórcio
        $exists = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
            ->where('client_id', $this->client_id)
            ->where('status', '!=', 'quit')
            ->exists();

        if ($exists) {
            $this->addError('client_id', 'Este cliente já participa deste consórcio.');
            return;
        }

        // Gerar número de participação
        $lastParticipant = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
            ->orderBy('participation_number', 'desc')
            ->first();

        $participationNumber = $lastParticipant ? $lastParticipant->participation_number + 1 : 1;

        // Criar participante
        $participant = ConsortiumParticipant::create([
            'consortium_id' => $this->consortium->id,
            'client_id' => $this->client_id,
            'participation_number' => $participationNumber,
            'entry_date' => $this->entry_date,
            'status' => 'active',
            'total_paid' => 0,
            'is_contemplated' => false,
            'notes' => $this->notes,
        ]);

        // Gerar pagamentos para todas as parcelas
        $this->generatePayments($participant);

        session()->flash('success', 'Participante adicionado com sucesso! Parcelas geradas automaticamente.');
        $this->closeModal();

        // Redirecionar para recarregar a página
        return redirect()->route('consortiums.show', $this->consortium);
    }

    /**
     * Gera os pagamentos mensais para o participante
     */
    private function generatePayments(ConsortiumParticipant $participant)
    {
        $startDate = \Carbon\Carbon::parse($this->consortium->start_date);

        for ($i = 1; $i <= $this->consortium->duration_months; $i++) {
            $dueDate = $startDate->copy()->addMonths($i - 1);

            \App\Models\ConsortiumPayment::create([
                'consortium_participant_id' => $participant->id,
                'reference_month' => $dueDate->month,
                'reference_year' => $dueDate->year,
                'amount' => $this->consortium->monthly_value,
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 'pending',
            ]);
        }
    }

    public function render()
    {
        $clients = Client::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('livewire.consortiums.add-participant', [
            'clients' => $clients,
        ]);
    }
}
