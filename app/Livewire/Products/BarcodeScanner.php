<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BarcodeScanner extends Component
{
    // Input do código de barras
    public string $barcodeInput = '';

    // Produto encontrado localmente
    public ?array $foundProduct = null;

    // Estado da busca
    public ?string $searchMessage = null;
    public string $searchStatus = ''; // 'success' | 'error' | 'warning'

    // Modo ativo: consulta | estoque | inventario | venda | preco | vincular
    public string $activeMode = 'consulta';

    // --- Modo Estoque ---
    public int $stockDelta = 1;
    public string $stockOperation = 'add'; // 'add' | 'remove' | 'set'
    public int $stockSetValue = 0;

    // --- Modo Inventário ---
    public array $inventoryItems = [];
    public int $inventoryQtyInput = 1;

    // --- Modo Venda ---
    public array $saleItems = [];
    public int $saleQtyInput = 1;

    // --- Busca Online ---
    public ?array $onlineResult = null;
    public bool $onlineLoading = false;
    public ?string $onlineError = null;
    public array $onlineDebug = [];

    // --- Modo Vincular ---
    public string $linkSearchTerm = '';
    public array $linkCandidates = [];
    public ?int $selectedProductId = null;
    public ?string $lastScannedBarcode = null;

    // --- Histórico de escaneamentos ---
    public array $scanHistory = [];

    // Toast
    public ?string $toastMessage = null;
    public string $toastType = 'success';

    // Produtos sem barcode para listagem rápida
    public array $productsWithoutBarcode = [];

    // --- Scan batch counter ---
    public int $scanCount = 0;
    public ?string $detectedFormat = null;

    public function mount(): void
    {
        $this->activeMode = 'consulta';
        $this->loadProductsWithoutBarcode();
    }

    // -------------------------------------------------
    // Pesquisa do código de barras (local + online)
    // -------------------------------------------------

    public function searchBarcode(): void
    {
        $code = trim($this->barcodeInput);

        if (empty($code)) {
            $this->searchMessage = 'Digite ou leia um código de barras.';
            $this->searchStatus = 'warning';
            return;
        }

        $this->onlineResult = null;
        $this->onlineError = null;
        $this->onlineDebug = [];
        $this->lastScannedBarcode = $code;
        $this->scanCount++;
        $this->detectedFormat = $this->detectBarcodeFormat($code);

        // 1) Busca local
        $product = Product::where('user_id', Auth::id())
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhere('product_code', $code);
            })
            ->with('category')
            ->first();

        if ($product) {
            $this->foundProduct = $this->productToArray($product);
            $this->searchMessage = null;
            $this->searchStatus = 'success';
            $this->addScanHistory($code, $this->foundProduct);

            match ($this->activeMode) {
                'inventario' => $this->addToInventory(),
                'venda'      => $this->addToSale(),
                default      => null,
            };

            $this->stockSetValue = $this->foundProduct['stock_quantity'] ?? 0;
            $this->barcodeInput = '';

            // Busca online em background para enriquecer dados
            $this->searchOnline($code);
            return;
        }

        // 2) Não encontrou localmente
        $this->foundProduct = null;
        $this->searchMessage = "Nenhum produto local encontrado para: \"{$code}\"";
        $this->searchStatus = 'error';
        $this->addScanHistory($code, null);

        // Busca online automaticamente
        $this->searchOnline($code);

        // Se no modo vincular, busca candidatos
        if ($this->activeMode === 'vincular') {
            $this->searchLinkCandidates();
        }

        $this->barcodeInput = '';
    }

    // -------------------------------------------------
    // Busca Online — Open Food Facts + Cosmos API
    // -------------------------------------------------

    public function searchOnline(?string $code = null): void
    {
        $barcode = $code ?? trim($this->barcodeInput);
        if (empty($barcode)) {
            return;
        }

        // Validar que é numérico (códigos de barra padrão EAN/UPC)
        if (!preg_match('/^\d{8,14}$/', $barcode)) {
            $this->onlineError = 'Formato de código de barras inválido para busca online.';
            return;
        }

        $this->onlineLoading = true;
        $this->onlineResult = null;
        $this->onlineError = null;
        $this->onlineDebug = [];

        $client = Http::acceptJson()
            ->withHeaders([
                'User-Agent' => 'FlowManager/1.0 BarcodeScanner',
            ])
            ->timeout(12)
            ->connectTimeout(6);

        try {
            $sources = [
                ['label' => 'Open Food Facts', 'url' => "https://world.openfoodfacts.org/api/v0/product/{$barcode}.json", 'type' => 'open-facts'],
                ['label' => 'Open Beauty Facts', 'url' => "https://world.openbeautyfacts.org/api/v0/product/{$barcode}.json", 'type' => 'open-facts'],
                ['label' => 'Open Products Facts', 'url' => "https://world.openproductsfacts.org/api/v0/product/{$barcode}.json", 'type' => 'open-facts'],
                ['label' => 'UPC Item DB', 'url' => 'https://api.upcitemdb.com/prod/trial/lookup', 'type' => 'upc-item-db'],
            ];

            foreach ($sources as $source) {
                $this->onlineDebug[] = 'Consultando ' . $source['label'];

                $response = $source['type'] === 'upc-item-db'
                    ? $client->get($source['url'], ['upc' => $barcode])
                    : $client->get($source['url']);

                $this->onlineDebug[] = $source['label'] . ' respondeu HTTP ' . $response->status();

                if (! $response->successful()) {
                    continue;
                }

                $data = $response->json();

                if ($source['type'] === 'open-facts') {
                    if (($data['status'] ?? 0) !== 1 || empty($data['product'])) {
                        $this->onlineDebug[] = $source['label'] . ' não encontrou produto para o GTIN.';
                        continue;
                    }

                    $p = $data['product'];
                    $this->onlineResult = [
                        'source'      => $source['label'],
                        'name'        => $p['product_name'] ?? $p['product_name_pt'] ?? $p['product_name_en'] ?? null,
                        'brand'       => $p['brands'] ?? null,
                        'description' => $p['generic_name'] ?? $p['generic_name_pt'] ?? $p['generic_name_en'] ?? null,
                        'categories'  => $p['categories'] ?? null,
                        'image_url'   => $p['image_front_url'] ?? $p['image_url'] ?? null,
                        'barcode'     => $barcode,
                        'quantity'    => $p['quantity'] ?? null,
                        'countries'   => $p['countries'] ?? null,
                        'ingredients' => $p['ingredients_text_pt'] ?? $p['ingredients_text'] ?? null,
                        'nutriscore'  => $p['nutriscore_grade'] ?? null,
                        'raw_data'    => array_intersect_key($p, array_flip([
                            'product_name', 'brands', 'categories', 'quantity',
                            'image_front_url', 'countries', 'stores', 'labels',
                        ])),
                    ];
                    $this->onlineDebug[] = 'Produto encontrado em ' . $source['label'];
                    return;
                }

                if (($data['code'] ?? '') === 'OK' && !empty($data['items'])) {
                    $item = $data['items'][0];
                    $this->onlineResult = [
                        'source'      => $source['label'],
                        'name'        => $item['title'] ?? null,
                        'brand'       => $item['brand'] ?? null,
                        'description' => $item['description'] ?? null,
                        'categories'  => $item['category'] ?? null,
                        'image_url'   => !empty($item['images']) ? $item['images'][0] : null,
                        'barcode'     => $barcode,
                        'quantity'    => $item['size'] ?? null,
                        'countries'   => null,
                        'ingredients' => null,
                        'nutriscore'  => null,
                        'raw_data'    => array_intersect_key($item, array_flip([
                            'title', 'brand', 'category', 'size', 'color', 'weight',
                        ])),
                    ];
                    $this->onlineDebug[] = 'Produto encontrado em ' . $source['label'];
                    return;
                }

                $this->onlineDebug[] = $source['label'] . ' não retornou itens válidos.';
            }

            Log::info('BarcodeScanner: busca online sem resultado', [
                'barcode' => $barcode,
                'debug' => $this->onlineDebug,
            ]);

            $this->onlineError = 'Nenhuma base online retornou dados para este código.';
        } catch (\Throwable $e) {
            Log::warning('BarcodeScanner: erro na busca online', [
                'barcode' => $barcode,
                'error'   => $e->getMessage(),
                'debug'   => $this->onlineDebug,
            ]);
            $this->onlineError = 'Erro ao consultar bases online. Tente novamente.';
        } finally {
            $this->onlineLoading = false;
        }
    }

    // -------------------------------------------------
    // Aplicar dados da busca online ao produto local
    // -------------------------------------------------

    public function applyOnlineData(): void
    {
        if (! $this->foundProduct || ! $this->onlineResult) {
            $this->toast('Nenhum produto local ou dados online para aplicar.', 'warning');
            return;
        }

        $product = Product::where('id', $this->foundProduct['id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $product) {
            $this->toast('Produto não encontrado.', 'error');
            return;
        }

        $updated = [];

        if (empty($product->barcode) && !empty($this->onlineResult['barcode'])) {
            $product->barcode = $this->onlineResult['barcode'];
            $updated[] = 'código de barras';
        }

        if (empty($product->brand) && !empty($this->onlineResult['brand'])) {
            $product->brand = $this->onlineResult['brand'];
            $updated[] = 'marca';
        }

        if (empty($product->description) && !empty($this->onlineResult['description'])) {
            $product->description = $this->onlineResult['description'];
            $updated[] = 'descrição';
        }

        if (empty($updated)) {
            $this->toast('Nenhum campo vazio para preencher automaticamente.', 'info');
            return;
        }

        $product->save();
        $this->foundProduct = $this->productToArray($product);

        $this->toast('Produto atualizado com dados online: ' . implode(', ', $updated), 'success');
    }

    // -------------------------------------------------
    // Modo Vincular — associar barcode a produto existente
    // -------------------------------------------------

    public function searchLinkCandidates(): void
    {
        $term = trim($this->linkSearchTerm);

        $query = Product::where('user_id', Auth::id())
            ->with('category');

        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('product_code', 'like', "%{$term}%")
                  ->orWhere('brand', 'like', "%{$term}%")
                  ->orWhere('model', 'like', "%{$term}%");
            });
        } else {
            // Mostrar produtos sem barcode por padrão
            $query->whereNull('barcode')->orWhere('barcode', '');
        }

        $this->linkCandidates = $query->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($p) => $this->productToArray($p))
            ->toArray();
    }

    public function updatedLinkSearchTerm(): void
    {
        $this->searchLinkCandidates();
    }

    public function linkBarcodeToProduct(int $productId): void
    {
        if (empty($this->lastScannedBarcode)) {
            $this->toast('Escaneie um código de barras primeiro.', 'warning');
            return;
        }

        // Verificar se já existe outro produto com esse barcode
        $existing = Product::where('user_id', Auth::id())
            ->where('barcode', $this->lastScannedBarcode)
            ->where('id', '!=', $productId)
            ->first();

        if ($existing) {
            $this->toast("Este código de barras já está vinculado ao produto: {$existing->name}", 'error');
            return;
        }

        $product = Product::where('id', $productId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $product) {
            $this->toast('Produto não encontrado.', 'error');
            return;
        }

        $product->barcode = $this->lastScannedBarcode;
        $product->save();

        $this->foundProduct = $this->productToArray($product);
        $this->linkCandidates = [];
        $this->linkSearchTerm = '';
        $this->searchMessage = null;
        $this->searchStatus = 'success';

        $this->loadProductsWithoutBarcode();
        $this->toast("Código de barras vinculado ao produto: {$product->name}", 'success');
    }

    public function loadProductsWithoutBarcode(): void
    {
        $this->productsWithoutBarcode = Product::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->whereNull('barcode')->orWhere('barcode', '');
            })
            ->with('category')
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(fn($p) => $this->productToArray($p))
            ->toArray();
    }

    // -------------------------------------------------
    // Modo Estoque
    // -------------------------------------------------

    public function updateStock(): void
    {
        if (! $this->foundProduct) {
            return;
        }

        $product = Product::where('id', $this->foundProduct['id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $product) {
            $this->toast('Produto não encontrado.', 'error');
            return;
        }

        $oldQty = $product->stock_quantity;

        match ($this->stockOperation) {
            'add'    => $product->stock_quantity = max(0, $product->stock_quantity + $this->stockDelta),
            'remove' => $product->stock_quantity = max(0, $product->stock_quantity - $this->stockDelta),
            'set'    => $product->stock_quantity = max(0, $this->stockSetValue),
            default  => null,
        };

        $product->save();
        $this->foundProduct['stock_quantity'] = $product->stock_quantity;

        $operationLabel = match ($this->stockOperation) {
            'add'    => "+{$this->stockDelta} unid.",
            'remove' => "-{$this->stockDelta} unid.",
            'set'    => "definido para {$this->stockSetValue} unid.",
        };

        $this->toast("Estoque atualizado: {$oldQty} → {$product->stock_quantity} ({$operationLabel})", 'success');
    }

    // -------------------------------------------------
    // Modo Inventário
    // -------------------------------------------------

    public function addToInventory(): void
    {
        if (! $this->foundProduct) {
            return;
        }

        $barcode = $this->foundProduct['barcode'] ?: $this->foundProduct['product_code'];

        if (isset($this->inventoryItems[$barcode])) {
            $this->inventoryItems[$barcode]['qty'] += $this->inventoryQtyInput;
        } else {
            $this->inventoryItems[$barcode] = [
                'product'    => $this->foundProduct,
                'qty'        => $this->inventoryQtyInput,
                'scanned_at' => now()->format('H:i:s'),
            ];
        }

        $this->toast("Produto adicionado ao inventário: {$this->foundProduct['name']}", 'info');
    }

    public function removeFromInventory(string $barcode): void
    {
        unset($this->inventoryItems[$barcode]);
    }

    public function updateInventoryQty(string $barcode, int $qty): void
    {
        if (isset($this->inventoryItems[$barcode])) {
            $this->inventoryItems[$barcode]['qty'] = max(0, $qty);
        }
    }

    public function applyInventory(): void
    {
        if (empty($this->inventoryItems)) {
            $this->toast('Nenhum item no inventário para aplicar.', 'warning');
            return;
        }

        $updated = 0;

        DB::transaction(function () use (&$updated) {
            foreach ($this->inventoryItems as $item) {
                $product = Product::where('id', $item['product']['id'])
                    ->where('user_id', Auth::id())
                    ->first();

                if ($product) {
                    $product->stock_quantity = $item['qty'];
                    $product->save();
                    $updated++;
                }
            }
        });

        $this->inventoryItems = [];
        $this->toast("{$updated} produto(s) atualizado(s) com o estoque do inventário!", 'success');
    }

    public function clearInventory(): void
    {
        $this->inventoryItems = [];
        $this->toast('Inventário limpo.', 'info');
    }

    // -------------------------------------------------
    // Modo Venda
    // -------------------------------------------------

    public function addToSale(): void
    {
        if (! $this->foundProduct) {
            return;
        }

        $barcode = $this->foundProduct['barcode'] ?: $this->foundProduct['product_code'];

        if (isset($this->saleItems[$barcode])) {
            $this->saleItems[$barcode]['qty'] += $this->saleQtyInput;
        } else {
            $this->saleItems[$barcode] = [
                'product' => $this->foundProduct,
                'qty'     => $this->saleQtyInput,
            ];
        }

        $this->toast("Adicionado à venda: {$this->foundProduct['name']} (x{$this->saleQtyInput})", 'info');
    }

    public function removeFromSale(string $barcode): void
    {
        unset($this->saleItems[$barcode]);
    }

    public function updateSaleQty(string $barcode, int $qty): void
    {
        if (isset($this->saleItems[$barcode])) {
            if ($qty <= 0) {
                unset($this->saleItems[$barcode]);
            } else {
                $this->saleItems[$barcode]['qty'] = $qty;
            }
        }
    }

    public function clearSale(): void
    {
        $this->saleItems = [];
        $this->toast('Lista de venda limpa.', 'info');
    }

    public function getSaleTotalProperty(): float
    {
        return collect($this->saleItems)->sum(fn($item) =>
            ($item['product']['price_sale'] ?? $item['product']['price'] ?? 0) * $item['qty']
        );
    }

    public function getSaleItemsCountProperty(): int
    {
        return collect($this->saleItems)->sum('qty');
    }

    // -------------------------------------------------
    // Estatísticas
    // -------------------------------------------------

    public function getStatsProperty(): array
    {
        $userId = Auth::id();
        $total = Product::where('user_id', $userId)->count();
        $withBarcode = Product::where('user_id', $userId)
            ->whereNotNull('barcode')->where('barcode', '!=', '')->count();
        $withoutBarcode = $total - $withBarcode;
        $lowStock = Product::where('user_id', $userId)
            ->where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0)->count();

        return [
            'total'          => $total,
            'with_barcode'   => $withBarcode,
            'without_barcode' => $withoutBarcode,
            'low_stock'      => $lowStock,
            'percentage'     => $total > 0 ? round(($withBarcode / $total) * 100) : 0,
        ];
    }

    // -------------------------------------------------
    // Histórico
    // -------------------------------------------------

    private function addScanHistory(string $code, ?array $product): void
    {
        array_unshift($this->scanHistory, [
            'code'       => $code,
            'product'    => $product,
            'scanned_at' => now()->format('H:i:s'),
            'found'      => $product !== null,
        ]);

        $this->scanHistory = array_slice($this->scanHistory, 0, 15);
    }

    public function clearHistory(): void
    {
        $this->scanHistory = [];
    }

    // -------------------------------------------------
    // Helpers
    // -------------------------------------------------

    private function productToArray(Product $product): array
    {
        return [
            'id'             => $product->id,
            'name'           => $product->name,
            'barcode'        => $product->barcode,
            'product_code'   => $product->product_code,
            'description'    => $product->description,
            'price'          => (float) $product->price,
            'price_sale'     => (float) ($product->price_sale ?? $product->price),
            'stock_quantity' => (int) $product->stock_quantity,
            'status'         => $product->status,
            'brand'          => $product->brand,
            'model'          => $product->model,
            'category_name'  => $product->category?->name ?? '—',
            'category_icon'  => $product->category?->icone ?? 'bi bi-box',
            'image'          => $product->image,
            'condition'      => $product->condition,
            'weight_grams'   => $product->weight_grams,
        ];
    }

    private function toast(string $message, string $type = 'success'): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
        $this->dispatch('show-toast', message: $message, type: $type);
    }

    public function setMode(string $mode): void
    {
        $validModes = ['consulta', 'estoque', 'inventario', 'venda', 'preco', 'vincular'];
        if (in_array($mode, $validModes)) {
            $this->activeMode = $mode;
            $this->foundProduct = null;
            $this->searchMessage = null;
            $this->barcodeInput = '';
            $this->onlineResult = null;
            $this->onlineError = null;
            $this->linkCandidates = [];
            $this->linkSearchTerm = '';

            if ($mode === 'vincular') {
                $this->loadProductsWithoutBarcode();
                $this->searchLinkCandidates();
            }
        }
    }

    /**
     * Detect barcode format from its length/pattern.
     */
    public function detectBarcodeFormat(string $code): string
    {
        $len = strlen($code);
        if (!ctype_digit($code)) {
            if (preg_match('/^[A-Z0-9\-\.\ \$\/\+\%]+$/', $code)) return 'CODE 39';
            return 'Desconhecido';
        }
        return match (true) {
            $len === 8  => 'EAN-8',
            $len === 13 => 'EAN-13',
            $len === 12 => 'UPC-A',
            $len === 6  => 'UPC-E',
            $len === 14 => 'ITF-14',
            $len >= 8 && $len <= 48 => 'CODE 128',
            default => 'Numérico',
        };
    }

    /**
     * Export scan history as CSV text.
     */
    public function getExportHistoryProperty(): string
    {
        if (empty($this->scanHistory)) return '';

        $lines = ["Hora,Código,Formato,Produto,Status"];
        foreach ($this->scanHistory as $entry) {
            $format = $this->detectBarcodeFormat($entry['code']);
            $product = $entry['found'] ? str_replace(',', ';', $entry['product']['name']) : '—';
            $status = $entry['found'] ? 'Encontrado' : 'Não encontrado';
            $lines[] = "{$entry['scanned_at']},{$entry['code']},{$format},{$product},{$status}";
        }
        return implode("\n", $lines);
    }

    /**
     * Enhanced session stats.
     */
    public function getSessionStatsProperty(): array
    {
        $total = count($this->scanHistory);
        $found = collect($this->scanHistory)->where('found', true)->count();
        $notFound = $total - $found;

        return [
            'total_scans'  => $total,
            'found'        => $found,
            'not_found'    => $notFound,
            'success_rate' => $total > 0 ? round(($found / $total) * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.products.barcode-scanner');
    }
}
