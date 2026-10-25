<?php

namespace App\Livewire\Promotions;

use App\Models\Category;
use App\Models\ClientQuoteRequest;
use App\Models\PortalKitExtra;
use App\Models\PortalKitType;
use App\Services\Portal\KitService;
use App\Services\Portal\StorefrontService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Monte seu kit (Vitrine): os tipos de kit que o cliente pode montar no catálogo
 * e os enfeites com preço (balão, chocolate, laço…).
 */
class PortalKits extends Component
{
    use WithFileUploads;

    public string $tab = 'tipos';

    protected $queryString = ['tab' => ['except' => 'tipos']];

    // Formulário do tipo de kit
    public bool $typeForm = false;
    public ?int $typeId = null;
    public string $t_name = '';
    public string $t_style = 'cesta';
    public string $t_description = '';
    public string $t_base_price = '';
    public string $t_min = '1';
    public string $t_max = '6';
    public array $t_cats = [];
    public bool $t_active = true;
    public ?string $t_image = null;
    public $t_upload = null;
    public array $t_options = [];
    public int $t_photos = 0;
    public bool $t_date = true;

    // Formulário do enfeite
    public bool $extraForm = false;
    public ?int $extraId = null;
    public string $e_name = '';
    public string $e_group = 'balao';
    public string $e_description = '';
    public string $e_price = '';
    public string $e_max = '5';
    public bool $e_active = true;
    public ?string $e_image = null;
    public $e_upload = null;

    public function seed(): void
    {
        $n = KitService::seed((int) Auth::id());
        $this->dispatch('notify', type: 'success', message: $n ? "Pronto: {$n} exemplos criados. Ajuste preços e fotos." : 'Os exemplos já estão aqui.');
    }

    // ─── Tipos ────────────────────────────────────────────────────────────────

    public function newType(?string $style = null): void
    {
        $this->resetErrorBag();
        $this->typeId = null;
        $this->t_style = isset(KitService::STYLES[$style]) ? $style : 'cesta';
        $this->t_name = $style ? KitService::style($style)[0] : '';
        $this->t_description = '';
        $this->t_base_price = '';
        $this->t_min = '1';
        $this->t_max = '6';
        $this->t_cats = [];
        $this->t_active = true;
        $this->t_image = null;
        $this->t_upload = null;
        $this->t_options = array_map(fn ($k) => KitService::optionIdea($k), KitService::STARTER_OPTIONS[$this->t_style] ?? ['laco']);
        $this->t_photos = in_array($this->t_style, ['balao', 'caneca'], true) ? 4 : 0;
        $this->t_date = true;
        $this->typeForm = true;
    }

    public function editType(int $id): void
    {
        if (! $t = $this->type($id)) {
            return;
        }
        $this->resetErrorBag();
        $this->typeId = $t->id;
        $this->t_name = $t->name;
        $this->t_style = $t->style;
        $this->t_description = (string) $t->description;
        $this->t_base_price = $this->num($t->base_price);
        $this->t_min = (string) $t->min_items;
        $this->t_max = (string) $t->max_items;
        $this->t_cats = array_map('strval', (array) ($t->category_ids ?? []));
        $this->t_active = (bool) $t->active;
        $this->t_image = $t->image;
        $this->t_upload = null;
        $this->t_options = array_map(fn ($o) => $o + ['required' => true], KitService::options($t));
        $this->t_photos = (int) $t->setting('photos', 0);
        $this->t_date = (bool) $t->setting('date', true);
        $this->typeForm = true;
    }

    public function addOption(?string $idea = null): void
    {
        if (count($this->t_options) >= 6) {
            return;
        }
        $this->t_options[] = $idea && isset(KitService::OPTION_IDEAS[$idea])
            ? KitService::optionIdea($idea)
            : ['name' => '', 'required' => true, 'choices' => [['label' => '', 'price' => 0, 'color' => null]]];
    }

    public function removeOption(int $i): void
    {
        unset($this->t_options[$i]);
        $this->t_options = array_values($this->t_options);
    }

    public function addChoice(int $i): void
    {
        if (isset($this->t_options[$i]) && count($this->t_options[$i]['choices']) < 12) {
            $this->t_options[$i]['choices'][] = ['label' => '', 'price' => 0, 'color' => null];
        }
    }

    public function removeChoice(int $i, int $j): void
    {
        unset($this->t_options[$i]['choices'][$j]);
        $this->t_options[$i]['choices'] = array_values($this->t_options[$i]['choices'] ?? []);
    }

    public function updatedTUpload(): void
    {
        $this->validate(['t_upload' => ['image', 'max:4096']], [], ['t_upload' => 'foto']);
    }

    public function saveType(): void
    {
        $this->t_base_price = str_replace(',', '.', trim($this->t_base_price)) ?: '0';
        $data = $this->validate([
            't_name' => ['required', 'string', 'max:80'],
            't_style' => ['required', 'in:' . implode(',', array_keys(KitService::STYLES))],
            't_description' => ['nullable', 'string', 'max:400'],
            't_base_price' => ['required', 'numeric', 'min:0', 'max:99999'],
            't_min' => ['required', 'integer', 'min:1', 'max:50'],
            't_max' => ['required', 'integer', 'min:1', 'max:50', 'gte:t_min'],
            't_upload' => ['nullable', 'image', 'max:4096'],
        ], ['t_max.gte' => 'O máximo tem que ser igual ou maior que o mínimo.'],
            ['t_name' => 'nome', 't_base_price' => 'preço da base', 't_min' => 'mínimo', 't_max' => 'máximo']);

        $image = $this->t_image;
        if ($this->t_upload) {
            $image = $this->t_upload->store('portal-kits', 'public');
            $this->drop($this->t_image);
        }
        $row = [
            'name' => $data['t_name'], 'style' => $data['t_style'], 'description' => $data['t_description'] ?: null,
            'base_price' => $data['t_base_price'], 'min_items' => (int) $data['t_min'], 'max_items' => (int) $data['t_max'],
            'category_ids' => array_values(array_map('intval', array_filter($this->t_cats))) ?: null,
            'active' => $this->t_active, 'image' => $image,
            'options' => array_values(array_map(fn ($o) => [
                'name' => mb_substr(trim((string) ($o['name'] ?? '')), 0, 40),
                'required' => (bool) ($o['required'] ?? true),
                'choices' => array_values(array_map(fn ($c) => [
                    'label' => mb_substr(trim((string) ($c['label'] ?? '')), 0, 40),
                    'price' => max(0, round((float) str_replace(',', '.', (string) ($c['price'] ?? 0)), 2)),
                    'color' => preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($c['color'] ?? '')) ? $c['color'] : null,
                ], array_filter($o['choices'] ?? [], fn ($c) => trim((string) ($c['label'] ?? '')) !== ''))),
            ], array_filter($this->t_options, fn ($o) => trim((string) ($o['name'] ?? '')) !== ''))),
            'settings' => ['photos' => max(0, min(6, $this->t_photos)), 'date' => $this->t_date],
        ];
        if ($this->typeId && ($t = $this->type($this->typeId))) {
            $t->update($row);
        } else {
            PortalKitType::create($row + ['user_id' => Auth::id(), 'position' => (int) PortalKitType::where('user_id', Auth::id())->max('position') + 1]);
        }
        $this->typeForm = false;
        $this->dispatch('notify', type: 'success', message: 'Kit "' . $data['t_name'] . '" salvo.');
    }

    public function removeTypeImage(): void
    {
        if ($this->typeId && ($t = $this->type($this->typeId))) {
            $this->drop($t->image);
            $t->update(['image' => null]);
        }
        $this->t_image = null;
        $this->t_upload = null;
    }

    public function toggleType(int $id): void
    {
        if ($t = $this->type($id)) {
            $t->update(['active' => ! $t->active]);
        }
    }

    public function deleteType(int $id): void
    {
        if ($t = $this->type($id)) {
            $this->drop($t->image);
            $t->delete();
            $this->dispatch('notify', type: 'success', message: 'Kit "' . $t->name . '" excluído.');
        }
    }

    public function moveType(int $id, int $dir): void
    {
        $this->move(KitService::types((int) Auth::id(), false), $id, $dir);
    }

    // ─── Enfeites ─────────────────────────────────────────────────────────────

    public function newExtra(?string $group = null): void
    {
        $this->resetErrorBag();
        $this->extraId = null;
        $this->e_name = '';
        $this->e_group = isset(KitService::GROUPS[$group]) ? $group : 'balao';
        $this->e_description = '';
        $this->e_price = '';
        $this->e_max = '5';
        $this->e_active = true;
        $this->e_image = null;
        $this->e_upload = null;
        $this->extraForm = true;
    }

    public function editExtra(int $id): void
    {
        if (! $e = $this->extra($id)) {
            return;
        }
        $this->resetErrorBag();
        $this->extraId = $e->id;
        $this->e_name = $e->name;
        $this->e_group = $e->group;
        $this->e_description = (string) $e->description;
        $this->e_price = $this->num($e->price);
        $this->e_max = (string) $e->max_qty;
        $this->e_active = (bool) $e->active;
        $this->e_image = $e->image;
        $this->e_upload = null;
        $this->extraForm = true;
    }

    public function updatedEUpload(): void
    {
        $this->validate(['e_upload' => ['image', 'max:4096']], [], ['e_upload' => 'foto']);
    }

    public function saveExtra(): void
    {
        $this->e_price = str_replace(',', '.', trim($this->e_price));
        $data = $this->validate([
            'e_name' => ['required', 'string', 'max:80'],
            'e_group' => ['required', 'in:' . implode(',', array_keys(KitService::GROUPS))],
            'e_description' => ['nullable', 'string', 'max:200'],
            'e_price' => ['required', 'numeric', 'min:0', 'max:99999'],
            'e_max' => ['required', 'integer', 'min:1', 'max:99'],
            'e_upload' => ['nullable', 'image', 'max:4096'],
        ], [], ['e_name' => 'nome', 'e_price' => 'preço', 'e_max' => 'máximo por kit']);

        $image = $this->e_image;
        if ($this->e_upload) {
            $image = $this->e_upload->store('portal-kits/enfeites', 'public');
            $this->drop($this->e_image);
        }
        $row = [
            'name' => $data['e_name'], 'group' => $data['e_group'], 'description' => $data['e_description'] ?: null,
            'price' => $data['e_price'], 'max_qty' => (int) $data['e_max'], 'active' => $this->e_active, 'image' => $image,
        ];
        if ($this->extraId && ($e = $this->extra($this->extraId))) {
            $e->update($row);
        } else {
            PortalKitExtra::create($row + ['user_id' => Auth::id(), 'position' => (int) PortalKitExtra::where('user_id', Auth::id())->max('position') + 1]);
        }
        $this->extraForm = false;
        $this->dispatch('notify', type: 'success', message: '"' . $data['e_name'] . '" salvo.');
    }

    public function removeExtraImage(): void
    {
        if ($this->extraId && ($e = $this->extra($this->extraId))) {
            $this->drop($e->image);
            $e->update(['image' => null]);
        }
        $this->e_image = null;
        $this->e_upload = null;
    }

    public function toggleExtra(int $id): void
    {
        if ($e = $this->extra($id)) {
            $e->update(['active' => ! $e->active]);
        }
    }

    public function deleteExtra(int $id): void
    {
        if ($e = $this->extra($id)) {
            $this->drop($e->image);
            $e->delete();
            $this->dispatch('notify', type: 'success', message: '"' . $e->name . '" excluído.');
        }
    }

    public function moveExtra(int $id, int $dir): void
    {
        $this->move(KitService::extras((int) Auth::id(), false), $id, $dir);
    }

    // ─── Apoio ────────────────────────────────────────────────────────────────

    private function move($rows, int $id, int $dir): void
    {
        $list = $rows->values();
        $i = $list->search(fn ($r) => $r->id === $id);
        $j = $i === false ? false : $i + ($dir < 0 ? -1 : 1);
        if ($i === false || $j < 0 || $j >= $list->count()) {
            return;
        }
        $ids = $list->pluck('id')->all();
        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
        foreach ($ids as $pos => $rid) {
            $list->firstWhere('id', $rid)->update(['position' => $pos + 1]);
        }
    }

    private function type(int $id): ?PortalKitType
    {
        return PortalKitType::where('user_id', Auth::id())->find($id);
    }

    private function extra(int $id): ?PortalKitExtra
    {
        return PortalKitExtra::where('user_id', Auth::id())->find($id);
    }

    private function drop(?string $path): void
    {
        if ($path && str_starts_with($path, 'portal-kits')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function num($v): string
    {
        return number_format((float) $v, 2, ',', '');
    }

    public function render()
    {
        $uid = (int) Auth::id();
        $types = KitService::ready() ? KitService::types($uid, false) : collect();
        $extras = KitService::ready() ? KitService::extras($uid, false) : collect();
        $orders = \Illuminate\Support\Facades\Schema::hasColumn('client_quote_requests', 'kit')
            ? ClientQuoteRequest::where('user_id', $uid)->whereNotNull('kit')->where('status', '!=', 'rejected')->count() : 0;

        return view('livewire.promotions.portal-kits', [
            'types' => $types,
            'extras' => $extras,
            'kitOrders' => $orders,
            'cats' => Category::where('user_id', $uid)->whereIn('id_category', StorefrontService::categoryCounts($uid)->keys())->orderBy('name')->get(['id_category', 'name']),
            'builderUrl' => route('portal.kit', ['userId' => $uid]),
            'catalogUrl' => route('portal.catalog', ['userId' => $uid]),
        ]);
    }
}
