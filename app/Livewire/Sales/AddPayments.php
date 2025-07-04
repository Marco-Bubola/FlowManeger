<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddPayments extends Component
{
    public $sale;
    public $payments = [];

    public function mount($saleId)
    {
        $this->sale = Sale::where('id', $saleId)
            ->where('user_id', Auth::id())
            ->with(['client', 'payments'])
            ->firstOrFail();

        $this->addPaymentRow();
    }

    public function addPaymentRow()
    {
        $this->payments[] = [
            'amount_paid' => '',
            'payment_method' => 'dinheiro',
            'payment_date' => now()->format('Y-m-d'),
        ];
    }

    public function removePaymentRow($index)
    {
        if (count($this->payments) > 1) {
            unset($this->payments[$index]);
            $this->payments = array_values($this->payments);
        }
    }

    public function addPayments()
    {
        $this->validate([
            'payments.*.amount_paid' => 'required|numeric|min:0.01',
            'payments.*.payment_method' => 'required|string',
            'payments.*.payment_date' => 'required|date',
        ], [
            'payments.*.amount_paid.required' => 'O valor do pagamento é obrigatório.',
            'payments.*.amount_paid.numeric' => 'O valor deve ser um número válido.',
            'payments.*.amount_paid.min' => 'O valor deve ser maior que zero.',
            'payments.*.payment_method.required' => 'O método de pagamento é obrigatório.',
            'payments.*.payment_date.required' => 'A data do pagamento é obrigatória.',
            'payments.*.payment_date.date' => 'A data deve ser válida.',
        ]);

        try {
            DB::transaction(function () {
                foreach ($this->payments as $paymentData) {
                    SalePayment::create([
                        'sale_id' => $this->sale->id,
                        'amount_paid' => $paymentData['amount_paid'],
                        'payment_method' => $paymentData['payment_method'],
                        'payment_date' => $paymentData['payment_date'],
                    ]);
                }

                // Atualizar total pago
                $totalPaid = SalePayment::where('sale_id', $this->sale->id)->sum('amount_paid');
                $this->sale->amount_paid = $totalPaid;

                // Atualizar status se necessário
                if ($totalPaid >= $this->sale->total_price) {
                    $this->sale->status = 'pago';
                } else {
                    $this->sale->status = 'pendente';
                }
                $this->sale->save();
            });

            $this->dispatch('success', 'Pagamentos adicionados com sucesso!');
            return redirect()->route('sales.show', $this->sale->id);

        } catch (\Exception $e) {
            $this->dispatch('error', 'Erro ao adicionar pagamentos: ' . $e->getMessage());
        }
    }

    public function getTotalPaymentsProperty()
    {
        return collect($this->payments)->sum(function ($payment) {
            return floatval($payment['amount_paid'] ?? 0);
        });
    }

    public function getRemainingAmountProperty()
    {
        return $this->sale->total_price - $this->sale->amount_paid;
    }

    public function render()
    {
        return view('livewire.sales.add-payments');
    }
}
