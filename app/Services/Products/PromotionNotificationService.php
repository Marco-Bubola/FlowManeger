<?php

namespace App\Services\Products;

use App\Models\ConsortiumNotification;
use App\Models\Promotion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Avisos de promoções no sino (module = 'promotions', categoria Vendas):
 *  - promotion_ending: a promoção acaba nas próximas 48 h (uma vez por promoção);
 *  - promotion_ended: a promoção foi encerrada sozinha (prazo ou estoque zerado).
 * Várias promoções do mesmo usuário na mesma rodada viram um aviso só.
 * Roda junto com promotions:refresh (de hora em hora).
 */
class PromotionNotificationService
{
    /** Antecedência do aviso "acabando". */
    public const ENDING_WINDOW_HOURS = 48;

    /** Encerramentos mais antigos que isso não avisam (rotina parada, migração). */
    public const ENDED_WINDOW_HOURS = 24;

    /** @return array{ending:int, ended:int} notificações criadas */
    public function check(?int $userId = null): array
    {
        return [
            'ending' => $this->notifyEndingSoon($userId),
            'ended' => $this->notifyAutoEnded($userId),
        ];
    }

    public function notifyEndingSoon(?int $userId = null): int
    {
        $promotions = $this->query($userId)
            ->where('status', Promotion::ATIVA)
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now())
            ->where('ends_at', '<=', now()->addHours(self::ENDING_WINDOW_HOURS))
            ->orderBy('ends_at')
            ->get();

        return $this->notifyGrouped($promotions, 'promotion_ending');
    }

    public function notifyAutoEnded(?int $userId = null): int
    {
        $promotions = $this->query($userId)
            ->where('status', Promotion::ENCERRADA)
            ->whereIn('ended_reason', ['vencida', 'sem_estoque'])
            ->where('ended_at', '>=', now()->subHours(self::ENDED_WINDOW_HOURS))
            ->orderBy('ended_at')
            ->get();

        return $this->notifyGrouped($promotions, 'promotion_ended');
    }

    protected function query(?int $userId)
    {
        return Promotion::query()
            ->with(['product' => fn ($q) => $q->withoutGlobalScopes()])
            ->whereNotNull('user_id')
            ->when($userId, fn ($q) => $q->where('user_id', $userId));
    }

    protected function notifyGrouped(Collection $promotions, string $type): int
    {
        if ($promotions->isEmpty()) {
            return 0;
        }

        $done = $this->notifiedIds($type);
        $count = 0;

        $promotions
            ->reject(fn (Promotion $p) => isset($done[$p->id]))
            ->groupBy('user_id')
            ->each(function (Collection $group, $userId) use ($type, &$count) {
                try {
                    $created = $type === 'promotion_ending'
                        ? $this->createEnding((int) $userId, $group)
                        : $this->createEnded((int) $userId, $group);
                    $count += $created ? 1 : 0;
                } catch (\Throwable $e) {
                    Log::warning('Falha ao criar notificação de promoção', ['type' => $type, 'error' => $e->getMessage()]);
                }
            });

        return $count;
    }

    protected function createEnding(int $userId, Collection $group): ?ConsortiumNotification
    {
        $first = $group->first();

        if ($group->count() === 1) {
            $title = 'Promoção acabando';
            $message = "A promoção de {$this->name($first)} ({$this->money($first->promo_price)}) acaba {$this->when($first->ends_at)}. "
                . 'Que tal divulgar mais uma vez ou prorrogar?';
        } else {
            $title = "{$group->count()} promoções acabando";
            $message = "{$this->names($group)}: a primeira acaba {$this->when($first->ends_at)}. "
                . 'Bom momento para uma última divulgação.';
        }

        return $this->create($userId, 'promotion_ending', $title, $message, $group, '/promotions', 'medium');
    }

    protected function createEnded(int $userId, Collection $group): ?ConsortiumNotification
    {
        $first = $group->first();

        if ($group->count() === 1) {
            $why = $first->ended_reason === 'sem_estoque' ? 'porque o estoque acabou' : 'porque chegou ao fim do prazo';
            $title = 'Promoção encerrada';
            $message = "A promoção de {$this->name($first)} terminou {$why}. O produto voltou ao preço normal.";
        } else {
            $title = "{$group->count()} promoções encerradas";
            $message = "Fim do prazo ou do estoque para {$this->names($group)}. Os produtos voltaram ao preço normal.";
        }

        return $this->create($userId, 'promotion_ended', $title, $message, $group, '/promotions?tab=encerradas', 'low');
    }

    protected function create(int $userId, string $type, string $title, string $message, Collection $group, string $url, string $priority): ?ConsortiumNotification
    {
        $first = $group->first();

        return ConsortiumNotification::createGeneric('promotions', $type, $userId, $title, $message, [
            'entity_type' => 'Promotion',
            'entity_id' => $first->id,
            'priority' => $priority,
            'action_url' => $url,
            'data' => [
                'promotion_ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'product_ids' => $group->pluck('product_id')->map(fn ($id) => (int) $id)->values()->all(),
            ],
        ]);
    }

    /** Promoções que já tiveram este aviso (inclui avisos excluídos pelo usuário). */
    protected function notifiedIds(string $type): array
    {
        $ids = [];
        ConsortiumNotification::withoutGlobalScopes()
            ->where('type', $type)
            ->where('entity_type', 'Promotion')
            ->where('created_at', '>=', now()->subDays(60))
            ->get(['entity_id', 'data'])
            ->each(function ($n) use (&$ids) {
                if ($n->entity_id) {
                    $ids[(int) $n->entity_id] = true;
                }
                foreach ((array) ($n->data['promotion_ids'] ?? []) as $id) {
                    $ids[(int) $id] = true;
                }
            });

        return $ids;
    }

    /** "hoje às 20h", "amanhã às 20h" ou "em 2 dias (12/10)", no fuso de exibição. */
    protected function when(Carbon $endsAt): string
    {
        $local = $endsAt->copy()->timezone(ConsortiumNotification::DISPLAY_TZ);
        $today = now(ConsortiumNotification::DISPLAY_TZ)->startOfDay();
        $days = (int) $today->diffInDays($local->copy()->startOfDay());
        // 20:59:59 (fim do dia em UTC) vira "21h"; 23:59 fica "23h59"
        $rounded = $local->format('i:s') === '59:59' && $local->format('H') !== '23' ? $local->copy()->addSecond() : $local;
        $hour = $rounded->format('i') === '00' ? $rounded->format('G') . 'h' : $rounded->format('G\hi');

        return match (true) {
            $days <= 0 => "hoje às {$hour}",
            $days === 1 => "amanhã às {$hour}",
            default => "em {$days} dias (" . $local->format('d/m') . ')',
        };
    }

    protected function name(Promotion $promotion): string
    {
        $name = trim((string) $promotion->product?->name);

        return $name === '' ? 'um produto' : \Illuminate\Support\Str::limit(mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8'), 50);
    }

    protected function names(Collection $group): string
    {
        // Produtos com o mesmo nome (variações) aparecem uma vez: "Batom Matte (2)"
        $names = $group->map(fn (Promotion $p) => $this->name($p))->countBy()
            ->map(fn ($n, $name) => $n > 1 ? "{$name} ({$n})" : $name)->values();

        return $names->count() > 2
            ? $names->take(2)->implode(', ') . ' e mais ' . ($names->count() - 2)
            : $names->implode(' e ');
    }

    protected function money($value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }
}
