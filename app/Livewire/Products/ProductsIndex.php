<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ProductsIndex extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';
    public string $category = '';
    public string $status_filtro = '';
    public string $tipo = '';
    public string $preco_min = '';
    public string $preco_max = '';
    public string $estoque = '';
    public string $estoque_valor = '';
    public string $data_inicio = '';
    public string $data_fim = '';
    public string $ordem = '';
    public bool $sem_imagem = false;
    public bool $em_promocao = false;
    public bool $semEstoque = false;
    // Filtro rápido por letra inicial do nome ('' = todas, '#' = número/símbolo)
    public string $letra = '';
    public bool $fullHdLayout = false;
    public bool $ultraLayout = false;
    public bool $ipadLandscapeLayout = false;
    public int $perPage = 18;
    public int $page = 1;
    // Rolagem infinita: quantos blocos de $perPage já aparecem na tela
    public int $pages = 1;

    // Modal de exclusão
    public ?Product $deletingProduct = null;
    public bool $showDeleteModal = false;

    // Modal de dicas
    public bool $showTipsModal = false;

    public function mount()
    {
        // Debug: forçar para testar
        // $this->showDeleteModal = true;

        // Define o padrão de itens por página para lojas específicas
        $this->queryString['perPage']['except'] = $this->defaultPerPage();

        if (!request()->query('perPage')) {
            $this->perPage = $this->defaultPerPage();
        }

        $this->alignPerPageToOptions();
    }

    private function isUltraWind(): bool
    {
        $clientName = env('CLIENT_NAME', config('app.client_name', ''));
        return strtolower(trim($clientName)) === 'ultra wind';
    }

    private function usesUltraMultipliers(): bool
    {
        // Permite ativar multiplicadores "ultra" a partir do cliente (largura)
        // ou pela variável de ambiente para compatibilidade.
        return $this->ultraLayout || $this->isUltraWind();
    }

    private function defaultPerPage(): int
    {
        if ($this->isUltraWind()) return 32;
        if ($this->ipadLandscapeLayout) return 21; // 3 linhas × 7 colunas
        return 18;
    }

    private function alignPerPageToOptions(): void
    {
        // A barra de paginação ajusta o "por página" ao número de colunas da grade
        // (múltiplos de 5 numa tela, de 6 em outra...), então aceita qualquer valor razoável.
        $current = (int) $this->perPage;
        if ($current < 1 || $current > 120) {
            $this->perPage = $this->defaultPerPage();
        }
    }

    // Seleção em massa
    public array $selectedProducts = [];
    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status_filtro' => ['except' => ''],
        'tipo' => ['except' => ''],
        'preco_min' => ['except' => ''],
        'preco_max' => ['except' => ''],
        'estoque' => ['except' => ''],
        'estoque_valor' => ['except' => ''],
        'sem_imagem' => ['except' => false],
        'em_promocao' => ['except' => false],
        'semEstoque' => ['except' => false],
        'letra' => ['except' => ''],
        'data_inicio' => ['except' => ''],
        'data_fim' => ['except' => ''],
        'ordem' => ['except' => ''],
        'perPage' => ['except' => 18],
        'ultraLayout' => ['except' => false],
    ];

    /** Qualquer filtro, busca ou ordem novo volta ao começo da lista. */
    public function updating($name, $value): void
    {
        if (in_array($name, ['search', 'category', 'status_filtro', 'tipo', 'preco_min', 'preco_max', 'estoque',
            'estoque_valor', 'data_inicio', 'data_fim', 'ordem', 'sem_imagem', 'em_promocao', 'semEstoque', 'perPage', 'letra'], true)) {
            $this->pages = 1;
        }
    }

    /** Rolagem infinita: mostra o próximo bloco quando chega perto do fim. */
    public function loadMore(): void
    {
        if ($this->pages * $this->chunk() < $this->products->total()) {
            $this->pages++;
        }
    }

    /**
     * Ao voltar de editar/ver um produto: carrega até ele aparecer de novo.
     * Procura a posição atual do produto (ou da família dele) na lista, porque
     * depois de editar ele pode ter mudado de lugar (ex.: ficou sem estoque e foi
     * para o fim). Sem achar, usa quantos cards estavam carregados antes.
     */
    public function restoreTo(int $count, ?int $pid = null): void
    {
        $target = null;
        if ($pid) {
            $p = Product::query()->select('id', 'parent_id')->find($pid);
            $target = $p ? ($p->parent_id ?: $p->id) : null;
        }

        if ($target) {
            $ids = $this->productsQuery()->pluck('products.id');
            $idx = $ids->search($target);
            if ($idx !== false) {
                $count = $idx + 1;
            }
        }

        $this->pages = max(1, min(150, (int) ceil(max(1, $count) / max(1, $this->chunk()))));
    }

    /** Letra → quantos produtos começam com ela (para o filtro rápido A-Z). */
    public function getLettersProperty(): array
    {
        $rows = Product::query()->whereNull('parent_id')
            ->selectRaw('UPPER(SUBSTR(TRIM(name), 1, 1)) as l, COUNT(*) as n')
            ->groupByRaw('UPPER(SUBSTR(TRIM(name), 1, 1))')
            ->pluck('n', 'l');

        $out = array_fill_keys(array_merge(range('A', 'Z'), ['#']), 0);
        foreach ($rows as $l => $n) {
            $k = strtoupper(\Illuminate\Support\Str::ascii((string) $l));
            $k = preg_match('/^[A-Z]$/', $k) ? $k : '#';
            $out[$k] += (int) $n;
        }

        return $out;
    }

    private function chunk(): int
    {
        return $this->perPage >= 1 && $this->perPage <= 120 ? $this->perPage : 18;
    }

    public function updatingPerPage($value): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->alignPerPageToOptions();
    }

    public function updatedFullHdLayout(bool $isFullHd): void
    {
        if ($this->isUltraWind()) {
            return;
        }

        // Ajusta o valor 'except' usado no queryString para perPage baseado
        // no layout Full HD. Não forçamos resetPage aqui porque esse método
        // pode ser chamado repetidamente durante re-renders do Livewire
        // (por exemplo quando o Alpine reaplica o valor) e isso causava
        // o comportamento em que a paginação voltava para a primeira página.
        $this->queryString['perPage']['except'] = $isFullHd ? 25 : 18;
        $this->alignPerPageToOptions();
    }

    public function updatedUltraLayout(bool $isUltra): void
    {
        // Alinha as opções de perPage quando o cliente informa ultra layout.
        // Não resetamos a página para evitar comportamento de retornar
        // à página 1 quando Alpine reaplica a propriedade.
        $this->alignPerPageToOptions();
    }

    public function updatedIpadLandscapeLayout(bool $isIpadLandscape): void
    {
        // Quando entra/sai do modo iPad landscape, realinha perPage para
        // múltiplos de 7 (landscape) ou 6 (outros), garantindo mín. 3 linhas.
        $this->queryString['perPage']['except'] = $isIpadLandscape ? 21 : 18;
        $this->alignPerPageToOptions();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingStatusFiltro()
    {
        $this->resetPage();
    }

    public function updatingTipo()
    {
        $this->resetPage();
    }

    public function updatingSemEstoque()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->category = '';
        $this->status_filtro = '';
        $this->tipo = '';
        $this->preco_min = '';
        $this->preco_max = '';
        $this->estoque = '';
        $this->estoque_valor = '';
        $this->data_inicio = '';
        $this->data_fim = '';
        $this->ordem = '';
        $this->sem_imagem = false;
        $this->em_promocao = false;
        $this->semEstoque = false;
        $this->resetPage();
    }

    public function toggleSort($field)
    {
        $currentOrder = $this->ordem;

        // Código: crescente (padrão) ⇄ decrescente
        if ($field === 'codigo') {
            $this->ordem = $currentOrder === '' ? 'codigo_desc' : '';
            $this->pages = 1;
            return;
        }

        // Se clicar no mesmo campo, inverte a direção
        if (str_contains($currentOrder, $field)) {
            // Se termina com _asc, muda para _desc
            if (str_ends_with($currentOrder, '_asc')) {
                $this->ordem = $field . '_desc';
            }
            // Se termina com _desc ou é "recentes"/"az", muda para _asc
            else {
                $this->ordem = $field . '_asc';
            }
        } else {
            // Primeira vez clicando nesse campo, começa com descendente (mais recente)
            if ($field === 'data') {
                $this->ordem = 'recentes'; // ou 'data_desc'
            } elseif ($field === 'updated') {
                $this->ordem = 'atualizados'; // ou 'updated_desc'
            } elseif ($field === 'nome') {
                $this->ordem = 'az'; // ou 'nome_asc'

            } else {
                $this->ordem = $field . '_desc';
            }
        }

        $this->resetPage();
    }

    public function setQuickFilter($filter)
    {
        // Limpar filtros anteriores
        $this->clearFilters();

        switch ($filter) {
            case 'baixo-estoque':
                $this->estoque = 'abaixo';
                $this->estoque_valor = '10'; // Considera baixo estoque produtos com menos de 10 unidades
                break;

            case 'sem-imagem':
                // Filtrar produtos sem imagem personalizada (que usam a imagem padrão)
                $this->sem_imagem = true;
                break;

            case 'novos':
                $this->data_inicio = now()->subDays(7)->format('Y-m-d'); // Produtos dos últimos 7 dias
                $this->ordem = 'recentes';
                break;

            case 'kits':
                $this->tipo = 'kit';
                break;

            case 'preco-zero':
                $this->preco_max = '0';
                break;

            case 'promocao':
                $this->em_promocao = true;
                break;
        }

        $this->resetPage();
    }

    public function applyPricePreset(string $preset): void
    {
        switch ($preset) {
            case 'all':
                $this->preco_min = '';
                $this->preco_max = '';
                break;
            case '0-99':
                $this->preco_min = '0';
                $this->preco_max = '99.99';
                break;
            case '100-299':
                $this->preco_min = '100';
                $this->preco_max = '299.99';
                break;
            case '300-999':
                $this->preco_min = '300';
                $this->preco_max = '999.99';
                break;
            case '1000+':
                $this->preco_min = '1000';
                $this->preco_max = '';
                break;
        }

        $this->resetPage();
    }

    public function applyDatePreset(string $preset): void
    {
        $today = now()->toDateString();

        switch ($preset) {
            case 'all':
                $this->data_inicio = '';
                $this->data_fim = '';
                break;
            case 'today':
                $this->data_inicio = $today;
                $this->data_fim = $today;
                break;
            case '7d':
                $this->data_inicio = now()->subDays(7)->toDateString();
                $this->data_fim = $today;
                break;
            case '30d':
                $this->data_inicio = now()->subDays(30)->toDateString();
                $this->data_fim = $today;
                break;
            case '90d':
                $this->data_inicio = now()->subDays(90)->toDateString();
                $this->data_fim = $today;
                break;
        }

        $this->resetPage();
    }

    public function applyStockPreset(string $preset): void
    {
        switch ($preset) {
            case 'all':
                $this->estoque = '';
                $this->estoque_valor = '';
                $this->semEstoque = false;
                break;
            case 'zero':
                $this->estoque = 'zerado';
                $this->estoque_valor = '';
                $this->semEstoque = true;
                break;
            case 'low-5':
                $this->estoque = 'abaixo';
                $this->estoque_valor = '5';
                $this->semEstoque = false;
                break;
            case 'low-10':
                $this->estoque = 'abaixo';
                $this->estoque_valor = '10';
                $this->semEstoque = false;
                break;
            case 'low-20':
                $this->estoque = 'abaixo';
                $this->estoque_valor = '20';
                $this->semEstoque = false;
                break;
        }

        $this->resetPage();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedProducts = $this->products->pluck('id')->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function updatedSelectedProducts()
    {
        $this->selectAll = count($this->selectedProducts) === $this->products->count();
    }

    public function deleteSelected()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Nenhum produto selecionado.');
            return;
        }

        $products = Product::whereIn('id', $this->selectedProducts)
            ->where('user_id', Auth::id())
            ->get();

        if ($products->isEmpty()) {
            session()->flash('error', 'Voce so pode excluir produtos da sua propria conta.');
            return;
        }

        foreach ($products as $product) {
            // Verificar se a imagem do produto não é a imagem padrão
            if ($product->image && $product->image !== 'product-placeholder.png') {
                // Excluir a imagem do produto
                Storage::delete('public/products/' . $product->image);
            }

            $product->delete();
        }

        $this->selectedProducts = [];
        $this->selectAll = false;

        session()->flash('success', count($products) . ' produtos excluídos com sucesso!');
    }

    public function exportSelected()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Nenhum produto selecionado.');
            return;
        }

        // Aqui você pode implementar a lógica de exportação
        session()->flash('info', 'Funcionalidade de exportação em desenvolvimento.');
    }

    public function confirmDelete($productId)
    {
        $this->deletingProduct = Product::whereKey($productId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$this->deletingProduct) {
            session()->flash('error', 'Voce so pode excluir produtos da sua propria conta.');
            return;
        }

        $this->showDeleteModal = true;
    }

    public function confirmDeleteSelected()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('warning', 'Nenhum produto selecionado para exclusão.');
            return;
        }

        $this->deletingProduct = Product::whereIn('id', $this->selectedProducts)
            ->where('user_id', Auth::id())
            ->first();

        if (!$this->deletingProduct) {
            session()->flash('error', 'Voce so pode excluir produtos da sua propria conta.');
            return;
        }

        $this->showDeleteModal = true;
    }

    public function delete()
    {
        // Se há produtos selecionados, excluir em massa
        if (!empty($this->selectedProducts)) {
            $deletedCount = 0;

            $products = Product::whereIn('id', $this->selectedProducts)
                ->where('user_id', Auth::id())
                ->get();

            foreach ($products as $product) {
                if ($product->image && $product->image !== 'product-placeholder.png') {
                    Storage::delete('public/products/' . $product->image);
                }

                $product->delete();
                $deletedCount++;
            }

            if ($deletedCount === 0) {
                session()->flash('error', 'Voce so pode excluir produtos da sua propria conta.');
                $this->showDeleteModal = false;
                $this->deletingProduct = null;
                return;
            }

            session()->flash('success', "{$deletedCount} produto(s) excluído(s) com sucesso!");
            $this->selectedProducts = [];
            $this->selectAll = false;
        }
        // Se há apenas um produto para deletar
        elseif ($this->deletingProduct) {
            // Verificar se a imagem do produto não é a imagem padrão
            if ($this->deletingProduct->image && $this->deletingProduct->image !== 'product-placeholder.png') {
                // Excluir a imagem do produto
                Storage::delete('public/products/' . $this->deletingProduct->image);
            }

            // Excluir o produto
            $this->deletingProduct->delete();

            session()->flash('success', 'Produto excluído com sucesso!');
        }

        $this->showDeleteModal = false;
        $this->deletingProduct = null;
    }

    /**
     * Gera as opções de paginação com base no cliente.
     * Ultra Wind: múltiplos de 8. Padrão: múltiplos de 6.
     */
    public function getPerPageOptions(): array
    {
        if ($this->usesUltraMultipliers()) {
            // Múltiplos de 8 para grids mais largos
            return [32, 40, 48, 56, 64, 80, 96];
        }

        if ($this->fullHdLayout) {
            // Layout pensado para monitores 1920px (5 cards por linha)
            return [25, 30, 35, 40, 45, 50];
        }

        if ($this->ipadLandscapeLayout) {
            // iPad landscape: 7 colunas por linha → múltiplos de 7
            return [14, 21, 28, 35, 42];
        }

        // Múltiplos de 6 (padrão)
        return [12, 18, 24, 30, 36, 42, 48];
    }

    public function toggleTips()
    {
        $this->showTipsModal = !$this->showTipsModal;
    }

    #[On('product-created')]
    #[On('product-updated')]
    #[On('kit-created')]
    #[On('kit-updated')]
    public function refreshList()
    {
        // Força o refresh dos dados
        $this->render();
    }

    public function getProductsProperty()
    {
        // Rolagem infinita: uma "página" só, que cresce de $perPage em $perPage
        $products = $this->productsQuery()->with(['activePromotion', 'images'])->paginate($this->chunk() * max(1, $this->pages), ['*'], 'page', 1);

        return $this->finishPaginator($products);
    }

    /** Lista com todos os filtros e a ordem atuais (sem paginar). */
    private function productsQuery()
    {
        $query = Product::query();

        // Variações: o card do principal mostra as variações (carrossel), então a
        // lista só traz principais e produtos sem variação, inclusive na busca.
        $query->whereNull('parent_id');

        // Busca por nome ou código; se bater numa variação, aparece o principal dela.
        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $code = '%' . str_replace('.', '', trim($this->search)) . '%';
            $query->where(function ($q) use ($term, $code) {
                $q->where('products.name', 'like', $term)
                    ->orWhere(DB::raw("REPLACE(products.product_code, '.', '')"), 'like', $code)
                    ->orWhereExists(function ($v) use ($term, $code) {
                        $v->select(DB::raw(1))->from('products as pv')
                            ->whereColumn('pv.parent_id', 'products.id')
                            ->where(fn ($w) => $w->where('pv.name', 'like', $term)
                                ->orWhere('pv.variation_value', 'like', $term)
                                ->orWhere(DB::raw("REPLACE(pv.product_code, '.', '')"), 'like', $code));
                    });
            });
        }

        // Filtro rápido por letra
        if ($this->letra !== '') {
            if ($this->letra === '#') {
                $query->whereRaw("UPPER(SUBSTR(TRIM(products.name), 1, 1)) NOT BETWEEN 'A' AND 'Z'");
            } elseif (preg_match('/^[A-Z]$/', $this->letra)) {
                $query->whereRaw('UPPER(SUBSTR(TRIM(products.name), 1, 1)) = ?', [$this->letra]);
            }
        }

        // Filtro por categoria
        if (!empty($this->category)) {
            $query->where('category_id', $this->category);
        }

        // Filtro de status
        $statusValidos = ['ativo', 'inativo', 'descontinuado'];
        if (!empty($this->status_filtro) && in_array($this->status_filtro, $statusValidos)) {
            $query->where('status', $this->status_filtro);
        }

        // Filtro por tipo (simples ou kit)
        if (!empty($this->tipo) && in_array($this->tipo, ['simples', 'kit'])) {
            $query->where('tipo', $this->tipo);
        }

        // Filtro por faixa de preço
        if (!empty($this->preco_min) && is_numeric($this->preco_min)) {
            $query->where('price_sale', '>=', $this->preco_min);
        }
        if (!empty($this->preco_max) && is_numeric($this->preco_max)) {
            $query->where('price_sale', '<=', $this->preco_max);
        }

        // Filtro por estoque
        if (!empty($this->estoque)) {
            if ($this->estoque === 'zerado') {
                $query->where('stock_quantity', 0);
            } elseif ($this->estoque === 'abaixo' && is_numeric($this->estoque_valor)) {
                $query->where('stock_quantity', '<', $this->estoque_valor);
            }
        }

        // Filtro por data de criação
        if (!empty($this->data_inicio)) {
            $query->whereDate('created_at', '>=', $this->data_inicio);
        }
        if (!empty($this->data_fim)) {
            $query->whereDate('created_at', '<=', $this->data_fim);
        }

        // Filtro para produtos sem imagem personalizada
        if ($this->sem_imagem) {
            $query->where(function ($q) {
                $q->whereNull('image')
                  ->orWhere('image', '')
                  ->orWhere('image', 'product-placeholder.png');
            });
        }

        // Filtro: somente produtos em promoção valendo agora
        if ($this->em_promocao) {
            $query->where('stock_quantity', '>', 0)
                ->whereHas('promotions', fn ($q) => $q->current());
        }

        // Filtro de sem estoque (apenas produtos simples)
        if ($this->semEstoque) {
            $query->where('tipo', 'simples')->where('stock_quantity', '<=', 0);
        }

        // Com estoque primeiro: kit (estoque vem dos itens) e família com alguma
        // variação com estoque contam como "com estoque".
        $inStock = "CASE WHEN products.tipo = 'kit' OR products.stock_quantity > 0 OR EXISTS ("
            . "SELECT 1 FROM products v WHERE v.parent_id = products.id AND v.stock_quantity > 0"
            . ") THEN 0 ELSE 1 END";
        // Código em ordem de número (95.691 antes de 1234.567); códigos sem número no fim.
        $codeNum = "CAST(REPLACE(products.product_code, '.', '') AS DECIMAL(20,0))";

        // Ordenação
        switch ($this->ordem) {
            case 'recentes':
            case 'data_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'antigas':
            case 'data_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'atualizados':
            case 'updated_desc':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'updated_asc':
                $query->orderBy('updated_at', 'asc');
                break;
            case 'az':
            case 'nome_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'za':
            case 'nome_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'preco_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'preco_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'estoque_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'estoque_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'codigo_desc':
                $query->orderByRaw($inStock)
                    ->orderByRaw("$codeNum = 0")
                    ->orderByRaw("$codeNum DESC")
                    ->orderBy('product_code', 'desc');
                break;
            default:
                // Padrão: código crescente, sem estoque por último
                $query->orderByRaw($inStock)
                    ->orderByRaw("$codeNum = 0")
                    ->orderByRaw($codeNum)
                    ->orderBy('product_code');
        }
        $query->orderBy('products.id');

        return $query;
    }

    private function finishPaginator($products)
    {

        // Garante que os links de paginação apontem para a rota pública (evita herdar
        // o caminho interno do Livewire como `/livewire/update`) e preserva os filtros
        // atuais, especialmente `perPage`, para que a seleção de itens por página não seja perdida.
        try {
            $appends = [
                'search' => $this->search,
                'category' => $this->category,
                'status_filtro' => $this->status_filtro,
                'tipo' => $this->tipo,
                'preco_min' => $this->preco_min,
                'preco_max' => $this->preco_max,
                'estoque' => $this->estoque,
                'estoque_valor' => $this->estoque_valor,
                'data_inicio' => $this->data_inicio,
                'data_fim' => $this->data_fim,
                'ordem' => $this->ordem,
                'perPage' => $this->perPage,
                'sem_imagem' => $this->sem_imagem ? 1 : 0,
                'em_promocao' => $this->em_promocao ? 1 : 0,
            ];

            // Remover chaves vazias para URLs mais limpas
            $appends = array_filter($appends, function ($v) {
                return $v !== '' && $v !== null;
            });

            $products->appends($appends);
            $products->setPath(route('products.index'));
        } catch (\Exception $e) {
            Log::warning('Falha ao ajustar path do paginator: ' . $e->getMessage());
        }

        return $products;
    }

    public function getCategoriesProperty()
    {
        $viewer = Auth::user();
        $visibleUserIds = $viewer instanceof User
            ? $viewer->visibleTeamUserIds('products')
            : [Auth::id()];

        return Category::whereIn('user_id', $visibleUserIds)
            ->where('type', 'product')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.products.products-index', [
            'products' => $this->products,
            'categories' => $this->categories,
            'perPageOptions' => $this->getPerPageOptions(),
        ]);
    }
}
