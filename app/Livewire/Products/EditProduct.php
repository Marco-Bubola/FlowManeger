<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProduct extends Component
{
    use WithFileUploads, HasNotifications;

    public Product $product;
    
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

    public function mount(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->product = $product;
        
        // Preenche os campos com os dados do produto
        $this->name = $product->name ?? '';
        $this->description = $product->description ?? '';
        
        // Formata os preços para o formato brasileiro (0,00)
        $price = (float)$product->price;
        $price_sale = (float)$product->price_sale;
        
        $this->price = number_format($price, 2, ',', '');
        $this->price_sale = number_format($price_sale, 2, ',', '');
        
        $this->stock_quantity = (string)$product->stock_quantity;
        $this->category_id = (string)$product->category_id;
        $this->product_code = $product->product_code ?? '';
        $this->status = $product->status ?? 'ativo';
        
        // Não preenchemos $this->image para evitar conflitos
        // A imagem atual será mostrada através de $product->image
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
            $validated = $this->validate();

            // Verificar se o código do produto já existe (exceto o atual)
            $existingProduct = Product::where('product_code', $this->product_code)
                                    ->where('id', '!=', $this->product->id)
                                    ->where('user_id', Auth::id())
                                    ->first();
            
            if ($existingProduct) {
                $this->addError('product_code', 'Este código de produto já está sendo usado por outro produto.');
                $this->notifyError('Código do produto já está em uso!');
                return;
            }

            // Atualizar imagem se necessário
            $imageName = $this->product->image;
            if ($this->image) {
                try {
                    // Remove a imagem anterior se existir
                    if ($this->product->image && Storage::disk('public')->exists('products/' . $this->product->image)) {
                        Storage::disk('public')->delete('products/' . $this->product->image);
                    }
                    
                    // Salva a nova imagem usando o disco public
                    $imagePath = $this->image->store('products', 'public');
                    $imageName = basename($imagePath);
                    
                    // Verificar se o arquivo foi realmente salvo
                    $fileExists = Storage::disk('public')->exists($imagePath);
                    
                    if (!$fileExists) {
                        throw new \Exception('O arquivo não foi salvo corretamente no storage.');
                    }
                    
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
            ]);

            if (!$updateResult) {
                throw new \Exception('Falha ao atualizar o produto no banco de dados.');
            }

            // Emite evento para atualizar a lista
            $this->dispatch('product-updated');

            // Redireciona com notificação de sucesso
            $this->redirectWithNotification(
                route('products.index'),
                'Produto "' . $this->name . '" atualizado com sucesso!'
            );

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
        return Category::where('user_id', Auth::id())->get();
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
            $category = Category::find($this->category_id);
            return $category ? $category->name : 'Escolha uma categoria...';
        }
        return 'Escolha uma categoria...';
    }

    public function getSelectedCategoryIconProperty()
    {
        if ($this->category_id) {
            $category = Category::find($this->category_id);
            return $category ? $this->getCategoryIcon($category->icone) : 'bi-grid-3x3-gap-fill';
        }
        return 'bi-grid-3x3-gap-fill';
    }

    public function render()
    {
        return view('livewire.products.edit-product', [
            'categories' => $this->categories,
        ]);
    }
}
