<?php

namespace App\Livewire\Promotions;

use App\Models\Category;
use App\Models\PortalBanner;
use App\Models\PortalCollection;
use App\Models\Product;
use App\Services\Portal\StorefrontService;
use App\Services\Promotions\StoreKind;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Página de criar e editar uma coleção da vitrine: produtos escolhidos ou uma
 * regra que se atualiza sozinha, e a capa (modelo de banner com as fotos da
 * coleção, imagem própria ou só cor), com a prévia de como fica no catálogo.
 */
class CollectionEditor extends Component
{
    use WithFileUploads;

    public ?int $colId = null;
    public string $kind = 'beleza';
    public string $c_name = '';
    public string $c_description = '';
    public string $c_color = 'violet';
    public bool $c_active = true;
    public bool $c_home = true;
    public $c_image = null;
    public ?string $c_image_old = null;
    public array $c_products = [];

    // Regra (coleção automática)
    public string $rule = 'manual';
    public array $r_cats = [];
    public string $r_min = '';
    public string $r_max = '';
    public string $r_term = '';
    public int $r_limit = 15;

    // Capa: modelo de banner, imagem própria ou cor
    public string $cover = 'modelo';
    public string $d_tpl = 'chegaram';
    public string $d_badge = '';
    public string $d_c1 = '#ffffff';
    public string $d_c2 = '#f5f3ff';
    public string $d_acc = '#7c3aed';
    public string $d_ink = 'dark';
    public string $d_anim = 'fotos';

    public string $pickFor = '';
    public string $pickSearch = '';

    public function mount(?int $collection = null): void
    {
        $this->kind = StoreKind::for(Auth::id());
        if ($collection) {
            $c = PortalCollection::where('user_id', Auth::id())->findOrFail($collection);
            $this->colId = $c->id;
            $this->c_name = $c->name;
            $this->c_description = (string) $c->description;
            $this->c_color = $c->color;
            $this->c_active = (bool) $c->active;
            $this->c_home = (bool) $c->show_on_home;
            $this->c_image_old = $c->image;
            $this->c_products = $c->productIds();
            $this->rule = array_key_exists((string) $c->rule, StorefrontService::COLLECTION_RULES) ? $c->rule : 'manual';
            $data = $c->rule_data ?? [];
            $this->r_cats = array_map('intval', (array) ($data['cats'] ?? []));
            $this->r_min = isset($data['min']) ? (string) $data['min'] : '';
            $this->r_max = isset($data['max']) ? (string) $data['max'] : '';
            $this->r_term = (string) ($data['term'] ?? '');
            $this->r_limit = (int) (($data['limit'] ?? null) ?: 15);
            if ($c->image) {
                $this->cover = 'imagem';
            } elseif ($c->hasDesign()) {
                $d = $c->design;
                $this->cover = 'modelo';
                $this->d_tpl = array_key_exists($d['tpl'], StorefrontService::TEMPLATES) ? $d['tpl'] : 'chegaram';
                $this->d_badge = (string) ($d['badge'] ?? '');
                [$this->d_c1, $this->d_c2, $this->d_acc] = [$d['c1'] ?? '#ffffff', $d['c2'] ?? '#f5f3ff', $d['acc'] ?? '#7c3aed'];
                $this->d_ink = ($d['ink'] ?? 'dark') === 'light' ? 'light' : 'dark';
                $this->d_anim = array_key_exists($d['anim'] ?? '', StorefrontService::ANIMATIONS) ? $d['anim'] : 'none';
            } else {
                $this->cover = 'cor';
            }

            return;
        }
        $idea = request()->query('ideia');
        if ($idea !== null && ($i = StorefrontService::COLLECTION_IDEAS[$this->kind][(int) $idea] ?? null)) {
            $this->useIdea((int) $idea);
        } else {
            $this->setCoverTemplate('chegaram');
        }
    }

    /** Começa por uma ideia pronta (nome, frase, regra e capa). */
    public function useIdea(int $i): void
    {
        $idea = StorefrontService::COLLECTION_IDEAS[$this->kind][$i] ?? null;
        if (! $idea) {
            return;
        }
        [$name, $desc, $rule, $data, $tpl] = $idea;
        $this->c_name = $name;
        $this->c_description = $desc;
        $this->rule = $rule;
        $this->r_term = (string) ($data['term'] ?? '');
        $this->r_min = isset($data['min']) ? (string) $data['min'] : '';
        $this->r_max = isset($data['max']) ? (string) $data['max'] : '';
        $this->cover = 'modelo';
        $this->setCoverTemplate($tpl);
        $this->resetErrorBag();
    }

    public function setRule(string $rule): void
    {
        $this->rule = array_key_exists($rule, StorefrontService::COLLECTION_RULES) ? $rule : 'manual';
        $this->resetErrorBag();
    }

    public function toggleCat(int $id): void
    {
        $this->r_cats = in_array($id, $this->r_cats, true)
            ? array_values(array_diff($this->r_cats, [$id]))
            : array_merge($this->r_cats, [$id]);
    }

    public function setCover(string $mode): void
    {
        $this->cover = in_array($mode, ['modelo', 'imagem', 'cor'], true) ? $mode : 'modelo';
    }

    public function setCoverTemplate(string $key): void
    {
        if ($t = StorefrontService::template($key, $this->kind)) {
            $this->d_tpl = $key;
            [$this->d_c1, $this->d_c2, $this->d_acc, $this->d_ink] = [$t['c1'], $t['c2'], $t['acc'], $t['ink']];
        }
    }

    public function setPalette(int $i): void
    {
        if ($c = StorefrontService::PALETTES[$i] ?? null) {
            [$this->d_c1, $this->d_c2, $this->d_acc, $this->d_ink] = $c;
        }
    }

    public function setAnim(string $anim): void
    {
        $this->d_anim = array_key_exists($anim, StorefrontService::ANIMATIONS) ? $anim : 'none';
    }

    public function removeImage(): void
    {
        $this->c_image = null;
        $this->c_image_old = null;
    }

    public function moveProduct(int $index, int $dir): void
    {
        $ids = array_values($this->c_products);
        $to = $index + ($dir < 0 ? -1 : 1);
        if (isset($ids[$index], $ids[$to])) {
            [$ids[$index], $ids[$to]] = [$ids[$to], $ids[$index]];
            $this->c_products = $ids;
        }
    }

    public function removeProduct(int $id): void
    {
        $this->c_products = array_values(array_filter($this->c_products, fn ($v) => (int) $v !== $id));
    }

    public function openPicker(): void
    {
        $this->pickFor = 'collection';
        $this->pickSearch = '';
    }

    public function closePicker(): void
    {
        $this->pickFor = '';
    }

    public function pick(int $id): void
    {
        $root = StorefrontService::rootsOf(Auth::id(), [$id])[0] ?? null;
        if (! $root) {
            return;
        }
        $list = array_map('intval', $this->c_products);
        $this->c_products = in_array($root, $list, true)
            ? array_values(array_filter($list, fn ($v) => $v !== $root))
            : array_merge($list, [$root]);
    }

    private function ruleData(): array
    {
        return array_filter([
            'cats'  => $this->rule === 'categoria' ? array_values(array_map('intval', $this->r_cats)) : null,
            'min'   => $this->rule === 'preco' && $this->r_min !== '' ? (float) str_replace(',', '.', $this->r_min) : null,
            'max'   => $this->rule === 'preco' && $this->r_max !== '' ? (float) str_replace(',', '.', $this->r_max) : null,
            'term'  => $this->rule === 'busca' ? trim($this->r_term) : null,
            'limit' => max(4, min(30, $this->r_limit)),
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    public function save()
    {
        $this->validate([
            'c_name'        => ['required', 'string', 'max:80'],
            'c_description' => ['nullable', 'string', 'max:200'],
            'c_color'       => ['required', 'in:' . implode(',', array_keys(StorefrontService::COLORS))],
            'c_image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'c_products'    => array_filter(['array', $this->rule === 'manual' ? 'min:1' : null]),
            'rule'          => ['required', 'in:' . implode(',', array_keys(StorefrontService::COLLECTION_RULES))],
            'r_cats'        => array_filter(['array', $this->rule === 'categoria' ? 'min:1' : null]),
            'r_term'        => array_filter(['nullable', 'string', 'max:60', $this->rule === 'busca' ? 'required' : null]),
            'r_min'         => ['nullable', 'regex:/^\d+([.,]\d{1,2})?$/'],
            'r_max'         => ['nullable', 'regex:/^\d+([.,]\d{1,2})?$/'],
            'r_limit'       => ['integer', 'min:4', 'max:30'],
            'd_tpl'         => ['required', 'in:' . implode(',', array_keys(StorefrontService::TEMPLATES))],
            'd_badge'       => ['nullable', 'string', 'max:30'],
            'd_c1'          => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'd_c2'          => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'd_acc'         => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'd_ink'         => ['required', 'in:light,dark'],
            'd_anim'        => ['required', 'in:' . implode(',', array_keys(StorefrontService::ANIMATIONS))],
        ], [
            'c_products.min' => 'Escolha pelo menos um produto.',
            'r_cats.min'     => 'Escolha pelo menos uma categoria.',
            'r_term.required'=> 'Escreva a palavra (ex.: perfume, mesa).',
            'r_min.regex'    => 'Use só números, ex.: 49,90',
            'r_max.regex'    => 'Use só números, ex.: 149,90',
            'c_image.max'    => 'A imagem pode ter até 5 MB.',
        ], ['c_name' => 'nome', 'c_image' => 'imagem']);
        if ($this->rule === 'preco' && $this->r_min === '' && $this->r_max === '') {
            $this->addError('r_max', 'Coloque o menor, o maior preço ou os dois.');

            return null;
        }
        if ($this->cover === 'imagem' && ! $this->c_image && ! $this->c_image_old) {
            $this->addError('c_image', 'Envie a imagem da capa ou escolha outra capa.');

            return null;
        }

        $uid = Auth::id();
        $old = $this->colId ? PortalCollection::where('user_id', $uid)->find($this->colId) : null;
        $image = $this->cover === 'imagem' ? ($this->c_image ? $this->c_image->store('portal-banners', 'public') : $this->c_image_old) : null;
        $row = [
            'name'         => trim($this->c_name),
            'slug'         => $this->uniqueSlug(trim($this->c_name), $old?->id),
            'description'  => trim($this->c_description) ?: null,
            'color'        => $this->c_color,
            'image'        => $image,
            'active'       => $this->c_active,
            'show_on_home' => $this->c_home,
            'rule'         => $this->rule,
            'rule_data'    => $this->ruleData(),
            'design'       => $this->cover === 'modelo' ? [
                'tpl' => $this->d_tpl, 'badge' => trim($this->d_badge) ?: null, 'c1' => strtolower($this->d_c1), 'c2' => strtolower($this->d_c2),
                'acc' => strtolower($this->d_acc), 'ink' => $this->d_ink, 'anim' => $this->d_anim,
            ] : null,
        ];
        if ($old) {
            if ($old->image && $old->image !== $image && str_starts_with($old->image, 'portal-banners/')) {
                Storage::disk('public')->delete($old->image);
            }
            // Banners que levam a esta coleção acompanham o novo nome.
            if ($old->slug !== $row['slug']) {
                PortalBanner::where('user_id', $uid)->where('link_type', 'collection')->where('link_value', $old->slug)->update(['link_value' => $row['slug']]);
            }
            $old->update($row);
            $c = $old;
        } else {
            $row['sort'] = (int) PortalCollection::where('user_id', $uid)->max('sort') + 1;
            $c = PortalCollection::create($row + ['user_id' => $uid]);
        }
        $c->syncProducts($this->rule === 'manual' ? $this->c_products : []);
        session()->flash('success', 'Coleção ' . $c->name . ' salva.');

        return $this->redirectRoute('promotions.storefront', ['tab' => 'colecoes'], navigate: false);
    }

    private function uniqueSlug(string $name, ?int $ignore): string
    {
        $base = Str::slug($name) ?: 'colecao';
        $slug = $base;
        $n = 2;
        while (PortalCollection::where('user_id', Auth::id())->where('slug', $slug)->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    public function render()
    {
        $uid = Auth::id();
        $roots = $this->rule === 'manual'
            ? array_map('intval', $this->c_products)
            : StorefrontService::ruleRoots($uid, $this->rule, $this->ruleData(), 60);
        $items = StorefrontService::productsFor($uid, $roots, 60);

        // Prévia da capa: uma coleção de mentira com o que está na tela.
        $previewCol = new PortalCollection([
            'name' => trim($this->c_name) ?: 'Nome da coleção', 'description' => trim($this->c_description) ?: null, 'color' => $this->c_color,
            'design' => $this->cover === 'modelo' ? ['tpl' => $this->d_tpl, 'badge' => trim($this->d_badge) ?: null, 'c1' => $this->d_c1, 'c2' => $this->d_c2,
                'acc' => $this->d_acc, 'ink' => $this->d_ink, 'anim' => $this->d_anim] : null,
        ]);
        $previewCol->setRelation('items', $items);

        $results = collect();
        if ($this->pickFor !== '') {
            $term = trim($this->pickSearch);
            $results = Product::where('user_id', $uid)->whereNull('parent_id')->whereIn('status', ['active', 'ativo'])
                ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('product_code', 'like', "%{$term}%")))
                ->orderByRaw('CASE WHEN stock_quantity > 0 OR made_to_order = 1 THEN 0 ELSE 1 END')
                ->orderBy('name')->limit(30)->get();
        }

        return view('livewire.promotions.collection-editor', [
            'items'      => $items,
            'manualList' => $this->rule === 'manual' ? $this->manualProducts() : collect(),
            'previewCol' => $previewCol,
            'templates'  => StorefrontService::templates($this->kind),
            'ideas'      => StorefrontService::COLLECTION_IDEAS[$this->kind] ?? [],
            'categories' => $this->rule === 'categoria'
                ? Category::where('user_id', $uid)->whereIn('id_category', Product::where('user_id', $uid)->whereNotNull('category_id')->distinct()->pluck('category_id'))->orderBy('name')->get(['id_category', 'name'])
                : collect(),
            'results'    => $results,
            'picked'     => array_map('intval', $this->c_products),
        ]);
    }

    /** Produtos escolhidos (com aviso de esgotado) para a lista da coleção manual. */
    private function manualProducts()
    {
        $ids = array_values(array_unique(array_map('intval', $this->c_products)));
        if (! $ids) {
            return collect();
        }
        $found = Product::where('user_id', Auth::id())->whereIn('id', $ids)->get()->keyBy('id');
        $stock = Product::where('user_id', Auth::id())
            ->where(fn ($q) => $q->whereIn('parent_id', $ids)->orWhereIn('id', $ids))
            ->whereIn('status', ['active', 'ativo'])
            ->get(['id', 'parent_id', 'stock_quantity', 'made_to_order'])
            ->groupBy(fn ($p) => (int) ($p->parent_id ?: $p->id))
            ->map(fn ($g) => $g->contains(fn ($p) => $p->made_to_order || $p->stock_quantity > 0));

        return collect($ids)->map(function ($id) use ($found, $stock) {
            $p = $found[$id] ?? null;
            $p?->setAttribute('sf_available', (bool) ($stock[$id] ?? false));

            return $p;
        })->filter()->values();
    }
}
