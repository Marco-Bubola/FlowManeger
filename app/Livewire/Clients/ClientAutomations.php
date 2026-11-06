<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientTask;
use App\Services\Clients\CrmAutomations;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Clientes › Automações: liga/desliga aniversário, pós-venda e cliente sumido,
 * edita a mensagem e mostra quem entra hoje e os próximos aniversários.
 */
#[Layout('components.layouts.app')]
class ClientAutomations extends Component
{
    public array $auto = [];
    public ?string $preview = null; // automação aberta em "Quem entra hoje"

    public function mount(): void
    {
        $this->auto = CrmAutomations::settings(Auth::user());
    }

    public function toggle(string $kind): void
    {
        if (! isset(CrmAutomations::KINDS[$kind])) {
            return;
        }
        $this->auto[$kind]['on'] = ! $this->auto[$kind]['on'];
        $this->persist();
        $this->dispatch('notify', type: 'success', message: CrmAutomations::KINDS[$kind]['label'] . ($this->auto[$kind]['on'] ? ' ligada.' : ' desligada.'));
    }

    public function insertVar(string $kind, string $var): void
    {
        if (isset($this->auto[$kind]) && isset(CrmAutomations::VARS[$var])) {
            $this->auto[$kind]['msg'] = rtrim($this->auto[$kind]['msg']) . ' ' . $var;
        }
    }

    public function resetMsg(string $kind): void
    {
        if (isset(CrmAutomations::KINDS[$kind])) {
            $this->auto[$kind]['msg'] = CrmAutomations::KINDS[$kind]['msg'];
        }
    }

    public function save(): void
    {
        foreach (array_keys(CrmAutomations::KINDS) as $k) {
            $this->validate([
                "auto.$k.msg" => 'required|string|max:1000',
                "auto.$k.days" => 'nullable|integer|min:1|max:90',
            ], ["auto.$k.msg.required" => 'Escreva a mensagem.']);
        }
        $this->persist();
        $this->dispatch('notify', type: 'success', message: 'Automações salvas.');
    }

    protected function persist(): void
    {
        $clean = [];
        foreach (CrmAutomations::KINDS as $k => $def) {
            $clean[$k] = [
                'on' => (bool) ($this->auto[$k]['on'] ?? false),
                'msg' => trim((string) ($this->auto[$k]['msg'] ?? '')) ?: $def['msg'],
                'days' => $def['days'] === null ? null : max(1, min(90, (int) ($this->auto[$k]['days'] ?? $def['days']))),
            ];
        }
        CrmAutomations::save(Auth::user(), $clean);
        $this->auto = $clean;
    }

    public function runNow(CrmAutomations $svc): void
    {
        $this->persist();
        $made = array_sum($svc->run(Auth::user()->fresh(), null, false));
        $this->dispatch('notify', type: 'success', message: $made ? ($made === 1 ? '1 tarefa criada em Tarefas.' : "{$made} tarefas criadas em Tarefas.") : 'Nada novo para hoje: ninguém faz aniversário, completa o pós-venda ou sumiu hoje (ou já foi criado).');
    }

    public function showPreview(?string $kind): void
    {
        $this->preview = $this->preview === $kind ? null : $kind;
    }

    public function render(CrmAutomations $svc)
    {
        $user = Auth::user();
        $today = $svc->candidates($user, null, array_keys(CrmAutomations::KINDS));
        $loja = CrmAutomations::storeName($user->id);
        $sample = new Client(['name' => 'Maria Souza']);

        return view('livewire.clients.client-automations', [
            'kinds' => CrmAutomations::KINDS,
            'vars' => CrmAutomations::VARS,
            'today' => $today,
            'samples' => collect(CrmAutomations::KINDS)->mapWithKeys(fn ($d, $k) => [$k => CrmAutomations::render($this->auto[$k]['msg'] ?? $d['msg'], $sample, ['loja' => $loja, 'produto' => 'rack de ferro', 'dias' => 120])]),
            'birthdays' => $svc->upcomingBirthdays($user),
            'optOut' => Client::query()->where('marketing_opt_out', true)->count(),
            'pending' => ClientTask::ownedBy($user->id)->open()->whereNotNull('auto')->count(),
        ]);
    }
}
