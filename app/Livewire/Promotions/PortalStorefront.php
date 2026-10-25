<?php

namespace App\Livewire\Promotions;

use App\Models\Category;
use App\Models\PortalBanner;
use App\Models\PortalCollection;
use App\Models\PortalSetting;
use App\Models\Product;
use App\Services\Portal\HomeLayout;
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

    public string $tab = 'inicio';

    // Página inicial (montagem): ordem dos blocos, escondidos, cores [topo 1, topo 2, principal], cantos e tema
    public array $home_order = [];
    public array $home_off = [];
    public array $home_colors = [];
    public string $home_style = 'padrao';
    public string $home_theme = 'padrao';
    public string $pendingTheme = '';
    public bool $withBanner = true;
    public bool $withNotice = true;
    public int $previewKey = 0;

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
    /** Filtro da janela: todos, vendidos, ofertas, novos, estoque */
    public string $pickFilter = 'todos';
    public int $pickCat = 0;

    protected $queryString = ['tab' => ['except' => 'inicio']];

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
        $this->loadHome($s);
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

    /* ---------- Página inicial ---------- */

    private function loadHome(?PortalSetting $s = null): void
    {
        $h = HomeLayout::get($s ?? PortalSetting::for(Auth::id()));
        $this->home_order = $h['order'];
        $this->home_off = $h['off'];
        $this->home_colors = $h['colors'];
        $this->home_style = $h['style'];
        $this->home_theme = $h['theme'];
    }

    private function saveHome(): void
    {
        if (! HomeLayout::ready()) {
            return;
        }
        HomeLayout::save(Auth::id(), [
            'theme' => $this->home_theme, 'order' => array_values($this->home_order), 'off' => array_values($this->home_off),
            'colors' => $this->home_colors, 'style' => $this->home_style,
        ]);
        $this->loadHome();
        $this->previewKey++;
    }

    public function moveSection(string $key, int $dir): void
    {
        $i = array_search($key, $this->home_order, true);
        $j = $i === false ? false : $i + $dir;
        if ($j === false || $j < 0 || $j >= count($this->home_order)) {
            return;
        }
        [$this->home_order[$i], $this->home_order[$j]] = [$this->home_order[$j], $this->home_order[$i]];
        $this->saveHome();
    }

    public function toggleSection(string $key): void
    {
        if (! isset(HomeLayout::SECTIONS[$key])) {
            return;
        }
        $this->home_off = in_array($key, $this->home_off, true)
            ? array_values(array_diff($this->home_off, [$key]))
            : array_merge($this->home_off, [$key]);
        $this->saveHome();
    }

    public function setStyle(string $style): void
    {
        if (isset(HomeLayout::STYLES[$style])) {
            $this->home_style = $style;
            $this->saveHome();
        }
    }

    public function updatedHomeColors(): void
    {
        $this->home_colors = array_map(fn ($c, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $c) ? strtolower($c) : $d, array_values($this->home_colors) + HomeLayout::BASE, HomeLayout::BASE);
        $this->saveHome();
    }

    public function pickTheme(string $key): void
    {
        $this->pendingTheme = isset(HomeLayout::THEMES[$key]) && $this->pendingTheme !== $key ? $key : '';
    }

    public function applyTheme(): void
    {
        $key = $this->pendingTheme;
        if (! isset(HomeLayout::THEMES[$key]) || ! HomeLayout::ready()) {
            return;
        }
        $done = HomeLayout::apply(Auth::id(), $key, $this->kind, $this->withBanner, $this->withNotice);
        $s = PortalSetting::for(Auth::id());
        $this->featured_title = (string) $s->featured_title;
        $this->notice_text = (string) $s->notice_text;
        $this->notice_active = (bool) $s->notice_active;
        $this->loadHome($s);
        $this->pendingTheme = '';
        $this->previewKey++;
        $t = HomeLayout::THEMES[$key];
        $extra = array_filter([in_array('banner', $done, true) ? 'banner no topo' : null, in_array('aviso', $done, true) ? 'aviso' : null]);
        $this->dispatch('notify', type: 'success', message: 'Tema ' . $t['name'] . ' aplicado' . ($extra ? ' (com ' . implode(' e ', $extra) . ')' : '') . '. Mude o que quiser abaixo.');
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

    /** O catálogo mostra até este tanto na fileira. */
    public const FEATURED_MAX = 15;

    /** Mudou a lista: já fica salva (sem botão de salvar). */
    private function persistFeatured(bool $quiet = true): void
    {
        $this->featuredIds = array_values(array_unique(array_map('intval', $this->featuredIds)));
        $s = PortalSetting::for(Auth::id());
        $s->featured_ids = $this->featuredIds;
        $s->save();
        if (! $quiet) {
            $this->dispatch('notify', type: 'success', message: 'Destaques salvos.');
        }
    }

    public function saveFeatured(): void
    {
        $this->validate(['featured_title' => ['nullable', 'string', 'max:60']], [], ['featured_title' => 'título']);
        $s = PortalSetting::for(Auth::id());
        $s->featured_title = trim($this->featured_title) ?: null;
        $s->save();
        $this->persistFeatured(false);
    }

    public function updatedFeaturedTitle(): void
    {
        $this->validate(['featured_title' => ['nullable', 'string', 'max:60']], [], ['featured_title' => 'título']);
        $s = PortalSetting::for(Auth::id());
        $s->featured_title = trim($this->featured_title) ?: null;
        $s->save();
    }

    public function moveFeatured(int $index, int $dir): void
    {
        $this->featuredIds = $this->moved($this->featuredIds, $index, $dir);
        $this->persistFeatured();
    }

    public function removeFeatured(int $id): void
    {
        $this->featuredIds = array_values(array_filter($this->featuredIds, fn ($v) => (int) $v !== $id));
        $this->persistFeatured();
    }

    public function clearFeatured(): void
    {
        $this->featuredIds = [];
        $this->persistFeatured(false);
    }

    /** Completa a fileira com os mais vendidos, as promoções ou os novos (sem repetir). */
    public function fillFeatured(string $src): void
    {
        if (! in_array($src, ['vendidos', 'ofertas', 'novos'], true)) {
            return;
        }
        $room = self::FEATURED_MAX - count($this->featuredIds);
        $add = array_values(array_diff(StorefrontService::ruleRoots(Auth::id(), $src, [], self::FEATURED_MAX * 2), $this->featuredIds));
        if ($room <= 0 || ! $add) {
            $this->dispatch('notify', type: 'info', message: $room <= 0 ? 'A fileira já está cheia (' . self::FEATURED_MAX . ' produtos).' : 'Nenhum produto novo para colocar.');

            return;
        }
        $add = array_slice($add, 0, $room);
        $this->featuredIds = array_merge($this->featuredIds, $add);
        $this->persistFeatured();
        $this->dispatch('notify', type: 'success', message: count($add) === 1 ? '1 produto colocado nos destaques.' : count($add) . ' produtos colocados nos destaques.');
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
        $this->pickFilter = 'todos';
        $this->pickCat = 0;
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
        $list = array_map('intval', $this->featuredIds);
        if (in_array($root, $list, true)) {
            $this->featuredIds = array_values(array_filter($list, fn ($v) => $v !== $root));
        } elseif (count($list) >= self::FEATURED_MAX) {
            $this->dispatch('notify', type: 'warning', message: 'O catálogo mostra até ' . self::FEATURED_MAX . ' destaques. Tire um para colocar outro.');

            return;
        } else {
            $this->featuredIds = array_merge($list, [$root]);
        }
        $this->persistFeatured();
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
        $found = Product::where('user_id', Auth::id())->whereIn('id', $ids)->with('activePromotion')->get()->keyBy('id');
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
            $ruled = in_array($this->pickFilter, ['vendidos', 'ofertas', 'novos'], true)
                ? StorefrontService::ruleRoots($uid, $this->pickFilter, [], 60) : null;
            $results = Product::where('user_id', $uid)
                ->whereNull('parent_id')
                ->whereIn('status', ['active', 'ativo'])
                ->with('activePromotion')
                ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('product_code', 'like', "%{$term}%")))
                ->when($ruled !== null, fn ($q) => $q->whereIn('id', $ruled ?: [0]))
                ->when($this->pickFilter === 'estoque', fn ($q) => $q->where(fn ($w) => $w->where('stock_quantity', '>', 0)->orWhere('made_to_order', true)))
                ->when($this->pickCat > 0, fn ($q) => $q->where('category_id', $this->pickCat))
                ->when($ruled === null, fn ($q) => $q->orderByRaw('CASE WHEN stock_quantity > 0 OR made_to_order = 1 THEN 0 ELSE 1 END')->orderBy('name'))
                ->limit(40)->get();
            if ($ruled) {
                $results = $results->sortBy(fn ($p) => array_search($p->id, $ruled, true))->values();
            }
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
            'pickCats'      => $this->pickFor !== '' ? \App\Models\Category::where('user_id', $uid)->whereIn('id_category', StorefrontService::categoryCounts($uid)->keys())->orderBy('name')->get(['id_category', 'name']) : collect(),
            'catalogUrl'    => route('portal.catalog', ['userId' => $uid]),
            'themes'        => $this->tab === 'inicio' ? HomeLayout::themes($this->kind) : [],
            'secInfo'       => $this->tab === 'inicio' ? $this->sectionInfo($uid, $banners, $collections) : [],
            'navInfo'       => $this->navInfo($banners, $collections),
        ]);
    }

    /** Situação de cada parte no menu das configurações. */
    private function navInfo($banners, $collections): array
    {
        $today = now()->startOfDay();
        $live = $banners->flatten()->filter(fn ($b) => $b->active && (! $b->ends_at || $today->lte($b->ends_at)) && (! $b->starts_at || $today->gte($b->starts_at)))->count();
        $n = fn ($v, $one, $many) => $v === 1 ? "1 {$one}" : "{$v} {$many}";
        $theme = HomeLayout::THEMES[$this->home_theme]['name'] ?? null;

        return [
            'inicio'    => $theme ? 'Tema: ' . $theme : null,
            'banners'   => $n($live, 'banner no ar', 'banners no ar'),
            'destaques' => $n(count($this->featuredIds), 'produto escolhido', 'produtos escolhidos'),
            'colecoes'  => $n($collections->where('active', true)->count(), 'coleção ligada', 'coleções ligadas'),
            'aviso'     => $this->notice_active && trim($this->notice_text) !== '' ? 'Ligado' : 'Desligado',
        ];
    }

    /** Uma linha de cada bloco: quanto tem e onde muda. */
    private function sectionInfo(int $uid, $banners, $collections): array
    {
        $live = fn ($place) => ($banners[$place] ?? collect())->filter(fn ($b) => $b->active && (! $b->ends_at || now()->startOfDay()->lte($b->ends_at)) && (! $b->starts_at || now()->startOfDay()->gte($b->starts_at)))->count();
        $home = $collections->where('active', true)->where('show_on_home', true)->count();
        $offers = \App\Models\Promotion::where('user_id', $uid)->where('status', \App\Models\Promotion::ATIVA)->count();
        $n = fn ($v, $one, $many) => $v === 1 ? "1 {$one}" : "{$v} {$many}";

        return [
            'hero'        => [$n($live('hero'), 'banner no ar', 'banners no ar'), 'banners'],
            'cats'        => [$n(\App\Models\Category::where('user_id', $uid)->whereIn('id_category', StorefrontService::categoryCounts($uid)->keys())->count(), 'categoria', 'categorias'), null],
            'featured'    => [$n(count($this->featuredIds), 'produto escolhido', 'produtos escolhidos'), 'destaques'],
            'offers'      => [$n($offers, 'promoção ativa', 'promoções ativas'), null],
            'collections' => [$n($home, 'coleção', 'coleções') . ' e ' . $n($live('strip'), 'faixa', 'faixas'), 'colecoes'],
            'recent'      => ['Só aparece para quem já abriu algum produto', null],
        ];
    }
}
