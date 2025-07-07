<?php

namespace App\Livewire\Invoices;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Smalot\PdfParser\Parser;

class UploadInvoice extends Component
{
    use WithFileUploads;

    public $bankId;
    public $file;
    public $transactions = [];
    public $showConfirmation = false;
    
    public $banks = [];
    public $categories = [];
    public $clients = [];

    protected $rules = [
        'file' => 'required|file|mimes:pdf,csv|max:10240', // 10MB max
    ];

    protected $messages = [
        'file.required' => 'O arquivo é obrigatório.',
        'file.mimes' => 'O arquivo deve ser PDF ou CSV.',
        'file.max' => 'O arquivo não pode ter mais de 10MB.',
    ];

    public function mount($bankId)
    {
        $this->bankId = $bankId;
        $this->loadData();
    }

    public function loadData()
    {
        $this->banks = Bank::all();
        $this->categories = Category::where('is_active', 1)
            ->where('user_id', auth()->id())
            ->get();
        $this->clients = Client::all();
    }

    public function uploadFile()
    {
        $this->validate();

        try {
            $filePath = $this->file->store('uploads', 'local');
            $fileExtension = $this->file->getClientOriginalExtension();
            
            if (strtolower($fileExtension) === 'pdf') {
                $this->transactions = $this->extractTransactionsFromPdf(storage_path('app/' . $filePath));
            } elseif (strtolower($fileExtension) === 'csv') {
                $this->transactions = $this->extractTransactionsFromCsv(storage_path('app/' . $filePath));
            }

            // Limpar o arquivo temporário
            Storage::delete($filePath);

            if (empty($this->transactions)) {
                session()->flash('error', 'Nenhuma transação foi encontrada no arquivo.');
                return;
            }

            $this->showConfirmation = true;
            session()->flash('success', count($this->transactions) . ' transações foram encontradas no arquivo.');

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao processar o arquivo: ' . $e->getMessage());
        }
    }

    public function confirmTransactions()
    {
        try {
            foreach ($this->transactions as $transaction) {
                if (!empty($transaction['date']) && !empty($transaction['description']) && !empty($transaction['value'])) {
                    Invoice::create([
                        'id_bank' => $this->bankId,
                        'invoice_date' => $transaction['date'],
                        'value' => $transaction['value'],
                        'description' => $transaction['description'],
                        'installments' => $transaction['installments'] ?? null,
                        'category_id' => $transaction['category_id'] ?? '1026',
                        'client_id' => $transaction['client_id'] ?? null,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            session()->flash('success', 'Transações confirmadas com sucesso!');
            $this->dispatch('invoice-created');
            
            return redirect()->route('invoices.index', ['bankId' => $this->bankId]);

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao confirmar as transações: ' . $e->getMessage());
        }
    }

    public function updateTransactionCategory($index, $categoryId)
    {
        if (isset($this->transactions[$index])) {
            $this->transactions[$index]['category_id'] = $categoryId;
        }
    }

    public function updateTransactionClient($index, $clientId)
    {
        if (isset($this->transactions[$index])) {
            $this->transactions[$index]['client_id'] = $clientId;
        }
    }

    public function removeTransaction($index)
    {
        if (isset($this->transactions[$index])) {
            unset($this->transactions[$index]);
            $this->transactions = array_values($this->transactions);
        }
    }

    public function cancelUpload()
    {
        $this->reset(['file', 'transactions', 'showConfirmation']);
    }

    protected function extractTransactionsFromCsv($csvPath)
    {
        $transactions = [];
        $categoryMapping = $this->getCategoryMapping();

        if (($handle = fopen($csvPath, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $data = array_map(function ($item) {
                    return trim(str_replace('"', '', $item));
                }, $data);

                if (count($data) >= 5) {
                    $transaction = [
                        'date' => $this->parseDate($data[0] ?? ''),
                        'description' => $data[1] ?? 'Descrição não disponível',
                        'installments' => $data[3] ?? 'Compra à vista',
                        'value' => $this->processValue($data[4] ?? '0'),
                        'category_id' => $this->determineCategoryId($data[1] ?? '', $categoryMapping),
                        'client_id' => null,
                    ];

                    if ($transaction['value'] > 0 && !empty($transaction['date']) && !empty($transaction['description'])) {
                        $transactions[] = $transaction;
                    }
                }
            }

            fclose($handle);
        }

        return $transactions;
    }

    protected function extractTransactionsFromPdf($pdfPath)
    {
        $transactions = [];
        $categoryMapping = $this->getCategoryMapping();
        
        try {
            $pdf = new Parser();
            $document = $pdf->parseFile($pdfPath);
            $text = $document->getText();
            
            if (empty($text)) {
                Log::error('Erro: Nenhum texto extraído do PDF.');
                return $transactions;
            }

            $lines = explode("\n", $text);
            $currentTransaction = [
                'description' => '',
                'installments' => '-',
                'value' => null,
                'category_id' => null,
                'date' => null,
                'client_id' => null,
            ];

            $monthMapping = [
                'jan' => '01', 'fev' => '02', 'mar' => '03', 'abr' => '04',
                'mai' => '05', 'jun' => '06', 'jul' => '07', 'ago' => '08',
                'set' => '09', 'out' => '10', 'nov' => '11', 'dez' => '12',
            ];

            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (empty($trimmedLine)) continue;

                // Processar data
                if (preg_match('/(\d{1,2})\sde\s([a-záàâãäéèêíóòôõöúç]{3})\.\s(\d{4})/', $trimmedLine, $dateMatches)) {
                    $day = $dateMatches[1];
                    $month = strtolower($dateMatches[2]);
                    $year = $dateMatches[3];

                    if (isset($monthMapping[$month])) {
                        $currentTransaction['date'] = $year . '-' . $monthMapping[$month] . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                    }
                }

                // Processar valor
                if (strpos($trimmedLine, 'R$') !== false) {
                    if (preg_match('/R\$\s*([-]?\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/', $trimmedLine, $valueMatches)) {
                        $currentTransaction['value'] = abs(floatval(str_replace(',', '.', str_replace('.', '', $valueMatches[1]))));
                    }
                }

                // Processar parcelas
                if (preg_match('/\(?\s*(\d+)\s*(?:de|\/)\s*(\d+)\s*\)?/', $trimmedLine, $parcelMatches)) {
                    $currentTransaction['installments'] = "{$parcelMatches[1]} de {$parcelMatches[2]}";
                }

                // Processar descrição
                if (!empty($trimmedLine) && !$this->shouldExcludeLine($trimmedLine)) {
                    $currentTransaction['description'] .= empty($currentTransaction['description']) ? $trimmedLine : ' ' . $trimmedLine;
                }

                // Se tiver todos os dados, adicionar à lista
                if (!empty($currentTransaction['date']) && !empty($currentTransaction['value']) && !empty($currentTransaction['description'])) {
                    $currentTransaction['category_id'] = $this->determineCategoryId($currentTransaction['description'], $categoryMapping);
                    $transactions[] = $currentTransaction;
                    
                    // Reset para próxima transação
                    $currentTransaction = [
                        'description' => '',
                        'installments' => '-',
                        'value' => null,
                        'category_id' => null,
                        'date' => null,
                        'client_id' => null,
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Erro ao processar PDF: ' . $e->getMessage());
        }

        return $transactions;
    }

    private function parseDate($dateString)
    {
        if (empty($dateString)) return '';
        
        $dateParts = explode('/', $dateString);
        if (count($dateParts) == 3) {
            return $dateParts[2] . '-' . str_pad($dateParts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
        }
        
        return $dateString;
    }

    private function processValue($value)
    {
        $value = str_replace(['R$', ' '], '', $value);
        $value = str_replace(',', '.', $value);
        $value = trim($value);
        
        return is_numeric($value) ? floatval($value) : 0;
    }

    private function determineCategoryId($description, $categoryMapping)
    {
        foreach ($categoryMapping as $keyword => $categoryId) {
            if (stripos($description, $keyword) !== false) {
                return $categoryId;
            }
        }
        return '1026'; // Categoria padrão
    }

    private function shouldExcludeLine($line)
    {
        $excludedKeywords = [
            'fatura', 'Período', 'Olá', 'LIMITES', 'crédito', 'aplicativo',
            'marco', 'juros', 'bloqueado', '0800', 'meses', 'IOF', 'ENCARGOS'
        ];
        
        foreach ($excludedKeywords as $keyword) {
            if (stripos($line, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function getCategoryMapping()
    {
        return [
            'BOTICARIO' => '1019',
            'NATURA' => '1019',
            'BEER' => '1018',
            'BURGER' => '1018',
            'SUPERMERCADO' => '1021',
            'ATACADAO' => '1021',
            'POSTO' => '1022',
            'PHARMA' => '1023',
            'DROGARIA' => '1023',
            'CLARO' => '1029',
            'MERCADOLIVRE' => '1024',
            'SHOPEE' => '1024',
        ];
    }

    public function render()
    {
        return view('livewire.invoices.upload-invoice');
    }
}
