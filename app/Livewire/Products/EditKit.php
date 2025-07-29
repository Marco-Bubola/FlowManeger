<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProdutoComponente;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditKit extends Component
{
    public Product $kit;

    // Propriedades do formulário
    public string $name = '';
    public string $price = '';
    public string $price_sale = '';
    public array $produtos = [];    public function mount(Product $kit)
    {
        if ($kit->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($kit->tipo !== 'kit') {
            abort(404, 'Product is not a kit.');
        }

        $this->kit = $kit;
        $this->name = $kit->name;
        $this->price = $kit->price;
        $this->price_sale = $kit->price_sale;

        // Inicializa o array de produtos disponíveis
        $availableProducts = Product::where('user_id', Auth::id())
            ->where('tipo', 'simples')
            ->where('status', 'ativo')
            ->get();

        // Carrega os componentes atuais do kit
        $kitComponents = ProdutoComponente::where('kit_produto_id', $kit->id)->get()->keyBy('componente_produto_id');

        foreach ($availableProducts as $product) {
            $component = $kitComponents->get($product->id);
            $this->produtos[$product->id] = [
                'selecionado' => $component ? true : false,
                'quantidade' => $component ? $component->quantidade : 1,
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
            'price' => 'required|numeric',
            'price_sale' => 'required|numeric',
            'produtos' => 'required|array',
        ];
    }

    public function update()
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

        // Atualiza o kit
        $this->kit->update([
            'name' => $this->name,
            'price' => $this->price,
            'price_sale' => $this->price_sale,
        ]);

        // Atualizar componentes do kit
        ProdutoComponente::where('kit_produto_id', $this->kit->id)->delete();

        foreach ($this->produtos as $produtoId => $dados) {
            if (isset($dados['selecionado']) && $dados['selecionado'] && isset($dados['quantidade']) && $dados['quantidade'] > 0) {
                ProdutoComponente::create([
                    'kit_produto_id' => $this->kit->id,
                    'componente_produto_id' => $produtoId,
                    'quantidade' => $dados['quantidade'],
                ]);
            }
        }

        session()->flash('success', 'Kit atualizado com sucesso!');

        // Emite evento para atualizar a lista
        $this->dispatch('kit-updated');

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
        return view('livewire.products.edit-kit', [
            'availableProducts' => $this->availableProducts,
        ]);
    }
}
