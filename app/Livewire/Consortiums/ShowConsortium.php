<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Consortium;
use Livewire\Attributes\Computed;

class ShowConsortium extends Component
{
    public $consortiumId;
    public $activeTab = 'overview';
    public $showToggleParticipantModal = false;
    public $showDeleteParticipantModal = false;
    public $selectedParticipantId = null;
    public $showExportModal = false;

    protected $listeners = [
        'payment-recorded' => '$refresh',
        'payment-cancelled' => '$refresh',
        'contemplation-updated' => '$refresh',
    ];

    public function mount(Consortium $consortium)
    {
        $this->consortiumId = $consortium->id;
    }

    /**
     * Limpa string para UTF-8 válido
     */
    private function cleanUtf8($string)
    {
        if ($string === null) return null;

        // Remove caracteres não UTF-8
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        // Remove caracteres de controle
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);

        // Remove BOM e outros caracteres problemáticos
        $string = preg_replace('/[\x{FEFF}\x{FFFE}]/u', '', $string);

        return $string;
    }

    #[Computed(persist: false)]
    public function consortium()
    {
        $consortium = Consortium::findOrFail($this->consortiumId);

        // Limpar campos de texto
        if ($consortium->name) {
            $consortium->name = $this->cleanUtf8($consortium->name);
        }
        if ($consortium->description) {
            $consortium->description = $this->cleanUtf8($consortium->description);
        }

        // Não serializar relacionamentos
        $consortium->setRelations([]);

        return $consortium;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function confirmToggleParticipant($participantId)
    {
        $this->selectedParticipantId = $participantId;
        $this->showToggleParticipantModal = true;
    }

    public function confirmDeleteParticipant($participantId)
    {
        $this->selectedParticipantId = $participantId;
        $this->showDeleteParticipantModal = true;
    }

    public function removeParticipant($participantId)
    {
        $participant = \App\Models\ConsortiumParticipant::find($participantId);

        if (!$participant) {
            session()->flash('error', 'Participante não encontrado.');
            $this->showDeleteParticipantModal = false;
            $this->selectedParticipantId = null;
            return;
        }

        // Verificar se pode remover (não contemplado, sem muitos pagamentos)
        if ($participant->is_contemplated) {
            session()->flash('error', 'Não é possível remover participante contemplado.');
            $this->showDeleteParticipantModal = false;
            $this->selectedParticipantId = null;
            return;
        }

        $paidPayments = $participant->payments()->where('status', 'paid')->count();
        if ($paidPayments > 0) {
            // Apenas marca como desistente
            $participant->update(['status' => 'quit']);
            session()->flash('success', 'Participante marcado como desistente.');
        } else {
            // Pode deletar completamente
            $participant->payments()->delete();
            $participant->delete();
            session()->flash('success', 'Participante removido com sucesso.');
        }

        $this->showDeleteParticipantModal = false;
        $this->selectedParticipantId = null;
        $this->dispatch('$refresh');
    }

    public function toggleParticipantStatus($participantId = null)
    {
        $id = $participantId ?? $this->selectedParticipantId;
        $participant = \App\Models\ConsortiumParticipant::find($id);

        if (!$participant) {
            session()->flash('error', 'Participante não encontrado.');
            return;
        }

        if ($participant->is_contemplated) {
            session()->flash('error', 'Não é possível alterar status de participante contemplado.');
            return;
        }

        if ($participant->status === 'active') {
            $participant->update(['status' => 'quit']);
            session()->flash('success', 'Participante desativado.');
        } elseif ($participant->status === 'quit') {
            $participant->update(['status' => 'active']);
            session()->flash('success', 'Participante reativado.');
        }

        $this->showToggleParticipantModal = false;
        $this->selectedParticipantId = null;
        $this->dispatch('$refresh');
    }

    public function deactivateConsortium()
    {
        $consortium = Consortium::find($this->consortiumId);
        $consortium->update(['status' => 'cancelled']);
        session()->flash('success', 'Consórcio desativado com sucesso.');
        return redirect()->route('consortiums.index');
    }


    #[Computed(persist: false)]
    public function participants()
    {
        if ($this->activeTab !== 'participants') {
            return collect();
        }

        $participants = Consortium::find($this->consortiumId)
            ->participants()
            ->with('client', 'payments', 'contemplation')
            ->orderBy('participation_number')
            ->get();

        // Limpar dados UTF-8 de cada participante
        return $participants->map(function ($participant) {
            if ($participant->notes) {
                $participant->notes = $this->cleanUtf8($participant->notes);
            }

            // Limpar dados do cliente
            if ($participant->client) {
                $participant->client->name = $this->cleanUtf8($participant->client->name ?? '');
                $participant->client->email = $this->cleanUtf8($participant->client->email ?? '');
                $participant->client->phone = $this->cleanUtf8($participant->client->phone ?? '');
                $participant->client->address = $this->cleanUtf8($participant->client->address ?? '');
            }

            return $participant;
        });
    }

    #[Computed(persist: false)]
    public function payments()
    {
        if ($this->activeTab !== 'payments') {
            return collect();
        }

        $participants = Consortium::find($this->consortiumId)
            ->participants()
            ->with([
                'payments' => function ($query) {
                    $query->orderBy('due_date');
                },
                'client'
            ])
            ->orderBy('participation_number')
            ->get();

        // Retorna participantes apenas se houver parcelas para exibir, já agrupadas por cliente
        return $participants->map(function ($participant) {
            if ($participant->client) {
                $participant->client->name = $this->cleanUtf8($participant->client->name ?? '');
                $participant->client->email = $this->cleanUtf8($participant->client->email ?? '');
            }

            return $participant;
        })->filter(fn ($participant) => $participant->payments->isNotEmpty());
    }

    #[Computed(persist: false)]
    public function draws()
    {
        if ($this->activeTab !== 'draws') {
            return collect();
        }

        $draws = Consortium::find($this->consortiumId)
            ->draws()
            ->with('winner.client')
            ->orderByDesc('draw_date')
            ->get();

        return $draws->map(function ($draw) {
            if ($draw->winner && $draw->winner->client) {
                $draw->winner->client->name = $this->cleanUtf8($draw->winner->client->name ?? '');
            }
            return $draw;
        });
    }

    #[Computed(persist: false)]
    public function contemplated()
    {
        if ($this->activeTab !== 'contemplated') {
            return collect();
        }

        $contemplated = Consortium::find($this->consortiumId)
            ->participants()
            ->where('is_contemplated', true)
            ->with('client', 'contemplation')
            ->get();

        return $contemplated->map(function ($participant) {
            if ($participant->client) {
                $participant->client->name = $this->cleanUtf8($participant->client->name ?? '');
                $participant->client->email = $this->cleanUtf8($participant->client->email ?? '');
            }
            return $participant;
        });
    }

    public function render()
    {
        return view('livewire.consortiums.show-consortium');
    }
}
