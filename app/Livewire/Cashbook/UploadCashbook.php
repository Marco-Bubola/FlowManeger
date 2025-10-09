<?php

namespace App\Livewire\Cashbook;

use App\Models\Cashbook;
use App\Models\Category;
use App\Models\Type;
use App\Models\Segment;
use App\Models\Client;
use App\Models\Cofrinho;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    'transactions.*.client_id' => 'nullable|exists:clients,id',
        'transactions.*.category_id' => 'required|exists:category,id_category',
        'transactions.*.cofrinho_id' => 'nullable|exists:cofrinhos,id',
    ];

    protected $messages = [
        'file.required' => 'Por favor, selecione um arquivo.',
        'file.mimes' => 'O arquivo deve ser PDF ou CSV.',
        'file.max' => 'O arquivo deve ter no máximo 2MB.',
        'cofrinho_id.exists' => 'O cofrinho selecionado não existe.',
        'transactions.*.category_id.required' => 'Todas as transações devem ter uma categoria selecionada.',
        'transactions.*.category_id.exists' => 'Uma ou mais categorias selecionadas são inválidas.',
        'transactions.*.cofrinho_id.exists' => 'Um ou mais cofrinhos selecionados são inválidos.',
    'transactions.*.client_id.exists' => 'Um ou mais clientes selecionados são inválidos.',
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
            ->select(['name', 'id_category'])
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
            // Apply automations so preview already shows mapped cofrinho/client/category
            foreach ($transactions as $i => $t) {
                $this->applyAutomationsToTransaction($transactions[$i]);
            }
        } elseif ($this->file->getClientOriginalExtension() === 'csv') {
            $transactions = $this->parseCsvFile();
            // Apply automations to CSV-parsed transactions as well
            foreach ($transactions as $i => $t) {
                $this->applyAutomationsToTransaction($transactions[$i]);
            }
        }

        $this->transactions = $transactions;
        $this->step = 2;
    }

    /**
     * Aplica regras automáticas para tentar mapear cofrinho, client e categoria
     * diretamente nas transações logo após o parsing, para aparecer na pré-visualização.
     */
    private function applyAutomationsToTransaction(array &$trans)
    {
        $desc = isset($trans['description']) ? mb_strtolower($trans['description']) : '';
        $title = isset($trans['title']) ? mb_strtolower($trans['title']) : '';

        // Dinheiro retirado/reservado Eudora -> cofrinho + client + tentar categoria PIX
        if (!empty($desc) && str_contains($desc, 'eudora') && str_contains($desc, 'dinheiro')){
            $eudoraCof = Cofrinho::where('user_id', Auth::id())->where('nome', 'like', '%eudora%')->first();
            $eudoraClient = Client::where('user_id', Auth::id())->where('name', 'like', '%eudora%')->first();
            if ($eudoraCof) {
                $trans['cofrinho_id'] = $eudoraCof->id;
            }
            if ($eudoraClient) {
                $trans['client_id'] = $eudoraClient->id;
            }
            // tentar localizar categoria PIX do usuário (por nome ou id_category conhecido)
            $pixCat = Category::where('user_id', Auth::id())
                ->where('is_active', 1)
                ->where('type', 'transaction')
                ->where(function($q){
                    $q->where('name', 'like', '%pix%')
                      ->orWhere('name', 'like', '%transfer%')
                      ->orWhere('id_category', '1013');
                })->first();
            if ($pixCat) {
                $trans['category_id'] = $pixCat->id_category ?? null;
            }
        }

        // Ana Carolina -> Aninha
        if (!empty($title) && str_contains($title, mb_strtolower('Ana Carolina Ferreira Coelho Freire'))){
            $aninha = Client::where('user_id', Auth::id())
                ->where(function($q){
                    $q->where('name', 'like', '%Aninha%')
                      ->orWhere('name', 'like', '%Ana Carolina%');
                })->first();
            if ($aninha) {
                $trans['client_id'] = $aninha->id;
            }
        }

        // transferência -> tentar PIX
        if ((!empty($title) && str_contains($title, 'transferencia')) || (!empty($desc) && str_contains($desc, 'transferencia'))){
            $pixCat = Category::where('user_id', Auth::id())
                ->where('is_active', 1)
                ->where('type', 'transaction')
                ->where(function($q){
                    $q->where('name', 'like', '%pix%')
                      ->orWhere('name', 'like', '%transfer%')
                      ->orWhere('id_category', '1013');
                })->first();
            if ($pixCat) {
                $trans['category_id'] = $pixCat->id_category ?? null;
            }
        }
    }

    protected function validateTransactions()
    {
        $errors = [];
        $hasErrors = false;

        foreach ($this->transactions as $index => $transaction) {
            $transactionErrors = [];

            // Validar categoria obrigatória
            if (empty($transaction['category_id'])) {
                $transactionErrors[] = 'Categoria é obrigatória';
                $hasErrors = true;
            } else {
                // Validar se a categoria existe e pertence ao usuário
                $categoryExists = Category::where('id_category', $transaction['category_id'])
                    ->where('user_id', Auth::id())
                    ->where('is_active', 1)
                    ->where('type', 'transaction')
                    ->exists();

                if (!$categoryExists) {
                    $transactionErrors[] = 'Categoria inválida ou não pertence ao usuário';
                    $hasErrors = true;
                }
            }

            // Validar cofrinho se fornecido
            if (!empty($transaction['cofrinho_id'])) {
                $cofrinhoExists = Cofrinho::where('id', $transaction['cofrinho_id'])
                    ->where('user_id', Auth::id())
                    ->exists();

                if (!$cofrinhoExists) {
                    $transactionErrors[] = 'Cofrinho inválido ou não pertence ao usuário';
                    $hasErrors = true;
                }
            }

            // Validar client se fornecido
            if (!empty($transaction['client_id'])) {
                $clientExists = Client::where('id', $transaction['client_id'])
                    ->where('user_id', Auth::id())
                    ->exists();

                if (!$clientExists) {
                    $transactionErrors[] = 'Cliente inválido ou não pertence ao usuário';
                    $hasErrors = true;
                }
            }

            // Validar valor
            if (empty($transaction['value']) || !is_numeric($transaction['value'])) {
                $transactionErrors[] = 'Valor é obrigatório e deve ser numérico';
                $hasErrors = true;
            }

            // Validar tipo
            if (empty($transaction['type_id'])) {
                $transactionErrors[] = 'Tipo é obrigatório';
                $hasErrors = true;
            }

            if (!empty($transactionErrors)) {
                $errors[$index + 1] = $transactionErrors; // +1 para mostrar número da linha começando em 1
            }
        }

        if ($hasErrors) {
            $errorMessage = 'Corrija os seguintes erros antes de confirmar:';
            foreach ($errors as $lineNumber => $lineErrors) {
                $errorMessage .= "\n\nLinha {$lineNumber}:";
                foreach ($lineErrors as $error) {
                    $errorMessage .= "\n• {$error}";
                }
            }

            session()->flash('error', $errorMessage);
            return false;
        }

        return true;
    }

    public function confirmTransactions()
    {
        if (empty($this->transactions)) {
            session()->flash('error', 'Nenhuma transação para confirmar.');
            return;
        }

        // Validar todas as transações antes de salvar
        if (!$this->validateTransactions()) {
            return;
        }

    $success = true;
    $duplicated = [];
    $inserted = [];
    $saveErrors = [];

        foreach ($this->transactions as $idx => $trans) {
            try {
                // --- Automations: map description/title to cofrinho/client/category ---
                // Normalize strings for matching
                $desc = isset($trans['description']) ? mb_strtolower($trans['description']) : '';
                $title = isset($trans['title']) ? mb_strtolower($trans['title']) : '';

                // If description mentions Eudora + dinheiro -> map cofrinho, client and try PIX category
                if (!empty($desc) && str_contains($desc, 'eudora') && str_contains($desc, 'dinheiro')) {
                    $eudoraCof = Cofrinho::where('user_id', Auth::id())->where('nome', 'like', '%eudora%')->first();
                    $eudoraClient = Client::where('user_id', Auth::id())->where('name', 'like', '%eudora%')->first();
                    if ($eudoraCof) {
                        $this->transactions[$idx]['cofrinho_id'] = $eudoraCof->id;
                        $trans['cofrinho_id'] = $eudoraCof->id;
                    }
                    if ($eudoraClient) {
                        $this->transactions[$idx]['client_id'] = $eudoraClient->id;
                        $trans['client_id'] = $eudoraClient->id;
                    }
                    $pixCat = Category::where('user_id', Auth::id())
                        ->where('is_active', 1)
                        ->where('type', 'transaction')
                        ->where(function($q){
                            $q->where('name', 'like', '%pix%')
                              ->orWhere('name', 'like', '%transfer%')
                              ->orWhere('id_category', '1013');
                        })->first();
                    if ($pixCat) {
                        $this->transactions[$idx]['category_id'] = $pixCat->id_category ?? null;
                        $trans['category_id'] = $this->transactions[$idx]['category_id'];
                    }
                }

                // If title matches specific full name, select client 'Aninha'
                if (!empty($title) && str_contains($title, mb_strtolower('Ana Carolina Ferreira Coelho Freire'))) {
                    // prefer exact alias 'Aninha' if present
                    $aninha = Client::where('user_id', Auth::id())
                        ->where(function($q) {
                            $q->where('name', 'like', '%Aninha%')
                              ->orWhere('name', 'like', '%Ana Carolina%');
                        })->first();
                    if ($aninha) {
                        $this->transactions[$idx]['client_id'] = $aninha->id;
                        $trans['client_id'] = $aninha->id;
                    }
                }

                // If title or description mentions 'transferencia' -> try PIX
                if ((!empty($title) && str_contains($title, 'transferencia')) || (!empty($desc) && str_contains($desc, 'transferencia'))) {
                    $pixCat = Category::where('user_id', Auth::id())
                        ->where('is_active', 1)
                        ->where('type', 'transaction')
                        ->where(function($q){
                            $q->where('name', 'like', '%pix%')
                              ->orWhere('name', 'like', '%transfer%')
                              ->orWhere('id_category', '1013');
                        })->first();
                    if ($pixCat) {
                        $this->transactions[$idx]['category_id'] = $pixCat->id_category ?? null;
                        $trans['category_id'] = $this->transactions[$idx]['category_id'];
                    }
                }
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
                $cashbook->category_id = $trans['category_id'] ?? null;
                $cashbook->type_id = $trans['type_id'];
                $cashbook->is_pending = $trans['is_pending'] ?? 0;
                $cashbook->cofrinho_id = $trans['cofrinho_id'] ?? null;
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
                Log::error('Exceção ao salvar transação', [
                    'index' => $idx,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $success = false;
                $saveErrors[] = "Linha " . ($idx + 1) . ": " . $e->getMessage();
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
            $message = 'Houve um erro ao salvar as transações.';
            if (!empty($saveErrors)) {
                $message .= "\n\nDetalhes:\n" . implode("\n", $saveErrors);
            }
            session()->flash('error', $message);
        }
    }

    public function backToUpload()
    {
        $this->step = 1;
        $this->transactions = [];
        $this->file = null;
    }

    public function removeTransaction($index)
    {
        // Remove a transação do array pelo índice
        if (isset($this->transactions[$index])) {
            array_splice($this->transactions, $index, 1);

            // Mensagem de feedback para o usuário
            session()->flash('info', 'Transação removida com sucesso.');
        }
    }

    public function updatedTransactions($value, $name)
    {
        // Validação em tempo real quando o usuário altera uma transação
        if (strpos($name, '.category_id') !== false) {
            $parts = explode('.', $name);
            $index = $parts[0];

            if (!empty($value)) {
                // Verificar se a categoria é válida
                $categoryExists = Category::where('id_category', $value)
                    ->where('user_id', Auth::id())
                    ->where('is_active', 1)
                    ->where('type', 'transaction')
                    ->exists();

                if (!$categoryExists) {
                    $this->addError("transactions.{$index}.category_id", 'Categoria inválida.');
                } else {
                    $this->resetErrorBag("transactions.{$index}.category_id");
                }
            }
        }

        if (strpos($name, '.cofrinho_id') !== false) {
            $parts = explode('.', $name);
            $index = $parts[0];

            if (!empty($value)) {
                // Verificar se o cofrinho é válido
                $cofrinhoExists = Cofrinho::where('id', $value)
                    ->where('user_id', Auth::id())
                    ->exists();

                if (!$cofrinhoExists) {
                    $this->addError("transactions.{$index}.cofrinho_id", 'Cofrinho inválido.');
                } else {
                    $this->resetErrorBag("transactions.{$index}.cofrinho_id");
                }
            }
        }
    }

    public function getTransactionValidationStatus($transaction)
    {
        $issues = [];

        if (empty($transaction['category_id'])) {
            $issues[] = 'Categoria não selecionada';
        }

        if (empty($transaction['value']) || !is_numeric($transaction['value'])) {
            $issues[] = 'Valor inválido';
        }

        if (empty($transaction['type_id'])) {
            $issues[] = 'Tipo não definido';
        }

        return [
            'isValid' => empty($issues),
            'issues' => $issues,
            'status' => empty($issues) ? 'complete' : 'incomplete'
        ];
    }

    public function getTotalValidTransactions()
    {
        $valid = 0;
        foreach ($this->transactions as $transaction) {
            $status = $this->getTransactionValidationStatus($transaction);
            if ($status['isValid']) {
                $valid++;
            }
        }
        return $valid;
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
            'Transferência' => '1013',
            'Ana Carolina' => '1013',
            'Carolina Ferreira Coelho Freire' => '1013',
            'Nu' => '1010',
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
            $isFirstRow = true;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Pular o cabeçalho se for a primeira linha
                if ($isFirstRow) {
                    $isFirstRow = false;

                    // Verificar se a primeira linha contém cabeçalhos
                    if (isset($data[0]) && (stripos($data[0], 'data') !== false || stripos($data[0], 'date') !== false)) {
                        continue; // Pular cabeçalho
                    }
                }

                // Limpar BOM Unicode se presente no primeiro campo
                if (isset($data[0])) {
                    $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
                    $data[0] = trim($data[0], '"﻿'); // Remove aspas e caracteres BOM
                }

                // Verificar se temos pelo menos 3 campos (data, valor, descrição)
                if (count($data) >= 3 && !empty(trim($data[0]))) {
                    try {
                        // Tentar fazer parse da data
                        $dateString = trim($data[0], '"');

                        // Tentar diferentes formatos de data
                        $date = null;
                        $dateFormats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];

                        foreach ($dateFormats as $format) {
                            $parsedDate = \DateTime::createFromFormat($format, $dateString);
                            if ($parsedDate !== false) {
                                $date = $parsedDate->format('Y-m-d');
                                break;
                            }
                        }

                        if (!$date) {
                            continue; // Pular se não conseguir fazer parse da data
                        }

                        // Processar valor
                        $value = str_replace([',', 'R$', ' '], ['', '', ''], trim($data[1], '"'));
                        $value = (float) $value;

                        if ($value == 0) {
                            continue; // Pular transações com valor zero
                        }

                        // Determinar tipo (receita ou despesa)
                        $typeId = $value > 0 ? 1 : 2; // 1 = receita, 2 = despesa
                        $value = abs($value);

                        $transactions[] = [
                            'date' => $date,
                            'value' => $value,
                            'description' => trim($data[2], '"') ?: 'Transação importada',
                            'category_id' => null,
                            'type_id' => $typeId,
                            'cofrinho_id' => null,
                            'note' => null,
                            'segment_id' => null,
                        ];

                    } catch (\Exception $e) {
                        // Log do erro e continuar com a próxima linha
                        Log::warning('Erro ao processar linha CSV', [
                            'data' => $data,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
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
