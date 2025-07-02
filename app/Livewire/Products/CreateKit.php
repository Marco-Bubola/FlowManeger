<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProdutoComponente;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateKit extends Component
{
    // Propriedades do formulário
    public string $name = '';
    public string $price = '';
    public string $price_sale = '';
    public array $produtos = [];

    public function mount()
    {
        // Inicializa o array de produtos disponíveis
        $availableProducts = Product::where('user_id', Auth::id())
            ->where('tipo', 'simples')
            ->where('status', 'ativo')
            ->get();

        foreach ($availableProducts as $product) {
            $this->produtos[$product->id] = [
                'selecionado' => false,
                'quantidade' => 1,
                'name' => $product->name,
                'price' => $product->price_sale,
                'stock' => $product->stock_quantity,
            ];
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'price_sale' => 'required|numeric|min:0',
            'produtos' => 'required|array',
        ];
    }

    public function store()
    {
        // Conversão correta dos valores monetários do formato brasileiro para americano
        $this->price = str_replace(',', '.', str_replace('.', '', $this->price));
        $this->price_sale = str_replace(',', '.', str_replace('.', '', $this->price_sale));

        // Validação do formulário
        $validated = $this->validate();

        // Verifica se pelo menos um produto foi selecionado
        $hasSelectedProducts = false;
        foreach ($this->produtos as $produtoId => $dados) {
            if (isset($dados['selecionado']) && $dados['selecionado'] && isset($dados['quantidade']) && $dados['quantidade'] > 0) {
                $hasSelectedProducts = true;
                break;
            }
        }

        if (!$hasSelectedProducts) {
            $this->addError('produtos', 'Selecione pelo menos um produto para o kit.');
            return;
        }

        // Cria o produto do tipo kit
        $kit = Product::create([
            'name' => $this->name,
            'description' => null,
            'price' => $this->price,
            'price_sale' => $this->price_sale,
            'stock_quantity' => 0, // O estoque do kit é gerenciado via componentes
            'category_id' => 1, // Pode ser ajustado se desejar selecionar categoria
            'user_id' => Auth::id(),
            'product_code' => 'KIT-' . strtoupper(uniqid()),
            'image' => null,
            'status' => 'ativo',
            'tipo' => 'kit',
            'custos_adicionais' => 0,
        ]);

        // Salva os componentes do kit
        foreach ($this->produtos as $produtoId => $dados) {
            if (isset($dados['selecionado']) && $dados['selecionado'] && isset($dados['quantidade']) && $dados['quantidade'] > 0) {
                ProdutoComponente::create([
                    'kit_produto_id' => $kit->id,
                    'componente_produto_id' => $produtoId,
                    'quantidade' => $dados['quantidade'],
                ]);
            }
        }

        session()->flash('success', 'Kit criado com sucesso!');

        // Reset do formulário
        $this->reset();

        // Emite evento para atualizar a lista
        $this->dispatch('kit-created');

        // Redireciona para a lista
        return redirect()->route('products.index');
    }

    public function getAvailableProductsProperty()
    {
        return Product::where('user_id', Auth::id())
            ->where('tipo', 'simples')
            ->where('status', 'ativo')
            ->get();
    }

    public function render()
    {
        return view('livewire.products.create-kit', [
            'availableProducts' => $this->availableProducts,
        ]);
    }
}
