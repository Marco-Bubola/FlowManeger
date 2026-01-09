<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Consortium;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class DeleteConsortium extends Component
{
    public $consortiumId;
    public $showToggleModal = false;
    public $showDeleteModal = false;

    protected $listeners = [
        'openToggleConsortiumModal' => 'confirmToggle',
        'openDeleteConsortiumModal' => 'confirmDelete'
    ];

    public function mount(Consortium $consortium)
    {
        $this->consortiumId = $consortium->id;
    }

    public function confirmToggle()
    {
        $this->showToggleModal = true;
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    #[Computed]
    public function consortium()
    {
        return Consortium::findOrFail($this->consortiumId);
    }

    public function toggleStatus()
    {
        $consortium = Consortium::find($this->consortiumId);

        if ($consortium->status === 'active') {
            $consortium->update(['status' => 'cancelled']);
            session()->flash('success', 'Consórcio desativado com sucesso.');
        } else {
            $consortium->update(['status' => 'active']);
            session()->flash('success', 'Consórcio ativado com sucesso.');
        }

        $this->showToggleModal = false;
        $this->dispatch('$refresh');
    }

    public function deleteConsortium()
    {
        $consortium = Consortium::find($this->consortiumId);

        // Verificar se pode excluir
        $hasParticipants = $consortium->participants()->count() > 0;
        $hasDraws = $consortium->draws()->count() > 0;

        if ($hasParticipants || $hasDraws) {
            session()->flash('error', 'Não é possível excluir consórcio com participantes ou sorteios. Desative o consórcio ao invés disso.');
            $this->showDeleteModal = false;
            return;
        }

        try {
            DB::beginTransaction();

            // Excluir em cascata (embora não deveria haver nada)
            $consortium->participants()->delete();
            $consortium->draws()->delete();
            $consortium->delete();

            DB::commit();

            session()->flash('success', 'Consórcio excluído com sucesso.');
            $this->showDeleteModal = false;
            return redirect()->route('consortiums.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao excluir consórcio: ' . $e->getMessage());
            $this->showDeleteModal = false;
        }
    }

    public function render()
    {
        return view('livewire.consortiums.delete-consortium');
    }
}
