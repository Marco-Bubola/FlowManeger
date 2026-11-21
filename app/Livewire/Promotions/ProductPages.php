<?php

namespace App\Livewire\Promotions;

use App\Models\PortalProductPage;
use App\Models\Product;
use App\Services\Portal\ProductPageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/** Páginas especiais de produto: as que já existem e a busca para criar uma nova. */
class ProductPages extends Component
{
    public string $search = '';

    public function toggle(int $id): void
    {
        if ($p = PortalProductPage::where('user_id', Auth::id())->find($id)) {
            $p->update(['active' => ! $p->active]);
        }
    }

    public function render()
    {
        $pages = ProductPageService::ready()
            ? PortalProductPage::where('user_id', Auth::id())->with(['product' => fn ($q) => $q->with('images')])->latest('updated_at')->get()->filter(fn ($p) => $p->product)
            : collect();
        $term = trim($this->search);
        $found = $term === '' ? collect() : Product::where('user_id', Auth::id())
            ->whereNull('parent_id')
            ->where(fn ($q) => $q->where('name', 'like', '%' . $term . '%')->orWhere('product_code', 'like', $term . '%'))
            ->with('images')->orderBy('name')->limit(12)->get();

        return view('livewire.promotions.product-pages', [
            'pages' => $pages,
            'found' => $found,
            'withPage' => $pages->pluck('product_id')->all(),
        ]);
    }
}
