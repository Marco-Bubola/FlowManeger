<?php

namespace Tests\Unit;

use App\Services\Invoices\InstallmentForecast;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class InstallmentForecastTest extends TestCase
{
    private function item(string $date, string $desc, float $value, string $installments): object
    {
        return (object) ['invoice_date' => $date, 'description' => $desc, 'value' => $value, 'installments' => $installments];
    }

    public function test_le_formatos_de_parcela(): void
    {
        $this->assertSame([4, 6], InstallmentForecast::parse('4 de 6'));
        $this->assertSame([2, 10], InstallmentForecast::parse('2/10'));
        $this->assertNull(InstallmentForecast::parse('Compra à vista'));
        $this->assertNull(InstallmentForecast::parse('-'));
        $this->assertNull(InstallmentForecast::parse('1 de 1'));
    }

    public function test_projeta_parcelas_restantes_a_partir_da_maior_lancada(): void
    {
        $invoices = collect([
            $this->item('2026-08-10', 'Loja X', 100, '1 de 4'),
            $this->item('2026-08-10', 'Loja X', 100, '2 de 4'),
        ]);

        // Parcela 2 caiu em setembro; faltam 3 (outubro) e 4 (novembro)
        $result = InstallmentForecast::forecast($invoices, Carbon::parse('2026-09-15'));

        $this->assertSame(['2026-10', '2026-11'], array_keys($result));
        $this->assertSame(100.0, $result['2026-10']['total']);
        $this->assertSame('4 de 4', $result['2026-11']['items'][0]['installment']);
    }

    public function test_soma_compras_diferentes_no_mesmo_mes(): void
    {
        $invoices = collect([
            $this->item('2026-09-01', 'A', 50, '1 de 3'),
            $this->item('2026-09-20', 'B', 30.5, '1 de 2'),
        ]);

        $result = InstallmentForecast::forecast($invoices, Carbon::parse('2026-09-30'));

        $this->assertSame(80.5, $result['2026-10']['total']);
        $this->assertSame(50.0, $result['2026-11']['total']);
    }

    public function test_respeita_o_limite_de_meses(): void
    {
        $invoices = collect([$this->item('2026-01-05', 'Longa', 10, '1 de 12')]);

        $result = InstallmentForecast::forecast($invoices, Carbon::parse('2026-01-05'), 3);

        $this->assertSame(['2026-02', '2026-03', '2026-04'], array_keys($result));
    }
}
