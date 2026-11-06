<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\ClientTask;
use App\Models\ConsortiumNotification;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Automações de relacionamento. Todo dia de manhã cada automação ligada
 * procura os clientes do dia e cria uma tarefa com a mensagem pronta
 * (o botão de WhatsApp da tarefa já abre a conversa com o texto).
 * Fica salvo em users.preferences.crm.auto.{aniversario|posvenda|sumido}.
 *
 * Não envia sozinho: o WhatsApp oficial só deixa a loja iniciar conversa
 * com modelos aprovados pela Meta.
 */
class CrmAutomations
{
    public const KINDS = [
        'aniversario' => [
            'label' => 'Aniversário do cliente',
            'icon' => 'bi-gift',
            'tone' => 'pink',
            'help' => 'No dia do aniversário (campo Data de nascimento do cliente).',
            'task' => 'Aniversário de {nome}: mandar parabéns',
            'msg' => "Oi {nome}, feliz aniversário! 🎉 Que seu dia seja incrível. Um abraço da {loja}!",
            'days' => null,
        ],
        'posvenda' => [
            'label' => 'Pós-venda',
            'icon' => 'bi-chat-heart',
            'tone' => 'emerald',
            'help' => 'Alguns dias depois de cada venda, para saber se gostou e pedir avaliação.',
            'task' => 'Pós-venda: perguntar se {nome} gostou',
            'msg' => "Oi {nome}, tudo bem? Passando para saber se você gostou do {produto}. Qualquer coisa estou à disposição! {loja}",
            'days' => 7,
        ],
        'sumido' => [
            'label' => 'Cliente sumido',
            'icon' => 'bi-hourglass-split',
            'tone' => 'amber',
            'help' => 'Quando o cliente passa de 90 dias sem comprar. Uma vez por sumiço.',
            'task' => '{nome} está sumido: mandar uma novidade',
            'msg' => "Oi {nome}, quanto tempo! Chegaram novidades aqui na {loja} e lembrei de você. Quer que eu te mande umas fotos?",
            'days' => null,
        ],
    ];

    public const VARS = ['{nome}' => 'primeiro nome', '{loja}' => 'nome da loja', '{produto}' => 'produto da venda', '{dias}' => 'dias sem comprar'];

    public static function settings(User $user): array
    {
        $saved = (array) data_get($user->preferences ?? [], 'crm.auto', []);
        $out = [];
        foreach (self::KINDS as $k => $def) {
            $s = (array) ($saved[$k] ?? []);
            $out[$k] = [
                'on' => (bool) ($s['on'] ?? false),
                'msg' => (string) ($s['msg'] ?? $def['msg']) ?: $def['msg'],
                'days' => $def['days'] === null ? null : max(1, min(90, (int) ($s['days'] ?? $def['days']))),
            ];
        }

        return $out;
    }

    public static function save(User $user, array $settings): void
    {
        $prefs = $user->preferences ?? [];
        data_set($prefs, 'crm.auto', $settings);
        $user->preferences = $prefs;
        $user->save();
    }

    public static function storeName(int $userId): string
    {
        try {
            $id = \App\Services\Portal\StoreIdentity::for($userId);
            if (! empty($id['name'])) {
                return $id['name'];
            }
        } catch (\Throwable $e) {
        }

        return User::find($userId)?->name ?? config('app.name');
    }

    public static function render(string $msg, Client $client, array $vars = []): string
    {
        $first = trim(explode(' ', trim((string) $client->name))[0] ?? '');

        return strtr($msg, [
            '{nome}' => $first ?: $client->name,
            '{loja}' => $vars['loja'] ?? '',
            '{produto}' => $vars['produto'] ?? 'produto',
            '{dias}' => (string) ($vars['dias'] ?? ''),
        ]);
    }

    protected static function optedIn()
    {
        return fn ($q) => $q->where(fn ($w) => $w->whereNull('clients.marketing_opt_out')->orWhere('clients.marketing_opt_out', false));
    }

    /**
     * Quem entra hoje em cada automação (sem criar nada).
     *
     * @return array<string, array<int, array{client: Client, ref: string, vars: array}>>
     */
    public function candidates(User $user, ?Carbon $today = null, ?array $only = null): array
    {
        $today = $today ?: Carbon::now(ConsortiumNotification::DISPLAY_TZ)->startOfDay();
        $set = self::settings($user);
        $loja = self::storeName($user->id);
        $base = fn () => Client::withoutGlobalScopes()->where('clients.user_id', $user->id)->tap(self::optedIn());
        $out = [];

        foreach (array_keys(self::KINDS) as $kind) {
            if ($only !== null ? ! in_array($kind, $only, true) : ! $set[$kind]['on']) {
                continue;
            }
            $rows = [];
            if ($kind === 'aniversario') {
                foreach ($base()->whereNotNull('birth_date')->whereMonth('birth_date', $today->month)->whereDay('birth_date', $today->day)->get() as $c) {
                    $rows[] = ['client' => $c, 'ref' => (string) $today->year, 'vars' => ['loja' => $loja]];
                }
            } elseif ($kind === 'posvenda') {
                $day = $today->copy()->subDays($set['posvenda']['days']);
                $sales = Sale::withoutGlobalScopes()->with(['saleItems.product:id,name'])
                    ->where('user_id', $user->id)->whereNotIn('status', ClientStatus::IGNORED_SALE_STATUSES)
                    ->whereBetween('created_at', [$day->copy()->utc(), $day->copy()->endOfDay()->utc()])
                    ->get();
                $clients = $base()->whereIn('clients.id', $sales->pluck('client_id'))->get()->keyBy('id');
                foreach ($sales as $s) {
                    if ($c = $clients->get($s->client_id)) {
                        $prod = optional($s->saleItems->first()?->product)->name;
                        $rows[] = ['client' => $c, 'ref' => 'venda-' . $s->id, 'vars' => ['loja' => $loja, 'produto' => $prod ?: 'que você levou']];
                    }
                }
            } else {
                $last = ClientStatus::lastSaleSql();
                $q = ClientStatus::apply($base(), 'sumido')->select('clients.*')->selectRaw("$last as last_sale_at");
                foreach ($q->get() as $c) {
                    $ls = Carbon::parse($c->last_sale_at);
                    $rows[] = ['client' => $c, 'ref' => 'ultima-' . $ls->toDateString(), 'vars' => ['loja' => $loja, 'dias' => (int) $ls->diffInDays(now())]];
                }
            }
            $out[$kind] = $rows;
        }

        return $out;
    }

    /** Cria as tarefas do dia. Devolve quantas foram criadas por automação. */
    public function run(User $user, ?Carbon $today = null, bool $notify = true): array
    {
        if (! Schema::hasTable('crm_automation_logs')) {
            return [];
        }
        $today = $today ?: Carbon::now(ConsortiumNotification::DISPLAY_TZ)->startOfDay();
        $set = self::settings($user);
        $created = [];
        $names = [];

        foreach ($this->candidates($user, $today) as $kind => $rows) {
            $created[$kind] = 0;
            foreach ($rows as $row) {
                $c = $row['client'];
                $exists = DB::table('crm_automation_logs')->where(['user_id' => $user->id, 'kind' => $kind, 'client_id' => $c->id, 'ref' => $row['ref']])->exists();
                if ($exists) {
                    continue;
                }
                $msg = self::render($set[$kind]['msg'], $c, $row['vars']);
                $task = ClientTask::create([
                    'user_id' => $user->id,
                    'client_id' => $c->id,
                    'title' => mb_substr(self::render(self::KINDS[$kind]['task'], $c, $row['vars']), 0, 160),
                    'notes' => $msg,
                    'message' => $msg,
                    'auto' => $kind,
                    'due_date' => $today->toDateString(),
                    'notified_at' => now(), // o aviso de resumo abaixo já cobre
                ]);
                DB::table('crm_automation_logs')->insert([
                    'user_id' => $user->id, 'kind' => $kind, 'client_id' => $c->id, 'ref' => $row['ref'],
                    'task_id' => $task->id, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $created[$kind]++;
                $names[] = $c->name;
            }
        }

        $total = array_sum($created);
        if ($notify && $total > 0) {
            $parts = [];
            foreach ($created as $k => $n) {
                if ($n) {
                    $parts[] = $n . ' ' . mb_strtolower(self::KINDS[$k]['label']);
                }
            }
            ConsortiumNotification::createGeneric('clients', 'task_due', $user->id,
                $total === 1 ? 'Mensagem pronta para enviar' : "{$total} mensagens prontas para enviar",
                implode(', ', $parts) . '. ' . collect($names)->unique()->take(3)->implode(', ') . (collect($names)->unique()->count() > 3 ? ' e outros.' : '.') . ' Abra Tarefas e toque no WhatsApp.',
                ['priority' => 'medium', 'action_url' => route('tasks.index')]);
        }

        return $created;
    }

    /** Próximos aniversários (para a tela). */
    public function upcomingBirthdays(User $user, int $days = 30)
    {
        $today = Carbon::now(ConsortiumNotification::DISPLAY_TZ)->startOfDay();

        return Client::withoutGlobalScopes()->where('user_id', $user->id)->whereNotNull('birth_date')->get(['id', 'name', 'phone', 'birth_date', 'marketing_opt_out'])
            ->map(function ($c) use ($today) {
                $b = Carbon::parse($c->birth_date);
                $next = Carbon::create($today->year, $b->month, min($b->day, Carbon::create($today->year, $b->month, 1)->daysInMonth), 0, 0, 0, $today->tz);
                if ($next->lt($today)) {
                    $next->addYear();
                }
                $c->next_birthday = $next;
                $c->in_days = (int) $today->diffInDays($next);

                return $c;
            })
            ->filter(fn ($c) => $c->in_days <= $days)->sortBy('in_days')->values();
    }
}
