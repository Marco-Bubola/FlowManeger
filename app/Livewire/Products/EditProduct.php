<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProduct extends Component
{
    use WithFileUploads, HasNotifications;

    public Product $product;
    public int $currentStep = 1;

    // Propriedades do formulário
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $price_sale = '';
    public string $stock_quantity = '';
    public string $category_id = '';
    public string $product_code = '';
    public $image;
    public string $status = 'ativo';
    
    // Campos Mercado Livre
    public string $barcode = '';
    public string $brand = '';
    public string $model = '';
    public string $warranty_months = '3';
    public string $condition = 'new';

    public function mount(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->product = $product;

        // Preenche os campos com os dados do produto
        $this->name = $product->name ?? '';
        $this->description = $product->description ?? '';

        // Os preços já vêm formatados como decimal(2) graças ao cast do Model
        // Convertemos para float e depois para string no formato americano
        $price = (float)($product->price ?? 0);
        $price_sale = (float)($product->price_sale ?? 0);

        // Formato americano para o componente currency-input
        $this->price = number_format($price, 2, '.', '');
        $this->price_sale = number_format($price_sale, 2, '.', '');

        $this->stock_quantity = (string)($product->stock_quantity ?? 0);
        $this->category_id = (string)($product->category_id ?? '');
        $this->product_code = $product->product_code ?? '';
        $this->status = $product->status ?? 'ativo';
        
        // Campos Mercado Livre
        $this->barcode = $product->barcode ?? '';
        $this->brand = $product->brand ?? '';
        $this->model = $product->model ?? '';
        $this->warranty_months = $product->warranty_months ? (string)$product->warranty_months : '3';
        $this->condition = $product->condition ?? 'new';

        // Não preenchemos $this->image para evitar conflitos
        // A imagem atual será mostrada através de $product->image
    }

    /**
     * Avança para a próxima etapa
     */
    public function nextStep()
    {
        $this->currentStep = min($this->currentStep + 1, 2);
    }

    /**
     * Retorna para a etapa anterior
     */
    public function previousStep()
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'price' => 'required|numeric',
            'price_sale' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'category_id' => 'required|exists:category,id_category',
            'product_code' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
            'barcode' => 'nullable|max:15',
            'brand' => 'nullable|max:100',
            'model' => 'nullable|max:100',
            'warranty_months' => 'nullable|integer|min:0|max:120',
            'condition' => 'nullable|in:new,used,refurbished',
            // Removido o status obrigatório pois ele é definido automaticamente
        ];
    }

    public function update()
    {
        try {
            // Conversão correta dos valores monetários do formato brasileiro para americano
            $price = str_replace(',', '.', $this->price);
            $price_sale = str_replace(',', '.', $this->price_sale);

            // Convertendo para float para garantir formato correto
            $price = (float)$price;
            $price_sale = (float)$price_sale;

            // Atualização temporária das propriedades para validação
            $this->price = (string)$price;
            $this->price_sale = (string)$price_sale;

            // Validação do formulário
            // Sanitizar o nome no backend (remover '/', '(', ')', '-' e normalizar espaços)
            $this->name = preg_replace('/\s+/', ' ', trim(preg_replace('/[\/()\-]/', '', $this->name)));

            $validated = $this->validate();

            // Atualizar imagem se necessário
            $imageName = $this->product->image;
            if ($this->image) {
                try {
                    // Criar diretório de produtos se não existir
                    if (!Storage::disk('public')->exists('products')) {
                        Storage::disk('public')->makeDirectory('products');
                    }

                    // Remove a imagem anterior se existir (exceto imagem padrão)
                    if ($this->product->image && $this->product->image !== 'product-placeholder.png' && Storage::disk('public')->exists('products/' . $this->product->image)) {
                        Storage::disk('public')->delete('products/' . $this->product->image);
                    }

                    // Salva a nova imagem usando o disco public
                    $imagePath = $this->image->store('products', 'public');
                    $imageName = basename($imagePath);

                    // Log para debug
                    logger('Imagem salva:', [
                        'imagePath' => $imagePath,
                        'imageName' => $imageName,
                        'full_path' => storage_path('app/public/' . $imagePath),
                        'exists' => file_exists(storage_path('app/public/' . $imagePath)),
                        'directory_exists' => is_dir(storage_path('app/public/products'))
                    ]);

                } catch (\Exception $e) {
                    logger('Erro ao salvar imagem:', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);

                    $this->notifyError('Erro ao salvar a imagem: ' . $e->getMessage());
                    return;
                }
            }

            // Atualizar o produto
            $updateResult = $this->product->update([
                'name' => $this->name,
                'description' => $this->description,
                'price' => $price,
                'price_sale' => $price_sale,
                'stock_quantity' => (int)$this->stock_quantity,
                'category_id' => (int)$this->category_id,
                'product_code' => $this->product_code,
                'image' => $imageName,
                'status' => 'ativo',
                'tipo' => 'simples',
                'custos_adicionais' => 0,
                // Campos Mercado Livre
                'barcode' => $this->barcode ?: null,
                'brand' => $this->brand ?: null,
                'model' => $this->model ?: null,
                'warranty_months' => $this->warranty_months ?: 3,
                'condition' => $this->condition ?: 'new',
            ]);

            if (!$updateResult) {
                throw new \Exception('Falha ao atualizar o produto no banco de dados.');
            }

            // Emite evento para atualizar a lista
            $this->dispatch('product-updated');

            // Notifica sucesso (exibe o nome já sanitizado)
            $this->notifySuccess('Produto "' . $this->name . '" atualizado com sucesso!');

            // Redireciona diretamente
            return redirect()->route('products.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Exibe os erros específicos
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = $field . ': ' . implode(', ', $messages);
            }

            $this->notifyError('Erros de validação: ' . implode(' | ', $errorMessages));

        } catch (\Exception $e) {
            logger('Erro ao atualizar produto:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'product_id' => $this->product->id
            ]);

            $this->notifyError('Erro inesperado ao atualizar o produto: ' . $e->getMessage());
        }
    }

    public function updatedImage()
    {
        // Validação básica da imagem quando é selecionada
        $this->validateOnly('image');

        // Debug para entender o que está acontecendo
        if ($this->image) {
            logger('Imagem selecionada:', [
                'original_name' => $this->image->getClientOriginalName(),
                'size' => $this->image->getSize(),
                'mime' => $this->image->getMimeType(),
                'temporary_url_available' => method_exists($this->image, 'temporaryUrl')
            ]);
        }

        // Este método é chamado automaticamente quando $this->image é atualizado
        // Força a re-renderização do componente
        $this->dispatch('image-updated');
    }

    public function getHasNewImageProperty()
    {
        return $this->image && is_object($this->image) && method_exists($this->image, 'temporaryUrl');
    }

    public function getCategoriesProperty()
    {
        return Category::where('user_id', Auth::id())
                      ->where('type', 'product')
                      ->where('is_active', 1)
                      ->orderBy('name')
                      ->get();
    }

    public function getCategoryIcon($iconeName)
    {
        $iconMapping = [
            'laptop' => 'bi-laptop',
            'phone' => 'bi-phone',
            'tablet' => 'bi-tablet',
            'headphones' => 'bi-headphones',
            'keyboard' => 'bi-keyboard',
            'mouse' => 'bi-mouse',
            'monitor' => 'bi-display',
            'camera' => 'bi-camera',
            'speaker' => 'bi-speaker',
            'printer' => 'bi-printer',
            'router' => 'bi-router',
            'hdd' => 'bi-hdd',
            'cpu' => 'bi-cpu',
            'gpu' => 'bi-gpu-card',
            'memory' => 'bi-memory',
            'motherboard' => 'bi-motherboard',
            'power' => 'bi-lightning',
            'cooler' => 'bi-fan',
            'case' => 'bi-pc-display',
            'cable' => 'bi-usb-c',
            'chair' => 'bi-chair',
            'desk' => 'bi-table',
            'tools' => 'bi-tools',
            'bag' => 'bi-bag',
            'book' => 'bi-book',
            'game' => 'bi-controller',
            'watch' => 'bi-smartwatch',
            'car' => 'bi-car-front',
            'house' => 'bi-house',
            'clothing' => 'bi-shirt',
            'food' => 'bi-egg-fried',
            'drink' => 'bi-cup-straw',
            'sport' => 'bi-trophy',
            'music' => 'bi-music-note',
            'health' => 'bi-heart-pulse',
            'beauty' => 'bi-palette',
            'clean' => 'bi-droplet',
            'pet' => 'bi-heart',
            'baby' => 'bi-balloon',
            'garden' => 'bi-flower1',
            'default' => 'bi-grid-3x3-gap-fill'
        ];

        return $iconMapping[$iconeName] ?? $iconMapping['default'];
    }

    public function getSelectedCategoryNameProperty()
    {
        if ($this->category_id) {
            // Buscar pela chave primária correta da categoria
            $category = Category::where('id_category', $this->category_id)->first();
            return $category ? $category->name : 'Escolha uma categoria...';
        }
        return 'Escolha uma categoria...';
    }

    public function getSelectedCategoryIconProperty()
    {
        if ($this->category_id) {
            // Buscar pela chave primária correta da categoria
            $category = Category::where('id_category', $this->category_id)->first();
            return $category ? $this->getCategoryIcon($category->icone) : 'bi-grid-3x3-gap-fill';
        }
        return 'bi-grid-3x3-gap-fill';
    }

    /**
     * Retorna a URL completa da imagem existente do produto
     */
    public function getExistingImageUrlProperty()
    {
        if ($this->product->image && $this->product->image !== 'product-placeholder.png') {
            return asset('storage/products/' . $this->product->image);
        }
        return null;
    }

    public function render()
    {
        return view('livewire.products.edit-product', [
            'categories' => $this->categories,
            'selectedCategoryName' => $this->selectedCategoryName,
            'selectedCategoryIcon' => $this->selectedCategoryIcon,
        ]);
    }
}
