<?php

namespace App\Livewire\Products;

use App\Services\Products\ProductSheetImporter;
use App\Services\Products\SheetReader;
use App\Services\Products\VariationService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Produtos › Importar planilha: para quem não tem PDF de fornecedor (móveis,
 * peças próprias). Lê CSV ou Excel, mostra o que vai entrar e só grava depois
 * do "Importar".
 */
class ImportSheet extends Component
{
    use WithFileUploads, HasNotifications;

    public $file;
    public array $items = [];
    public array $missing = [];
    public array $ignored = [];
    public string $fileName = '';

    public function updatedFile(SheetReader $reader, ProductSheetImporter $importer): void
    {
        $this->validate(['file' => 'required|file|max:4096|mimes:csv,txt,xlsx'], [
            'file.mimes' => 'Use uma planilha .xlsx ou .csv.',
            'file.max' => 'A planilha passa de 4 MB.',
        ]);

        try {
            $ext = strtolower($this->file->getClientOriginalExtension() ?: 'csv');
            $rows = $reader->read($this->file->getRealPath(), $ext);
            $plan = $importer->plan(Auth::id(), $rows);
        } catch (\Throwable $e) {
            $this->reset(['items', 'missing', 'ignored']);
            $this->addError('file', $e->getMessage());

            return;
        }

        $this->fileName = $this->file->getClientOriginalName();
        $this->items = array_slice($plan['items'], 0, 500);
        $this->missing = $plan['missing'];
        $this->ignored = $plan['ignored'];
        if (count($plan['items']) > 500) {
            $this->notifyWarning('A planilha tem mais de 500 linhas. Entram as 500 primeiras; importe o resto depois.');
        }
    }

    public function import(ProductSheetImporter $importer, VariationService $variations)
    {
        $ok = collect($this->items)->where('status', 'ok')->count();
        if ($ok === 0) {
            $this->notifyWarning('Nenhuma linha pronta para importar.');

            return null;
        }

        $created = $importer->import(Auth::id(), $this->items, $variations);
        session()->flash('success', $created === 1 ? '1 produto importado.' : "{$created} produtos importados.");

        return redirect()->route('products.index');
    }

    public function clear(): void
    {
        $this->reset(['file', 'items', 'missing', 'ignored', 'fileName']);
    }

    /** Planilha modelo com o cabeçalho e três exemplos (abre no Excel com acento). */
    public static function templateResponse()
    {
        $lines = array_merge([ProductSheetImporter::TEMPLATE_HEADER], ProductSheetImporter::TEMPLATE_ROWS);
        $csv = "\xEF\xBB\xBF" . implode("\r\n", array_map(
            fn ($r) => implode(';', array_map(fn ($c) => str_contains($c, ';') || str_contains($c, '"') ? '"' . str_replace('"', '""', $c) . '"' : $c, $r)),
            $lines
        )) . "\r\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo-produtos.csv"',
        ]);
    }

    public function render()
    {
        $counts = collect($this->items)->countBy('status');

        return view('livewire.products.import-sheet', [
            'okCount' => (int) ($counts['ok'] ?? 0),
            'skipCount' => (int) ($counts['skip'] ?? 0),
            'errorCount' => (int) ($counts['error'] ?? 0),
            'groupCount' => collect($this->items)->where('status', 'ok')->where('note', 'Principal do grupo')->count(),
        ]);
    }
}
