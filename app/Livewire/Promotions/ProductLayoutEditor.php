<?php

namespace App\Livewire\Promotions;

use App\Http\Controllers\ClientPortalController;
use App\Models\Product;
use App\Services\Portal\ProductLayout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Montar a página do produto (todas as páginas de produto da loja): ordem e
 * visibilidade das partes, nome, fotos, preço e botões, com a página de
 * verdade ao lado como prévia.
 */
class ProductLayoutEditor extends Component
{
    public array $layout = [];
    public array $saved = [];
    public ?int $sampleId = null;

    public function mount(): void
    {
        $this->layout = $this->saved = ProductLayout::for((int) Auth::id());
        $this->sampleId = $this->samples()->first()?->id;
    }

    private function samples()
    {
        // Produtos principais com foto; os que têm variações primeiro (mostram mais coisas na prévia).
        return Product::where('user_id', Auth::id())->whereNull('parent_id')->whereIn('status', ['active', 'ativo'])
            ->withCount('variants')
            ->orderByDesc('variants_count')->orderBy('name')
            ->limit(40)->get(['id', 'name', 'user_id', 'parent_id', 'image', 'product_code']);
    }

    public function updatedLayout(): void
    {
        $this->layout = ProductLayout::normalize($this->layout);
    }

    public function move(string $list, string $key, int $dir): void
    {
        if (! in_array($list, ['parts', 'sections'], true)) {
            return;
        }
        $order = $this->layout[$list];
        $i = array_search($key, $order, true);
        $j = $i === false ? false : $i + ($dir < 0 ? -1 : 1);
        if ($i === false || ! isset($order[$j])) {
            return;
        }
        [$order[$i], $order[$j]] = [$order[$j], $order[$i]];
        $this->layout[$list] = $order;
    }

    public function toggleOff(string $list, string $key): void
    {
        if (! in_array($list, ['parts', 'sections'], true)) {
            return;
        }
        $off = $this->layout[$list . '_off'];
        $this->layout[$list . '_off'] = in_array($key, $off, true) ? array_values(array_diff($off, [$key])) : array_merge($off, [$key]);
        $this->layout = ProductLayout::normalize($this->layout);
    }

    public function choose(string $key, string $value): void
    {
        if (isset(ProductLayout::CHOICES[$key][$value])) {
            $this->layout[$key] = $value;
        }
    }

    public function flip(string $key): void
    {
        if (in_array($key, ProductLayout::FLAGS, true)) {
            $this->layout[$key] = ! ($this->layout[$key] ?? true);
        }
    }

    public function resetDefaults(): void
    {
        $this->layout = ProductLayout::normalize([]);
    }

    public function save(): void
    {
        $this->layout = $this->saved = ProductLayout::save((int) Auth::id(), $this->layout);
        $this->dispatch('notify', type: 'success', message: 'Pronto. Todas as páginas de produto já estão assim no site.');
    }

    public function render()
    {
        $samples = $this->samples();
        $sample = $samples->firstWhere('id', $this->sampleId) ?? $samples->first();
        $url = $sample ? ClientPortalController::productUrl($sample) : null;

        return view('livewire.promotions.product-layout-editor', [
            'samples' => $samples,
            'previewUrl' => $url ? $url . '?pagina=' . ProductLayout::encode($this->layout) : null,
            'siteUrl' => $url,
            'dirty' => ProductLayout::normalize($this->layout) !== $this->saved,
        ]);
    }
}
