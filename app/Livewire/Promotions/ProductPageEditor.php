<?php

namespace App\Livewire\Promotions;

use App\Models\PortalProductPage;
use App\Models\Product;
use App\Services\Portal\ProductPageService as Svc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Página especial de um produto no catálogo: o dono monta a página com blocos
 * (banner, foto com texto, destaques, galeria, vídeo, medidas, perguntas,
 * depoimentos, contagem, chamada…), escolhe cores e o que esconder, e vê a
 * prévia de computador e celular ao lado.
 */
class ProductPageEditor extends Component
{
    use WithFileUploads;

    public int $productId;
    public int $rootId;
    public bool $exists = false;
    public bool $active = true;
    public bool $choosing = false;
    public array $settings = [];
    public array $blocks = [];
    public ?string $open = null;
    /** Envio de fotos, por bloco e campo: "id-image" ou "id-photos". */
    public array $uploads = [];
    /** Fotos enviadas nesta página e ainda não salvas (apagadas se sair sem salvar). */
    public array $newFiles = [];

    public function mount(int $product): void
    {
        $p = Product::where('user_id', Auth::id())->findOrFail($product);
        $this->rootId = (int) ($p->parent_id ?: $p->id);
        $this->productId = $this->rootId;
        $page = Svc::ready() ? PortalProductPage::where('user_id', Auth::id())->where('product_id', $this->rootId)->first() : null;
        $this->exists = (bool) $page;
        $this->active = $page ? (bool) $page->active : true;
        $this->settings = Svc::settings($page?->settings);
        $this->blocks = Svc::blocks($page?->blocks ?? []);
        $this->choosing = ! $page;
    }

    private function product(): Product
    {
        return Product::where('user_id', Auth::id())->with('images')->findOrFail($this->rootId);
    }

    public function useTemplate(string $key): void
    {
        $t = Svc::templates()[$key] ?? null;
        if (! $t) {
            return;
        }
        $this->settings = Svc::settings($t[3] + $this->settings);
        $this->blocks = Svc::blocks(array_map(fn ($b) => $b + ['id' => uniqid('b')], $t[4]));
        $this->choosing = false;
        $this->open = $this->blocks[0]['id'] ?? null;
    }

    public function addBlock(string $type): void
    {
        if (! isset(Svc::BLOCKS[$type]) || count($this->blocks) >= Svc::MAX_BLOCKS) {
            return;
        }
        $b = Svc::block($type);
        $this->blocks[] = $b;
        $this->open = $b['id'];
        $this->dispatch('ppe-scroll', id: $b['id']);
    }

    private function index(string $id): ?int
    {
        foreach ($this->blocks as $i => $b) {
            if (($b['id'] ?? null) === $id) {
                return $i;
            }
        }

        return null;
    }

    public function move(string $id, int $dir): void
    {
        $i = $this->index($id);
        $j = $i === null ? null : $i + ($dir < 0 ? -1 : 1);
        if ($j === null || ! isset($this->blocks[$j])) {
            return;
        }
        [$this->blocks[$i], $this->blocks[$j]] = [$this->blocks[$j], $this->blocks[$i]];
    }

    public function duplicate(string $id): void
    {
        $i = $this->index($id);
        if ($i === null || count($this->blocks) >= Svc::MAX_BLOCKS) {
            return;
        }
        $copy = $this->blocks[$i];
        $copy['id'] = uniqid('b');
        array_splice($this->blocks, $i + 1, 0, [$copy]);
        $this->open = $copy['id'];
    }

    public function remove(string $id): void
    {
        $i = $this->index($id);
        if ($i !== null) {
            array_splice($this->blocks, $i, 1);
        }
    }

    public function toggle(string $id): void
    {
        $this->open = $this->open === $id ? null : $id;
    }

    public function toggleHidden(string $id): void
    {
        if (($i = $this->index($id)) !== null) {
            $this->blocks[$i]['hidden'] = ! ($this->blocks[$i]['hidden'] ?? false);
        }
    }

    public function addItem(string $id, string $field): void
    {
        $i = $this->index($id);
        if ($i === null || ! is_array($this->blocks[$i][$field] ?? null) || count($this->blocks[$i][$field]) >= 12) {
            return;
        }
        $this->blocks[$i][$field][] = match ($this->blocks[$i]['type'] . '.' . $field) {
            'destaques.items' => ['icon' => 'bi-star', 'title' => 'Vantagem', 'text' => ''],
            'perguntas.items' => ['q' => 'Nova pergunta', 'a' => ''],
            'depoimentos.items' => ['text' => '', 'name' => ''],
            'medidas.rows' => ['k' => '', 'v' => ''],
            default => '',
        };
    }

    public function removeItem(string $id, string $field, int $j): void
    {
        $i = $this->index($id);
        if ($i !== null && isset($this->blocks[$i][$field][$j])) {
            array_splice($this->blocks[$i][$field], $j, 1);
        }
    }

    public function setIcon(string $id, int $j, string $icon): void
    {
        $i = $this->index($id);
        if ($i !== null && in_array($icon, Svc::ICONS, true)) {
            if ($j < 0) {
                $this->blocks[$i]['icon'] = $icon;
            } elseif (isset($this->blocks[$i]['items'][$j])) {
                $this->blocks[$i]['items'][$j]['icon'] = $icon;
            }
        }
    }

    public function updatedUploads($value, $key): void
    {
        [$id, $field] = array_pad(explode('-', (string) $key, 2), 2, 'image');
        $i = $this->index($id);
        $files = is_array($value) ? $value : [$value];
        unset($this->uploads[$key]);
        if ($i === null) {
            return;
        }
        foreach ($files as $f) {
            if (! $f || ! method_exists($f, 'getMimeType') || ! str_starts_with((string) $f->getMimeType(), 'image/') || $f->getSize() > 8 * 1024 * 1024) {
                $this->dispatch('notify', type: 'error', message: 'Envie fotos JPG, PNG ou WEBP de até 8 MB.');

                continue;
            }
            $path = $f->store('portal-product-pages/' . Auth::id(), 'public');
            $this->newFiles[] = $path;
            if ($field === 'photos') {
                if (count($this->blocks[$i]['photos']) < 24) {
                    $this->blocks[$i]['photos'][] = $path;
                }
            } else {
                $this->blocks[$i][$field] = $path;
            }
        }
    }

    public function clearImage(string $id, string $field = 'image'): void
    {
        if (($i = $this->index($id)) !== null) {
            $this->blocks[$i][$field] = null;
        }
    }

    public function removePhoto(string $id, int $j): void
    {
        $i = $this->index($id);
        if ($i !== null && isset($this->blocks[$i]['photos'][$j])) {
            array_splice($this->blocks[$i]['photos'], $j, 1);
        }
    }

    /** Textos com limite, cores válidas e só os campos de cada bloco. */
    private function clean(): array
    {
        $cut = function ($v) use (&$cut) {
            if (is_array($v)) {
                return array_map($cut, $v);
            }

            return is_string($v) ? mb_substr($v, 0, 2000) : $v;
        };
        $blocks = Svc::blocks($cut($this->blocks));
        foreach ($blocks as &$b) {
            foreach (['c1', 'c2'] as $c) {
                if (array_key_exists($c, $b)) {
                    $b[$c] = Svc::color($b[$c], Svc::BLOCKS[$b['type']][3][$c]);
                }
            }
            foreach (['image'] as $f) {
                if (! empty($b[$f]) && ! str_starts_with((string) $b[$f], 'portal-product-pages/' . Auth::id() . '/')) {
                    $b[$f] = null;
                }
            }
            if (isset($b['photos'])) {
                $b['photos'] = array_values(array_filter((array) $b['photos'], fn ($p) => is_string($p) && str_starts_with($p, 'portal-product-pages/' . Auth::id() . '/')));
            }
            if ($b['type'] === 'video') {
                $b['url'] = trim((string) $b['url']);
            }
        }

        return $blocks;
    }

    public function save(): void
    {
        if (! Svc::ready()) {
            $this->dispatch('notify', type: 'error', message: 'Falta atualizar o banco de dados do servidor.');

            return;
        }
        $old = PortalProductPage::where('user_id', Auth::id())->where('product_id', $this->rootId)->first();
        $blocks = $this->clean();
        $settings = Svc::settings($this->settings);
        PortalProductPage::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $this->rootId],
            ['active' => $this->active, 'settings' => $settings, 'blocks' => $blocks],
        );
        // Fotos que saíram da página
        $used = collect($blocks)->flatMap(fn ($b) => array_merge([$b['image'] ?? null], $b['photos'] ?? []))->filter()->all();
        $before = collect($old?->blocks ?? [])->flatMap(fn ($b) => array_merge([$b['image'] ?? null], $b['photos'] ?? []))->filter();
        foreach ($before->concat($this->newFiles)->unique()->diff($used) as $gone) {
            Storage::disk('public')->delete($gone);
        }
        $this->newFiles = [];
        $this->blocks = $blocks;
        $this->settings = $settings;
        $this->exists = true;
        $this->dispatch('notify', type: 'success', message: $this->active ? 'Página especial salva e no ar.' : 'Página salva (desligada no site).');
    }

    public function deletePage(): void
    {
        $page = PortalProductPage::where('user_id', Auth::id())->where('product_id', $this->rootId)->first();
        if ($page) {
            foreach (collect($page->blocks ?? [])->flatMap(fn ($b) => array_merge([$b['image'] ?? null], $b['photos'] ?? []))->filter()->concat($this->newFiles) as $f) {
                Storage::disk('public')->delete($f);
            }
            $page->delete();
        }
        $this->dispatch('notify', type: 'success', message: 'Página especial apagada. O produto volta para a página normal.');
        $this->redirectRoute('promotions.product-pages', navigate: true);
    }

    public function render()
    {
        $p = $this->product();
        $photos = array_values(array_filter((array) $p->all_images));
        $previewSpecial = ['settings' => Svc::settings($this->settings), 'blocks' => Svc::blocks($this->blocks)];

        return view('livewire.promotions.product-page-editor', [
            'product' => $p,
            'photos' => $photos,
            'special' => $previewSpecial,
            'templates' => Svc::templates(),
            'storeVariants' => Svc::storeOptions((int) Auth::id()),
            'pageUrl' => \App\Http\Controllers\ClientPortalController::productUrl($p) . '?previa=1',
        ]);
    }
}
