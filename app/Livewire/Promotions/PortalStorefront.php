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

    // Banner
    public bool $showBanner = false;
    public ?int $bannerId = null;
    public string $b_placement = 'hero';
    public string $b_title = '';
    public string $b_subtitle = '';
    public string $b_button = '';
    public string $b_link_type = 'none';
    public string $b_link_value = '';
    public string $b_color = 'blue';
    public bool $b_active = true;
    public string $b_starts = '';
    public string $b_ends = '';
    public $b_image = null;
    public $b_image_mobile = null;
    public ?string $b_image_old = null;
    public ?string $b_mobile_old = null;
    // Modelo pronto (banner desenhado, tudo editável) ou imagem própria
    public string $b_mode = 'modelo';
    public string $b_tpl = 'oferta';
    public string $b_badge = '';
    public string $b_c1 = '#ff7733';
    public string $b_c2 = '#e11d48';
    public string $b_acc = '#fff7ed';
    public string $b_ink = 'light';
    public array $b_photos = [];
    public $b_photo = null;
    /** Fotos enviadas nesta janela e ainda não salvas (apagadas se cancelar). */
    public array $b_newFiles = [];

    // Destaques
    public string $featured_title = '';
    public array $featuredIds = [];

    // Coleção
    public bool $showCollection = false;
    public ?int $colId = null;
    public string $c_name = '';
    public string $c_description = '';
    public string $c_color = 'violet';
    public bool $c_active = true;
    public bool $c_home = true;
    public $c_image = null;
    public ?string $c_image_old = null;
    public array $c_products = [];

    // Aviso no topo
    public string $notice_text = '';
    public string $notice_link = '';
    public bool $notice_active = false;

    // Escolher produtos (destaques, coleção ou link do banner)
    public string $pickFor = '';
    public string $pickSearch = '';

    protected $queryString = ['tab' => ['except' => 'banners']];

    public function mount(): void
    {
        $s = PortalSetting::for(Auth::id());
        $this->featured_title = (string) $s->featured_title;
        $this->featuredIds = array_values(array_map('intval', $s->featured_ids ?? []));
        $this->notice_text = (string) $s->notice_text;
        $this->notice_link = (string) $s->notice_link;
        $this->notice_active = (bool) $s->notice_active;
    }

    /* ---------- Banners ---------- */

    public function newBanner(string $placement = 'hero', ?string $tpl = null): void
    {
        $this->resetBanner();
        $this->b_placement = array_key_exists($placement, PortalBanner::PLACEMENTS) ? $placement : 'hero';
        $this->b_mode = 'modelo';
        $this->b_tpl = '';
        $this->applyTemplate(array_key_exists((string) $tpl, StorefrontService::TEMPLATES) ? $tpl : 'oferta');
        $this->showBanner = true;
    }

    /**
     * Troca o modelo: cores e selo do modelo novo; os textos só mudam se ainda
     * estão vazios ou iguais aos do modelo anterior (o que você escreveu fica).
     */
    public function applyTemplate(string $key): void
    {
        $t = StorefrontService::TEMPLATES[$key] ?? null;
        if (! $t) {
            return;
        }
        $prev = StorefrontService::TEMPLATES[$this->b_tpl] ?? [];
        foreach (['b_badge' => 'badge', 'b_title' => 'title', 'b_subtitle' => 'subtitle', 'b_button' => 'button'] as $prop => $f) {
            $cur = trim($this->{$prop});
            if ($cur === '' || $cur === ($prev[$f] ?? null)) {
                $this->{$prop} = $t[$f];
            }
        }
        $this->b_tpl = $key;
        $this->b_c1 = $t['c1'];
        $this->b_c2 = $t['c2'];
        $this->b_acc = $t['acc'];
        $this->b_ink = $t['ink'];
        if ($this->b_link_type === 'none' && $t['link'] === 'offers') {
            $this->b_link_type = 'offers';
        }
        $max = $this->photoMax();
        if (count($this->b_photos) < $max) {
            $have = collect($this->b_photos)->pluck('p')->filter()->all();
            foreach ($this->samplePhotos($max + count($have)) as $ref) {
                if (count($this->b_photos) >= $max) {
                    break;
                }
                if (! in_array($ref['p'], $have, true)) {
                    $this->b_photos[] = $ref;
                }
            }
        }
    }

    public function setPalette(int $i): void
    {
        if ($c = StorefrontService::PALETTES[$i] ?? null) {
            [$this->b_c1, $this->b_c2, $this->b_acc, $this->b_ink] = $c;
        }
    }

    public function setMode(string $mode): void
    {
        $this->b_mode = $mode === 'imagem' ? 'imagem' : 'modelo';
        $this->resetErrorBag();
        // Texto de exemplo do modelo não vai por cima da arte própria.
        if ($this->b_mode === 'imagem' && ! $this->bannerId && ($t = StorefrontService::TEMPLATES[$this->b_tpl] ?? null)) {
            foreach (['b_title' => 'title', 'b_subtitle' => 'subtitle', 'b_button' => 'button'] as $prop => $f) {
                if (trim($this->{$prop}) === $t[$f]) {
                    $this->{$prop} = '';
                }
            }
        }
    }

    public function updatedBPlacement(): void
    {
        $this->b_photos = array_slice($this->b_photos, 0, max($this->photoMax(), 1));
    }

    public function updatedBPhoto(): void
    {
        $this->validate(['b_photo' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120']], ['b_photo.max' => 'A foto pode ter até 5 MB.'], ['b_photo' => 'foto']);
        $path = $this->b_photo->store('portal-banners', 'public');
        $this->b_photo = null;
        $this->b_newFiles[] = $path;
        $this->addPhoto(['f' => $path]);
    }

    public function removePhoto(int $index): void
    {
        $ph = $this->b_photos[$index] ?? null;
        if (! $ph) {
            return;
        }
        array_splice($this->b_photos, $index, 1);
        if (! empty($ph['f']) && in_array($ph['f'], $this->b_newFiles, true)) {
            $this->dropFiles([$ph['f']]);
            $this->b_newFiles = array_values(array_diff($this->b_newFiles, [$ph['f']]));
        }
    }

    public function movePhoto(int $index, int $dir): void
    {
        $this->b_photos = $this->moved($this->b_photos, $index, $dir);
    }

    public function closeBanner(): void
    {
        $this->dropFiles($this->b_newFiles);
        $this->b_newFiles = [];
        $this->showBanner = false;
        $this->pickFor = '';
    }

    /** Quantas fotos o modelo mostra (vitrine mostra 3; os outros, 1). */
    private function photoMax(): int
    {
        return $this->b_tpl === 'vitrine' ? 3 : 1;
    }

    private function addPhoto(array $ref): void
    {
        $max = $this->photoMax();
        if (count($this->b_photos) >= $max) {
            $out = array_splice($this->b_photos, $max - 1);
            foreach ($out as $o) {
                if (! empty($o['f']) && in_array($o['f'], $this->b_newFiles, true)) {
                    $this->dropFiles([$o['f']]);
                    $this->b_newFiles = array_values(array_diff($this->b_newFiles, [$o['f']]));
                }
            }
        }
        $this->b_photos[] = $ref;
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

    public function editBanner(int $id): void
    {
        $b = $this->banner($id);
        if (! $b) {
            return;
        }
        $this->resetBanner();
        $this->bannerId = $b->id;
        $this->b_placement = $b->placement;
        $this->b_title = (string) $b->title;
        $this->b_subtitle = (string) $b->subtitle;
        $this->b_button = (string) $b->button_text;
        $this->b_link_type = $b->link_type;
        $this->b_link_value = (string) $b->link_value;
        $this->b_color = $b->color;
        $this->b_active = (bool) $b->active;
        $this->b_starts = $b->starts_at?->format('Y-m-d') ?? '';
        $this->b_ends = $b->ends_at?->format('Y-m-d') ?? '';
        $this->b_image_old = $b->image;
        $this->b_mobile_old = $b->image_mobile;
        if ($b->isDesign()) {
            $d = $b->design;
            $t = StorefrontService::TEMPLATES[$d['tpl']] ?? StorefrontService::TEMPLATES['oferta'];
            $this->b_mode = 'modelo';
            $this->b_tpl = array_key_exists($d['tpl'], StorefrontService::TEMPLATES) ? $d['tpl'] : 'oferta';
            $this->b_badge = (string) ($d['badge'] ?? '');
            $this->b_c1 = $d['c1'] ?? $t['c1'];
            $this->b_c2 = $d['c2'] ?? $t['c2'];
            $this->b_acc = $d['acc'] ?? $t['acc'];
            $this->b_ink = ($d['ink'] ?? 'light') === 'dark' ? 'dark' : 'light';
            $this->b_photos = array_values(array_filter($d['photos'] ?? [], fn ($ph) => ! empty($ph['p']) || ! empty($ph['f'])));
        } else {
            $this->b_mode = 'imagem';
        }
        $this->showBanner = true;
    }

    public function updatedBLinkType(): void
    {
        $this->b_link_value = '';
    }

    public function removeBannerImage(string $which): void
    {
        if ($which === 'mobile') {
            $this->b_image_mobile = null;
            $this->b_mobile_old = null;
        } else {
            $this->b_image = null;
            $this->b_image_old = null;
        }
    }

    public function saveBanner(): void
    {
        $this->validate([
            'b_placement'    => ['required', 'in:' . implode(',', array_keys(PortalBanner::PLACEMENTS))],
            'b_title'        => ['nullable', 'string', 'max:120'],
            'b_subtitle'     => ['nullable', 'string', 'max:200'],
            'b_button'       => ['nullable', 'string', 'max:40'],
            'b_link_type'    => ['required', 'in:' . implode(',', array_keys(StorefrontService::LINK_TYPES))],
            'b_link_value'   => array_filter(['nullable', 'string', 'max:255', in_array($this->b_link_type, ['collection', 'category', 'product', 'url'], true) ? 'required' : null,
                $this->b_link_type === 'url' ? 'url:http,https' : null]),
            'b_color'        => ['required', 'in:' . implode(',', array_keys(StorefrontService::COLORS))],
            'b_starts'       => ['nullable', 'date'],
            'b_ends'         => array_filter(['nullable', 'date', $this->b_starts ? 'after_or_equal:b_starts' : null]),
            'b_image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'b_image_mobile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'b_mode'         => ['required', 'in:modelo,imagem'],
            'b_tpl'          => ['required', 'in:' . implode(',', array_keys(StorefrontService::TEMPLATES))],
            'b_badge'        => ['nullable', 'string', 'max:30'],
            'b_c1'           => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'b_c2'           => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'b_acc'          => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'b_ink'          => ['required', 'in:light,dark'],
        ], [
            'b_c1.regex' => 'Escolha a cor.', 'b_c2.regex' => 'Escolha a cor.', 'b_acc.regex' => 'Escolha a cor.',
            'b_link_value.required' => 'Escolha para onde o banner leva.',
            'b_link_value.url' => 'Cole o endereço completo, começando com https://',
            'b_ends.after_or_equal' => 'O fim tem que ser depois do começo.',
            'b_image.max' => 'A imagem pode ter até 5 MB.',
            'b_image_mobile.max' => 'A imagem pode ter até 5 MB.',
        ], ['b_image' => 'imagem', 'b_image_mobile' => 'imagem do celular', 'b_title' => 'título']);

        $modelo = $this->b_mode === 'modelo';
        $hasImage = $this->b_image || $this->b_image_old;
        if ($modelo && trim($this->b_title) === '' && trim($this->b_badge) === '') {
            $this->addError('b_title', 'Escreva um título para o banner.');

            return;
        }
        if (! $modelo && ! $hasImage && trim($this->b_title) === '') {
            $this->addError('b_image', 'Coloque uma imagem ou escreva um título.');

            return;
        }

        $old = $this->bannerId ? $this->banner($this->bannerId) : null;
        $design = null;
        if ($modelo) {
            $image = $mobile = null;
            $design = [
                'tpl' => $this->b_tpl, 'badge' => trim($this->b_badge) ?: null,
                'c1' => strtolower($this->b_c1), 'c2' => strtolower($this->b_c2), 'acc' => strtolower($this->b_acc), 'ink' => $this->b_ink,
                'photos' => array_values(array_map(fn ($ph) => ! empty($ph['f']) ? ['f' => (string) $ph['f']] : ['p' => (int) $ph['p']],
                    array_slice($this->b_photos, 0, $this->photoMax()))),
            ];
        } else {
            $image = $this->b_image ? $this->b_image->store('portal-banners', 'public') : $this->b_image_old;
            $mobile = $this->b_placement === 'side' ? null
                : ($this->b_image_mobile ? $this->b_image_mobile->store('portal-banners', 'public') : $this->b_mobile_old);
        }
        $keepFiles = array_merge([$image, $mobile], collect($design['photos'] ?? [])->pluck('f')->filter()->all());

        $row = [
            'placement'   => $this->b_placement,
            'image'       => $image,
            'image_mobile'=> $mobile,
            'color'       => $this->b_color,
            'title'       => trim($this->b_title) ?: null,
            'subtitle'    => trim($this->b_subtitle) ?: null,
            'button_text' => trim($this->b_button) ?: null,
            'link_type'   => $this->b_link_type,
            'link_value'  => $this->b_link_type === 'none' || $this->b_link_type === 'offers' ? null : trim($this->b_link_value),
            'active'      => $this->b_active,
            'starts_at'   => $this->b_starts ?: null,
            'ends_at'     => $this->b_ends ?: null,
            'design'      => $design,
        ];
        // Fotos enviadas que não entraram (trocou de modelo ou foi para imagem própria).
        $this->dropFiles($this->b_newFiles, $keepFiles);
        $this->b_newFiles = [];
        if ($old) {
            $this->dropFiles(array_merge([$old->image, $old->image_mobile], $old->designFiles()), $keepFiles);
            $old->update($row);
        } else {
            $row['sort'] = (int) PortalBanner::where('user_id', Auth::id())->where('placement', $this->b_placement)->max('sort') + 1;
            PortalBanner::create($row + ['user_id' => Auth::id()]);
        }
        $this->showBanner = false;
        $this->dispatch('notify', type: 'success', message: 'Banner salvo.');
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

    public function newCollection(): void
    {
        $this->resetCollection();
        $this->showCollection = true;
    }

    public function editCollection(int $id): void
    {
        $c = $this->collection($id);
        if (! $c) {
            return;
        }
        $this->resetCollection();
        $this->colId = $c->id;
        $this->c_name = $c->name;
        $this->c_description = (string) $c->description;
        $this->c_color = $c->color;
        $this->c_active = (bool) $c->active;
        $this->c_home = (bool) $c->show_on_home;
        $this->c_image_old = $c->image;
        $this->c_products = $c->productIds();
        $this->showCollection = true;
    }

    public function removeCollectionImage(): void
    {
        $this->c_image = null;
        $this->c_image_old = null;
    }

    public function moveCollectionProduct(int $index, int $dir): void
    {
        $this->c_products = $this->moved($this->c_products, $index, $dir);
    }

    public function removeCollectionProduct(int $id): void
    {
        $this->c_products = array_values(array_filter($this->c_products, fn ($v) => (int) $v !== $id));
    }

    public function saveCollection(): void
    {
        $this->validate([
            'c_name'        => ['required', 'string', 'max:80'],
            'c_description' => ['nullable', 'string', 'max:200'],
            'c_color'       => ['required', 'in:' . implode(',', array_keys(StorefrontService::COLORS))],
            'c_image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'c_products'    => ['array', 'min:1'],
        ], [
            'c_products.min' => 'Escolha pelo menos um produto.',
            'c_image.max' => 'A imagem pode ter até 5 MB.',
        ], ['c_name' => 'nome', 'c_image' => 'imagem']);

        $old = $this->colId ? $this->collection($this->colId) : null;
        $image = $this->c_image ? $this->c_image->store('portal-banners', 'public') : $this->c_image_old;
        $row = [
            'name'         => trim($this->c_name),
            'slug'         => $this->uniqueSlug(trim($this->c_name), $old?->id),
            'description'  => trim($this->c_description) ?: null,
            'color'        => $this->c_color,
            'image'        => $image,
            'active'       => $this->c_active,
            'show_on_home' => $this->c_home,
        ];
        if ($old) {
            $this->dropFiles([$old->image], [$image]);
            // Banners que levam a esta coleção acompanham o novo nome.
            if ($old->slug !== $row['slug']) {
                PortalBanner::where('user_id', Auth::id())->where('link_type', 'collection')->where('link_value', $old->slug)->update(['link_value' => $row['slug']]);
            }
            $old->update($row);
            $c = $old;
        } else {
            $row['sort'] = (int) PortalCollection::where('user_id', Auth::id())->max('sort') + 1;
            $c = PortalCollection::create($row + ['user_id' => Auth::id()]);
        }
        $c->syncProducts($this->c_products);
        $this->showCollection = false;
        $this->dispatch('notify', type: 'success', message: 'Coleção ' . $c->name . ' salva.');
    }

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
        $this->pickFor = in_array($for, ['featured', 'collection', 'banner', 'photos'], true) ? $for : '';
        $this->pickSearch = '';
    }

    public function closePicker(): void
    {
        $this->pickFor = '';
    }

    public function pick(int $id): void
    {
        if ($this->pickFor === 'photos') {
            if (! Product::where('user_id', Auth::id())->whereKey($id)->exists()) {
                return;
            }
            $at = collect($this->b_photos)->search(fn ($ph) => (int) ($ph['p'] ?? 0) === $id);
            if ($at !== false) {
                array_splice($this->b_photos, $at, 1);
            } else {
                $this->addPhoto(['p' => $id]);
            }
            if ($this->photoMax() === 1 || count($this->b_photos) >= $this->photoMax()) {
                $this->pickFor = '';
            }

            return;
        }
        $root = StorefrontService::rootsOf(Auth::id(), [$id])[0] ?? null;
        if (! $root) {
            return;
        }
        if ($this->pickFor === 'banner') {
            $this->b_link_value = (string) $root;
            $this->pickFor = '';

            return;
        }
        $prop = $this->pickFor === 'collection' ? 'c_products' : 'featuredIds';
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

    /** Apaga do disco as imagens antigas que não estão mais em uso. */
    private function dropFiles(array $old, array $keep = []): void
    {
        foreach (array_filter($old) as $path) {
            if (! in_array($path, $keep, true) && str_starts_with($path, 'portal-banners/')) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function resetBanner(): void
    {
        $this->resetErrorBag();
        $this->bannerId = null;
        $this->b_placement = 'hero';
        $this->b_title = $this->b_subtitle = $this->b_button = $this->b_link_value = $this->b_starts = $this->b_ends = '';
        $this->b_link_type = 'none';
        $this->b_color = 'blue';
        $this->b_active = true;
        $this->b_image = $this->b_image_mobile = null;
        $this->b_image_old = $this->b_mobile_old = null;
        $this->pickFor = '';
        $this->dropFiles($this->b_newFiles);
        $this->b_newFiles = [];
        $this->b_mode = 'modelo';
        $this->b_tpl = 'oferta';
        $this->b_badge = '';
        [$this->b_c1, $this->b_c2, $this->b_acc, $this->b_ink] = ['#ff7733', '#e11d48', '#fff7ed', 'light'];
        $this->b_photos = [];
        $this->b_photo = null;
    }

    private function resetCollection(): void
    {
        $this->resetErrorBag();
        $this->colId = null;
        $this->c_name = $this->c_description = '';
        $this->c_color = 'violet';
        $this->c_active = $this->c_home = true;
        $this->c_image = null;
        $this->c_image_old = null;
        $this->c_products = [];
        $this->pickFor = '';
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
        $counts = \Illuminate\Support\Facades\DB::table('portal_collection_product')
            ->whereIn('portal_collection_id', $collections->pluck('id'))
            ->selectRaw('portal_collection_id, COUNT(*) as n')->groupBy('portal_collection_id')->pluck('n', 'portal_collection_id');

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
            'collection' => array_map('intval', $this->c_products),
            'featured'   => array_map('intval', $this->featuredIds),
            'banner'     => [(int) $this->b_link_value],
            'photos'     => collect($this->b_photos)->pluck('p')->filter()->map(fn ($v) => (int) $v)->all(),
            default      => [],
        };

        $bannerProduct = $this->b_link_type === 'product' && (int) $this->b_link_value > 0
            ? Product::where('user_id', $uid)->find((int) $this->b_link_value) : null;

        // Fotos de exemplo para a galeria de modelos e fotos da prévia do banner aberto.
        $samples = $this->tab === 'banners' ? PortalBanner::photoUrls($uid, $this->samplePhotos(3)) : [];

        return view('livewire.promotions.portal-storefront', [
            'samples'       => $samples,
            'bPhotoUrls'    => $this->showBanner ? PortalBanner::photoUrls($uid, $this->b_photos, true) : [],
            'banners'       => $banners,
            'collections'   => $collections,
            'counts'        => $counts,
            'featured'      => $this->productsById($this->featuredIds),
            'colProducts'   => $this->showCollection ? $this->productsById($this->c_products) : collect(),
            'results'       => $results,
            'picked'        => $picked,
            'bannerProduct' => $bannerProduct,
            'categories'    => $this->b_link_type === 'category'
                ? Category::where('user_id', $uid)->whereIn('id_category', Product::where('user_id', $uid)->whereNotNull('category_id')->distinct()->pluck('category_id'))->orderBy('name')->get(['id_category', 'name'])
                : collect(),
            'catalogUrl'    => route('portal.catalog', ['userId' => $uid]),
        ]);
    }
}
