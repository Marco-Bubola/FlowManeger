<?php

namespace App\Livewire\Promotions;

use App\Models\Category;
use App\Models\PortalBanner;
use App\Models\PortalCollection;
use App\Models\PortalSetting;
use App\Models\Product;
use App\Services\Portal\StorefrontService;
use App\Services\Promotions\StoreKind;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Página de criar e editar um banner da vitrine: modelo pronto (tudo editável,
 * com animação) ou imagem própria, com prévia de computador e celular ao lado.
 * Os modelos e textos seguem o tipo da loja (beleza, móveis ou outra).
 */
class BannerEditor extends Component
{
    use WithFileUploads;

    public ?int $bannerId = null;
    public string $kind = 'beleza';
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
    public string $b_tpl = '';
    public string $b_badge = '';
    public string $b_c1 = '#ff7733';
    public string $b_c2 = '#e11d48';
    public string $b_acc = '#fff7ed';
    public string $b_ink = 'light';
    public string $b_lay = 'esq';
    public string $b_src = 'manual';
    public string $b_anim = 'fotos';
    public string $b_fmt = 'padrao';
    /** Grupo aberto na galeria de modelos. */
    public string $galleryGroup = 'datas';
    public array $b_photos = [];
    public $b_photo = null;
    /** Fotos enviadas nesta página e ainda não salvas (apagadas se cancelar). */
    public array $b_newFiles = [];

    // Escolher produto (foto do modelo ou link do banner)
    public string $pickFor = '';
    public string $pickSearch = '';

    public function mount(?int $banner = null): void
    {
        $this->kind = StoreKind::for(Auth::id());
        if ($banner) {
            $this->load($banner);

            return;
        }
        $place = (string) request()->query('lugar', 'hero');
        $this->b_placement = array_key_exists($place, PortalBanner::PLACEMENTS) ? $place : 'hero';
        $tpl = (string) request()->query('modelo');
        $list = StorefrontService::templates($this->kind);
        $this->applyTemplate($list->has($tpl) ? $tpl : ($list->has('oferta') ? 'oferta' : $list->keys()->first()));
    }

    private function load(int $id): void
    {
        $b = PortalBanner::where('user_id', Auth::id())->findOrFail($id);
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
            $this->b_mode = 'modelo';
            $this->b_tpl = array_key_exists($d['tpl'], StorefrontService::TEMPLATES) ? $d['tpl'] : 'oferta';
            $t = $this->tpl();
            $this->b_badge = (string) ($d['badge'] ?? '');
            $this->b_c1 = $d['c1'] ?? $t['c1'];
            $this->b_c2 = $d['c2'] ?? $t['c2'];
            $this->b_acc = $d['acc'] ?? $t['acc'];
            $this->b_ink = ($d['ink'] ?? 'light') === 'dark' ? 'dark' : 'light';
            $this->b_photos = array_values(array_filter($d['photos'] ?? [], fn ($ph) => ! empty($ph['p']) || ! empty($ph['f'])));
            $this->b_lay = array_key_exists($d['lay'] ?? '', StorefrontService::LAYOUTS) ? $d['lay'] : 'esq';
            $this->b_src = array_key_exists($d['src'] ?? '', StorefrontService::PHOTO_SOURCES) ? $d['src'] : 'manual';
            $this->b_anim = array_key_exists($d['anim'] ?? '', StorefrontService::ANIMATIONS) ? $d['anim'] : 'none';
            $this->b_fmt = StorefrontService::format($d['fmt'] ?? null);
            $this->galleryGroup = $t['group'];
        } else {
            $this->b_mode = 'imagem';
            $this->b_tpl = 'oferta';
        }
    }

    /** O modelo aberto, com os textos do tipo da loja. */
    private function tpl(?string $key = null): array
    {
        return StorefrontService::template($key ?? $this->b_tpl, $this->kind) ?? StorefrontService::template('oferta', $this->kind);
    }

    /**
     * Troca o modelo: cores e selo do modelo novo; os textos só mudam se ainda
     * estão vazios ou iguais aos do modelo anterior (o que você escreveu fica).
     */
    public function applyTemplate(string $key): void
    {
        $t = StorefrontService::template($key, $this->kind);
        if (! $t) {
            return;
        }
        $prev = $this->b_tpl !== '' ? $this->tpl() : [];
        foreach (['b_badge' => 'badge', 'b_title' => 'title', 'b_subtitle' => 'subtitle', 'b_button' => 'button'] as $prop => $f) {
            $cur = trim($this->{$prop});
            if ($cur === '' || $cur === ($prev[$f] ?? null)) {
                $this->{$prop} = $t[$f];
            }
        }
        $prevSrc = $prev['src'] ?? 'manual';
        $this->b_tpl = $key;
        $this->b_fmt = StorefrontService::format($t['fmt'] ?? null);
        $this->galleryGroup = $t['group'];
        if ($this->b_src === $prevSrc || $this->b_src === 'manual') {
            $this->b_src = $t['src'];
        }
        // Data comemorativa: o banner já fica no ar no período dela.
        if ($t['date'] && ($d = \App\Services\Promotions\CommercialCalendar::next($t['date']))
            && (! $this->bannerId || ($this->b_starts === '' && $this->b_ends === ''))) {
            $today = now(\App\Models\Promotion::tz())->startOfDay();
            $this->b_starts = $d['start']->gt($today) ? $d['start']->format('Y-m-d') : '';
            $this->b_ends = $d['end']->format('Y-m-d');
        }
        $this->b_c1 = $t['c1'];
        $this->b_c2 = $t['c2'];
        $this->b_acc = $t['acc'];
        $this->b_ink = $t['ink'];
        $autoLinks = ['offers', 'news', 'bestsellers'];
        if (in_array($t['link'], $autoLinks, true) && ($this->b_link_type === 'none' || in_array($this->b_link_type, $autoLinks, true))) {
            $this->b_link_type = $t['link'];
        } elseif ($t['link'] === 'none' && in_array($this->b_link_type, $autoLinks, true) && ! $this->bannerId) {
            $this->b_link_type = 'none';
        }
        $this->fillPhotos();
    }

    /** Corta as fotos ao que o modelo e a animação usam e completa com produtos de exemplo. */
    private function fillPhotos(): void
    {
        $max = $this->photoMax();
        $this->dropExtra(array_splice($this->b_photos, $max));
        if ($this->b_src === 'manual' && count($this->b_photos) < $max) {
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

    /** Tipo da loja: troca a lista de modelos; num banner novo, o modelo aberto ganha os textos do tipo. */
    public function setKind(string $kind): void
    {
        $old = $this->tpl();
        $this->kind = StoreKind::set(Auth::id(), $kind);
        $list = StorefrontService::templates($this->kind);
        if ($this->bannerId || $this->b_mode !== 'modelo') {
            return;
        }
        foreach (['b_badge' => 'badge', 'b_title' => 'title', 'b_subtitle' => 'subtitle', 'b_button' => 'button'] as $prop => $f) {
            if (trim($this->{$prop}) === ($old[$f] ?? null)) {
                $this->{$prop} = '';
            }
        }
        $key = $list->has($this->b_tpl) ? $this->b_tpl : ($list->where('group', $this->galleryGroup)->keys()->first() ?? $list->keys()->first());
        $this->b_tpl = '';
        $this->applyTemplate($key);
    }

    public function setSource(string $src): void
    {
        $this->b_src = array_key_exists($src, StorefrontService::PHOTO_SOURCES) ? $src : 'manual';
        $links = ['novos' => 'news', 'vendidos' => 'bestsellers', 'ofertas' => 'offers'];
        if (isset($links[$this->b_src]) && in_array($this->b_link_type, ['none', 'offers', 'news', 'bestsellers'], true)) {
            $this->b_link_type = $links[$this->b_src];
        }
        if ($this->b_src === 'manual' && ! $this->b_photos) {
            $this->b_photos = $this->samplePhotos($this->photoMax());
        }
    }

    public function setAnim(string $anim): void
    {
        $this->b_anim = array_key_exists($anim, StorefrontService::ANIMATIONS) ? $anim : 'none';
        $this->fillPhotos();
    }

    public function setFormat(string $fmt): void
    {
        $this->b_fmt = StorefrontService::format($fmt);
        $this->fillPhotos();
    }

    public function setLayout(string $lay): void
    {
        $this->b_lay = array_key_exists($lay, StorefrontService::LAYOUTS) ? $lay : 'esq';
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
        if ($this->b_mode === 'imagem' && ! $this->bannerId) {
            $t = $this->tpl();
            foreach (['b_title' => 'title', 'b_subtitle' => 'subtitle', 'b_button' => 'button'] as $prop => $f) {
                if (trim($this->{$prop}) === $t[$f]) {
                    $this->{$prop} = '';
                }
            }
        }
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
        if ($ph) {
            array_splice($this->b_photos, $index, 1);
            $this->dropExtra([$ph]);
        }
    }

    public function movePhoto(int $index, int $dir): void
    {
        $ids = array_values($this->b_photos);
        $to = $index + ($dir < 0 ? -1 : 1);
        if (isset($ids[$index], $ids[$to])) {
            [$ids[$index], $ids[$to]] = [$ids[$to], $ids[$index]];
            $this->b_photos = $ids;
        }
    }

    /** Sair sem salvar: as fotos enviadas agora são apagadas. */
    public function cancel()
    {
        $this->dropFiles($this->b_newFiles);
        $this->b_newFiles = [];

        return $this->redirectRoute('promotions.storefront', ['tab' => 'banners'], navigate: false);
    }

    /** Quantas fotos o banner usa (o modelo mostra 1 ou 3; trocando as fotos, 3 vezes isso). */
    private function photoMax(): int
    {
        return StorefrontService::photoCap($this->b_tpl, $this->b_anim, $this->b_fmt);
    }

    private function addPhoto(array $ref): void
    {
        $max = $this->photoMax();
        if (count($this->b_photos) >= $max) {
            $this->dropExtra(array_splice($this->b_photos, $max - 1));
        }
        $this->b_photos[] = $ref;
    }

    /** Fotos enviadas agora que saíram do banner: apaga do disco. */
    private function dropExtra(array $photos): void
    {
        foreach ($photos as $o) {
            if (! empty($o['f']) && in_array($o['f'], $this->b_newFiles, true)) {
                $this->dropFiles([$o['f']]);
                $this->b_newFiles = array_values(array_diff($this->b_newFiles, [$o['f']]));
            }
        }
    }

    /** Produtos com foto para os modelos já abrirem bonitos (destaques primeiro). */
    private function samplePhotos(int $n): array
    {
        $uid = Auth::id();
        $real = fn ($q) => $q->whereNotNull('image')->where('image', '!=', 'product-placeholder.png')->where('image', 'not like', 'ml_%');
        $ids = collect(PortalSetting::for($uid)->featured_ids ?? [])->map(fn ($v) => (int) $v);
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

    public function saveBanner()
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
            'b_lay'          => ['required', 'in:' . implode(',', array_keys(StorefrontService::LAYOUTS))],
            'b_src'          => ['required', 'in:' . implode(',', array_keys(StorefrontService::PHOTO_SOURCES))],
            'b_anim'         => ['required', 'in:' . implode(',', array_keys(StorefrontService::ANIMATIONS))],
            'b_fmt'          => ['required', 'in:' . implode(',', array_keys(StorefrontService::FORMATS))],
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

            return null;
        }
        if (! $modelo && ! $hasImage && trim($this->b_title) === '') {
            $this->addError('b_image', 'Coloque uma imagem ou escreva um título.');

            return null;
        }

        $old = $this->bannerId ? PortalBanner::where('user_id', Auth::id())->find($this->bannerId) : null;
        $design = null;
        if ($modelo) {
            $image = $mobile = null;
            $design = [
                'tpl' => $this->b_tpl, 'badge' => trim($this->b_badge) ?: null,
                'c1' => strtolower($this->b_c1), 'c2' => strtolower($this->b_c2), 'acc' => strtolower($this->b_acc), 'ink' => $this->b_ink,
                'lay' => $this->b_lay, 'src' => $this->b_src, 'anim' => $this->b_anim, 'fmt' => $this->b_fmt,
                'photos' => array_values(array_map(fn ($ph) => ! empty($ph['f']) ? ['f' => (string) $ph['f']] : ['p' => (int) $ph['p']],
                    $this->b_src === 'manual' ? array_slice($this->b_photos, 0, $this->photoMax()) : [])),
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
        session()->flash('success', 'Banner salvo.');

        return $this->redirectRoute('promotions.storefront', ['tab' => 'banners'], navigate: false);
    }

    /* ---------- Escolher produto ---------- */

    public function openPicker(string $for): void
    {
        $this->pickFor = in_array($for, ['banner', 'photos'], true) ? $for : '';
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
        if ($this->pickFor === 'banner' && ($root = StorefrontService::rootsOf(Auth::id(), [$id])[0] ?? null)) {
            $this->b_link_value = (string) $root;
            $this->pickFor = '';
        }
    }

    /** Apaga do disco as imagens que não estão mais em uso. */
    private function dropFiles(array $old, array $keep = []): void
    {
        foreach (array_filter($old) as $path) {
            if (! in_array($path, $keep, true) && str_starts_with($path, 'portal-banners/')) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function render()
    {
        $uid = Auth::id();
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
            'banner' => [(int) $this->b_link_value],
            'photos' => collect($this->b_photos)->pluck('p')->filter()->map(fn ($v) => (int) $v)->all(),
            default  => [],
        };

        $auto = [];
        foreach (['novos', 'vendidos', 'ofertas'] as $src) {
            $auto[$src] = PortalBanner::autoPhotoUrls($uid, $src, 12);
        }
        $bPhotoUrls = $this->b_src === 'manual'
            ? PortalBanner::photoUrls($uid, $this->b_photos, true)
            : array_slice($auto[$this->b_src], 0, $this->photoMax());

        return view('livewire.promotions.banner-editor', [
            'samples'       => PortalBanner::photoUrls($uid, $this->samplePhotos(3)),
            'auto'          => $auto,
            'bPhotoUrls'    => $bPhotoUrls,
            'photoMax'      => $this->photoMax(),
            'picsNow'       => StorefrontService::picsFor($this->b_tpl, $this->b_fmt),
            'tplData'       => $this->tpl(),
            'templates'     => StorefrontService::templates($this->kind),
            'collections'   => PortalCollection::where('user_id', $uid)->orderBy('sort')->orderBy('id')->get(['id', 'name', 'slug']),
            'results'       => $results,
            'picked'        => $picked,
            'bannerProduct' => $this->b_link_type === 'product' && (int) $this->b_link_value > 0
                ? Product::where('user_id', $uid)->find((int) $this->b_link_value) : null,
            'categories'    => $this->b_link_type === 'category'
                ? Category::where('user_id', $uid)->whereIn('id_category', Product::where('user_id', $uid)->whereNotNull('category_id')->distinct()->pluck('category_id'))->orderBy('name')->get(['id_category', 'name'])
                : collect(),
        ]);
    }
}
