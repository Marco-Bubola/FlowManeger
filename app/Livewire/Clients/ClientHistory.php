<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientNote;
use App\Models\ClientTask;
use App\Services\Clients\ClientTimeline;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Cliente › Histórico: linha do tempo com tudo o que aconteceu com o cliente,
 * "Anotar contato" (ligação, WhatsApp, visita...) e tarefas ligadas a ele.
 */
#[Layout('components.layouts.app')]
class ClientHistory extends Component
{
    public Client $client;

    #[Url(as: 'ver', except: 'todos')]
    public string $group = 'todos';

    public int $limit = 40;

    // Anotar contato
    public string $noteKind = 'whatsapp';
    public string $noteBody = '';
    public string $noteDate = '';
    public ?int $editingNoteId = null;

    // Nova tarefa
    public bool $withTask = false;
    public string $taskTitle = '';
    public string $taskDate = '';
    public string $taskTime = '';

    public function mount($cliente): void
    {
        $this->client = Client::where('user_id', Auth::id())->findOrFail($cliente);
        $this->noteDate = $this->now()->format('Y-m-d\TH:i');
        $this->taskDate = $this->now()->addDay()->toDateString();
    }

    protected function now(): Carbon
    {
        return Carbon::now(\App\Models\ConsortiumNotification::DISPLAY_TZ);
    }

    public function setGroup(string $group): void
    {
        $this->group = array_key_exists($group, ClientTimeline::GROUPS) ? $group : 'todos';
        $this->limit = 40;
    }

    public function loadMore(): void
    {
        $this->limit += 40;
    }

    public function quickDate(int $days): void
    {
        $this->withTask = true;
        $this->taskDate = $this->now()->addDays($days)->toDateString();
    }

    public function saveNote(): void
    {
        $rules = [
            'noteKind' => 'required|in:' . implode(',', array_keys(ClientNote::KINDS)),
            'noteBody' => ($this->withTask && ! $this->editingNoteId ? 'nullable' : 'required') . '|string|max:3000',
            'noteDate' => 'required|date',
        ];
        if ($this->withTask && ! $this->editingNoteId) {
            $rules += ['taskTitle' => 'required|string|max:160', 'taskDate' => 'required|date', 'taskTime' => 'nullable|date_format:H:i'];
        }
        $this->validate($rules, [
            'noteBody.required' => 'Escreva o que foi conversado.',
            'taskTitle.required' => 'Diga o que precisa ser feito.',
            'taskDate.required' => 'Escolha o dia da tarefa.',
        ]);

        $uid = Auth::id();
        $saved = [];
        if (trim($this->noteBody) !== '') {
            $data = ['kind' => $this->noteKind, 'body' => trim($this->noteBody), 'happened_at' => Carbon::parse($this->noteDate, \App\Models\ConsortiumNotification::DISPLAY_TZ)->utc()];
            if ($this->editingNoteId) {
                ClientNote::where('user_id', $uid)->where('client_id', $this->client->id)->whereKey($this->editingNoteId)->update($data);
                $saved[] = 'Anotação atualizada';
            } else {
                ClientNote::create($data + ['user_id' => $uid, 'client_id' => $this->client->id]);
                $saved[] = 'Contato anotado';
            }
        }
        if ($this->withTask && ! $this->editingNoteId) {
            ClientTask::create([
                'user_id' => $uid,
                'client_id' => $this->client->id,
                'title' => trim($this->taskTitle),
                'due_date' => $this->taskDate,
                'due_time' => $this->taskTime ?: null,
            ]);
            $saved[] = 'tarefa criada para ' . Carbon::parse($this->taskDate)->format('d/m');
        }

        $this->resetNoteForm();
        $this->dispatch('notify', type: 'success', message: ucfirst(implode(' e ', $saved)) . '.');
    }

    public function editNote(int $id): void
    {
        $note = ClientNote::where('user_id', Auth::id())->where('client_id', $this->client->id)->findOrFail($id);
        $this->editingNoteId = $note->id;
        $this->noteKind = $note->kind;
        $this->noteBody = $note->body;
        $this->noteDate = $note->happened_at->copy()->setTimezone(\App\Models\ConsortiumNotification::DISPLAY_TZ)->format('Y-m-d\TH:i');
        $this->withTask = false;
        $this->dispatch('crm-note-focus');
    }

    public function cancelEdit(): void
    {
        $this->resetNoteForm();
    }

    public function deleteNote(int $id): void
    {
        ClientNote::where('user_id', Auth::id())->where('client_id', $this->client->id)->whereKey($id)->delete();
        if ($this->editingNoteId === $id) {
            $this->resetNoteForm();
        }
        $this->dispatch('notify', type: 'success', message: 'Anotação apagada.');
    }

    public function toggleTask(int $id): void
    {
        $task = ClientTask::ownedBy(Auth::id())->where('client_id', $this->client->id)->findOrFail($id);
        $task->done_at = $task->done_at ? null : now();
        $task->save();
        $this->dispatch('notify', type: 'success', message: $task->done_at ? 'Tarefa feita.' : 'Tarefa reaberta.');
    }

    protected function resetNoteForm(): void
    {
        $this->reset(['noteBody', 'editingNoteId', 'withTask', 'taskTitle', 'taskTime']);
        $this->noteDate = $this->now()->format('Y-m-d\TH:i');
        $this->taskDate = $this->now()->addDay()->toDateString();
        $this->resetValidation();
    }

    public function render(ClientTimeline $timeline)
    {
        $uid = Auth::id();
        $items = $timeline->for($this->client, $uid, $this->group, $this->limit + 1);

        return view('livewire.clients.client-history', [
            'items' => $items->take($this->limit),
            'hasMore' => $items->count() > $this->limit,
            'stats' => $timeline->stats($this->client, $uid),
            'openTasks' => ClientTask::ownedBy($uid)->open()->where('client_id', $this->client->id)
                ->orderBy('due_date')->orderBy('due_time')->get(),
            'kinds' => ClientNote::KINDS,
            'groups' => ClientTimeline::GROUPS,
            'waPhone' => \App\Services\Clients\ClientContact::whatsapp($this->client->phone),
        ]);
    }
}
