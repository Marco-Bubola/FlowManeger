<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateProduct extends Component
{
    use WithFileUploads;

    // Propriedades do formulário
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $price_sale = '';
    public string $stock_quantity = '';
    public string $category_id = '';
    public string $product_code = '';
    public $image;
    
    // Campos Mercado Livre
    public string $barcode = '';
    public string $brand = '';
    public string $model = '';
    public string $warranty_months = '3';
    public string $condition = 'new';

    // Propriedade para controlar os steppers
    public int $currentStep = 1;

    // Modal de dicas
    public bool $showTipsModal = false;

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
        ];
    }

    public function nextStep()
    {
        // Conversão dos valores monetários antes da validação
        $this->convertCurrencyValues();

        // Validação específica da etapa 1
        $this->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric',
            'price_sale' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'category_id' => 'required|exists:category,id_category',
            'product_code' => 'required',
        ]);

        $this->currentStep = 2;
    }

    private function convertCurrencyValues()
    {
        // Converte valores: aceita tanto formato BR (1.234,56) quanto já numérico (22.49) do currency-input
        $normalize = function ($value) {
            if ($value === '' || $value === null) {
                return $value;
            }
            $value = trim((string) $value);
            if ($value === '') {
                return '';
            }
            // Se já está no formato numérico (ex: 22.49 ou 22) não tem vírgula como decimal
            if (str_contains($value, ',') && !str_contains($value, '.')) {
                $value = str_replace(',', '.', $value);
            } elseif (str_contains($value, ',') && str_contains($value, '.')) {
                // Formato BR: 1.234,56 -> remover pontos (milhar) e trocar vírgula por ponto
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } elseif (preg_match('/^\d+\.\d+$/', $value)) {
                // Já é "22.49" - não alterar
                return $value;
            } elseif (str_contains($value, '.') && !str_contains($value, ',')) {
                // Pode ser 1.234 (milhar) ou 22.49 (decimal). Se só um ponto e 2 decimais após, é decimal
                if (preg_match('/\.\d{1,2}$/', $value)) {
                    return $value;
                }
                $value = str_replace('.', '', $value);
            }
            return $value;
        };

        $beforePrice = $this->price;
        $beforePriceSale = $this->price_sale;

        $this->price = $normalize($this->price);
        $this->price_sale = $normalize($this->price_sale);

        Log::channel('single')->info('CreateProduct convertCurrencyValues', [
            'price_before' => $beforePrice,
            'price_after' => $this->price,
            'price_sale_before' => $beforePriceSale,
            'price_sale_after' => $this->price_sale,
        ]);
    }

    public function previousStep()
    {
        $this->currentStep = 1;
    }

    public function store()
    {
        Log::channel('single')->info('CreateProduct store() INÍCIO', [
            'price_raw' => $this->price,
            'price_sale_raw' => $this->price_sale,
            'price_type' => gettype($this->price),
            'price_sale_type' => gettype($this->price_sale),
        ]);

        // Conversão dos valores monetários antes da validação
        $this->convertCurrencyValues();

        Log::channel('single')->info('CreateProduct store() APÓS convertCurrencyValues', [
            'price' => $this->price,
            'price_sale' => $this->price_sale,
        ]);

        try {
            // Validação completa do formulário na etapa final
            $validated = $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('single')->warning('CreateProduct store() VALIDAÇÃO FALHOU', [
                'errors' => $e->errors(),
                'price_at_fail' => $this->price,
                'price_sale_at_fail' => $this->price_sale,
            ]);
            throw $e;
        }

        // Tratamento da imagem
        $imageName = null;
        if ($this->image) {
            try {
                // Salva a nova imagem usando o disco public (igual ao EditProduct)
                $imagePath = $this->image->store('products', 'public');
                $imageName = basename($imagePath);

                // Verificar se o arquivo foi realmente salvo
                $fullPath = Storage::disk('public')->path($imagePath);
                $fileExists = Storage::disk('public')->exists($imagePath);

                // Log para debug
                logger('Imagem salva no CreateProduct:', [
                    'original_name' => $this->image->getClientOriginalName(),
                    'path' => $imagePath,
                    'final_name' => $imageName,
                    'full_path' => $fullPath,
                    'file_exists' => $fileExists,
                    'size' => $this->image->getSize(),
                    'mime' => $this->image->getMimeType()
                ]);

                if (!$fileExists) {
                    throw new \Exception('O arquivo não foi salvo corretamente no storage.');
                }

            } catch (\Exception $e) {
                logger('Erro ao salvar imagem no CreateProduct:', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);

                session()->flash('error', 'Erro ao salvar a imagem: ' . $e->getMessage());
                return;
            }
        }

        // Verifica se já existe um produto com o mesmo código e preço
        $existingProduct = Product::where('product_code', $this->product_code)
            ->where('price', $this->price)
            ->where('price_sale', $this->price_sale)
            ->first();

        if ($existingProduct) {
            // Se o produto já existe com o mesmo código e preço, apenas atualizamos a quantidade
            $existingProduct->stock_quantity += (int) $this->stock_quantity;
            $existingProduct->save();

            session()->flash('success', 'Produto atualizado com sucesso!');
        } else {
            // Se o produto não existe com o mesmo código e preço, cria um novo
            Product::create([
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'price_sale' => $this->price_sale,
                'stock_quantity' => $this->stock_quantity,
                'category_id' => $this->category_id,
                'user_id' => Auth::id(),
                'product_code' => $this->product_code,
                'image' => $imageName,
                'status' => 'ativo', // Padrão sempre ativo
                'tipo' => 'simples',
                'custos_adicionais' => 0,
                // Campos Mercado Livre
                'barcode' => $this->barcode ?: null,
                'brand' => $this->brand ?: null,
                'model' => $this->model ?: null,
                'warranty_months' => $this->warranty_months ?: 3,
                'condition' => $this->condition ?: 'new',
            ]);

            session()->flash('success', 'Produto adicionado com sucesso!');
        }

        // Reset do formulário
        $this->reset();

        // Emite evento para atualizar a lista
        $this->dispatch('product-created');

        // Redireciona para a lista
        return redirect()->route('products.index');
    }

    public function toggleTips()
    {
        $this->showTipsModal = !$this->showTipsModal;
    }

    public function getCategoriesProperty()
    {
        return Category::where('user_id', Auth::id())
                      ->where('type', 'product')
                      ->where('is_active', 1)
                      ->orderBy('name')
                      ->get();
    }

    public function getCategoryIcon($icone)
    {
        // Mapear ícones icons8 para Bootstrap Icons
        $iconMap = [
            'icons8-perfume' => 'bi-emoji-heart-eyes',
            'icons8-nubank' => 'bi-credit-card-2-front',
            'icons8-pagamento' => 'bi-currency-dollar',
            'icons8-pix' => 'bi-lightning-charge',
            'icons8-xp' => 'bi-graph-up-arrow',
            'icons8-inter' => 'bi-bank',
            'icons8-rendimento' => 'bi-graph-up',
            'icons8-restaurante' => 'bi-cup-straw',
            'icons8-beleza' => 'bi-heart',
            'icons8-supermercado' => 'bi-cart',
            'icons8-transporte' => 'bi-bus-front',
            'icons8-casa' => 'bi-house',
            'icons8-saude' => 'bi-heart-pulse',
            'icons8-educacao' => 'bi-book',
            'icons8-entretenimento' => 'bi-controller',
            'icons8-vestuario' => 'bi-bag',
            'icons8-tecnologia' => 'bi-laptop',
            'icons8-combustivel' => 'bi-fuel-pump',
            'icons8-farmacia' => 'bi-capsule',
            'icons8-pet' => 'bi-heart',
        ];

        return $iconMap[$icone] ?? 'bi-tag';
    }

    public function render()
    {
        return view('livewire.products.create-product', [
            'categories' => $this->categories,
        ]);
    }

    public function updatedImage()
    {
        // Validação básica da imagem quando é selecionada
        $this->validateOnly('image');

        // Debug para entender o que está acontecendo
        if ($this->image) {
            logger('Imagem selecionada no CreateProduct:', [
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
}
