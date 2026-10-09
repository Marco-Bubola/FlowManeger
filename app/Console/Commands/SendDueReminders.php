<?php

namespace App\Console\Commands;

use App\Models\Consortium;
use App\Models\ConsortiumNotification;
use App\Models\Sale;
use App\Models\User;
use App\Services\Collections\CollectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cobranças: uma notificação por usuário com o resumo do dia
 * ("3 parcelas vencem hoje, 2 vencidas sem lembrete"), só quando há algo.
 * O envio ao cliente continua pela tela Gestão › Cobranças (um clique no
 * WhatsApp); envio automático exigiria a API do WhatsApp Business.
 */
class SendDueReminders extends Command
{
    protected $signature = 'reminders:due {--user= : Apenas para este usuário}';

    protected $description = 'Notifica cada usuário sobre parcelas que vencem hoje e vencidas sem lembrete';

    public function handle(CollectionService $service): int
    {
        $users = User::query()
            ->when($this->option('user'), fn ($q, $id) => $q->whereKey($id))
            ->where(fn ($q) => $q->whereIn('id', Sale::withoutGlobalScopes()->select('user_id'))
                ->orWhereIn('id', Consortium::query()->select('user_id')))
            ->get();

        $created = 0;

        foreach ($users as $user) {
            try {
                $rows = $service->rows($user);
                $dueToday = $rows->filter(fn ($r) => $r['days'] === 0);
                $overdueNoReminder = $rows->filter(fn ($r) => $r['days'] < 0 && ! $r['last_reminder']);

                if ($dueToday->isEmpty() && $overdueNoReminder->isEmpty()) {
                    continue;
                }

                // Uma por dia: não repete se o comando rodar de novo
                $already = ConsortiumNotification::query()
                    ->where('user_id', $user->id)
                    ->where('module', 'cobrancas')
                    ->where('created_at', '>=', now()->startOfDay())
                    ->exists();
                if ($already) {
                    continue;
                }

                $parts = [];
                if ($dueToday->isNotEmpty()) {
                    $n = $dueToday->count();
                    $parts[] = $n . ' ' . ($n === 1 ? 'parcela vence hoje' : 'parcelas vencem hoje');
                }
                if ($overdueNoReminder->isNotEmpty()) {
                    $n = $overdueNoReminder->count();
                    $parts[] = $n . ' ' . ($n === 1 ? 'vencida sem lembrete' : 'vencidas sem lembrete');
                }

                $total = $dueToday->sum('value') + $overdueNoReminder->sum('value');

                ConsortiumNotification::createGeneric(
                    'cobrancas',
                    'payment_overdue',
                    $user->id,
                    'Cobranças do dia',
                    ucfirst(implode(', ', $parts)) . ' (R$ ' . number_format($total, 2, ',', '.') . '). Toque para lembrar pelo WhatsApp.',
                    [
                        'priority' => $overdueNoReminder->isNotEmpty() ? 'high' : 'medium',
                        'action_url' => $this->url($overdueNoReminder->isNotEmpty() ? 'vencidas' : 'vencendo'),
                        'data' => [
                            'due_today' => $dueToday->count(),
                            'overdue_without_reminder' => $overdueNoReminder->count(),
                            'total' => round($total, 2),
                        ],
                    ]
                );
                $created++;
            } catch (\Throwable $e) {
                Log::warning('reminders:due falhou para o usuário', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Notificações criadas: {$created}");

        return self::SUCCESS;
    }

    protected function url(string $filter): ?string
    {
        try {
            return route('gestao.collections', $filter === 'vencidas' ? [] : ['filter' => $filter]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
