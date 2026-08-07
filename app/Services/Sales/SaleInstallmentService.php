<?php

namespace App\Services\Sales;

use App\Models\Sale;
use App\Models\VendaParcela;
use Carbon\Carbon;

/**
 * Regra única de parcelamento de vendas.
 *
 * Antes existiam quatro implementações de `recalcularParcelas` (EditSale,
 * EditPrices, AddProducts e a geração no CreateSale), com regras diferentes:
 *
 *   · EditSale e AddProducts dividiam `total / nº de parcelas`, ignorando o que
 *     já havia sido pago — reeditar uma venda com parcela paga desbalanceava tudo;
 *   · todas usavam `round(total / n, 2)` no valor de TODAS as parcelas, então
 *     a soma não fechava com o total (100,00 em 3x virava 33,33 × 3 = 99,99).
 *
 * Aqui a regra é uma só:
 *   1. parcelas já pagas são intocáveis;
 *   2. o que resta (total − pago) é dividido entre as pendentes;
 *   3. o resíduo de centavos entra na última parcela pendente, de modo que a
 *      soma feche exatamente com o total da venda.
 */
class SaleInstallmentService
{
    /**
     * Sincroniza as parcelas da venda com o total atual.
     *
     * @return array{criadas: int, atualizadas: int, removidas: int, soma: float}
     */
    public function sync(Sale $sale): array
    {
        $numeroParcelas = (int) $sale->parcelas;

        // Venda à vista (ou parcelas inválidas): não deve ter parcelas.
        if ($sale->tipo_pagamento !== 'parcelado' || $numeroParcelas <= 1) {
            $removidas = VendaParcela::where('sale_id', $sale->id)->count();
            VendaParcela::where('sale_id', $sale->id)->delete();

            return ['criadas' => 0, 'atualizadas' => 0, 'removidas' => $removidas, 'soma' => 0.0];
        }

        $existentes = VendaParcela::where('sale_id', $sale->id)
            ->orderBy('numero_parcela')
            ->get();

        // Quantidade mudou (ou ainda não existem): recria do zero, preservando
        // as parcelas já pagas para não apagar histórico de recebimento.
        if ($existentes->count() !== $numeroParcelas) {
            return $this->recriar($sale, $existentes, $numeroParcelas);
        }

        return $this->redistribuir($sale, $existentes);
    }

    /**
     * Recria a régua de parcelas mantendo as pagas.
     */
    private function recriar(Sale $sale, $existentes, int $numeroParcelas): array
    {
        $pagas = $existentes->where('status', 'paga')->values();

        // Não é possível reduzir para menos parcelas do que já foram pagas.
        $numeroParcelas = max($numeroParcelas, $pagas->count());

        $primeiroVencimento = $this->primeiroVencimento($sale);

        VendaParcela::where('sale_id', $sale->id)
            ->where('status', '!=', 'paga')
            ->delete();

        $criadas = 0;

        for ($i = $pagas->count() + 1; $i <= $numeroParcelas; $i++) {
            VendaParcela::create([
                'sale_id' => $sale->id,
                'numero_parcela' => $i,
                'valor' => 0, // valor definido na redistribuição abaixo
                'data_vencimento' => $primeiroVencimento->copy()->addMonths($i - 1)->format('Y-m-d'),
                'status' => 'pendente',
            ]);
            $criadas++;
        }

        $atualizadas = VendaParcela::where('sale_id', $sale->id)->orderBy('numero_parcela')->get();
        $resultado = $this->redistribuir($sale, $atualizadas);

        return [
            'criadas' => $criadas,
            'atualizadas' => $resultado['atualizadas'],
            'removidas' => 0,
            'soma' => $resultado['soma'],
        ];
    }

    /**
     * Distribui (total − pago) entre as parcelas pendentes, sem perder centavos.
     */
    private function redistribuir(Sale $sale, $parcelas): array
    {
        $pendentes = $parcelas->where('status', '!=', 'paga')->values();

        if ($pendentes->isEmpty()) {
            return [
                'criadas' => 0,
                'atualizadas' => 0,
                'removidas' => 0,
                'soma' => (float) $parcelas->sum('valor'),
            ];
        }

        // Trabalha em centavos: divisão inteira não perde resto.
        $totalCentavos = (int) round((float) $sale->total_price * 100);
        $pagoCentavos = (int) round((float) $parcelas->where('status', 'paga')->sum('valor') * 100);
        $restanteCentavos = max(0, $totalCentavos - $pagoCentavos);

        $qtd = $pendentes->count();
        $base = intdiv($restanteCentavos, $qtd);
        $resto = $restanteCentavos - ($base * $qtd);

        $atualizadas = 0;

        foreach ($pendentes as $i => $parcela) {
            // O resto (sempre < qtd centavos) vai na última parcela pendente,
            // garantindo que a soma feche exatamente com o total.
            $centavos = $base + ($i === $qtd - 1 ? $resto : 0);
            $valor = round($centavos / 100, 2);

            if ((float) $parcela->valor !== $valor) {
                $parcela->update(['valor' => $valor]);
                $atualizadas++;
            }
        }

        $soma = (float) VendaParcela::where('sale_id', $sale->id)->sum('valor');

        return ['criadas' => 0, 'atualizadas' => $atualizadas, 'removidas' => 0, 'soma' => $soma];
    }

    /**
     * A tabela `sales` não tem coluna `sale_date`: a data da venda é o created_at.
     */
    private function primeiroVencimento(Sale $sale): Carbon
    {
        return $sale->created_at ? $sale->created_at->copy() : now();
    }
}
