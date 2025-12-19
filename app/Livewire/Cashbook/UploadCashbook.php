<?php

namespace App\Livewire\Cashbook;

use App\Models\Cashbook;
use App\Models\Bank;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Smalot\PdfParser\Parser as PdfParser;

class UploadCashbook extends Component
{
    use WithFileUploads;

    public $file;
    public $step = 1;
    public $transactions = [];
    public $selectedBank;
    public $selectedCategory;

    protected $rules = [
        'file' => 'required|file|mimes:pdf,csv|max:10240',
    ];

    public function mount()
    {
        Log::info('UploadCashbook mount called', [
            'user_id' => Auth::id(),
            'step' => $this->step
        ]);
    }

    public function updatedFile()
    {
        Log::info('UploadCashbook file updated', [
            'user_id' => Auth::id(),
            'file_name' => $this->file ? $this->file->getClientOriginalName() : 'null',
            'file_type' => $this->file ? $this->file->getMimeType() : 'null'
        ]);
    }

    public function testClick()
    {
        Log::info('TEST CLICK FUNCIONOU!', ['user_id' => Auth::id()]);
        session()->flash('success', 'Botão clicado com sucesso!');
    }

    public function processFile()
    {
        Log::info('UploadCashbook processFile called', [
            'user_id' => Auth::id(),
            'file' => $this->file ? get_class($this->file) : 'null',
            'has_file' => isset($this->file)
        ]);

        try {
            $this->validate();

            Log::info('File validation passed');

            $extension = $this->file->getClientOriginalExtension();
            Log::info('File extension: ' . $extension);

            if ($extension === 'pdf') {
                $this->transactions = $this->parsePdf();
            } elseif ($extension === 'csv') {
                $this->transactions = $this->parseCsv();
            }

            Log::info('Transactions parsed', [
                'count' => count($this->transactions)
            ]);

            $this->step = 2;

            Log::info('Step changed to 2');

        } catch (\Exception $e) {
            Log::error('Error processing file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Erro ao processar arquivo: ' . $e->getMessage());
        }
    }

    private function parsePdf()
    {
        Log::info('Starting PDF parse');

        $parser = new PdfParser();
        $pdf = $parser->parseFile($this->file->getRealPath());
        $text = $pdf->getText();

        Log::info('PDF text extracted', ['length' => strlen($text)]);

        // Lógica básica de parsing
        $transactions = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            // Regex simples para detectar padrões comuns
            if (preg_match('/(\d{2}\/\d{2}\/\d{4}).*?(\d+[,\.]\d{2})/', $line, $matches)) {
                $transactions[] = [
                    'date' => $matches[1],
                    'description' => trim($line),
                    'amount' => str_replace(',', '.', $matches[2]),
                    'type' => 'expense'
                ];
            }
        }

        Log::info('PDF transactions extracted', ['count' => count($transactions)]);

        return $transactions;
    }

    private function parseCsv()
    {
        Log::info('Starting CSV parse');

        $transactions = [];
        $file = fopen($this->file->getRealPath(), 'r');

        // Skip header
        fgetcsv($file);

        while (($data = fgetcsv($file)) !== false) {
            $transactions[] = [
                'date' => $data[0] ?? '',
                'description' => $data[1] ?? '',
                'amount' => $data[2] ?? '0',
                'type' => 'expense'
            ];
        }

        fclose($file);

        Log::info('CSV transactions extracted', ['count' => count($transactions)]);

        return $transactions;
    }

    public function saveTransactions()
    {
        Log::info('Saving transactions', [
            'count' => count($this->transactions)
        ]);

        foreach ($this->transactions as $transaction) {
            Cashbook::create([
                'user_id' => Auth::id(),
                'bank_id' => $this->selectedBank,
                'category_id' => $this->selectedCategory,
                'date' => $transaction['date'],
                'description' => $transaction['description'],
                'amount' => $transaction['amount'],
                'type' => $transaction['type']
            ]);
        }

        session()->flash('success', count($this->transactions) . ' transações importadas com sucesso!');

        return redirect()->route('cashbook.index');
    }

    public function cancel()
    {
        $this->reset();
        $this->step = 1;
    }

    public function render()
    {
        return view('livewire.cashbook.upload-cashbook', [
            'banks' => Bank::where('user_id', Auth::id())->get(),
            'categories' => Category::where('user_id', Auth::id())->get()
        ]);
    }
}
