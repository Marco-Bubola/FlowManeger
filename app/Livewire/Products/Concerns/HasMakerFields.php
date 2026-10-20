<?php

namespace App\Livewire\Products\Concerns;

use App\Models\Product;

/**
 * Campos do Novo/Editar produto pensados para peças grandes ou feitas à mão
 * (móveis de ferro e madeira): embalagem para o frete, sob encomenda com
 * prazo de produção e ficha de custo (materiais e horas que somam o custo).
 */
trait HasMakerFields
{
    public string $weight_kg = '';      // a tela pede em kg; o banco guarda gramas
    public string $length_cm = '';
    public string $width_cm = '';
    public string $height_cm = '';
    public bool $made_to_order = false;
    public string $production_days = '';
    /** @var array<int, array{n: string, q: string, v: string}> */
    public array $cost_items = [];

    protected function makerRules(): array
    {
        return [
            'weight_kg' => 'nullable|numeric|min:0|max:1000',
            'length_cm' => 'nullable|numeric|min:0|max:1000',
            'width_cm' => 'nullable|numeric|min:0|max:1000',
            'height_cm' => 'nullable|numeric|min:0|max:1000',
            'production_days' => 'nullable|integer|min:0|max:365',
            'cost_items' => 'array|max:40',
            'cost_items.*.n' => 'nullable|string|max:80',
            'cost_items.*.q' => 'nullable|numeric|min:0',
            'cost_items.*.v' => 'nullable|numeric|min:0',
        ];
    }

    protected function fillMakerFields(Product $product): void
    {
        // Mostra com vírgula, como o resto do app (0,9 kg); ao salvar volta a ponto
        $num = fn ($v) => $v !== null && (float) $v > 0 ? str_replace('.', ',', rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.')) : '';
        $this->weight_kg = $product->weight_grams ? $num($product->weight_grams / 1000) : '';
        $this->length_cm = $num($product->length_cm);
        $this->width_cm = $num($product->width_cm);
        $this->height_cm = $num($product->height_cm);
        $this->made_to_order = (bool) $product->made_to_order;
        $this->production_days = $product->production_days ? (string) $product->production_days : '';
        $this->cost_items = collect($product->cost_items ?? [])
            ->map(fn ($i) => ['n' => (string) ($i['n'] ?? ''), 'q' => (string) ($i['q'] ?? '1'), 'v' => (string) ($i['v'] ?? '')])
            ->values()->all();
    }

    /** Colunas do produto a partir dos campos da tela. */
    protected function makerData(): array
    {
        $dec = fn ($v) => trim((string) $v) === '' ? null : round((float) str_replace(',', '.', (string) $v), 2);
        $kg = $dec($this->weight_kg);

        return [
            'weight_grams' => $kg ? (int) round($kg * 1000) : null,
            'length_cm' => $dec($this->length_cm) ?: null,
            'width_cm' => $dec($this->width_cm) ?: null,
            'height_cm' => $dec($this->height_cm) ?: null,
            'made_to_order' => $this->made_to_order,
            'production_days' => $this->made_to_order && (int) $this->production_days > 0 ? (int) $this->production_days : null,
            'cost_items' => $this->cleanCostItems() ?: null,
        ];
    }

    /** Antes de validar: vírgula vira ponto nos números (o teclado do celular manda "1,5"). */
    protected function normalizeMakerNumbers(): void
    {
        foreach (['weight_kg', 'length_cm', 'width_cm', 'height_cm'] as $f) {
            $this->{$f} = str_replace(',', '.', trim($this->{$f}));
        }
        foreach ($this->cost_items as $i => $row) {
            $this->cost_items[$i]['q'] = str_replace(',', '.', trim((string) ($row['q'] ?? '')));
            $this->cost_items[$i]['v'] = str_replace(',', '.', trim((string) ($row['v'] ?? '')));
        }
    }

    protected function cleanCostItems(): array
    {
        return collect($this->cost_items)
            ->map(fn ($i) => [
                'n' => trim((string) ($i['n'] ?? '')),
                'q' => (float) str_replace(',', '.', (string) ($i['q'] ?? '')) ?: 1,
                'v' => round((float) str_replace(',', '.', (string) ($i['v'] ?? '')), 2),
            ])
            ->filter(fn ($i) => $i['n'] !== '' || $i['v'] > 0)
            ->values()->all();
    }

    /** Sob encomenda costuma não ter peça pronta: estoque vazio vira 0. */
    public function updatedMadeToOrder(): void
    {
        if ($this->made_to_order && property_exists($this, 'stock_quantity') && trim((string) $this->stock_quantity) === '') {
            $this->stock_quantity = '0';
        }
    }

    public function addCostItem(): void
    {
        $this->cost_items[] = ['n' => '', 'q' => '1', 'v' => ''];
    }

    public function removeCostItem(int $index): void
    {
        unset($this->cost_items[$index]);
        $this->cost_items = array_values($this->cost_items);
    }

    public function getCostItemsTotalProperty(): float
    {
        return Product::costItemsTotal($this->cost_items);
    }

    /** "Usar como custo": a soma da ficha vai para o campo Custo. */
    public function applyCostItemsTotal(): void
    {
        $total = $this->costItemsTotal;
        if ($total <= 0) {
            return;
        }
        $this->price = number_format($total, 2, '.', '');
        $this->dispatch('currency-input-set', name: 'price', value: $this->price);
    }
}
