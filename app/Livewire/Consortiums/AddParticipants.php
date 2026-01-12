<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Consortium;
use App\Models\Client;
use App\Models\ConsortiumParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class AddParticipants extends Component
{
    use WithPagination;

    public Consortium $consortium;
    public $search = '';
    public $selectedClients = [];
    public $entry_date = '';
    public $notes = [];
    public $filterStatus = 'all';

    protected $queryString = ['search' => ['except' => '']];

    public function mount(Consortium $consortium)
    {
        $this->consortium = $consortium;
        $this->entry_date = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleClient($clientId)
    {
        if (in_array($clientId, $this->selectedClients)) {
            // Remover cliente da seleção
            $this->selectedClients = array_diff($this->selectedClients, [$clientId]);
            unset($this->notes[$clientId]);
        } else {
            // Adicionar cliente à seleção
            $this->selectedClients[] = $clientId;
            $this->notes[$clientId] = '';
        }
    }

    public function removeClient($clientId)
    {
        $this->selectedClients = array_diff($this->selectedClients, [$clientId]);
        unset($this->notes[$clientId]);
    }

    public function updateNote($clientId, $note)
    {
        $this->notes[$clientId] = $note;
    }

    public function save()
    {
        if (empty($this->selectedClients)) {
            session()->flash('error', 'Selecione pelo menos um cliente para adicionar.');
            return;
        }

        $this->validate([
            'entry_date' => 'required|date',
        ]);

        // Validar se pode adicionar participantes
        $totalToAdd = count($this->selectedClients);
        $currentParticipants = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
            ->where('status', '!=', 'quit')
            ->count();

        if (($currentParticipants + $totalToAdd) > $this->consortium->max_participants) {
            session()->flash('error', 'Não é possível adicionar todos os participantes. O consórcio já atingiu ou ultrapassaria o número máximo de participantes.');
            return;
        }

        // Adicionar todos os participantes da lista
        $addedCount = 0;
        foreach ($this->selectedClients as $clientId) {
            // Verificar se o cliente já participa deste consórcio
            $exists = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
                ->where('client_id', $clientId)
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
                'client_id' => $clientId,
                'participation_number' => $participationNumber,
                'entry_date' => $this->entry_date,
                'status' => 'active',
                'total_paid' => 0,
                'is_contemplated' => false,
                'notes' => $this->notes[$clientId] ?? '',
            ]);

            // Gerar pagamentos para todas as parcelas
            $this->generatePayments($participant);
            $addedCount++;
        }

        $message = $addedCount === 1
            ? 'Participante adicionado com sucesso! Parcelas geradas automaticamente.'
            : "{$addedCount} participantes adicionados com sucesso! Parcelas geradas automaticamente.";

        session()->flash('success', $message);

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
        // Obter IDs de clientes que já participam
        $existingParticipantIds = ConsortiumParticipant::where('consortium_id', $this->consortium->id)
            ->where('status', '!=', 'quit')
            ->pluck('client_id')
            ->toArray();

        // Buscar clientes disponíveis
        $availableClients = Client::where('user_id', Auth::id())
            ->whereNotIn('id', $existingParticipantIds)
            ->when($this->search, fn($q) => $q->where(function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->orderBy('name')
            ->paginate(15);

        // Obter detalhes dos clientes selecionados
        $selectedClientsData = Client::whereIn('id', $this->selectedClients)
            ->orderBy('name')
            ->get();

        return view('livewire.consortiums.add-participants', [
            'availableClients' => $availableClients,
            'selectedClientsData' => $selectedClientsData,
        ]);
    }
}
