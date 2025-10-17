<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditPayments extends Component
{
    public $sale;
    public $payments = [];

    public function mount($saleId)
    {
        $this->sale = Sale::where('id', $saleId)
            ->where('user_id', Auth::id())
            ->with(['client', 'payments'])
            ->firstOrFail();

        $this->loadPayments();
    }

    public function loadPayments()
    {
        $this->payments = [];

        foreach ($this->sale->payments as $payment) {
            $this->payments[] = [
                'id' => $payment->id,
                'amount_paid' => $payment->amount_paid,
                'payment_method' => $payment->payment_method,
                'payment_date' => $payment->payment_date,

                'created_at' => $payment->created_at->format('d/m/Y H:i'),
            ];
        }
    }

    public function updatePayments()
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
                // Carregar pagamentos antigos para detectar mudanças (especialmente descontos)
                $existingPayments = SalePayment::where('sale_id', $this->sale->id)->get()->keyBy('id');

                foreach ($this->payments as $paymentData) {
                    $id = $paymentData['id'];
                    $old = $existingPayments->get($id);

                    SalePayment::where('id', $id)->update([
                        'amount_paid' => $paymentData['amount_paid'],
                        'payment_method' => $paymentData['payment_method'],
                        'payment_date' => $paymentData['payment_date'],
                    ]);

                    // Se o método anterior era desconto, e foi alterado/valor alterado, ajustar total_price
                    if ($old) {
                        $oldMethod = $old->payment_method;
                        $oldAmount = floatval($old->amount_paid);
                        $newMethod = $paymentData['payment_method'];
                        $newAmount = floatval($paymentData['amount_paid']);

                        if ($oldMethod === 'desconto' && $newMethod === 'desconto') {
                            // Ajustar diferença
                            $diff = $newAmount - $oldAmount;
                            $this->sale->total_price = max(0, $this->sale->total_price - $diff);
                            $this->sale->save();
                        } elseif ($oldMethod === 'desconto' && $newMethod !== 'desconto') {
                            // Removemos um desconto antigo: aumentar total_price
                            $this->sale->total_price = $this->sale->total_price + $oldAmount;
                            $this->sale->save();
                        } elseif ($oldMethod !== 'desconto' && $newMethod === 'desconto') {
                            // Novo desconto aplicado: reduzir total_price
                            $this->sale->total_price = max(0, $this->sale->total_price - $newAmount);
                            $this->sale->save();
                        }
                    }
                }

                // Atualizar total pago (exclui 'desconto')
                $totalPaid = SalePayment::where('sale_id', $this->sale->id)
                    ->where('payment_method', '<>', 'desconto')
                    ->sum('amount_paid');
                $this->sale->amount_paid = $totalPaid;

                // Atualizar status se necessário
                if ($totalPaid >= $this->sale->total_price) {
                    $this->sale->status = 'pago';
                } else {
                    $this->sale->status = 'pendente';
                }
                $this->sale->save();
            });

            $this->dispatch('success', 'Pagamentos atualizados com sucesso!');
            return redirect()->route('sales.show', $this->sale->id);

        } catch (\Exception $e) {
            $this->dispatch('error', 'Erro ao atualizar pagamentos: ' . $e->getMessage());
        }
    }

    public function removePayment($index)
    {
        if (count($this->payments) <= 1) {
            $this->dispatch('error', 'Não é possível remover o último pagamento da venda.');
            return;
        }

        $paymentId = $this->payments[$index]['id'];

        try {
            DB::transaction(function () use ($paymentId, $index) {
                $payment = SalePayment::where('id', $paymentId)->first();
                if ($payment) {
                    // Se for desconto, restaurar o total_price
                    if ($payment->payment_method === 'desconto') {
                        $this->sale->total_price = $this->sale->total_price + floatval($payment->amount_paid);
                        $this->sale->save();
                    }

                    SalePayment::where('id', $paymentId)->delete();
                }

                unset($this->payments[$index]);
                $this->payments = array_values($this->payments);

                // Atualizar total pago (exclui 'desconto')
                $totalPaid = SalePayment::where('sale_id', $this->sale->id)
                    ->where('payment_method', '<>', 'desconto')
                    ->sum('amount_paid');
                $this->sale->amount_paid = $totalPaid;

                // Atualizar status se necessário
                if ($totalPaid >= $this->sale->total_price) {
                    $this->sale->status = 'pago';
                } else {
                    $this->sale->status = 'pendente';
                }
                $this->sale->save();
            });

            $this->dispatch('success', 'Pagamento removido com sucesso!');

        } catch (\Exception $e) {
            $this->dispatch('error', 'Erro ao remover pagamento: ' . $e->getMessage());
        }
    }

    public function getTotalPaymentsProperty()
    {
        return collect($this->payments)->sum('amount_paid');
    }

    public function getRemainingAmountProperty()
    {
        return $this->sale->total_price - $this->totalPayments;
    }

    public function render()
    {
        return view('livewire.sales.edit-payments');
    }
}
