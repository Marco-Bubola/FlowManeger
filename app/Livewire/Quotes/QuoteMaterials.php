<?php

namespace App\Livewire\Quotes;

use App\Models\QuoteMaterial;
use App\Services\Quotes\QuoteService;
use App\Support\Quotes\QuoteDefaults;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Tabela de materiais e horas: a base do custo dos orçamentos (e do robô).
 * Começa com a mesma tabela do robô Móveis Stylo; a pessoa ajusta os preços.
 */
#[Layout('components.layouts.app')]
class QuoteMaterials extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'grupo', except: '')]
    public string $category = '';

    // Novo item
    public bool $showForm = false;
    public string $newCategory = '';
    public string $newName = '';
    public string $newUnit = 'un';
    public string $newPrice = '';
    public string $newNote = '';

    public function mount(): void
    {
        QuoteService::materials(Auth::id());
    }

    protected function own(int $id): ?QuoteMaterial
    {
        return QuoteMaterial::where('user_id', Auth::id())->find($id);
    }

    public function setCategory(string $cat): void
    {
        $this->category = $this->category === $cat ? '' : $cat;
    }

    public function updatePrice(int $id, $value): void
    {
        if (!$m = $this->own($id)) {
            return;
        }
        $v = $this->parseMoney($value);
        $m->update(['price' => $v, 'estimated' => false]);
        $this->dispatch('notify', type: 'success', message: $m->name . ': ' . QuoteService::money($v));
    }

    public function updateField(int $id, string $field, $value): void
    {
        if (!in_array($field, ['name', 'unit', 'note', 'category'], true) || !$m = $this->own($id)) {
            return;
        }
        $value = trim((string) $value);
        if ($field === 'name' && $value === '') {
            return;
        }
        $max = ['name' => 160, 'unit' => 20, 'note' => 200, 'category' => 80][$field];
        $m->update([$field => mb_substr($value, 0, $max) ?: null]);
    }

    public function confirmPrice(int $id): void
    {
        $this->own($id)?->update(['estimated' => false]);
    }

    public function delete(int $id): void
    {
        if ($m = $this->own($id)) {
            $m->delete();
            $this->dispatch('notify', type: 'success', message: 'Item tirado da tabela.');
        }
    }

    public function openForm(?string $cat = null): void
    {
        $this->resetValidation();
        $this->newCategory = $cat ?? ($this->category ?: '');
        $this->newName = $this->newNote = $this->newPrice = '';
        $this->newUnit = 'un';
        $this->showForm = true;
    }

    public function add(): void
    {
        $this->validate([
            'newCategory' => 'required|string|max:80',
            'newName' => 'required|string|max:160',
            'newUnit' => 'required|string|max:20',
        ], [], ['newCategory' => 'grupo', 'newName' => 'nome', 'newUnit' => 'unidade']);

        $pos = (int) QuoteMaterial::where('user_id', Auth::id())->where('category', $this->newCategory)->max('position');
        if (!$pos) {
            $pos = (int) QuoteMaterial::where('user_id', Auth::id())->max('position') + 10;
        }
        QuoteMaterial::create([
            'user_id' => Auth::id(), 'category' => trim($this->newCategory), 'name' => trim($this->newName), 'unit' => $this->newUnit,
            'price' => $this->parseMoney($this->newPrice), 'note' => trim($this->newNote) ?: null, 'estimated' => false, 'position' => $pos + 1,
        ]);
        $this->showForm = false;
        $this->dispatch('notify', type: 'success', message: 'Item colocado na tabela.');
    }

    /** Traz de volta os itens da tabela do robô que não estão mais aqui (sem mexer nos seus preços). */
    public function restoreDefaults(): void
    {
        $have = QuoteMaterial::where('user_id', Auth::id())->pluck('name')->map(fn ($n) => mb_strtolower($n))->flip();
        $n = 0;
        foreach (QuoteDefaults::MATERIALS as $i => [$cat, $name, $unit, $price, $note]) {
            if (!isset($have[mb_strtolower($name)])) {
                QuoteMaterial::create(['user_id' => Auth::id(), 'category' => $cat, 'name' => $name, 'unit' => $unit, 'price' => $price,
                    'note' => $note ?: null, 'estimated' => true, 'position' => ($i + 1) * 10]);
                $n++;
            }
        }
        $this->dispatch('notify', type: 'success', message: $n ? "$n item(ns) do robô voltaram para a tabela." : 'Sua tabela já tem todos os itens do robô.');
    }

    protected function parseMoney($v): float
    {
        $s = preg_replace('/[^\d,.\-]/', '', (string) $v);
        if (str_contains($s, ',')) {
            $s = str_replace(['.', ','], ['', '.'], $s);
        }

        return max(0, round((float) $s, 2));
    }

    public function render()
    {
        $all = QuoteMaterial::where('user_id', Auth::id())->orderBy('position')->orderBy('id')->get();
        $cats = $all->groupBy('category')->map->count();
        $rows = $all;
        if ($this->category !== '') {
            $rows = $rows->where('category', $this->category);
        }
        if (($q = mb_strtolower(trim($this->search))) !== '') {
            $rows = $rows->filter(fn ($m) => str_contains(mb_strtolower($m->name . ' ' . $m->category . ' ' . $m->note), $q));
        }

        return view('livewire.quotes.quote-materials', [
            'groups' => $rows->groupBy('category'),
            'cats' => $cats,
            'total' => $all->count(),
            'estimated' => $all->where('estimated', true)->count(),
            'units' => QuoteDefaults::UNITS,
        ])->title('Materiais e horas');
    }
}
