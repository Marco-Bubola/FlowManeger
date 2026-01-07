<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\ConsortiumPayment;
use Livewire\Attributes\Validate;

class RecordPayment extends Component
{
    public ConsortiumPayment $payment;
    public $showModal = false;

    #[Validate('required|date')]
    public $payment_date = '';

    #[Validate('required|string|max:50')]
    public $payment_method = '';

    public $notes = '';

    public function mount(ConsortiumPayment $payment)
    {
        $this->payment = $payment;
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_method = 'dinheiro';
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->payment_date = now()->format('Y-m-d');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['payment_date', 'payment_method', 'notes']);
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_method = 'dinheiro';
        $this->resetValidation();
    }

    public function recordPayment()
    {
        // Verificar se já foi pago
        if ($this->payment->status === 'paid') {
            session()->flash('error', 'Este pagamento já foi registrado.');
            return;
        }

        $this->validate();

        // Atualizar pagamento
        $this->payment->update([
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'status' => 'paid',
        ]);

        // Atualizar total pago do participante
        $participant = $this->payment->participant;
        $participant->update([
            'total_paid' => $participant->payments()->where('status', 'paid')->sum('amount')
        ]);

        session()->flash('success', 'Pagamento registrado com sucesso!');
        $this->closeModal();

        // Refresh da página
        $this->dispatch('payment-recorded');
    }

    public function render()
    {
        return view('livewire.consortiums.record-payment');
    }
}
