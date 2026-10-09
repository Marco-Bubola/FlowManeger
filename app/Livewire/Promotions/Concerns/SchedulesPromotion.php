<?php

namespace App\Livewire\Promotions\Concerns;

use App\Models\Promotion;
use Carbon\Carbon;

/**
 * Campos "Começa: agora / em uma data" (data + hora) e "Termina" usados na
 * Nova promoção e no modal de edição. Tudo digitado no fuso de São Paulo
 * (config app.schedule_timezone); o PromotionService converte para UTC.
 */
trait SchedulesPromotion
{
    public string $startMode = 'now'; // now | date
    public string $startDate = '';    // Y-m-d (São Paulo)
    public string $startTime = '08:00';
    public string $endsAt = '';       // Y-m-d (São Paulo), vale até 23:59 do dia

    /** Atalhos: chave => rótulo. */
    public function schedulePresets(): array
    {
        return [
            'weekend'  => 'Este fim de semana (sex–dom)',
            'tomorrow' => 'Amanhã',
            'nextweek' => 'Próxima semana',
        ];
    }

    public function applyPreset(string $key): void
    {
        $now = now(Promotion::tz());
        [$start, $end] = match ($key) {
            // Sexta 08h até domingo; já no fim de semana, começa agora (ou às 08h da sexta).
            'weekend'  => $this->weekendRange($now),
            'tomorrow' => [$now->copy()->addDay()->setTime(8, 0), $now->copy()->addDay()],
            'nextweek' => [$now->copy()->next(Carbon::MONDAY)->setTime(8, 0), $now->copy()->next(Carbon::MONDAY)->addDays(6)],
            default    => [false, null],
        };
        if ($start === false) {
            return;
        }

        if ($start === null || $start->lte($now)) {
            $this->startMode = 'now';
            $this->startDate = '';
        } else {
            $this->startMode = 'date';
            $this->startDate = $start->format('Y-m-d');
            $this->startTime = $start->format('H:i');
        }
        $this->endsAt = $end->format('Y-m-d');
        $this->resetErrorBag('dates');
    }

    /** @return array{0: ?Carbon, 1: Carbon} */
    private function weekendRange(Carbon $now): array
    {
        return match ($now->dayOfWeek) {
            Carbon::FRIDAY   => [$now->copy()->setTime(8, 0), $now->copy()->addDays(2)],
            Carbon::SATURDAY => [null, $now->copy()->addDay()],
            Carbon::SUNDAY   => [null, $now->copy()],
            default          => [$now->copy()->next(Carbon::FRIDAY)->setTime(8, 0), $now->copy()->next(Carbon::FRIDAY)->addDays(2)],
        };
    }

    public function setStartMode(string $mode): void
    {
        $this->startMode = $mode === 'date' ? 'date' : 'now';
        if ($this->startMode === 'date' && $this->startDate === '') {
            $this->startDate = now(Promotion::tz())->addDay()->format('Y-m-d');
        }
    }

    /** Valor para o PromotionService: null = agora, ou "Y-m-d H:i" em São Paulo. */
    protected function startsAtValue(): ?string
    {
        if ($this->startMode !== 'date' || $this->startDate === '') {
            return null;
        }
        $time = preg_match('/^\d{2}:\d{2}$/', $this->startTime) ? $this->startTime : '00:00';

        return $this->startDate . ' ' . $time;
    }

    protected function fillScheduleFrom(?Promotion $promo): void
    {
        $start = $promo?->startsLocal();
        if ($start && $promo->starts_at->isFuture()) {
            $this->startMode = 'date';
            $this->startDate = $start->format('Y-m-d');
            $this->startTime = $start->format('H:i');
        } else {
            $this->startMode = 'now';
            $this->startDate = '';
            $this->startTime = '08:00';
        }
        $this->endsAt = $promo?->endsLocal()?->format('Y-m-d') ?? '';
    }

    /** Resumo para mostrar embaixo dos campos: "Começa sex 10/10 às 08h e vai até dom 12/10". */
    public function getScheduleSummaryProperty(): string
    {
        $start = null;
        if ($value = $this->startsAtValue()) {
            try {
                $start = Carbon::parse($value, Promotion::tz());
            } catch (\Throwable) {
                $start = null;
            }
        }
        $end = null;
        if ($this->endsAt !== '') {
            try {
                $end = Carbon::parse($this->endsAt, Promotion::tz());
            } catch (\Throwable) {
                $end = null;
            }
        }

        $text = $start && $start->isFuture() ? 'Começa ' . Promotion::humanDateTime($start) : 'Começa agora';
        $text .= $end ? ' e vai até ' . Promotion::humanDateTime($end, false) . ' (fim do dia)' : ' e vai até acabar o estoque';

        return $text;
    }
}
