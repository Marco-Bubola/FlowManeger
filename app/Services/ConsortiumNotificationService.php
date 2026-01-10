<?php

namespace App\Services;

use App\Models\Consortium;
use App\Models\ConsortiumNotification;
use App\Models\ConsortiumParticipant;
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
            'total' => 0,
        ];

        try {
            DB::beginTransaction();

            // Verificar sorteios disponíveis
            $stats['draw_available'] = $this->checkDrawsAvailable();

            // Verificar resgates pendentes
            $stats['redemption_pending'] = $this->checkRedemptionsPending();

            $stats['total'] = $stats['draw_available'] + $stats['redemption_pending'];

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
            if (!$consortium->canDraw()) {
                continue;
            }

            // Verificar se já existe notificação recente (últimas 24h)
            $hasRecentNotification = ConsortiumNotification::where('consortium_id', $consortium->id)
                ->where('type', 'draw_available')
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if ($hasRecentNotification) {
                continue;
            }

            // Criar notificação
            ConsortiumNotification::createDrawAvailable($consortium);
            $count++;
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
                $query->where('redemption_type', 'pending')
                    ->where('contemplation_date', '<=', now()->subDays(7)); // Pelo menos 7 dias atrás
            })
            ->get();

        foreach ($participants as $participant) {
            if (!$participant->contemplation) {
                continue;
            }

            $daysSince = $participant->contemplation->contemplation_date->diffInDays(now());

            // Notificar a cada 7 dias, 15 dias, 30 dias e depois a cada 30 dias
            $shouldNotify = false;
            
            if ($daysSince >= 7 && $daysSince < 8) {
                $shouldNotify = true; // 7 dias
            } elseif ($daysSince >= 15 && $daysSince < 16) {
                $shouldNotify = true; // 15 dias
            } elseif ($daysSince >= 30 && $daysSince < 31) {
                $shouldNotify = true; // 30 dias
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

            // Criar notificação
            ConsortiumNotification::createRedemptionPending($participant);
            $count++;
        }

        return $count;
    }

    /**
     * Limpar notificações antigas (90 dias lidas, 180 dias não lidas)
     */
    public function cleanOldNotifications(): array
    {
        $stats = [
            'read' => 0,
            'unread' => 0,
            'total' => 0,
        ];

        // Deletar notificações lidas com mais de 90 dias
        $stats['read'] = ConsortiumNotification::read()
            ->where('created_at', '<', now()->subDays(90))
            ->forceDelete();

        // Deletar notificações não lidas com mais de 180 dias
        $stats['unread'] = ConsortiumNotification::unread()
            ->where('created_at', '<', now()->subDays(180))
            ->forceDelete();

        $stats['total'] = $stats['read'] + $stats['unread'];

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
            ->with(['consortium', 'participant.client'])
            ->orderBy('created_at', 'desc')
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
        if ($consortium->canDraw()) {
            ConsortiumNotification::createDrawAvailable($consortium);
            $count++;
        }

        // Verificar resgates pendentes neste consórcio
        $participants = $consortium->participants()
            ->with(['contemplation', 'client'])
            ->where('is_contemplated', true)
            ->whereHas('contemplation', function ($query) {
                $query->where('redemption_type', 'pending');
            })
            ->get();

        foreach ($participants as $participant) {
            ConsortiumNotification::createRedemptionPending($participant);
            $count++;
        }

        return $count;
    }
}
