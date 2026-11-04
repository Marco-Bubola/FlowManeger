<?php

namespace App\Livewire\Tasks;

use App\Models\Client;
use App\Models\ClientTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Tarefas e follow-ups: Hoje (com as atrasadas), Próximas e Feitas.
 * Cada tarefa pode estar ligada a um cliente (abre o Histórico dele).
 */
#[Layout('components.layouts.app')]
class TasksIndex extends Component
{
    #[Url(as: 'ver', except: 'hoje')]
    public string $tab = 'hoje';

    #[Url(as: 'cliente', except: null)]
    public ?int $clientFilter = null;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public int $limit = 30;

    // Nova / editar tarefa
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $title = '';
    public string $notes = '';
    public string $dueDate = '';
    public string $dueTime = '';
    public ?int $clientId = null;
    public string $clientSearch = '';

    public function mount(): void
    {
        $this->dueDate = ClientTask::today()->toDateString();
        if ($this->clientFilter) {
            $this->clientId = $this->clientFilter;
        }
    }

    protected function ownedClient(?int $id): ?Client
    {
        return $id ? Client::where('user_id', Auth::id())->find($id) : null;
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['hoje', 'proximas', 'feitas'], true) ? $tab : 'hoje';
        $this->limit = 30;
    }

    public function clearClient(): void
    {
        $this->clientFilter = null;
    }

    public function loadMore(): void
    {
        $this->limit += 30;
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function quickDate(int $days): void
    {
        $this->dueDate = ClientTask::today()->addDays($days)->toDateString();
    }

    public function pickClient(int $id): void
    {
        $c = $this->ownedClient($id);
        $this->clientId = $c?->id;
        $this->clientSearch = '';
    }

    public function edit(int $id): void
    {
        $t = ClientTask::ownedBy(Auth::id())->findOrFail($id);
        $this->editingId = $t->id;
        $this->title = $t->title;
        $this->notes = (string) $t->notes;
        $this->dueDate = $t->due_date->toDateString();
        $this->dueTime = $t->due_time ? substr($t->due_time, 0, 5) : '';
        $this->clientId = $t->client_id;
        $this->clientSearch = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:160',
            'notes' => 'nullable|string|max:2000',
            'dueDate' => 'required|date',
            'dueTime' => 'nullable|date_format:H:i',
        ], [
            'title.required' => 'Diga o que precisa ser feito.',
            'dueDate.required' => 'Escolha o dia.',
        ]);

        $data = [
            'title' => trim($this->title),
            'notes' => trim($this->notes) ?: null,
            'due_date' => $this->dueDate,
            'due_time' => $this->dueTime ?: null,
            'client_id' => $this->ownedClient($this->clientId)?->id,
        ];

        if ($this->editingId) {
            $task = ClientTask::ownedBy(Auth::id())->findOrFail($this->editingId);
            if ($task->due_date->toDateString() !== $this->dueDate || substr((string) $task->due_time, 0, 5) !== $this->dueTime) {
                $data['notified_at'] = null; // nova data: avisa de novo
            }
            $task->update($data);
            $msg = 'Tarefa atualizada.';
        } else {
            ClientTask::create($data + ['user_id' => Auth::id()]);
            $msg = 'Tarefa criada para ' . Carbon::parse($this->dueDate)->format('d/m') . '.';
        }

        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function toggle(int $id): void
    {
        $task = ClientTask::ownedBy(Auth::id())->findOrFail($id);
        $task->done_at = $task->done_at ? null : now();
        $task->save();
        $this->dispatch('notify', type: 'success', message: $task->done_at ? 'Feita! 🎉' : 'Tarefa reaberta.');
    }

    public function postpone(int $id, int $days): void
    {
        $task = ClientTask::ownedBy(Auth::id())->open()->findOrFail($id);
        $base = $task->due_date->lt(ClientTask::today()) ? ClientTask::today() : $task->due_date->copy();
        $task->update(['due_date' => $base->addDays($days)->toDateString(), 'notified_at' => null]);
        $this->dispatch('notify', type: 'success', message: 'Adiada para ' . $task->fresh()->dueLabel() . '.');
    }

    public function delete(int $id): void
    {
        ClientTask::ownedBy(Auth::id())->whereKey($id)->delete();
        if ($this->editingId === $id) {
            $this->cancel();
        }
        $this->dispatch('notify', type: 'success', message: 'Tarefa apagada.');
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'notes', 'dueTime', 'clientSearch']);
        $this->clientId = $this->clientFilter;
        $this->dueDate = ClientTask::today()->toDateString();
        $this->resetValidation();
    }

    public function render()
    {
        $uid = Auth::id();
        $today = ClientTask::today()->toDateString();

        $base = fn () => ClientTask::ownedBy($uid)
            ->when($this->clientFilter, fn ($q) => $q->where('client_id', $this->clientFilter))
            ->when(trim($this->search) !== '', function ($q) {
                $s = '%' . trim($this->search) . '%';
                $q->where(fn ($w) => $w->where('title', 'like', $s)->orWhere('notes', 'like', $s)
                    ->orWhereIn('client_id', Client::where('user_id', Auth::id())->where('name', 'like', $s)->select('id')));
            });

        $counts = [
            'atrasadas' => $base()->open()->where('due_date', '<', $today)->count(),
            'hoje' => $base()->open()->where('due_date', '<=', $today)->count(),
            'proximas' => $base()->open()->where('due_date', '>', $today)->count(),
            'feitas' => $base()->whereNotNull('done_at')->count(),
        ];

        $query = match ($this->tab) {
            'proximas' => $base()->open()->where('due_date', '>', $today)->orderBy('due_date')->orderByRaw('due_time IS NULL, due_time'),
            'feitas' => $base()->whereNotNull('done_at')->orderByDesc('done_at'),
            default => $base()->open()->where('due_date', '<=', $today)->orderBy('due_date')->orderByRaw('due_time IS NULL, due_time'),
        };
        $tasks = $query->with('client:id,name,phone,caminho_foto')->limit($this->limit + 1)->get();

        $clientResults = collect();
        if ($this->showForm && mb_strlen(trim($this->clientSearch)) >= 2) {
            $clientResults = Client::where('user_id', $uid)->where('name', 'like', '%' . trim($this->clientSearch) . '%')
                ->orderBy('name')->limit(8)->get(['id', 'name', 'phone']);
        }

        return view('livewire.tasks.tasks-index', [
            'tasks' => $tasks->take($this->limit),
            'hasMore' => $tasks->count() > $this->limit,
            'counts' => $counts,
            'filterClient' => $this->ownedClient($this->clientFilter),
            'formClient' => $this->ownedClient($this->clientId),
            'clientResults' => $clientResults,
        ]);
    }
}
