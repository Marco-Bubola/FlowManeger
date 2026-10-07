<?php

namespace Tests\Unit;

use App\Models\Bank;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class BankDueDateTest extends TestCase
{
    public function test_vence_no_mes_seguinte_quando_dia_e_antes_do_fechamento(): void
    {
        $bank = new Bank(['due_day' => 5]);

        $this->assertSame('2026-11-05', $bank->dueDateFor(Carbon::parse('2026-10-28'))->toDateString());
    }

    public function test_vence_no_mesmo_mes_quando_dia_e_depois_do_fechamento(): void
    {
        $bank = new Bank(['due_day' => 15]);

        $this->assertSame('2026-10-15', $bank->dueDateFor(Carbon::parse('2026-10-05'))->toDateString());
    }

    public function test_ajusta_para_o_ultimo_dia_do_mes(): void
    {
        $bank = new Bank(['due_day' => 31]);

        $this->assertSame('2027-02-28', $bank->dueDateFor(Carbon::parse('2027-01-31'))->toDateString());
    }

    public function test_sem_dia_de_vencimento_retorna_nulo(): void
    {
        $this->assertNull((new Bank())->dueDateFor(Carbon::parse('2026-10-05')));
    }
}
