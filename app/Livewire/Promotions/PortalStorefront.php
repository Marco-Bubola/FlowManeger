<?php

namespace App\Livewire\Promotions;

use App\Models\Category;
use App\Models\PortalBanner;
use App\Models\PortalCollection;
use App\Models\PortalSetting;
use App\Models\Product;
use App\Services\Portal\StorefrontService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Vitrine do catálogo: banners (topo, faixa e laterais), produtos em destaque,
 * coleções com banner e o aviso no topo. Tudo aparece na página inicial do portal.
 */
class PortalStorefront extends Component
{
    use WithFileUploads;

    public string $tab = 'banners';

    /** Grupo aberto na fileira de modelos prontos. */
    public string $galleryGroup = 'datas';
    public string $kind = 'beleza';

    // Destaques
    public string $featured_title = '';
    public array $featuredIds = [];

    // Aviso no topo
    public string $notice_text = '';
    public string $notice_link = '';
    public bool $notice_active = false;

    // Escolher produtos (destaques, coleção ou link do banner)
    public string $pickFor = '';
    public string $pickSearch = '';

    protected $queryString = ['tab' => ['except' => 'banners']];

    public function mount()
    {
        // Link antigo "?modelo=": o banner agora tem página própria.
        if (($m = (string) request()->query('modelo')) && isset(StorefrontService::TEMPLATES[$m])) {
            return $this->redirectRoute('promotions.storefront.banner.create', ['modelo' => $m], navigate: false);
        }
        $this->kind = \App\Services\Promotions\StoreKind::for(Auth::id());
        $s = PortalSetting::for(Auth::id());
        $this->featured_title = (string) $s->featured_title;
        $this->featuredIds = array_values(array_map('intval', $s->featured_ids ?? []));
        $this->notice_text = (string) $s->notice_text;
        $this->notice_link = (string) $s->notice_link;
        $this->notice_active = (bool) $s->notice_active;
        // Uma coleção pronta para cada categoria, criada uma vez; as categorias novas pelo botão.
        if (\Illuminate\Support\Facades\Schema::hasColumn('portal_settings', 'home') && empty(($s->home ?? [])['cats_done'])) {
            $made = StorefrontService::createCategoryCollections(Auth::id(), $this->kind);
            $s->home = array_merge($s->home ?? [], ['cats_done' => true]);
            $s->save();
            if ($made) {
                session()->flash('success', $made === 1 ? 'Criamos 1 coleção para a sua categoria.' : 'Criamos ' . $made . ' coleções, uma para cada categoria.');
            }
        }
    }

    public function createCategoryCollections(): void
    {
        $made = StorefrontService::createCategoryCollections(Auth::id(), $this->kind);
        $this->dispatch('notify', type: 'success', message: $made === 1 ? '1 coleção criada.' : $made . ' coleções criadas.');
    }

    public function setKind(string $kind): void
    {
        $this->kind = \App\Services\Promotions\StoreKind::set(Auth::id(), $kind);
    }

    /** Produtos com foto para os modelos já abrirem bonitos (destaques primeiro). */
    private function samplePhotos(int $n): array
    {
        $uid = Auth::id();
        $real = fn ($q) => $q->whereNotNull('image')->where('image', '!=', 'product-placeholder.png')->where('image', 'not like', 'ml_%');
        $ids = collect($this->featuredIds)->map(fn ($v) => (int) $v);
        $withImg = $ids->isEmpty() ? collect() : Product::where('user_id', $uid)->whereIn('id', $ids)->where($real)->pluck('id');
        $ordered = $ids->filter(fn ($id) => $withImg->contains($id));
        if ($ordered->count() < $n) {
            $more = Product::where('user_id', $uid)->whereNull('parent_id')->whereIn('status', ['active', 'ativo'])->where($real)
                ->where(fn ($q) => $q->where('stock_quantity', '>', 0)->orWhere('made_to_order', true))
                ->whereNotIn('id', $ordered->all())->orderByDesc('id')->limit($n)->pluck('id');
            $ordered = $ordered->concat($more);
        }

        return $ordered->take($n)->map(fn ($id) => ['p' => (int) $id])->values()->all();
    }

    public function toggleBanner(int $id): void
    {
        if ($b = $this->banner($id)) {
            $b->update(['active' => ! $b->active]);
        }
    }

    public function moveBanner(int $id, int $dir): void
    {
        $b = $this->banner($id);
        if (! $b) {
            return;
        }
        $list = PortalBanner::where('user_id', Auth::id())->where('placement', $b->placement)->orderBy('sort')->orderBy('id')->get()->values();
        $this->swap($list, $list->search(fn ($x) => $x->id === $b->id), $dir);
    }

    public function deleteBanner(int $id): void
    {
        if ($b = $this->banner($id)) {
            $this->dropFiles(array_merge([$b->image, $b->image_mobile], $b->designFiles()));
            $b->delete();
            $this->dispatch('notify', type: 'success', message: 'Banner excluído.');
        }
    }

    /* ---------- Destaques ---------- */

    public function saveFeatured(): void
    {
        $this->validate(['featured_title' => ['nullable', 'string', 'max:60']], [], ['featured_title' => 'título']);
        $s = PortalSetting::for(Auth::id());
        $s->featured_title = trim($this->featured_title) ?: null;
        $s->featured_ids = array_values(array_unique(array_map('intval', $this->featuredIds)));
        $s->save();
        $this->dispatch('notify', type: 'success', message: 'Destaques salvos.');
    }

    public function moveFeatured(int $index, int $dir): void
    {
        $this->featuredIds = $this->moved($this->featuredIds, $index, $dir);
    }

    public function removeFeatured(int $id): void
    {
        $this->featuredIds = array_values(array_filter($this->featuredIds, fn ($v) => (int) $v !== $id));
    }

    /* ---------- Coleções ---------- */

    public function toggleCollection(int $id, string $field = 'active'): void
    {
        if (($c = $this->collection($id)) && in_array($field, ['active', 'show_on_home'], true)) {
            $c->update([$field => ! $c->{$field}]);
        }
    }

    public function moveCollection(int $id, int $dir): void
    {
        $list = PortalCollection::where('user_id', Auth::id())->orderBy('sort')->orderBy('id')->get()->values();
        $this->swap($list, $list->search(fn ($x) => $x->id === $id), $dir);
    }

    public function deleteCollection(int $id): void
    {
        if ($c = $this->collection($id)) {
            $this->dropFiles([$c->image]);
            $c->delete();
            $this->dispatch('notify', type: 'success', message: 'Coleção excluída.');
        }
    }

    /* ---------- Aviso no topo ---------- */

    public function saveNotice(): void
    {
        $this->validate([
            'notice_text' => array_filter(['nullable', 'string', 'max:160', $this->notice_active ? 'required' : null]),
            'notice_link' => ['nullable', 'url:http,https', 'max:255'],
        ], [
            'notice_text.required' => 'Escreva o aviso para ligar.',
            'notice_link.url' => 'Cole o endereço completo, começando com https://',
        ], ['notice_text' => 'aviso', 'notice_link' => 'link']);
        $s = PortalSetting::for(Auth::id());
        $s->notice_text = trim($this->notice_text) ?: null;
        $s->notice_link = trim($this->notice_link) ?: null;
        $s->notice_active = $this->notice_active;
        $s->save();
        $this->dispatch('notify', type: 'success', message: 'Aviso salvo.');
    }

    /* ---------- Escolher produtos ---------- */

    public function openPicker(string $for): void
    {
        $this->pickFor = $for === 'featured' ? $for : '';
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
        $prop = 'featuredIds';
        $list = array_map('intval', $this->{$prop});
        $this->{$prop} = in_array($root, $list, true)
            ? array_values(array_filter($list, fn ($v) => $v !== $root))
            : array_merge($list, [$root]);
    }

    /* ---------- Apoio ---------- */

    private function banner(int $id): ?PortalBanner
    {
        return PortalBanner::where('user_id', Auth::id())->find($id);
    }

    private function collection(int $id): ?PortalCollection
    {
        return PortalCollection::where('user_id', Auth::id())->find($id);
    }

    private function swap($list, $pos, int $dir): void
    {
        if ($pos === false) {
            return;
        }
        $to = $pos + ($dir < 0 ? -1 : 1);
        if ($to < 0 || $to >= $list->count()) {
            return;
        }
        $items = $list->all();
        [$items[$pos], $items[$to]] = [$items[$to], $items[$pos]];
        foreach (array_values($items) as $i => $m) {
            if ($m->sort !== $i) {
                $m->update(['sort' => $i]);
            }
        }
    }

    private function moved(array $ids, int $index, int $dir): array
    {
        $ids = array_values($ids);
        $to = $index + ($dir < 0 ? -1 : 1);
        if (! isset($ids[$index], $ids[$to])) {
            return $ids;
        }
        [$ids[$index], $ids[$to]] = [$ids[$to], $ids[$index]];

        return $ids;
    }

    /** Apaga do disco as imagens antigas que não estão mais em uso. */
    private function dropFiles(array $old, array $keep = []): void
    {
        foreach (array_filter($old) as $path) {
            if (! in_array($path, $keep, true) && str_starts_with($path, 'portal-banners/')) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /** Produtos (principal da família) para mostrar nas listas escolhidas. */
    private function productsById(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (! $ids) {
            return collect();
        }
        $found = Product::where('user_id', Auth::id())->whereIn('id', $ids)->get()->keyBy('id');
        // Estoque somado da família, para avisar o que está esgotado (e não aparece no catálogo).
        $stock = Product::where('user_id', Auth::id())
            ->where(fn ($q) => $q->whereIn('parent_id', $ids)->orWhereIn('id', $ids))
            ->whereIn('status', ['active', 'ativo'])
            ->get(['id', 'parent_id', 'stock_quantity', 'made_to_order'])
            ->groupBy(fn ($p) => (int) ($p->parent_id ?: $p->id))
            ->map(fn ($g) => $g->contains(fn ($p) => $p->made_to_order || $p->stock_quantity > 0));

        return collect($ids)->map(function ($id) use ($found, $stock) {
            $p = $found[$id] ?? null;
            if ($p) {
                $p->setAttribute('sf_available', (bool) ($stock[$id] ?? false));
            }

            return $p;
        })->filter()->values();
    }

    public function render()
    {
        $uid = Auth::id();
        $banners = PortalBanner::where('user_id', $uid)->orderBy('sort')->orderBy('id')->get()->groupBy('placement');
        $collections = PortalCollection::where('user_id', $uid)->orderBy('sort')->orderBy('id')->get();
        // Quantos produtos à venda cada coleção mostra agora (as automáticas pela regra).
        $counts = $this->tab === 'colecoes'
            ? $collections->mapWithKeys(fn ($c) => [$c->id => StorefrontService::productsFor($uid, $c->rootIds(60), 60)->count()])
            : collect();

        $results = collect();
        if ($this->pickFor !== '') {
            $term = trim($this->pickSearch);
            $results = Product::where('user_id', $uid)
                ->whereNull('parent_id')
                ->whereIn('status', ['active', 'ativo'])
                ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('product_code', 'like', "%{$term}%")))
                ->orderByRaw('CASE WHEN stock_quantity > 0 OR made_to_order = 1 THEN 0 ELSE 1 END')
                ->orderBy('name')
                ->limit(30)->get();
        }
        $picked = match ($this->pickFor) {
            'featured'   => array_map('intval', $this->featuredIds),
            default      => [],
        };

        // Fotos de exemplo para a fileira de modelos prontos.
        $samples = $this->tab === 'banners' ? PortalBanner::photoUrls($uid, $this->samplePhotos(3)) : [];
        $auto = [];
        if ($this->tab === 'banners') {
            foreach (['novos', 'vendidos', 'ofertas'] as $src) {
                $auto[$src] = PortalBanner::autoPhotoUrls($uid, $src, 3);
            }
        }

        return view('livewire.promotions.portal-storefront', [
            'samples'       => $samples,
            'auto'          => $auto,
            'banners'       => $banners,
            'collections'   => $collections,
            'counts'        => $counts,
            'missingCats'   => $this->tab === 'colecoes' ? StorefrontService::categoriesWithoutCollection($uid) : collect(),
            'featured'      => $this->productsById($this->featuredIds),
            'results'       => $results,
            'picked'        => $picked,
            'catalogUrl'    => route('portal.catalog', ['userId' => $uid]),
        ]);
    }
}
