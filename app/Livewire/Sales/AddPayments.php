<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
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
        // Normalizar valores que podem vir com formatação BR (ex: "1.234,56")
        foreach ($this->payments as $i => $p) {
            if (isset($p['amount_paid'])) {
                $val = (string) $p['amount_paid'];
                // Remover quaisquer caracteres exceto dígitos, ponto, vírgula e sinal negativo
                $val = preg_replace('/[^0-9,\.\-]/', '', $val);

                // Se houver vírgula e ponto, considerar pontos como separador de milhares
                if (strpos($val, ',') !== false && strpos($val, '.') !== false) {
                    $val = str_replace('.', '', $val); // remover milhares
                    $val = str_replace(',', '.', $val); // vírgula -> ponto decimal
                } else {
                    // Se houver somente vírgula, converter para ponto
                    if (strpos($val, ',') !== false) {
                        $val = str_replace(',', '.', $val);
                    }
                }

                // Remover zeros à esquerda estranhos e normalizar
                $val = trim($val);
                $this->payments[$i]['amount_paid'] = $val;
            }
        }

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
                    // Se for desconto, iremos registrar como um pagamento com método 'desconto'
                    // e reduzir o total_price da venda em seguida.
                    $created = SalePayment::create([
                        'sale_id' => $this->sale->id,
                        'amount_paid' => $paymentData['amount_paid'],
                        'payment_method' => $paymentData['payment_method'],
                        'payment_date' => $paymentData['payment_date'],
                    ]);

                    if (isset($paymentData['payment_method']) && $paymentData['payment_method'] === 'desconto') {
                        // Aplicar desconto: reduzir total_price, sem deixar negativo
                        $discount = floatval($paymentData['amount_paid']);
                        $newTotal = max(0, $this->sale->total_price - $discount);
                        $this->sale->total_price = $newTotal;
                        $this->sale->save();
                    }
                }

                // Atualizar total pago (exclui 'desconto' que reduz o preço, não conta como pagamento)
                $totalPaid = SalePayment::where('sale_id', $this->sale->id)
                    ->where('payment_method', '<>', 'desconto')
                    ->sum('amount_paid');

                // Atualizar status e valor pago
                $updateData = [
                    'amount_paid' => $totalPaid,
                    'status' => ($totalPaid >= $this->sale->total_price) ? 'pago' : 'pendente'
                ];

                $this->sale->update($updateData);
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
