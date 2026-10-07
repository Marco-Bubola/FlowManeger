<?php

namespace App\Livewire\Cashbook;

use App\Models\LancamentoRecorrente;
use App\Services\Cashbook\RecurringEntryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RecurringIndex extends Component
{
    public function toggle(int $id): void
    {
        $recurring = LancamentoRecorrente::where('user_id', Auth::id())->findOrFail($id);
        $recurring->update(['ativo' => ! $recurring->ativo]);

        $this->dispatch('notify', type: 'success', message: $recurring->ativo ? 'Recorrência retomada.' : 'Recorrência pausada.');
    }

    public function remove(int $id): void
    {
        LancamentoRecorrente::where('user_id', Auth::id())->findOrFail($id)->delete();

        // Os lançamentos já criados no livro-caixa continuam lá
        $this->dispatch('notify', type: 'success', message: 'Recorrência excluída. Os lançamentos já criados foram mantidos.');
    }

    public function render()
    {
        return view('livewire.cashbook.recurring-index', [
            'items' => LancamentoRecorrente::with(['category', 'type'])
                ->where('user_id', Auth::id())
                ->orderByDesc('ativo')
                ->orderBy('proximo_vencimento')
                ->get(),
            'frequencies' => RecurringEntryService::FREQUENCIES,
        ]);
    }
}
