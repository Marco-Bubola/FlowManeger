<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Services\Products\VariationService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class BulkEditProducts extends Component
{
    use HasNotifications;

    public array $productsData = [];
    public string $search = '';
    public string $filterStatus = '';
    public string $sortBy = 'updated_at';
    public array $savedStatus = [];

    public int $currentPage = 1;
    public int $perPage = 120;
    public int $totalProducts = 0;
    public int $totalPages = 1;

    // ─── Vincular em massa (variações) ────────────────────────────────────────
    public array $selectedToLink = [];   // IDs marcados
    public bool $showLinkModal = false;
    public string $linkParentId = '';    // qual vira o produto-pai

    public function mount(): void
    {
        $this->loadProducts();
    }

    protected function baseQuery()
    {
        return Product::where('user_id', Auth::id())
            ->when($this->search, fn($q) => $q
                ->where(function($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('product_code', 'like', "%{$this->search}%")
                        ->orWhere('barcode', 'like', "%{$this->search}%");
                }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->sortBy === 'name', fn($q) => $q->orderBy('name'))
            ->when($this->sortBy === 'updated_at', fn($q) => $q->orderByDesc('updated_at'))
            ->when($this->sortBy === 'price_sale', fn($q) => $q->orderByDesc('price_sale'));
    }

    public function loadProducts(): void
    {
        $this->totalProducts = $this->baseQuery()->count();
        $this->totalPages = max(1, (int) ceil($this->totalProducts / $this->perPage));

        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }

        $offset = ($this->currentPage - 1) * $this->perPage;

        $query = $this->baseQuery()
            ->skip($offset)
            ->take($this->perPage)
            ->get();

        $this->productsData = $query->map(fn($p) => [
            'id'             => $p->id,
            'name'           => $p->name ?? '',
            'product_code'   => $p->product_code ?? '',
            'barcode'        => $p->barcode ?? '',
            'stock_quantity' => (string)($p->stock_quantity ?? 0),
            'price'          => number_format((float)$p->price, 2, '.', ''),
            'price_sale'     => number_format((float)$p->price_sale, 2, '.', ''),
            'category_id'    => (int)($p->category_id ?? 0),
            'status'         => $p->status ?? 'ativo',
            'image'          => $p->image,
            'image_url'      => $p->image_url,
        ])->values()->toArray();

        $this->savedStatus = [];

        // Avisa o cliente para limpar o "carrinho" de editados (nova listagem)
        $this->dispatch('bulk-reloaded');
    }

    public function updatedSearch(): void       { $this->currentPage = 1; $this->loadProducts(); }
    public function updatedFilterStatus(): void { $this->currentPage = 1; $this->loadProducts(); }
    public function updatedSortBy(): void       { $this->currentPage = 1; $this->loadProducts(); }
    public function updatedPerPage(): void      { $this->currentPage = 1; $this->loadProducts(); }

    public function goToPage(int $page): void
    {
        $this->currentPage = max(1, min($page, $this->totalPages));
        $this->loadProducts();
        $this->dispatch('scroll-to-top');
    }

    public function nextPage(): void
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->loadProducts();
            $this->dispatch('scroll-to-top');
        }
    }

    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadProducts();
            $this->dispatch('scroll-to-top');
        }
    }

    public function getPagesArrayProperty(): array
    {
        $current = $this->currentPage;
        $total = $this->totalPages;
        $delta = 2;
        $range = [];
        $rangeWithDots = [];
        $l = null;

        for ($i = 1; $i <= $total; $i++) {
            if ($i == 1 || $i == $total || ($i >= $current - $delta && $i <= $current + $delta)) {
                $range[] = $i;
            }
        }

        foreach ($range as $i) {
            if ($l !== null) {
                if ($i - $l === 2) {
                    $rangeWithDots[] = $l + 1;
                } elseif ($i - $l !== 1) {
                    $rangeWithDots[] = '...';
                }
            }
            $rangeWithDots[] = $i;
            $l = $i;
        }

        return $rangeWithDots;
    }

    public function saveProductWithImage(int $index, ?string $base64 = null, bool $silent = false): void
    {
        $data = $this->productsData[$index] ?? null;
        if (!$data) return;

        $product = Product::find($data['id']);
        if (!$product || $product->user_id !== Auth::id()) {
            if (!$silent) $this->notifyError('Produto não encontrado.');
            return;
        }

        $imageName = $product->image;

        if ($base64 && str_starts_with($base64, 'data:image')) {
            try {
                $base64Data = substr($base64, strpos($base64, ',') + 1);
                $imageData  = base64_decode($base64Data);

                $extension = 'jpg';
                if (str_contains($base64, 'image/png'))  $extension = 'png';
                elseif (str_contains($base64, 'image/webp')) $extension = 'webp';
                elseif (str_contains($base64, 'image/gif'))  $extension = 'gif';

                $filename = 'product_' . $data['id'] . '_' . time() . '.' . $extension;

                if (!Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }

                if ($product->image && $product->image !== 'product-placeholder.png'
                    && Storage::disk('public')->exists('products/' . $product->image)) {
                    Storage::disk('public')->delete('products/' . $product->image);
                }

                Storage::disk('public')->put('products/' . $filename, $imageData);
                $imageName = $filename;
            } catch (\Exception $e) {
                if (!$silent) $this->notifyError('Erro ao salvar imagem: ' . $e->getMessage());
                return;
            }
        }

        $product->update([
            'name'           => $data['name'],
            'product_code'   => $data['product_code'],
            'barcode'        => $data['barcode'] ?? null,
            'stock_quantity' => (int)$data['stock_quantity'],
            'price'          => (float)$data['price'],
            'price_sale'     => (float)$data['price_sale'],
            'category_id'    => (int)$data['category_id'],
            'image'          => $imageName,
        ]);

        $this->productsData[$index]['image']     = $imageName;
        $this->productsData[$index]['image_url'] = asset('storage/products/' . $imageName);

        $this->savedStatus[$index] = 'saved';
        if (!$silent) $this->notifySuccess('Produto "' . $data['name'] . '" salvo!');
    }

    public function saveProduct(int $index): void
    {
        $this->saveProductWithImage($index, null);
    }

    /**
     * Salva TODOS os produtos da página de uma vez (campos de texto).
     * Imagens novas continuam sendo salvas individualmente pelo botão do card.
     */
    public function saveAll(): void
    {
        $ids = collect($this->productsData)->pluck('id')->all();
        $products = Product::whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->get()
            ->keyBy('id');

        $count = 0;
        foreach ($this->productsData as $index => $data) {
            $product = $products->get($data['id']);
            if (!$product) continue;

            $product->update([
                'name'           => $data['name'],
                'product_code'   => $data['product_code'],
                'barcode'        => $data['barcode'] ?? null,
                'stock_quantity' => (int)$data['stock_quantity'],
                'price'          => (float)$data['price'],
                'price_sale'     => (float)$data['price_sale'],
                'category_id'    => (int)$data['category_id'],
            ]);

            $this->savedStatus[$index] = 'saved';
            $count++;
        }

        $this->notifySuccess("{$count} produto(s) salvos com sucesso!");
    }

    public function removeProduct(int $index): void
    {
        $data = $this->productsData[$index] ?? null;
        if (!$data) return;

        $product = Product::find($data['id']);
        if ($product && $product->user_id === Auth::id()) {
            $product->delete();
        }

        array_splice($this->productsData, $index, 1);
        $this->totalProducts = max(0, $this->totalProducts - 1);
        $this->totalPages = max(1, (int) ceil($this->totalProducts / $this->perPage));
        $this->notifySuccess('Produto removido!');
    }

    // ─── Vincular em massa como variações ─────────────────────────────────────

    /** Produtos atualmente selecionados (para mostrar no modal). */
    public function getSelectedLinkProductsProperty()
    {
        if (empty($this->selectedToLink)) {
            return collect();
        }
        $ids = array_map('intval', $this->selectedToLink);
        return Product::where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'product_code', 'image', 'stock_quantity', 'price_sale', 'tipo', 'parent_id', 'is_variation_parent']);
    }

    public function clearLinkSelection(): void
    {
        $this->selectedToLink = [];
        $this->linkParentId = '';
        $this->showLinkModal = false;
    }

    public function openLinkModal(): void
    {
        if (count($this->selectedToLink) < 2) {
            $this->notifyError('Selecione pelo menos 2 produtos para agrupar como variações.');
            return;
        }
        // Pai padrão = o primeiro selecionado (de preferência um que não seja kit/variante)
        $candidates = $this->selectedLinkProducts;
        $best = $candidates->first(fn ($p) => $p->tipo !== 'kit' && !$p->parent_id) ?? $candidates->first();
        $this->linkParentId = (string) ($best->id ?? $this->selectedToLink[0]);
        $this->showLinkModal = true;
    }

    public function confirmBulkLink(VariationService $variations): void
    {
        if (count($this->selectedToLink) < 2 || !$this->linkParentId) {
            $this->notifyError('Seleção inválida.');
            return;
        }

        $parent = Product::where('user_id', Auth::id())->find((int) $this->linkParentId);
        if (!$parent) {
            $this->notifyError('Produto-pai não encontrado.');
            return;
        }
        if ($parent->tipo === 'kit') {
            $this->notifyError('Um kit não pode ser o produto-pai de variações.');
            return;
        }

        $childIds = array_filter(
            array_map('intval', $this->selectedToLink),
            fn ($id) => $id !== (int) $this->linkParentId
        );

        $linked = 0;
        $skipped = 0;
        foreach ($childIds as $cid) {
            $child = Product::where('user_id', Auth::id())->find($cid);
            if (!$child || $child->tipo === 'kit') {
                $skipped++;
                continue;
            }
            try {
                $value = 'R$ ' . number_format((float) $child->price_sale, 2, ',', '.');
                $variations->attach($parent, $child, 'Valor', $value);
                $linked++;
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        $this->clearLinkSelection();
        $this->loadProducts();
        $this->dispatch('product-updated');

        if ($linked > 0) {
            $msg = "{$linked} produto(s) vinculados como variações!";
            if ($skipped > 0) {
                $msg .= " ({$skipped} ignorado(s) — kit ou já agrupado)";
            }
            $this->notifySuccess($msg);
        } else {
            $this->notifyError('Nenhum produto pôde ser vinculado (verifique se não são kits ou já são variações).');
        }
    }

    public function render()
    {
        $categories = Category::where('user_id', Auth::id())->get();

        return view('livewire.products.bulk-edit-products', [
            'categories' => $categories,
            'pagesArray' => $this->getPagesArrayProperty(),
            'selectedLinkProducts' => $this->selectedLinkProducts,
        ])->layout('components.layouts.app');
    }
}
