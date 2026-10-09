<?php

namespace App\Services;

use App\Models\Consortium;
use App\Models\ConsortiumNotification;
use App\Models\ConsortiumParticipant;
use App\Models\ConsortiumPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsortiumNotificationService
{
    /**
     * Verifica todos os consórcios e cria notificações necessárias
     */
    public function checkAndCreateNotifications(): array
    {
        $stats = [
            'draw_available' => 0,
            'redemption_pending' => 0,
            'installment_overdue' => 0,
            'total' => 0,
        ];

        try {
            DB::beginTransaction();

            // Verificar sorteios disponíveis
            $stats['draw_available'] = $this->checkDrawsAvailable();

            // Verificar resgates pendentes
            $stats['redemption_pending'] = $this->checkRedemptionsPending();

            // Parcelas que venceram sem pagamento (um aviso por parcela)
            $stats['installment_overdue'] = $this->checkOverdueInstallments();

            $stats['total'] = $stats['draw_available'] + $stats['redemption_pending'] + $stats['installment_overdue'];

            DB::commit();

            Log::info('Consortium notifications checked', $stats);

            return $stats;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checking consortium notifications: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica consórcios disponíveis para sorteio
     */
    protected function checkDrawsAvailable(): int
    {
        $count = 0;

        // Buscar consórcios ativos com modo de sorteio
        $consortiums = Consortium::where('status', 'active')
            ->where('mode', 'draw')
            ->with(['draws', 'participants'])
            ->get();

        foreach ($consortiums as $consortium) {
            // Verificar se está pronto para sorteio
            if (!$consortium->canPerformDraw()) {
                continue;
            }

            // Não repete enquanto houver aviso não lido ou um aviso dos últimos 3 dias
            $hasRecentNotification = ConsortiumNotification::where('consortium_id', $consortium->id)
                ->where('type', 'draw_available')
                ->where(fn ($q) => $q->where('is_read', false)->orWhere('created_at', '>=', now()->subDays(3)))
                ->exists();

            if ($hasRecentNotification) {
                continue;
            }

            // Criar notificação (null = usuário desligou avisos de consórcio)
            if (ConsortiumNotification::createDrawAvailable($consortium)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Verifica contemplados com resgate pendente
     */
    protected function checkRedemptionsPending(): int
    {
        $count = 0;

        // Buscar participantes contemplados com resgate pendente
        $participants = ConsortiumParticipant::with(['client', 'consortium', 'contemplation'])
            ->where('is_contemplated', true)
            ->whereHas('contemplation', function ($query) {
                // Resgate ainda pendente (resgatado ou cancelado não avisa)
                $query->whereNotIn('status', ['redeemed', 'cancelled'])
                    ->where('contemplation_date', '<=', now()->subDays(7)); // Pelo menos 7 dias atrás
            })
            ->get();

        foreach ($participants as $participant) {
            if (!$participant->contemplation) {
                continue;
            }

            // Carbon 3 devolve float: usa dias inteiros
            $daysSince = (int) floor($participant->contemplation->contemplation_date->diffInDays(now()));

            // Notificar a cada 7 dias, 15 dias, 30 dias e depois a cada 30 dias
            $shouldNotify = false;

            if (in_array($daysSince, [7, 15, 30], true)) {
                $shouldNotify = true; // 7, 15 e 30 dias
            } elseif ($daysSince > 30 && $daysSince % 30 === 0) {
                $shouldNotify = true; // A cada 30 dias após os 30 primeiros
            }

            if (!$shouldNotify) {
                continue;
            }

            // Verificar se já existe notificação recente (último dia)
            $hasRecentNotification = ConsortiumNotification::where('related_participant_id', $participant->id)
                ->where('type', 'redemption_pending')
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if ($hasRecentNotification) {
                continue;
            }

            // Criar notificação (null = usuário desligou avisos de consórcio)
            if (ConsortiumNotification::createRedemptionPending($participant)) {
                $count++;
            }
        }

        return $count;
    }

    /** Só parcelas vencidas há no máximo N dias avisam (sem enxurrada de atrasos antigos). */
    public const OVERDUE_WINDOW_DAYS = 7;

    /**
     * Parcelas de consórcio que venceram e não foram pagas. Avisa o dono do
     * consórcio uma vez por parcela; várias do mesmo consórcio no mesmo dia
     * viram um aviso só ("3 parcelas atrasadas").
     *
     * @return int notificações criadas
     */
    public function checkOverdueInstallments(?int $consortiumId = null): int
    {
        $payments = ConsortiumPayment::query()
            ->with(['participant.client', 'participant.consortium'])
            ->whereIn('status', ['pending', 'overdue', 'late'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->whereDate('due_date', '>=', today()->subDays(self::OVERDUE_WINDOW_DAYS))
            ->whereHas('participant', fn ($q) => $q->where('status', '!=', 'quit')
                ->whereHas('consortium', fn ($c) => $c->where('status', '!=', 'cancelled')
                    ->when($consortiumId, fn ($w) => $w->whereKey($consortiumId))))
            ->orderBy('due_date')
            ->get();

        if ($payments->isEmpty()) {
            return 0;
        }

        $notified = $this->notifiedOverduePaymentIds();
        $count = 0;

        $pending = $payments
            ->reject(fn (ConsortiumPayment $p) => isset($notified[$p->id]) || !$p->participant?->consortium?->user_id)
            ->groupBy(fn (ConsortiumPayment $p) => $p->participant->consortium_id);

        foreach ($pending as $group) {
            if ($this->createInstallmentOverdue($group)) {
                $count++;
            }
        }

        return $count;
    }

    /** IDs de parcelas que já geraram aviso (inclui avisos excluídos pelo usuário). */
    protected function notifiedOverduePaymentIds(): array
    {
        $ids = [];
        ConsortiumNotification::withoutGlobalScopes()
            ->where('type', 'installment_overdue')
            ->where('created_at', '>=', now()->subDays(self::OVERDUE_WINDOW_DAYS + 30))
            ->get(['entity_id', 'data'])
            ->each(function ($n) use (&$ids) {
                if ($n->entity_id) {
                    $ids[(int) $n->entity_id] = true;
                }
                foreach ((array) ($n->data['payment_ids'] ?? []) as $id) {
                    $ids[(int) $id] = true;
                }
            });

        return $ids;
    }

    /** @param Collection<int, ConsortiumPayment> $payments parcelas de um mesmo consórcio */
    protected function createInstallmentOverdue(Collection $payments): ?ConsortiumNotification
    {
        $first = $payments->first();
        $consortium = $first->participant->consortium;
        $total = (float) $payments->sum('amount');
        $money = fn (float $v) => 'R$ ' . number_format($v, 2, ',', '.');
        $name = fn (ConsortiumPayment $p) => $p->participant->client?->name ?? 'Participante';

        if ($payments->count() === 1) {
            $ref = $first->reference_month_name
                ? "de {$first->reference_month_name}/{$first->reference_year}"
                : 'de ' . $first->due_date->format('d/m');
            $title = 'Parcela atrasada';
            $message = "A parcela {$ref} de {$name($first)} ({$money($total)}) no consórcio \"{$consortium->name}\" venceu em "
                . $first->due_date->format('d/m') . ' e ainda não foi paga.';
        } else {
            $names = $payments->map($name)->unique()->values();
            $who = $names->count() > 2
                ? $names->take(2)->implode(', ') . ' e mais ' . ($names->count() - 2)
                : $names->implode(' e ');
            $title = 'Parcelas atrasadas';
            $message = "{$payments->count()} parcelas do consórcio \"{$consortium->name}\" venceram sem pagamento ({$money($total)}): {$who}.";
        }

        return ConsortiumNotification::createGeneric(
            'consortium',
            'installment_overdue',
            (int) $consortium->user_id,
            $title,
            $message,
            [
                'entity_type' => 'ConsortiumPayment',
                'entity_id' => $first->id,
                'consortium_id' => $consortium->id,
                'related_participant_id' => $payments->count() === 1 ? $first->consortium_participant_id : null,
                'priority' => 'high',
                'action_url' => route('consortiums.show', $consortium, false),
                'data' => [
                    'payment_ids' => $payments->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                    'total' => round($total, 2),
                    'oldest_due_date' => $payments->min('due_date')?->toDateString(),
                ],
            ]
        );
    }

    /**
     * Limpar notificações antigas (90 dias lidas, 180 dias não lidas)
     */
    public function cleanOldNotifications(): array
    {
        $stats = [
            'read' => 0,
            'unread' => 0,
            'deleted' => 0,
            'total' => 0,
        ];

        // Deletar notificações lidas com mais de 90 dias
        $stats['read'] = ConsortiumNotification::purge(ConsortiumNotification::withoutGlobalScopes()
            ->where('is_read', true)
            ->where('created_at', '<', now()->subDays(90)));

        // Deletar notificações não lidas com mais de 180 dias
        $stats['unread'] = ConsortiumNotification::purge(ConsortiumNotification::withoutGlobalScopes()
            ->where('is_read', false)
            ->where('created_at', '<', now()->subDays(180)));

        // Excluídas pelo usuário (soft delete) há mais de 30 dias
        $stats['deleted'] = ConsortiumNotification::supportsSoftDeletes()
            ? (int) ConsortiumNotification::onlyTrashed()->where('deleted_at', '<', now()->subDays(30))->forceDelete()
            : 0;

        $stats['total'] = $stats['read'] + $stats['unread'] + $stats['deleted'];

        Log::info('Old consortium notifications cleaned', $stats);

        return $stats;
    }

    /**
     * Obter estatísticas de notificações
     */
    public function getStats(int $userId): array
    {
        return [
            'total' => ConsortiumNotification::forUser($userId)->count(),
            'unread' => ConsortiumNotification::unread()->forUser($userId)->count(),
            'by_type' => [
                'draw_available' => ConsortiumNotification::ofType('draw_available')->forUser($userId)->count(),
                'redemption_pending' => ConsortiumNotification::ofType('redemption_pending')->forUser($userId)->count(),
            ],
            'by_priority' => [
                'high' => ConsortiumNotification::highPriority()->forUser($userId)->count(),
                'medium' => ConsortiumNotification::where('priority', 'medium')->forUser($userId)->count(),
                'low' => ConsortiumNotification::where('priority', 'low')->forUser($userId)->count(),
            ],
            'recent' => ConsortiumNotification::recent()->forUser($userId)->count(),
        ];
    }

    /**
     * Obter notificações recentes de um usuário
     */
    public function getRecentNotifications(int $userId, int $limit = 10): Collection
    {
        return ConsortiumNotification::forUser($userId)
            ->latest('created_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Disparar notificações para um consórcio específico
     */
    public function triggerForConsortium(Consortium $consortium): int
    {
        $count = 0;

        // Verificar sorteio disponível
        if ($consortium->canPerformDraw()
            && !ConsortiumNotification::where('consortium_id', $consortium->id)->where('type', 'draw_available')->unread()->exists()) {
            $count += ConsortiumNotification::createDrawAvailable($consortium) ? 1 : 0;
        }

        // Verificar resgates pendentes neste consórcio
        $participants = $consortium->participants()
            ->with(['contemplation', 'client'])
            ->where('is_contemplated', true)
            ->whereHas('contemplation', function ($query) {
                $query->whereNotIn('status', ['redeemed', 'cancelled']);
            })
            ->get();

        foreach ($participants as $participant) {
            $pending = ConsortiumNotification::where('related_participant_id', $participant->id)
                ->where('type', 'redemption_pending')->unread()->exists();
            if (!$pending && ConsortiumNotification::createRedemptionPending($participant)) {
                $count++;
            }
        }

        // Parcelas vencidas deste consórcio
        $count += $this->checkOverdueInstallments($consortium->id);

        return $count;
    }
}
