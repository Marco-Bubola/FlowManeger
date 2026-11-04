<?php

namespace App\Console\Commands;

use App\Models\ClientTask;
use App\Models\ConsortiumNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Avisa no sino as tarefas do dia: as sem hora a partir das 08:00, as com hora
 * quando chega a hora, e as atrasadas que ainda não tinham sido avisadas.
 * Um aviso por pessoa juntando as tarefas; cada tarefa é avisada uma vez.
 */
class NotifyDueTasks extends Command
{
    protected $signature = 'tasks:avisar';

    protected $description = 'Avisa no sino as tarefas e follow-ups que chegaram no dia/hora';

    public function handle(): int
    {
        if (! Schema::hasTable('client_tasks')) {
            return self::SUCCESS;
        }

        $now = Carbon::now(ConsortiumNotification::DISPLAY_TZ);
        $today = $now->toDateString();
        $clock = $now->format('H:i:s');

        $due = ClientTask::with('client:id,name')->open()->whereNull('notified_at')
            ->where(function ($q) use ($today, $clock, $now) {
                $morning = $now->format('H:i') >= '08:00';
                $q->where('due_date', '<', $today)
                    ->orWhere(fn ($w) => $w->where('due_date', $today)->whereNotNull('due_time')->where('due_time', '<=', $clock))
                    ->when($morning, fn ($q2) => $q2->orWhere(fn ($w) => $w->where('due_date', $today)->whereNull('due_time')));
            })
            ->orderBy('due_date')->orderBy('due_time')
            ->get();

        $sent = 0;
        foreach ($due->groupBy('user_id') as $userId => $tasks) {
            $n = $tasks->count();
            $first = $tasks->first();
            $title = $n === 1 ? 'Tarefa: ' . $first->title : "{$n} tarefas para hoje";
            $lines = $tasks->take(3)->map(fn ($t) => $t->title . ($t->client ? ' (' . $t->client->name . ')' : '') . ($t->isLate() ? ' · atrasada' : ''))->implode('; ');
            $message = $n === 1
                ? trim(($first->client ? 'Cliente: ' . $first->client->name . '. ' : '') . ($first->dueLabel() . ($first->isLate() ? ', atrasada.' : '.')))
                : $lines . ($n > 3 ? ' e mais ' . ($n - 3) . '.' : '.');

            ConsortiumNotification::createGeneric('clients', 'task_due', (int) $userId, $title, $message, [
                'priority' => $tasks->contains(fn ($t) => $t->isLate()) ? 'high' : 'medium',
                'action_url' => $n === 1 && $first->client_id ? route('clients.historico', $first->client_id) : route('tasks.index'),
                'data' => ['tasks' => $tasks->pluck('id')->all()],
            ]);
            ClientTask::whereIn('id', $tasks->pluck('id'))->update(['notified_at' => now()]);
            $sent += $n;
        }

        $this->info("Tarefas avisadas: {$sent}");

        return self::SUCCESS;
    }
}
