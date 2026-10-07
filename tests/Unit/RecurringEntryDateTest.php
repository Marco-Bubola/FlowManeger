<?php

namespace Tests\Unit;

use App\Services\Cashbook\RecurringEntryService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class RecurringEntryDateTest extends TestCase
{
    public function test_proxima_data_por_frequencia(): void
    {
        $base = Carbon::parse('2026-01-31');

        $this->assertSame('2026-02-01', RecurringEntryService::nextDate($base, 'diaria')->toDateString());
        $this->assertSame('2026-02-07', RecurringEntryService::nextDate($base, 'semanal')->toDateString());
        $this->assertSame('2026-02-28', RecurringEntryService::nextDate($base, 'mensal')->toDateString());
        $this->assertSame('2027-01-31', RecurringEntryService::nextDate($base, 'anual')->toDateString());
    }

    public function test_mensal_volta_ao_dia_original(): void
    {
        $feb = Carbon::parse('2026-02-28');

        $this->assertSame('2026-03-31', RecurringEntryService::nextDate($feb, 'mensal', 31)->toDateString());
    }
}
