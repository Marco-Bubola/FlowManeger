<?php

namespace App\Livewire\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Rolagem infinita nas listas (igual à lista de produtos): em vez de trocar de página,
 * a lista cresce de bloco em bloco quando o fim aparece na tela.
 *
 * Usa o número de página do WithPagination como "quantos blocos já aparecem":
 * resetPage() (filtros, busca, ordem) volta para o primeiro bloco e o ?page=N da
 * URL mantém a mesma quantidade carregada ao recarregar a tela.
 * A tela usa <x-infinite-feed :paginator="..." />.
 */
trait WithInfiniteScroll
{
    /** Teto de itens numa tela só, para a página não ficar pesada demais. */
    protected int $infiniteMax = 1200;

    public function loadMore(string $pageName = 'page'): void
    {
        $this->setPage($this->getPage($pageName) + 1, $pageName);
    }

    /** Quantos itens mostrar agora (blocos carregados × tamanho do bloco). */
    protected function infiniteSize(int $chunk, string $pageName = 'page'): int
    {
        $chunk = max(1, $chunk);

        return min($this->infiniteMax, $chunk * max(1, (int) $this->getPage($pageName)));
    }

    /** Pagina uma consulta: sempre a "página 1" com todos os blocos já carregados. */
    protected function infinitePaginate($query, int $chunk, string $pageName = 'page', array $columns = ['*'])
    {
        return $query->paginate($this->infiniteSize($chunk, $pageName), $columns, $pageName, 1);
    }

    /** Mesmo que infinitePaginate, para listas montadas em memória. */
    protected function infiniteCollection(Collection $items, int $chunk, string $pageName = 'page'): LengthAwarePaginator
    {
        $size = $this->infiniteSize($chunk, $pageName);

        return new LengthAwarePaginator($items->take($size)->values(), $items->count(), $size, 1, ['pageName' => $pageName]);
    }
}
