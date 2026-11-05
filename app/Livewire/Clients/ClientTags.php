<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientTag;
use App\Services\Clients\ClientStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Clientes › Etiquetas: criar, renomear, mudar a cor e apagar etiquetas,
 * e ver quantos clientes há em cada status e em cada origem.
 */
#[Layout('components.layouts.app')]
class ClientTags extends Component
{
    public string $name = '';
    public string $color = 'indigo';
    public ?int $editingId = null;
    public string $editName = '';
    public string $editColor = 'indigo';

    public function create(): void
    {
        $this->validate(['name' => 'required|string|max:40', 'color' => 'in:' . implode(',', ClientTag::COLORS)], ['name.required' => 'Dê um nome para a etiqueta.']);
        $name = trim($this->name);
        if (ClientTag::where('user_id', Auth::id())->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
            $this->addError('name', 'Já existe uma etiqueta com esse nome.');

            return;
        }
        ClientTag::create(['user_id' => Auth::id(), 'name' => $name, 'color' => $this->color]);
        $this->reset('name');
        $this->dispatch('notify', type: 'success', message: 'Etiqueta criada.');
    }

    public function edit(int $id): void
    {
        $t = ClientTag::where('user_id', Auth::id())->findOrFail($id);
        $this->editingId = $t->id;
        $this->editName = $t->name;
        $this->editColor = $t->color;
    }

    public function saveEdit(): void
    {
        $this->validate(['editName' => 'required|string|max:40', 'editColor' => 'in:' . implode(',', ClientTag::COLORS)]);
        ClientTag::where('user_id', Auth::id())->whereKey($this->editingId)->update(['name' => trim($this->editName), 'color' => $this->editColor]);
        $this->editingId = null;
        $this->dispatch('notify', type: 'success', message: 'Etiqueta salva.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        $tag = ClientTag::where('user_id', Auth::id())->findOrFail($id);
        DB::table('client_tag')->where('client_tag_id', $tag->id)->delete();
        $tag->delete();
        if ($this->editingId === $id) {
            $this->editingId = null;
        }
        $this->dispatch('notify', type: 'success', message: 'Etiqueta apagada. Os clientes continuam cadastrados.');
    }

    public function render()
    {
        $tags = ClientTag::where('user_id', Auth::id())->orderBy('name')->get();
        $counts = DB::table('client_tag')->whereIn('client_tag_id', $tags->pluck('id'))
            ->selectRaw('client_tag_id, COUNT(*) as n')->groupBy('client_tag_id')->pluck('n', 'client_tag_id');

        $sources = Client::query()->selectRaw("COALESCE(source, 'sem') as src, COUNT(*) as n")->groupBy('src')->pluck('n', 'src');

        return view('livewire.clients.client-tags', [
            'tags' => $tags,
            'counts' => $counts,
            'statusCounts' => ClientStatus::counts(),
            'sourceCounts' => $sources,
        ]);
    }
}
