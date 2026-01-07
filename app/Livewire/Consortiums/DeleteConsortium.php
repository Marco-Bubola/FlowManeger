<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Consortium;

class DeleteConsortium extends Component
{
    public Consortium $consortium;
    public $showModal = false;

    public function mount(Consortium $consortium)
    {
        $this->consortium = $consortium;
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function deleteConsortium()
    {
        // Verificar se pode excluir
        $hasParticipants = $this->consortium->participants()->count() > 0;
        $hasDraws = $this->consortium->draws()->count() > 0;

        if ($hasParticipants || $hasDraws) {
            session()->flash('error', 'Não é possível excluir consórcio com participantes ou sorteios. Use a opção "Desativar" ao invés disso.');
            $this->closeModal();
            return;
        }

        // Pode excluir
        $this->consortium->delete();
        session()->flash('success', 'Consórcio excluído com sucesso.');

        return redirect()->route('consortiums.index');
    }

    public function deactivate()
    {
        $this->consortium->update(['status' => 'cancelled']);
        session()->flash('success', 'Consórcio desativado com sucesso.');
        $this->closeModal();

        return redirect()->route('consortiums.index');
    }

    public function render()
    {
        return view('livewire.consortiums.delete-consortium');
    }
}
