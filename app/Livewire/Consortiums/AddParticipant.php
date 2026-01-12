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

    public $client_id = '';
    public $entry_date = '';
    public $notes = '';
    public $search = '';

    // Array para armazenar múltiplos participantes
    public $participants = [];

    protected $listeners = ['openAddParticipantModal' => 'openModal'];

    public function mount(Consortium $consortium)
    {
        $this->consortium = $consortium;
        $this->entry_date = now()->format('Y-m-d');
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->entry_date = now()->format('Y-m-d');
        $this->participants = [];
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['client_id', 'notes', 'participants']);
        $this->entry_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function addToList()
    {
        // Validar campos antes de adicionar à lista
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'entry_date' => 'required|date',
        ]);

        // Verificar se o cliente já está na lista
        $alreadyInList = collect($this->participants)->contains('client_id', $this->client_id);
        if ($alreadyInList) {
            $this->addError('client_id', 'Este cliente já está na lista.');
            return;
        }

        // Verificar se o cliente já participa deste consórcio
        $exists = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
            ->where('client_id', $this->client_id)
            ->where('status', '!=', 'quit')
            ->exists();

        if ($exists) {
            $this->addError('client_id', 'Este cliente já participa deste consórcio.');
            return;
        }

        // Obter nome do cliente
        $client = Client::find($this->client_id);

        // Adicionar à lista
        $this->participants[] = [
            'client_id' => $this->client_id,
            'client_name' => $client->name,
            'entry_date' => $this->entry_date,
            'notes' => $this->notes,
        ];

        // Limpar campos
        $this->reset(['client_id', 'notes']);
        $this->entry_date = now()->format('Y-m-d');
        $this->resetValidation();

        session()->flash('success_temp', 'Participante adicionado à lista!');
    }

    public function removeFromList($index)
    {
        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);
    }

    public function addParticipant()
    {
        // Se não há participantes na lista, adicionar o atual
        if (empty($this->participants)) {
            $this->validate([
                'client_id' => 'required|exists:clients,id',
                'entry_date' => 'required|date',
            ]);

            $client = Client::find($this->client_id);
            $this->participants[] = [
                'client_id' => $this->client_id,
                'client_name' => $client->name,
                'entry_date' => $this->entry_date,
                'notes' => $this->notes,
            ];
        }

        // Validar se pode adicionar participantes
        $totalToAdd = count($this->participants);
        $currentParticipants = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
            ->where('status', '!=', 'quit')
            ->count();

        if (($currentParticipants + $totalToAdd) > $this->consortium->total_participants) {
            session()->flash('error', 'Não é possível adicionar todos os participantes. O consórcio já atingiu ou ultrapassaria o número máximo de participantes.');
            return;
        }

        // Adicionar todos os participantes da lista
        $addedCount = 0;
        foreach ($this->participants as $participantData) {
            // Verificar se o cliente já participa deste consórcio (verificação adicional)
            $exists = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
                ->where('client_id', $participantData['client_id'])
                ->where('status', '!=', 'quit')
                ->exists();

            if ($exists) {
                continue;
            }

            // Gerar número de participação
            $lastParticipant = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
                ->orderBy('participation_number', 'desc')
                ->first();

            $participationNumber = $lastParticipant ? $lastParticipant->participation_number + 1 : 1;

            // Criar participante
            $participant = ConsortiumParticipant::create([
                'consortium_id' => $this->consortium->id,
                'client_id' => $participantData['client_id'],
                'participation_number' => $participationNumber,
                'entry_date' => $participantData['entry_date'],
                'status' => 'active',
                'total_paid' => 0,
                'is_contemplated' => false,
                'notes' => $participantData['notes'],
            ]);

            // Gerar pagamentos para todas as parcelas
            $this->generatePayments($participant);
            $addedCount++;
        }

        $message = $addedCount === 1
            ? 'Participante adicionado com sucesso! Parcelas geradas automaticamente.'
            : "{$addedCount} participantes adicionados com sucesso! Parcelas geradas automaticamente.";

        session()->flash('success', $message);
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
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('livewire.consortiums.add-participant', [
            'clients' => $clients,
        ]);
    }
}
