<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\ConsortiumPayment;
use Illuminate\Support\Facades\DB;

class CancelPayment extends Component
{
    public ConsortiumPayment $payment;
    public $showModal = false;

    public function mount(ConsortiumPayment $payment)
    {
        $this->payment = $payment;
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function cancelPayment()
    {
        // Verificar se ainda pode ser cancelado
        if ($this->payment->status !== 'paid') {
            session()->flash('error', 'Este pagamento não está marcado como pago.');
            return;
        }

        DB::transaction(function () {
            $participant = $this->payment->participant;

            // Reverter o pagamento para pendente
            $this->payment->update([
                'status' => 'pending',
                'payment_date' => null,
                'payment_method' => null,
            ]);

            // Recalcular total pago do participante
            $participant->update([
                'total_paid' => $participant->payments()->where('status', 'paid')->sum('amount')
            ]);

            // Se o participante foi contemplado por quitação (payoff), reverter a contemplação
            if ($participant->is_contemplated && $participant->contemplation_type === 'payoff') {
                $consortium = $participant->consortium;

                // Verificar se ainda há pagamentos pendentes
                $pendingPayments = $participant->payments()->where('status', '!=', 'paid')->count();

                if ($pendingPayments > 0) {
                    // Reverter contemplação
                    $participant->update([
                        'is_contemplated' => false,
                        'status' => 'active',
                        'contemplation_date' => null,
                        'contemplation_type' => null,
                    ]);

                    // Deletar o registro de contemplação se existir
                    if ($participant->contemplation) {
                        $participant->contemplation->delete();
                    }
                }
            }
        });

        session()->flash('success', 'Pagamento cancelado e revertido para pendente com sucesso!');
        $this->closeModal();
        $this->dispatch('payment-cancelled');
    }

    public function render()
    {
        return view('livewire.consortiums.cancel-payment');
    }
}
