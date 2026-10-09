<?php

namespace App\Livewire\Products;

use App\Models\MlStockLog;
use App\Models\Product;
use App\Services\Stock\LowStockService;
use App\Traits\HasNotifications;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Acertar estoque das variações.
 * Antes da correção de 08/10/2026 o envio de PDF / cadastro às vezes punha o
 * estoque na linha errada da família (no principal, ou numa cor que não era a
 * dela). Aqui as famílias com cara de erro aparecem primeiro e o estoque de cada
 * linha é acertado de uma vez, numa transação só.
 */
class VariationStockFix extends Component
{
    use HasNotifications;
    use WithPagination;

    public const PER_PAGE = 12;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filter = 'suspeitas'; // suspeitas | todas

    /** Estoque editado: [rootId => [productId => qtd]] */
    public array $stocks = [];

    /** Variante escolhida no "mover estoque do principal para...": [rootId => productId] */
    public array $moveTarget = [];

    public function updating($name): void
    {
        if (in_array($name, ['search', 'filter'], true)) {
            $this->resetPage();
        }
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter === 'todas' ? 'todas' : 'suspeitas';
        $this->resetPage();
    }

    /**
     * Todas as famílias (pai + variantes) do usuário logado, já com os sinais de erro.
     * Kit não é variação: fica de fora.
     */
    public function families(?int $userId = null): Collection
    {
        $userId ??= (int) Auth::id();

        $rows = Product::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where(fn ($q) => $q->where('tipo', '!=', 'kit')->orWhereNull('tipo'))
            ->where(fn ($q) => $q->where('is_variation_parent', true)->orWhereNotNull('parent_id'))
            ->orderBy('variation_sort')
            ->orderBy('id')
            ->get(['id', 'user_id', 'tipo', 'name', 'product_code', 'parent_id', 'is_variation_parent', 'variation_attribute', 'variation_value', 'variation_sort', 'stock_quantity', 'image']);

        $parents = $rows->whereNull('parent_id')->keyBy('id');

        return $rows->whereNotNull('parent_id')
            ->groupBy('parent_id')
            ->filter(fn ($variants, $rootId) => $parents->has($rootId))
            ->map(fn ($variants, $rootId) => self::analyze($parents[$rootId], $variants->values()))
            ->values();
    }

    /** Sinais de estoque na linha errada (regras explicadas na tela). */
    public static function analyze(Product $parent, Collection $variants): array
    {
        $flags = [];
        $parentStock = (int) $parent->stock_quantity;
        $zeroVariants = $variants->filter(fn ($v) => (int) $v->stock_quantity <= 0)->count();
        $withStock = $variants->filter(fn ($v) => (int) $v->stock_quantity > 0)->count();

        if ($parentStock > 0 && $zeroVariants > 0) {
            $flags['parent_stock'] = $zeroVariants === $variants->count()
                ? 'Principal com estoque e todas as variações zeradas'
                : "Principal com estoque e {$zeroVariants} variação(ões) zerada(s)";
        }

        $members = collect([$parent])->merge($variants);
        $codes = $members->map(fn ($p) => mb_strtolower(trim((string) $p->product_code)))->filter();
        if ($codes->count() !== $codes->unique()->count()) {
            $flags['duplicate_code'] = 'Código repetido dentro da família';
        }

        $labels = $variants->map(fn ($v) => mb_strtolower(trim((string) ($v->variation_value ?: $v->name))))->filter();
        if ($labels->count() !== $labels->unique()->count()) {
            $flags['duplicate_name'] = 'Variações com o mesmo nome';
        }

        if ($parentStock <= 0 && $variants->count() >= 2 && $withStock === 1) {
            $flags['concentrated'] = 'Todo o estoque numa só variação';
        }

        // Peso: estoque no principal e código repetido pesam mais
        $score = (isset($flags['parent_stock']) ? 4 : 0) + (isset($flags['duplicate_code']) ? 3 : 0)
            + (isset($flags['duplicate_name']) ? 2 : 0) + (isset($flags['concentrated']) ? 1 : 0);

        return [
            'root_id' => (int) $parent->id,
            'parent' => $parent,
            'variants' => $variants,
            'flags' => $flags,
            'score' => $score,
            'total' => $parentStock + (int) $variants->sum('stock_quantity'),
        ];
    }

    /** Leva todo o estoque (editado) do principal para a variante escolhida. */
    public function moveParentStock(int $rootId): void
    {
        $target = (int) ($this->moveTarget[$rootId] ?? 0);
        if (! $target || ! isset($this->stocks[$rootId][$rootId], $this->stocks[$rootId][$target]) || $target === $rootId) {
            $this->notifyWarning('Escolha para qual variação mover o estoque.');
            return;
        }

        $amount = max(0, (int) $this->stocks[$rootId][$rootId]);
        $this->stocks[$rootId][$target] = max(0, (int) $this->stocks[$rootId][$target]) + $amount;
        $this->stocks[$rootId][$rootId] = 0;
    }

    /** Volta os números da família para o que está salvo. */
    public function resetFamily(int $rootId): void
    {
        unset($this->stocks[$rootId], $this->moveTarget[$rootId]);
    }

    public function saveFamily(int $rootId): void
    {
        $userId = (int) Auth::id();
        $edited = $this->stocks[$rootId] ?? [];

        $parent = Product::withoutGlobalScopes()->where('user_id', $userId)
            ->whereNull('parent_id')->find($rootId);
        if (! $parent || $parent->isKit() || empty($edited)) {
            $this->notifyError('Família não encontrada.');
            return;
        }

        foreach ($edited as $qty) {
            if (! is_numeric($qty) || (int) $qty < 0 || (int) $qty > 1000000 || (string) (int) $qty !== trim((string) $qty)) {
                $this->notifyError('Use só números inteiros de 0 para cima.');
                return;
            }
        }

        try {
            $result = self::applyStocks($userId, $parent, $edited);
        } catch (\Throwable $e) {
            Log::error('VariationStockFix: erro ao salvar família', ['root' => $rootId, 'error' => $e->getMessage()]);
            $this->notifyError('Não foi possível salvar: ' . $e->getMessage());
            return;
        }

        unset($this->stocks[$rootId], $this->moveTarget[$rootId]);

        if ($result['changed'] === []) {
            $this->notifyInfo('Nada mudou nesta família.');
            return;
        }

        $this->notifySuccess(sprintf(
            'Estoque de "%s" acertado (%d linha(s)). Total: %d → %d',
            $parent->name, count($result['changed']), $result['before'], $result['after']
        ));
    }

    /**
     * Grava os novos estoques da família numa transação. Só mexe em linhas que são
     * da família e do usuário. O ProductObserver grava cada mudança em
     * Movimentações; aqui só se marca a nota como acerto.
     *
     * @return array{changed: array<int,int>, before: int, after: int}
     */
    public static function applyStocks(int $userId, Product $parent, array $edited): array
    {
        $changed = [];
        $before = 0;
        $after = 0;

        DB::transaction(function () use ($userId, $parent, $edited, &$changed, &$before, &$after) {
            $members = Product::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->where(fn ($q) => $q->where('id', $parent->id)->orWhere('parent_id', $parent->id))
                ->lockForUpdate()
                ->get();

            foreach ($members as $member) {
                $old = (int) $member->stock_quantity;
                $before += $old;

                if ($member->isKit() || ! array_key_exists($member->id, $edited)) {
                    $after += $old;
                    continue;
                }

                $new = max(0, (int) $edited[$member->id]);
                $after += $new;

                if ($new !== $old) {
                    $member->update(['stock_quantity' => $new]);
                    $changed[$member->id] = $new - $old;

                    try {
                        MlStockLog::where('product_id', $member->id)
                            ->whereNull('ml_publication_id')
                            ->latest('id')
                            ->first()
                            ?->update(['notes' => "Acerto de estoque das variações: {$old} → {$new}"]);
                    } catch (\Throwable $e) {
                        // nota é só detalhe; a mudança já foi registrada
                    }
                }
            }
        });

        if ($changed !== []) {
            try {
                app(LowStockService::class)->checkProducts($userId, array_keys($changed));
            } catch (\Throwable $e) {
                Log::warning('VariationStockFix: aviso de estoque baixo falhou', ['error' => $e->getMessage()]);
            }
        }

        return compact('changed', 'before', 'after');
    }

    public function render()
    {
        $all = $this->families();
        $suspectCount = $all->where('score', '>', 0)->count();

        $term = mb_strtolower(trim($this->search));
        $list = $all
            ->when($this->filter !== 'todas', fn ($c) => $c->where('score', '>', 0))
            ->when($term !== '', fn ($c) => $c->filter(function ($f) use ($term) {
                return collect([$f['parent']])->merge($f['variants'])->contains(fn ($p) =>
                    str_contains(mb_strtolower((string) $p->name), $term)
                    || str_contains(mb_strtolower((string) $p->product_code), $term)
                    || str_contains(mb_strtolower((string) $p->variation_value), $term));
            }))
            ->sort(fn ($a, $b) => [$b['score'], mb_strtolower($a['parent']->name)] <=> [$a['score'], mb_strtolower($b['parent']->name)])
            ->values();

        $page = max(1, min($this->getPage(), (int) ceil(max(1, $list->count()) / self::PER_PAGE)));
        $families = new LengthAwarePaginator(
            $list->forPage($page, self::PER_PAGE)->values(),
            $list->count(),
            self::PER_PAGE,
            $page
        );

        // Valores editáveis começam no estoque salvo (edições em andamento ficam)
        foreach ($families as $f) {
            foreach (collect([$f['parent']])->merge($f['variants']) as $p) {
                if (! isset($this->stocks[$f['root_id']][$p->id])) {
                    $this->stocks[$f['root_id']][$p->id] = (int) $p->stock_quantity;
                }
            }
        }

        return view('livewire.products.variation-stock-fix', [
            'families' => $families,
            'suspectCount' => $suspectCount,
            'familyCount' => $all->count(),
        ])->layout('components.layouts.app');
    }
}
