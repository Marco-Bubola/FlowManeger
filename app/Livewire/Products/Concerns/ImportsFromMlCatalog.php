<?php

namespace App\Livewire\Products\Concerns;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\MercadoLivre\ProductService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Ficha técnica + importar do catálogo do Mercado Livre (fotos, capa, descrição,
 * ficha técnica, marca, modelo, EAN e nome). Usado no novo produto e no editar produto.
 * A tela usa o partial livewire.products.partials.ml-catalog-import.
 */
trait ImportsFromMlCatalog
{
    // Ficha técnica [{n: nome, v: valor}]
    public array $specs = [];

    public bool $showMlImport = false;
    public string $mlQuery = '';
    public array $mlCandidates = [];
    public array $mlData = [];          // produto do catálogo escolhido
    public array $mlPhotos = [];        // [índice => bool] fotos marcadas
    public array $mlTake = [];          // o que levar: desc, specs, brand, name, cover
    public string $mlSearchError = '';

    // Produto novo: fotos e capa esperam o produto ser salvo
    public array $mlPendingPhotos = [];
    public string $mlPendingCover = '';

    // Variações: o que veio do ML também vai para as outras variações da família
    public bool $mlToFamily = true;
    public array $mlFamilyTake = [];    // campos de texto a copiar ao salvar: desc, specs, brand

    /** Produto que recebe fotos e capa na hora (null = ainda não foi salvo). */
    abstract protected function mlTargetProduct(): ?Product;

    protected function mlAfterApply(): void {}

    protected function mlKnownPhotoUrls(): array
    {
        $p = $this->mlTargetProduct();
        return $p ? $p->images()->pluck('source_url')->filter()->all() : $this->mlPendingPhotos;
    }

    protected function mlHasMainImage(): bool
    {
        $p = $this->mlTargetProduct();
        if ($p) {
            return $p->image && $p->image !== 'product-placeholder.png';
        }
        return (bool) ($this->image ?? null) || $this->mlPendingCover !== '';
    }

    /** As outras variações da família do produto (vazio se não tiver variações). */
    protected function mlFamilyOthers(): \Illuminate\Support\Collection
    {
        $p = $this->mlTargetProduct();
        if (!$p || !$p->hasVariations()) {
            return collect();
        }
        return $p->family()->where('id', '!=', $p->id)->get();
    }

    public function getMlFamilyCountProperty(): int
    {
        return $this->mlFamilyOthers()->count();
    }

    /** Campos da ficha que são de cada variação (cor, tamanho...) e não são copiados. */
    protected static function mlIsVariantSpec(string $name): bool
    {
        $n = mb_strtolower(trim($name));
        foreach (['cor', 'color', 'tamanho', 'size', 'tonalidade', 'tom ', 'sabor', 'fragr', 'aroma', 'voltagem', 'numeração', 'estampa'] as $k) {
            if ($n === trim($k) || str_starts_with($n, $k)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Depois de salvar: descrição, ficha técnica, marca e modelo trazidos do ML vão
     * para as outras variações. Na ficha, o que é de cada variação (cor, tamanho...) fica.
     */
    protected function propagateMlToFamily(Product $saved): int
    {
        $take = $this->mlFamilyTake;
        $this->mlFamilyTake = [];
        if (!$take) {
            return 0;
        }
        $saved->refresh();
        $count = 0;
        foreach ($saved->family()->where('id', '!=', $saved->id)->get() as $other) {
            $data = [];
            if (!empty($take['desc']) && trim((string) $saved->description) !== '') {
                $data['description'] = $saved->description;
            }
            if (!empty($take['specs']) && !empty($saved->specs)) {
                $merged = collect($other->specs ?? [])->keyBy(fn ($s) => mb_strtolower($s['n'] ?? ''));
                foreach ($saved->specs as $s) {
                    $key = mb_strtolower($s['n'] ?? '');
                    if ($key === '' || self::mlIsVariantSpec($key)) {
                        continue;
                    }
                    $merged[$key] = ['n' => $s['n'], 'v' => $s['v']];
                }
                $data['specs'] = $merged->values()->take(60)->all() ?: null;
            }
            if (!empty($take['brand'])) {
                if ($saved->brand) { $data['brand'] = $saved->brand; }
                if ($saved->model) { $data['model'] = $saved->model; }
            }
            if ($data) {
                $other->update($data);
                $count++;
            }
        }
        return $count;
    }

    /** Liga as fotos do ML na galeria (sem repetir). Devolve quantas entraram. */
    protected function attachMlPhotos(Product $product, array $urls): int
    {
        $have = $product->images()->pluck('source_url')->filter()->all();
        $maxSort = (int) ($product->images()->max('sort_order') ?? 0);
        $added = 0;
        foreach ($urls as $url) {
            if (in_array($url, $have, true)) {
                continue;
            }
            ProductImage::create([
                'product_id' => $product->id,
                'filename'   => basename((string) parse_url($url, PHP_URL_PATH)),
                'alt_text'   => $product->name,
                'source'     => 'mercadolivre',
                'source_url' => $url,
                'sort_order' => ++$maxSort,
            ]);
            $have[] = $url;
            $added++;
        }
        return $added;
    }

    public function removePendingPhoto(int $i): void
    {
        unset($this->mlPendingPhotos[$i]);
        $this->mlPendingPhotos = array_values($this->mlPendingPhotos);
    }

    /** Depois de salvar o produto novo: fotos e capa que estavam esperando. */
    protected function attachPendingMlMedia(Product $product): void
    {
        if ($this->mlPendingPhotos) {
            $this->attachMlPhotos($product, $this->mlPendingPhotos);
        }
        if ($this->mlPendingCover !== '' && (!$product->image || $product->image === 'product-placeholder.png')) {
            $this->saveCoverFromUrl($product, $this->mlPendingCover);
        }
        $this->mlPendingPhotos = [];
        $this->mlPendingCover = '';
    }

    // ─── Ficha técnica ───────────────────────────────────────────────────────

    public function addSpec(): void
    {
        $this->specs[] = ['n' => '', 'v' => ''];
    }

    public function removeSpec(int $i): void
    {
        unset($this->specs[$i]);
        $this->specs = array_values($this->specs);
    }

    protected function cleanSpecs(): array
    {
        return collect($this->specs)
            ->map(fn ($s) => ['n' => trim((string) ($s['n'] ?? '')), 'v' => trim((string) ($s['v'] ?? ''))])
            ->filter(fn ($s) => $s['n'] !== '' && $s['v'] !== '')
            ->unique('n')
            ->values()
            ->all();
    }

    // ─── Importar do catálogo do Mercado Livre ───────────────────────────────

    public function openMlImport(): void
    {
        $this->resetMlImport();
        $this->mlQuery = $this->barcode ?: $this->name;
        $this->showMlImport = true;
        if (trim($this->mlQuery) !== '') {
            $this->searchMlCatalog();
        }
    }

    public function closeMlImport(): void
    {
        $this->showMlImport = false;
        $this->resetMlImport();
    }

    private function resetMlImport(): void
    {
        $this->mlCandidates = [];
        $this->mlData = [];
        $this->mlPhotos = [];
        $this->mlTake = [];
        $this->mlSearchError = '';
    }

    public function searchMlCatalog(): void
    {
        $term = trim($this->mlQuery);
        $this->resetMlImport();
        if (mb_strlen($term) < 3) {
            $this->mlSearchError = 'Digite o código de barras ou o nome do produto.';
            return;
        }

        try {
            $res = (new ProductService())->catalogCandidates($term, Auth::id());
            if (!$res['success']) {
                $this->mlSearchError = 'Não deu para buscar no Mercado Livre agora. Confira se a conta do ML está conectada.';
                return;
            }
            $this->mlCandidates = $res['items'];
            if (empty($this->mlCandidates)) {
                $this->mlSearchError = 'Nada encontrado no catálogo do ML. Tente pelo nome do produto.';
            } elseif (count($this->mlCandidates) === 1) {
                $this->pickMlProduct($this->mlCandidates[0]['id']);
            }
        } catch (\Throwable $e) {
            Log::error('ImportsFromMlCatalog::searchMlCatalog', ['e' => $e->getMessage()]);
            $this->mlSearchError = 'Erro ao buscar no Mercado Livre.';
        }
    }

    public function pickMlProduct(string $catalogId): void
    {
        if (!collect($this->mlCandidates)->contains('id', $catalogId)) {
            return;
        }

        try {
            $data = (new ProductService())->catalogDetails($catalogId, Auth::id());
        } catch (\Throwable $e) {
            Log::error('ImportsFromMlCatalog::pickMlProduct', ['e' => $e->getMessage()]);
            $data = ['success' => false];
        }

        if (!($data['success'] ?? false)) {
            $this->mlSearchError = 'Não deu para abrir esse produto do catálogo.';
            return;
        }

        $this->mlSearchError = '';
        $this->mlData = $data;
        // Fotos que o produto já tem não aparecem marcadas de novo.
        $have = $this->mlKnownPhotoUrls();
        $this->mlPhotos = collect($data['pictures'])->map(fn ($u) => !in_array($u, $have, true))->all();
        $this->mlTake = [
            'desc'  => ($data['description'] !== '' || !empty($data['features'])),
            'specs' => !empty($data['specs']),
            'brand' => (bool) ($data['brand'] || $data['model'] || $data['gtin']),
            'name'  => false,
            'cover' => !$this->mlHasMainImage() && !empty($data['pictures']),
        ];
    }

    public function backToMlResults(): void
    {
        $this->mlData = [];
        $this->mlPhotos = [];
        $this->mlTake = [];
    }

    /** Texto da descrição montado com o que veio do catálogo. */
    public function mlDescriptionText(): string
    {
        $parts = [];
        if (($this->mlData['description'] ?? '') !== '') {
            $parts[] = $this->mlData['description'];
        }
        if (!empty($this->mlData['features'])) {
            $parts[] = collect($this->mlData['features'])->map(fn ($f) => '• ' . $f)->implode("\n");
        }
        return mb_substr(implode("\n\n", $parts), 0, 5000);
    }

    public function applyMlImport(): void
    {
        if (empty($this->mlData)) {
            return;
        }
        $d = $this->mlData;
        $t = $this->mlTake;
        $done = [];

        if (!empty($t['name']) && $d['name'] !== '') {
            $this->name = mb_substr($d['name'], 0, 255);
            $done[] = 'nome';
        }
        if (!empty($t['desc'])) {
            $text = $this->mlDescriptionText();
            if ($text !== '') {
                $this->description = $text;
                $done[] = 'descrição';
            }
        }
        if (!empty($t['specs']) && !empty($d['specs'])) {
            // Junta com a ficha atual: o que veio do ML atualiza os campos de mesmo nome.
            $merged = collect($this->cleanSpecs())->keyBy(fn ($s) => mb_strtolower($s['n']));
            foreach ($d['specs'] as $s) {
                $merged[mb_strtolower($s['n'])] = ['n' => $s['n'], 'v' => mb_substr($s['v'], 0, 255)];
            }
            $this->specs = $merged->values()->take(60)->all();
            $done[] = 'ficha técnica';
        }
        if (!empty($t['brand'])) {
            if ($d['brand']) { $this->brand = mb_substr($d['brand'], 0, 100); }
            if ($d['model']) { $this->model = mb_substr($d['model'], 0, 100); }
            if ($d['gtin'] && !$this->barcode) { $this->barcode = mb_substr($d['gtin'], 0, 15); }
            $done[] = 'marca e modelo';
        }

        $chosen = collect($d['pictures'])->filter(fn ($u, $i) => !empty($this->mlPhotos[$i]))->values();
        $target = $this->mlTargetProduct();

        $others = $this->mlToFamily ? $this->mlFamilyOthers() : collect();

        if ($target) {
            // Produto já existe: fotos entram na galeria na hora (são links do ML).
            $added = $this->attachMlPhotos($target, $chosen->all());
            foreach ($others as $other) {
                $this->attachMlPhotos($other, $chosen->all());
            }
        } else {
            // Produto novo: guarda e entra na galeria quando salvar.
            $before = count($this->mlPendingPhotos);
            $this->mlPendingPhotos = collect($this->mlPendingPhotos)->merge($chosen)->unique()->values()->all();
            $added = count($this->mlPendingPhotos) - $before;
        }
        if ($added) {
            $done[] = $added . ($added === 1 ? ' foto' : ' fotos');
        }

        // Capa: a primeira foto escolhida vira a imagem principal.
        if (!empty($t['cover']) && ($cover = $chosen->first() ?? ($d['pictures'][0] ?? null))) {
            if ($target) {
                if ($this->saveCoverFromUrl($target, $cover)) {
                    $done[] = 'imagem principal';
                    // Variação sem foto ganha a mesma capa; quem já tem foto própria fica com a dela.
                    // Cada uma ganha a própria cópia do arquivo (trocar a foto de uma não apaga a da outra).
                    $file = $target->fresh()->image;
                    foreach ($others as $other) {
                        if ((!$other->image || $other->image === 'product-placeholder.png') && Storage::disk('public')->exists('products/' . $file)) {
                            $copy = 'ml_' . $other->id . '_' . \Illuminate\Support\Str::random(8) . '.' . pathinfo($file, PATHINFO_EXTENSION);
                            Storage::disk('public')->copy('products/' . $file, 'products/' . $copy);
                            $other->update(['image' => $copy]);
                        }
                    }
                }
            } else {
                $this->mlPendingCover = $cover;
                $done[] = 'imagem principal';
            }
        }

        if ($others->isNotEmpty() && $done) {
            $this->mlFamilyTake = array_filter([
                'desc'  => !empty($t['desc']),
                'specs' => !empty($t['specs']) && !empty($d['specs']),
                'brand' => !empty($t['brand']),
            ]);
            $done[] = 'também nas ' . $others->count() . ' outras variações';
        }

        $this->mlAfterApply();
        $this->showMlImport = false;
        $this->resetMlImport();

        if ($done) {
            $this->notifySuccess('Trazido do Mercado Livre: ' . implode(', ', $done) . '. Confira e toque em Salvar.');
        } else {
            $this->notifyError('Nada marcado para trazer.');
        }
    }

    protected function saveCoverFromUrl(Product $product, string $url): bool
    {
        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(15)->get($url);
            $type = (string) $resp->header('Content-Type');
            if (!$resp->successful() || !str_starts_with($type, 'image/') || strlen($resp->body()) > 5 * 1024 * 1024) {
                return false;
            }
            $ext = match (true) {
                str_contains($type, 'png')  => 'png',
                str_contains($type, 'webp') => 'webp',
                default                     => 'jpg',
            };
            $file = 'ml_' . $product->id . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;
            Storage::disk('public')->put('products/' . $file, $resp->body());

            // A imagem antiga fica no disco: pode ser a mesma de outra variação.
            $product->update(['image' => $file]);
            return true;
        } catch (\Throwable $e) {
            Log::warning('ImportsFromMlCatalog::saveCoverFromUrl', ['e' => $e->getMessage()]);
            return false;
        }
    }
}
