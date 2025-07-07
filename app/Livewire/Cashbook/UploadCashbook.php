<?php

namespace App\Livewire\Cashbook;

use App\Models\Cashbook;
use App\Models\Category;
use App\Models\Type;
use App\Models\Segment;
use App\Models\Client;
use App\Models\Cofrinho;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Smalot\PdfParser\Parser;
use Carbon\Carbon;

class UploadCashbook extends Component
{
    use WithFileUploads;

    public $file;
    public $transactions = [];
    public $categories = [];
    public $types = [];
    public $segments = [];
    public $clients = [];
    public $cofrinhos = [];
    public $cofrinho_id = '';
    public $step = 1; // 1 = upload, 2 = preview
    
    protected $rules = [
        'file' => 'required|mimes:pdf,csv|max:2048',
        'cofrinho_id' => 'nullable|exists:cofrinhos,id',
    ];

    protected $messages = [
        'file.required' => 'Por favor, selecione um arquivo.',
        'file.mimes' => 'O arquivo deve ser PDF ou CSV.',
        'file.max' => 'O arquivo deve ter no máximo 2MB.',
        'cofrinho_id.exists' => 'O cofrinho selecionado não existe.',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->categories = Category::where('user_id', Auth::id())
            ->where('is_active', 1)
            ->where('type', 'transaction')
            ->select(['name', 'id_category as id'])
            ->get();

        $this->types = Type::all();

        $this->segments = Segment::where('user_id', Auth::id())
            ->select(['name', 'id'])
            ->get();

        $this->clients = Client::where('user_id', Auth::id())
            ->select(['id', 'name'])
            ->get();

        $this->cofrinhos = Cofrinho::where('user_id', Auth::id())->get();
    }

    public function processFile()
    {
        $this->validate();

        $transactions = [];

        if ($this->file->getClientOriginalExtension() === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($this->file->getPathname());
            $text = $pdf->getText();
            $transactions = $this->extractTransactionsFromPdf($text);
        } elseif ($this->file->getClientOriginalExtension() === 'csv') {
            $transactions = $this->parseCsvFile();
        }

        $this->transactions = $transactions;
        $this->step = 2;
    }

    public function confirmTransactions()
    {
        if (empty($this->transactions)) {
            session()->flash('error', 'Nenhuma transação para confirmar.');
            return;
        }

        $success = true;
        $duplicated = [];
        $inserted = [];

        foreach ($this->transactions as $idx => $trans) {
            try {
                // Verificar e corrigir o formato da data
                $dateFormats = ['d-m-Y', 'Y-m-d'];
                $validDate = false;
                $dateFormatted = null;

                foreach ($dateFormats as $format) {
                    if (Carbon::hasFormat($trans['date'], $format)) {
                        $dateFormatted = Carbon::createFromFormat($format, $trans['date'])->format('Y-m-d');
                        $validDate = true;
                        break;
                    }
                }

                if (!$validDate) {
                    session()->flash('error', 'Erro: Data inválida ou ausente em uma das transações.');
                    return;
                }

                // Validar campos obrigatórios
                if (empty($trans['value']) || empty($trans['category_id']) || empty($trans['type_id'])) {
                    session()->flash('error', 'Erro: Campos obrigatórios ausentes em uma das transações.');
                    return;
                }

                // Verificar duplicidade
                $exists = Cashbook::where('user_id', Auth::id())
                    ->where('date', $dateFormatted)
                    ->where('value', $trans['value'])
                    ->where('description', $trans['description'] ?? null)
                    ->where('category_id', $trans['category_id'])
                    ->where('type_id', $trans['type_id'])
                    ->where(function($q) use ($trans) {
                        if (isset($trans['client_id'])) {
                            $q->where('client_id', $trans['client_id']);
                        } else {
                            $q->whereNull('client_id');
                        }
                    })
                    ->exists();

                if ($exists) {
                    $duplicated[] = [
                        'date' => $trans['date'],
                        'value' => $trans['value'],
                        'description' => $trans['description'] ?? '',
                    ];
                    continue;
                }

                // Salvar transação
                $cashbook = new Cashbook();
                $cashbook->user_id = Auth::id();
                $cashbook->client_id = $trans['client_id'] ?? null;
                $cashbook->date = $dateFormatted;
                $cashbook->value = $trans['value'];
                $cashbook->description = $trans['description'] ?? null;
                $cashbook->category_id = $trans['category_id'];
                $cashbook->type_id = $trans['type_id'];
                $cashbook->is_pending = $trans['is_pending'] ?? 0;
                $cashbook->cofrinho_id = $this->cofrinho_id ?? null;
                $cashbook->inc_datetime = now();

                if ($cashbook->save()) {
                    $inserted[] = [
                        'date' => $dateFormatted,
                        'value' => $trans['value'],
                        'description' => $trans['description'] ?? '',
                    ];
                } else {
                    $success = false;
                }
            } catch (\Exception $e) {
                \Log::error('Exceção ao salvar transação', [
                    'index' => $idx,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $success = false;
            }
        }

        if ($success) {
            $msg = 'Transações salvas com sucesso.';
            if (count($duplicated) > 0) {
                session()->flash('success', $msg);
                session()->flash('warning', 'Algumas transações não foram inseridas pois já existiam.');
                session()->flash('warning_details', [
                    'inserted' => $inserted,
                    'duplicated' => $duplicated
                ]);
            } else {
                session()->flash('success', $msg);
            }
            
            $this->dispatch('transaction-created');
            $this->redirect(route('cashbook.index'));
        } else {
            session()->flash('error', 'Houve um erro ao salvar as transações.');
        }
    }

    public function backToUpload()
    {
        $this->step = 1;
        $this->transactions = [];
        $this->file = null;
    }

    public function cancel()
    {
        $this->redirect(route('cashbook.index'));
    }

    protected function extractTransactionsFromPdf($text)
    {
        // Mesmo código da UploadCashbookController
        $transactions = [];
        $lines = explode("\n", $text);
        $currentTransaction = [
            'date' => null,
            'description' => '',
            'value' => null,
            'category_id' => null,
            'type_id' => null,
        ];

        $categoryMapping = [
            'PIX' => '1013',
            'Rendimentos' => '1016',
            'Santander' => '1014',
            'Inter' => '1015',
        ];

        $irrelevantKeywords = [
            'Saldo final',
            'ID da operação',
            'Valor',
            'Saldo',
            'Página',
            '2/',
            '3/',
        ];

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (empty($trimmedLine)) {
                continue;
            }

            $skipLine = false;
            foreach ($irrelevantKeywords as $keyword) {
                if (stripos($trimmedLine, $keyword) !== false) {
                    $skipLine = true;
                    break;
                }
            }
            if ($skipLine) {
                continue;
            }

            if (preg_match('/(\d{2}-\d{2}-\d{4})/', $trimmedLine, $dateMatches)) {
                if (!empty($currentTransaction['date']) && !is_null($currentTransaction['value'])) {
                    $currentTransaction['category_id'] = $this->determineCategoryId($currentTransaction['description'], $categoryMapping);

                    if ($currentTransaction['value'] < 0) {
                        $currentTransaction['value'] = abs($currentTransaction['value']);
                    }

                    $transactions[] = $currentTransaction;
                }

                $currentTransaction = [
                    'date' => $dateMatches[1],
                    'description' => '',
                    'value' => null,
                    'category_id' => null,
                    'type_id' => null,
                ];
                $trimmedLine = trim(str_replace($dateMatches[0], '', $trimmedLine));
            }

            if (strpos($trimmedLine, 'R$') !== false) {
                if (preg_match('/R\$\s*([-]?\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/', $trimmedLine, $valueMatches)) {
                    $currentTransaction['value'] = str_replace(',', '.', str_replace('.', '', $valueMatches[1]));
                    $currentTransaction['value'] = abs($currentTransaction['value']);
                    $currentTransaction['type_id'] = (strpos($valueMatches[1], '-') === 0) ? '2' : '1';
                    $trimmedLine = trim(str_replace($valueMatches[0], '', $trimmedLine));
                }
            }

            if (preg_match('/(\D+)(\d{8,})/', $trimmedLine, $descriptionMatches)) {
                $trimmedLine = $descriptionMatches[1];
            }

            $trimmedLine = preg_replace('/\b\d{8,}\b/', '', $trimmedLine);
            $trimmedLine = preg_replace('/R\$\s*[-]?\d{1,3}(?:\.\d{3})*(?:,\d{2})?/', '', $trimmedLine);

            if (!empty($trimmedLine)) {
                if (!empty($currentTransaction['description'])) {
                    $currentTransaction['description'] .= ' ';
                }
                $currentTransaction['description'] .= trim($trimmedLine);
            }
        }

        if (!empty($currentTransaction['date']) && !is_null($currentTransaction['value'])) {
            if (empty($currentTransaction['description'])) {
                $currentTransaction['description'] = 'Rendimentos';
            }
            $currentTransaction['category_id'] = $this->determineCategoryId($currentTransaction['description'], $categoryMapping);

            if ($currentTransaction['value'] < 0) {
                $currentTransaction['value'] = abs($currentTransaction['value']);
            }

            $transactions[] = $currentTransaction;
        }

        return array_filter($transactions, function ($transaction) {
            return !empty($transaction['date']) && !is_null($transaction['value']);
        });
    }

    private function determineCategoryId($description, $categoryMapping)
    {
        foreach ($categoryMapping as $keyword => $categoryId) {
            if (stripos($description, $keyword) !== false) {
                return $categoryId;
            }
        }
        return null;
    }

    private function parseCsvFile()
    {
        $transactions = [];
        if (($handle = fopen($this->file->getPathname(), 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $transactions[] = [
                    'date' => $data[0],
                    'value' => $data[1],
                    'description' => $data[2],
                    'category_id' => null,
                    'type_id' => null,
                    'note' => null,
                    'segment_id' => null,
                ];
            }
            fclose($handle);
        }
        return $transactions;
    }

    public function render()
    {
        return view('livewire.cashbook.upload-cashbook');
    }
}
