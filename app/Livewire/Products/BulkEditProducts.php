<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
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

    public function mount(): void
    {
        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $query = Product::where('user_id', Auth::id())
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('product_code', 'like', "%{$this->search}%")
                ->orWhere('barcode', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->sortBy === 'name', fn($q) => $q->orderBy('name'))
            ->when($this->sortBy === 'updated_at', fn($q) => $q->orderByDesc('updated_at'))
            ->when($this->sortBy === 'price_sale', fn($q) => $q->orderByDesc('price_sale'))
            ->limit(120)
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
    }

    public function updatedSearch(): void  { $this->loadProducts(); }
    public function updatedFilterStatus(): void { $this->loadProducts(); }
    public function updatedSortBy(): void  { $this->loadProducts(); }

    public function saveProductWithImage(int $index, ?string $base64 = null): void
    {
        $data = $this->productsData[$index] ?? null;
        if (!$data) return;

        $product = Product::find($data['id']);
        if (!$product || $product->user_id !== Auth::id()) {
            $this->notifyError('Produto não encontrado.');
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
                $this->notifyError('Erro ao salvar imagem: ' . $e->getMessage());
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
        $this->notifySuccess('Produto "' . $data['name'] . '" salvo!');
    }

    public function saveProduct(int $index): void
    {
        $this->saveProductWithImage($index, null);
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
        $this->notifySuccess('Produto removido!');
    }

    public function render()
    {
        $categories = Category::where('user_id', Auth::id())->get();

        return view('livewire.products.bulk-edit-products', [
            'categories' => $categories,
        ])->layout('components.layouts.app');
    }
}
