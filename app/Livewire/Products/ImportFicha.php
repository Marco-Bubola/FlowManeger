<?php

namespace App\Livewire\Products;

use App\Services\Products\ProductFichaImporter;
use App\Services\Products\VariationService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Produtos › Importar ficha: cadastra um produto completo (variações, preços,
 * embalagem, sob encomenda, fichas e fotos) a partir da ficha feita por uma IA
 * com o questionário da Móveis Stylo. O robô de cadastro usa esta tela:
 * ids #ficha-file, #ficha-photo e os data-testid ficham estáveis.
 */
class ImportFicha extends Component
{
    use WithFileUploads, HasNotifications;

    public $fichaFile;
    public $photoBatch = [];
    public $photoOne;
    public array $photos = [];
    public string $fichaText = '';
    public string $fichaName = '';
    public ?array $plan = null;
    public string $parseError = '';
    public array $result = [];

    protected function photoRule(): string
    {
        return 'image|mimes:jpg,jpeg,png,webp|max:4096';
    }

    public function updatedFichaFile(): void
    {
        $this->validate(['fichaFile' => 'file|max:512'], ['fichaFile.max' => 'A ficha passa de 512 KB.']);
        $this->fichaText = (string) file_get_contents($this->fichaFile->getRealPath());
        $this->fichaName = $this->fichaFile->getClientOriginalName();
        $this->fichaFile = null;
        $this->result = [];
        $this->replan();
    }

    public function updatedFichaText(): void
    {
        $this->result = [];
        $this->replan();
    }

    public function updatedPhotoBatch(): void
    {
        $this->validate(['photoBatch.*' => $this->photoRule()], ['photoBatch.*.max' => 'Cada foto pode ter até 4 MB.', 'photoBatch.*.image' => 'Use fotos JPG, PNG ou WEBP.']);
        foreach ($this->photoBatch as $file) {
            $this->addPhoto($file);
        }
        $this->photoBatch = [];
        $this->replan();
    }

    /** Uma foto por vez (o robô manda assim para não estourar o limite do servidor). */
    public function updatedPhotoOne(): void
    {
        $this->validate(['photoOne' => $this->photoRule()], ['photoOne.max' => 'Cada foto pode ter até 4 MB.', 'photoOne.image' => 'Use fotos JPG, PNG ou WEBP.']);
        $this->addPhoto($this->photoOne);
        $this->photoOne = null;
        $this->replan();
    }

    protected function addPhoto($file): void
    {
        $name = $file->getClientOriginalName();
        // Mesmo nome de novo: troca pela nova
        $this->photos = array_values(array_filter($this->photos, fn ($p) => $p->getClientOriginalName() !== $name));
        $this->photos[] = $file;
        if (count($this->photos) > 60) {
            array_shift($this->photos);
        }
    }

    public function removePhoto(int $i): void
    {
        unset($this->photos[$i]);
        $this->photos = array_values($this->photos);
        $this->replan();
    }

    protected function replan(): void
    {
        $this->plan = null;
        $this->parseError = '';
        if (trim($this->fichaText) === '') {
            return;
        }
        $importer = app(ProductFichaImporter::class);
        try {
            $data = $importer->parse($this->fichaText);
            $this->plan = $importer->plan(Auth::id(), $data, $this->photoNames());
        } catch (\Throwable $e) {
            $this->parseError = $e->getMessage();
        }
    }

    protected function photoNames(): array
    {
        return array_map(fn ($p) => $p->getClientOriginalName(), $this->photos);
    }

    public function import(ProductFichaImporter $importer, VariationService $variations): void
    {
        $this->replan();
        if (!$this->plan || $this->plan['errors']) {
            $this->notifyWarning($this->parseError ?: 'A ficha tem erro. Corrija e envie de novo.');

            return;
        }
        if (!collect($this->plan['rows'])->contains(fn ($r) => $r['status'] === 'ok')) {
            $this->notifyWarning('Nada novo para importar nesta ficha.');

            return;
        }

        $files = [];
        foreach ($this->photos as $p) {
            $files[$p->getClientOriginalName()] = $p;
        }

        try {
            $created = $importer->import(Auth::id(), $this->plan, $files, $variations);
        } catch (\Throwable $e) {
            report($e);
            $this->notifyError('Não deu para importar: ' . $e->getMessage());

            return;
        }

        $this->result = [
            'name' => $this->plan['product']['name'],
            'items' => $created,
            'ml_title' => $this->plan['product']['ml_title'],
            'ml_type' => $this->plan['product']['ml_type'],
            'ml_link' => $this->plan['product']['ml_link'],
            'skipped' => collect($this->plan['rows'])->where('status', 'skip')->count(),
        ];
        $this->notifySuccess(count($created) === 1 ? '1 produto cadastrado.' : count($created) . ' produtos cadastrados.');
        $this->reset(['fichaText', 'fichaName', 'photos', 'plan', 'parseError']);
    }

    public function clear(): void
    {
        $this->reset(['fichaText', 'fichaName', 'photos', 'plan', 'parseError', 'result', 'photoBatch', 'photoOne', 'fichaFile']);
    }

    public function render()
    {
        return view('livewire.products.import-ficha', [
            'photoNames' => $this->photoNames(),
        ]);
    }
}
