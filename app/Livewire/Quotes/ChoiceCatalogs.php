<?php

namespace App\Livewire\Quotes;

use App\Models\ChoiceCatalog;
use App\Models\ChoiceItem;
use App\Models\Product;
use App\Services\Choices\ChoiceService;
use App\Support\Choices\ChoiceDefaults;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Catálogo de estofados e cores (tecidos, cores de ferragem...). O produto liga
 * as listas que usa e o portal mostra uma lista suspensa para cada uma.
 * O robô Móveis Stylo lê e grava este mesmo catálogo.
 */
#[Layout('components.layouts.app')]
class ChoiceCatalogs extends Component
{
    use WithFileUploads;

    #[Url(as: 'lista', except: 0)]
    public int $catalogId = 0;

    // Nova lista
    public bool $showNewList = false;
    public string $listName = '';
    public string $listKind = 'estofado';

    // Novo(s) item(ns)
    public bool $showForm = false;
    public string $newMaterial = '';
    public string $newNames = '';
    public string $newHex = '';
    public string $newExtra = '';
    public $newPhoto = null;

    public $photo = null;      // troca de foto de um item
    public int $photoFor = 0;

    /** Cor de amostra pelo nome (o Marco pode trocar depois). */
    public const COLORS = [
        'preto' => '#1f1f1f', 'branco' => '#f8f8f6', 'off white' => '#f1ede4', 'off-white' => '#f1ede4', 'gelo' => '#eef0f0',
        'cinza' => '#8a8d91', 'grafite' => '#3c4043', 'chumbo' => '#4a4d50', 'prata' => '#c0c4c8', 'marrom' => '#6b4226',
        'café' => '#4b2e20', 'cafe' => '#4b2e20', 'chocolate' => '#3d2418', 'caramelo' => '#b06a2c', 'bege' => '#d8c3a5',
        'areia' => '#cdb891', 'nude' => '#e3c4ad', 'creme' => '#efe3c8', 'fendi' => '#b8a48c', 'camurça' => '#a47551',
        'azul' => '#2b5aa8', 'azul marinho' => '#1f2f57', 'marinho' => '#1f2f57', 'verde' => '#3f7d4e', 'verde musgo' => '#5a6b37',
        'musgo' => '#5a6b37', 'vermelho' => '#b3262d', 'vinho' => '#6d1f2f', 'bordô' => '#6d1f2f', 'bordo' => '#6d1f2f',
        'rosa' => '#e59bb3', 'amarelo' => '#e8c33b', 'mostarda' => '#c99a2e', 'laranja' => '#e07a2e', 'terracota' => '#b5573a',
        'dourado' => '#c9a13b', 'ouro' => '#c9a13b', 'bronze' => '#8c6239', 'cobre' => '#b06a3b', 'roxo' => '#6b3fa0', 'lilás' => '#b59cd6',
        'sépia' => '#d6cabd', 'sepia' => '#d6cabd', 'turquesa' => '#3ac3cc', 'ocre' => '#6f5444', 'roma' => '#c24d5c',
        'azul royal' => '#2346a8', 'amarelo areia' => '#e3cf98', 'asfalto' => '#3f4246', 'verde limão' => '#78b72a', 'verde menta' => '#a6e3c3',
    ];

    public function mount(): void
    {
        $first = ChoiceCatalog::where('user_id', Auth::id())->orderBy('position')->orderBy('id')->value('id');
        if (! $this->catalogId || ! $this->ownCatalog($this->catalogId)) {
            $this->catalogId = (int) $first;
        }
    }

    protected function ownCatalog(int $id): ?ChoiceCatalog
    {
        return ChoiceCatalog::where('user_id', Auth::id())->find($id);
    }

    protected function own(int $id): ?ChoiceItem
    {
        return ChoiceItem::where('user_id', Auth::id())->find($id);
    }

    public function selectCatalog(int $id): void
    {
        if ($this->ownCatalog($id)) {
            $this->catalogId = $id;
            $this->showForm = false;
        }
    }

    public function openNewList(string $kind = 'estofado'): void
    {
        $this->resetValidation();
        $this->listKind = isset(ChoiceService::KINDS[$kind]) ? $kind : 'outro';
        $this->listName = $kind === 'outro' ? '' : ($kind === 'ferragem' ? 'Cores de ferragem' : 'Estofados');
        $this->showNewList = true;
    }

    public function createList(): void
    {
        $this->validate(['listName' => 'required|string|max:80', 'listKind' => 'required|in:' . implode(',', array_keys(ChoiceService::KINDS))],
            [], ['listName' => 'nome da lista']);
        $c = ChoiceCatalog::create([
            'user_id' => Auth::id(), 'name' => trim($this->listName), 'kind' => $this->listKind,
            'position' => (int) ChoiceCatalog::where('user_id', Auth::id())->max('position') + 1,
        ]);
        $this->catalogId = $c->id;
        $this->showNewList = false;
        $this->openForm();
        $this->dispatch('notify', type: 'success', message: 'Lista criada. Agora coloque os itens.');
    }

    /** Começa com os corinos do mostruário (nome, código e amostra). */
    public function startWithCorino(): void
    {
        $c = ChoiceCatalog::where('user_id', Auth::id())->where('kind', 'estofado')->orderBy('id')->first()
            ?? ChoiceCatalog::create(['user_id' => Auth::id(), 'name' => 'Estofados', 'kind' => 'estofado',
                'position' => (int) ChoiceCatalog::where('user_id', Auth::id())->max('position') + 1]);
        $have = ChoiceItem::where('choice_catalog_id', $c->id)->where('material', 'Corino')->get()
            ->map(fn ($i) => mb_strtolower($i->name . '|' . $i->code))->flip();
        $pos = (int) ChoiceItem::where('choice_catalog_id', $c->id)->max('position');
        $n = 0;
        foreach (ChoiceDefaults::CORINO as [$name, $code, $hex]) {
            if (isset($have[mb_strtolower($name . '|' . $code)])) {
                continue;
            }
            ChoiceItem::create(['user_id' => Auth::id(), 'choice_catalog_id' => $c->id, 'material' => 'Corino', 'name' => $name,
                'code' => $code, 'hex' => $hex, 'extra_price' => 0, 'active' => true, 'position' => ++$pos]);
            $n++;
        }
        $this->catalogId = $c->id;
        $this->showNewList = false;
        $this->dispatch('notify', type: 'success', message: $n ? "$n cores de corino colocadas." : 'Os corinos do mostruário já estão na lista.');
    }

    public function renameList(string $name): void
    {
        $name = trim($name);
        if ($name !== '' && $c = $this->ownCatalog($this->catalogId)) {
            $c->update(['name' => mb_substr($name, 0, 80)]);
        }
    }

    public function deleteList(): void
    {
        if (! $c = $this->ownCatalog($this->catalogId)) {
            return;
        }
        foreach (ChoiceItem::where('choice_catalog_id', $c->id)->whereNotNull('image')->pluck('image') as $img) {
            Storage::disk('public')->delete($img);
        }
        ChoiceItem::where('choice_catalog_id', $c->id)->delete();
        $c->delete();
        $this->catalogId = (int) ChoiceCatalog::where('user_id', Auth::id())->orderBy('position')->value('id');
        $this->dispatch('notify', type: 'success', message: 'Lista apagada. Os produtos que usavam ela deixam de mostrar essa escolha.');
    }

    public function openForm(?string $material = null): void
    {
        $this->resetValidation();
        $this->newMaterial = $material ?? '';
        $this->newNames = $this->newHex = '';
        $this->newExtra = $material !== null
            ? $this->fmt((float) ChoiceItem::where('choice_catalog_id', $this->catalogId)->where('material', $material)->max('extra_price'))
            : '';
        $this->newPhoto = null;
        $this->showForm = true;
    }

    public function add(): void
    {
        if (! $c = $this->ownCatalog($this->catalogId)) {
            return;
        }
        $this->validate([
            'newMaterial' => 'nullable|string|max:80',
            'newNames' => 'required|string|max:600',
            'newHex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'newPhoto' => 'nullable|image|max:6144',
        ], [], ['newNames' => 'cor', 'newHex' => 'amostra', 'newPhoto' => 'foto']);

        $names = collect(preg_split('/[,;\n]+/', $this->newNames))->map(fn ($n) => trim($n))->filter()->unique()->take(40)->values();
        $material = trim($this->newMaterial) ?: null;
        $extra = $this->parseMoney($this->newExtra);
        $photo = $this->newPhoto && $names->count() === 1 ? $this->newPhoto->store('choices/' . Auth::id(), 'public') : null;
        $pos = (int) ChoiceItem::where('choice_catalog_id', $c->id)->max('position');
        foreach ($names as $n) {
            // "Preto 2792" ou "Preto - 2792": o número no fim é o código do fornecedor.
            $code = null;
            if (preg_match('/^(.*?)[\s\-–:#]+(?:c[oó]d\.?\s*)?(\d{3,12})$/iu', $n, $m) && trim($m[1]) !== '') {
                [$n, $code] = [trim($m[1]), $m[2]];
            }
            ChoiceItem::create([
                'user_id' => Auth::id(), 'choice_catalog_id' => $c->id, 'material' => $material ? mb_substr($material, 0, 80) : null,
                'name' => mb_substr($n, 0, 80), 'code' => $code, 'hex' => $names->count() === 1 && $this->newHex ? strtolower($this->newHex) : $this->guessHex($n),
                'image' => $photo, 'extra_price' => $extra, 'active' => true, 'position' => ++$pos,
            ]);
        }
        $this->showForm = false;
        $this->newPhoto = null;
        $this->dispatch('notify', type: 'success', message: $names->count() > 1 ? $names->count() . ' cores colocadas.' : 'Item colocado.');
    }

    public function updateField(int $id, string $field, $value): void
    {
        if (! in_array($field, ['name', 'material', 'hex', 'code'], true) || ! $it = $this->own($id)) {
            return;
        }
        $value = trim((string) $value);
        if ($field === 'name' && $value === '') {
            return;
        }
        if ($field === 'hex' && ! preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return;
        }
        $it->update([$field => mb_substr($value, 0, $field === 'code' ? 30 : 80) ?: null]);
    }

    public function updateExtra(int $id, $value): void
    {
        if ($it = $this->own($id)) {
            $it->update(['extra_price' => $this->parseMoney($value)]);
        }
    }

    /** O valor a mais vale para todas as cores do mesmo tecido. */
    public function updateMaterialExtra(string $material, $value): void
    {
        $v = $this->parseMoney($value);
        $n = ChoiceItem::where('user_id', Auth::id())->where('choice_catalog_id', $this->catalogId)
            ->when($material === '', fn ($q) => $q->whereNull('material'), fn ($q) => $q->where('material', $material))
            ->update(['extra_price' => $v]);
        $this->dispatch('notify', type: 'success', message: ($material ?: 'Sem tecido') . ': + R$ ' . $this->fmt($v) . " em $n cor(es)");
    }

    public function renameMaterial(string $from, string $to): void
    {
        $to = trim($to);
        ChoiceItem::where('user_id', Auth::id())->where('choice_catalog_id', $this->catalogId)
            ->when($from === '', fn ($q) => $q->whereNull('material'), fn ($q) => $q->where('material', $from))
            ->update(['material' => $to !== '' ? mb_substr($to, 0, 80) : null]);
    }

    public function toggle(int $id): void
    {
        if ($it = $this->own($id)) {
            $it->update(['active' => ! $it->active]);
        }
    }

    public function pickPhoto(int $id): void
    {
        $this->photoFor = $id;
    }

    public function updatedPhoto(): void
    {
        $this->validate(['photo' => 'image|max:6144'], [], ['photo' => 'foto']);
        if ($it = $this->own($this->photoFor)) {
            if ($it->image && ChoiceItem::where('image', $it->image)->count() === 1) {
                Storage::disk('public')->delete($it->image);
            }
            $it->update(['image' => $this->photo->store('choices/' . Auth::id(), 'public')]);
        }
        $this->photo = null;
        $this->photoFor = 0;
    }

    public function removePhoto(int $id): void
    {
        if (($it = $this->own($id)) && $it->image) {
            if (ChoiceItem::where('image', $it->image)->count() === 1) {
                Storage::disk('public')->delete($it->image);
            }
            $it->update(['image' => null]);
        }
    }

    public function delete(int $id): void
    {
        if ($it = $this->own($id)) {
            if ($it->image && ChoiceItem::where('image', $it->image)->count() === 1) {
                Storage::disk('public')->delete($it->image);
            }
            $it->delete();
            $this->dispatch('notify', type: 'success', message: $it->label() . ' saiu da lista.');
        }
    }

    protected function guessHex(string $name): ?string
    {
        $n = mb_strtolower(trim($name));
        if (isset(self::COLORS[$n])) {
            return self::COLORS[$n];
        }
        foreach (self::COLORS as $k => $hex) {
            if (preg_match('/\b' . preg_quote($k, '/') . '\b/u', $n)) {
                return $hex;
            }
        }

        return null;
    }

    protected function parseMoney($v): float
    {
        $s = preg_replace('/[^\d,.\-]/', '', (string) $v);
        if (str_contains($s, ',')) {
            $s = str_replace(['.', ','], ['', '.'], $s);
        }

        return max(0, round((float) $s, 2));
    }

    protected function fmt(float $v): string
    {
        return $v > 0 ? number_format($v, 2, ',', '.') : '';
    }

    public function render()
    {
        $catalogs = ChoiceService::catalogs((int) Auth::id());
        $current = $catalogs->firstWhere('id', $this->catalogId);
        $usedBy = 0;
        if ($current) {
            $usedBy = Product::where('user_id', Auth::id())->whereNotNull('choice_lists')->get(['id', 'choice_lists'])
                ->filter(fn ($p) => collect($p->choice_lists)->contains(fn ($l) => (int) ($l['c'] ?? 0) === $current->id))->count();
        }

        return view('livewire.quotes.choice-catalogs', [
            'catalogs' => $catalogs,
            'current' => $current,
            'groups' => $current ? $current->items->groupBy(fn ($i) => (string) $i->material) : collect(),
            'materials' => $current ? $current->items->pluck('material')->filter()->unique()->values() : collect(),
            'usedBy' => $usedBy,
            'kinds' => ChoiceService::KINDS,
        ])->title('Estofados e cores');
    }
}
