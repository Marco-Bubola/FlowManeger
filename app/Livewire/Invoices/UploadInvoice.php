<?php

namespace App\Livewire\Invoices;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceCategoryLearning;
use App\Models\InvoiceUploadHistory;
use App\Services\GeminiTransactionProcessorService;
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
    public $processing = false;

    public $banks = [];
    public $categories = [];
    public $clients = [];

    public $uploadHistory = [];
    public $currentUploadId = null;

    // Modais
    public $showDetailsModal = false;
    public $selectedUpload = null;
    public $confirmDeleteUploadId = null;

    protected $listeners = [
        'confirmTransactions',
        'cancelUpload',
        'confirmDeleteUpload'
    ];

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
        $this->loadUploadHistory();
    }

    public function loadData()
    {
        $this->banks = Bank::all();
        // Carregar apenas categorias do tipo 'transaction' (transação)
        $this->categories = Category::where('is_active', 1)
            ->where('user_id', Auth::id())
            ->where('type', 'transaction')
            ->get();
        $this->clients = Client::all();
    }

    public function loadUploadHistory()
    {
        $this->uploadHistory = InvoiceUploadHistory::forUserAndBank(Auth::id(), $this->bankId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        Log::info('Histórico carregado', [
            'user_id' => Auth::id(),
            'bank_id' => $this->bankId,
            'count' => $this->uploadHistory->count(),
            'uploads' => $this->uploadHistory->pluck('id', 'filename')->toArray()
        ]);
    }

    public function uploadFile()
    {
        $this->validate();

        try {
            // Log de início do processamento
            Log::info('Iniciando processamento do arquivo', [
                'file_name' => $this->file->getClientOriginalName(),
                'file_size' => $this->file->getSize(),
                'user_id' => Auth::id()
            ]);

            // Salvar arquivo PDF para visualização futura
            $filePath = null;
            if ($this->file) {
                $filePath = $this->file->store('uploads/invoices', 'public');
            }

            // Emite evento para mostrar feedback de carregamento
            $this->dispatch('processing-file', 'Processando arquivo...');

            // Usar getRealPath() para obter o caminho temporário diretamente
            $tempPath = $this->file->getRealPath();
            $fileExtension = $this->file->getClientOriginalExtension();

            Log::info('Processando arquivo temporário', [
                'temp_path' => $tempPath,
                'extension' => $fileExtension,
                'file_exists' => file_exists($tempPath)
            ]);

            if (strtolower($fileExtension) === 'pdf') {
                $this->transactions = $this->extractTransactionsFromPdf($tempPath);
            } elseif (strtolower($fileExtension) === 'csv') {
                $this->transactions = $this->extractTransactionsFromCsv($tempPath);
            }

            Log::info('Transações extraídas', [
                'count' => count($this->transactions),
                'transactions' => $this->transactions
            ]);

            if (empty($this->transactions)) {
                session()->flash('error', 'Nenhuma transação foi encontrada no arquivo.');
                $this->dispatch('file-error', 'Nenhuma transação encontrada');
                return;
            }

            // Calcular valor total das transações
            $totalValue = array_sum(array_column($this->transactions, 'value'));

            // Verificar duplicatas e marcar transações
            foreach ($this->transactions as $index => &$transaction) {
                $exists = Invoice::where('id_bank', $this->bankId)
                    ->where('invoice_date', $transaction['date'])
                    ->where('value', $transaction['value'])
                    ->where('description', $transaction['description'])
                    ->where('user_id', Auth::id())
                    ->exists();

                $transaction['is_duplicate'] = $exists;
                $transaction['force_create'] = false; // Flag para forçar criação
            }
            unset($transaction); // Quebrar referência

            // Criar registro de histórico
            $uploadHistory = InvoiceUploadHistory::create([
                'user_id' => Auth::id(),
                'bank_id' => $this->bankId,
                'filename' => $this->file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $fileExtension,
                'total_transactions' => count($this->transactions),
                'total_value' => $totalValue,
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $this->currentUploadId = $uploadHistory->id;

            $this->showConfirmation = true;
            session()->flash('success', count($this->transactions) . ' transações foram encontradas no arquivo.');
            $this->dispatch('file-processed', count($this->transactions) . ' transações encontradas');

        } catch (\Exception $e) {
            Log::error('Erro ao processar arquivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            session()->flash('error', 'Erro ao processar o arquivo: ' . $e->getMessage());
            $this->dispatch('file-error', 'Erro ao processar arquivo: ' . $e->getMessage());
        }
    }

    public function confirmTransactions()
    {
        // Evitar re-submissões concorrentes
        if ($this->processing) {
            Log::warning('confirmTransactions chamado enquanto já está em processamento');
            session()->flash('info', 'Processamento em andamento. Aguarde um momento.');
            return;
        }

        $this->processing = true;

        try {
            $created = 0;
            $skipped = 0;
            $totalValue = 0;
            $createdTransactions = [];
            $skippedTransactions = [];

            foreach ($this->transactions as $transaction) {
                if (empty($transaction['date']) || empty($transaction['description']) || empty($transaction['value'])) {
                    Log::warning('Transação ignorada - dados incompletos', [
                        'transaction' => $transaction
                    ]);
                    continue;
                }

                // Checar duplicata: mesmo banco, data, valor, descrição e usuário
                // Mas permitir se force_create estiver true
                $exists = Invoice::where('id_bank', $this->bankId)
                    ->where('invoice_date', $transaction['date'])
                    ->where('value', $transaction['value'])
                    ->where('description', $transaction['description'])
                    ->where('user_id', Auth::id())
                    ->exists();

                $forceCreate = $transaction['force_create'] ?? false;

                if ($exists && !$forceCreate) {
                    $skipped++;
                    $skippedTransactions[] = [
                        'description' => $transaction['description'],
                        'value' => $transaction['value'],
                        'date' => $transaction['date'],
                        'reason' => 'duplicata'
                    ];
                    Log::info('Invoice ignorada por duplicata', [
                        'date' => $transaction['date'],
                        'value' => $transaction['value'],
                        'description' => $transaction['description']
                    ]);
                    continue;
                }

                // Criar a invoice
                $invoice = Invoice::create([
                    'id_bank' => $this->bankId,
                    'invoice_date' => $transaction['date'],
                    'value' => $transaction['value'],
                    'description' => $transaction['description'],
                    'installments' => $transaction['installments'] ?? null,
                    'category_id' => $transaction['category_id'] ?? '1',
                    'client_id' => $transaction['client_id'] ?? null,
                    'user_id' => Auth::id(),
                ]);

                $created++;
                $totalValue += $transaction['value'];

                $createdTransactions[] = [
                    'id' => $invoice->id,
                    'description' => $transaction['description'],
                    'value' => $transaction['value'],
                    'date' => $transaction['date']
                ];

                // APRENDIZADO: Salvar a associação descrição → categoria para aprendizado futuro
                if (!empty($transaction['category_id'])) {
                    InvoiceCategoryLearning::learn(
                        $transaction['description'],
                        $transaction['category_id'],
                        Auth::id()
                    );

                    Log::info('Padrão de categorização aprendido', [
                        'description' => $transaction['description'],
                        'category_id' => $transaction['category_id'],
                        'normalized_pattern' => InvoiceCategoryLearning::normalizeDescription($transaction['description'])
                    ]);
                }
            }

            // Atualizar histórico de upload com estatísticas finais
            if ($this->currentUploadId) {
                InvoiceUploadHistory::where('id', $this->currentUploadId)->update([
                    'transactions_created' => $created,
                    'transactions_updated' => 0,
                    'transactions_skipped' => $skipped,
                    'total_value' => $totalValue,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'summary' => [
                        'created' => $createdTransactions,
                        'skipped' => $skippedTransactions,
                    ],
                ]);

                Log::info('Upload finalizado com sucesso', [
                    'upload_id' => $this->currentUploadId,
                    'created' => $created,
                    'skipped' => $skipped,
                    'total_value' => $totalValue
                ]);
            }

            // Limpar estado completamente
            $this->reset(['file', 'transactions', 'showConfirmation', 'currentUploadId']);
            $this->processing = false;

            // Recarregar histórico
            $this->loadUploadHistory();

            $msg = "✅ {$created} transações criadas com sucesso!" . ($skipped > 0 ? " {$skipped} duplicatas ignoradas." : "");
            session()->flash('success', $msg);

            Log::info('Redirecionando para index', ['bankId' => $this->bankId]);

            // Redirecionar para a index do banco
            return redirect()->route('invoices.index', ['bankId' => $this->bankId]);

        } catch (\Exception $e) {
            $this->processing = false;

            Log::error('Erro ao confirmar transações', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Atualizar histórico como failed
            if ($this->currentUploadId) {
                InvoiceUploadHistory::where('id', $this->currentUploadId)->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                ]);
            }

            session()->flash('error', 'Erro ao confirmar as transações: ' . $e->getMessage());

            // Não redirecionar em caso de erro, manter na página
            return;
        }
    }

    public function updateTransactionCategory($index, $categoryId)
    {
        if (isset($this->transactions[$index])) {
            $this->transactions[$index]['category_id'] = $categoryId;
        }
    }

    /**
     * Atualiza a data de uma transação (usada pela view durante o upload)
     * Normaliza a data usando parseDate() e registra logs para depuração.
     * Espera receber formatos como d/m/Y, Y-m-d, d-m-Y ou m/d/Y.
     * Pode ser chamado da view com: wire:change="updateTransactionDate({{ $index }}, $event.target.value)"
     *
     * @param int $index
     * @param string $date
     * @return void
     */
    public function updateTransactionDate($index, $date)
    {
        if (!isset($this->transactions[$index])) {
            return;
        }

        // Tentar normalizar com parseDate
        $parsed = $this->parseDate($date);

        // Se parseDate não conseguiu, tentar criar DateTime diretamente
        if (empty($parsed)) {
            try {
                $dt = new \DateTime($date);
                $parsed = $dt->format('Y-m-d');
            } catch (\Exception $e) {
                // Manter o valor recebido caso não seja possível normalizar
                Log::warning('Falha ao parsear data informada pelo usuário, mantendo valor bruto', [
                    'index' => $index,
                    'input_date' => $date,
                    'error' => $e->getMessage(),
                ]);
                $parsed = $date;
            }
        }

        $this->transactions[$index]['date'] = $parsed;

        Log::info('Data da transação atualizada pelo usuário', [
            'index' => $index,
            'date' => $parsed,
            'original_input' => $date,
        ]);
    }

    public function updateTransactionClient($index, $clientId)
    {
        if (isset($this->transactions[$index])) {
            $this->transactions[$index]['client_id'] = $clientId;
        }
    }

    public function forceCreateTransaction($index)
    {
        if (isset($this->transactions[$index])) {
            $this->transactions[$index]['force_create'] = true;
            $this->transactions[$index]['is_duplicate'] = false;
            session()->flash('success', 'Transação marcada para criação forçada.');
        }
    }

    public function removeDuplicates()
    {
        $removedCount = 0;
        $this->transactions = array_values(array_filter($this->transactions, function($transaction) use (&$removedCount) {
            if ($transaction['is_duplicate'] ?? false) {
                $removedCount++;
                return false;
            }
            return true;
        }));

        session()->flash('success', $removedCount . ' transações duplicadas foram removidas.');

        Log::info('Duplicatas removidas', [
            'count' => $removedCount,
            'remaining' => count($this->transactions)
        ]);
    }

    public function showUploadDetails($uploadId)
    {
        $this->selectedUpload = InvoiceUploadHistory::find($uploadId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedUpload = null;
    }

    public function confirmDeleteUpload($uploadId)
    {
        $this->confirmDeleteUploadId = $uploadId;
        $this->dispatch('show-delete-upload-modal');
    }

    public function deleteUpload()
    {
        try {
            $upload = InvoiceUploadHistory::find($this->confirmDeleteUploadId);

            if ($upload) {
                // Deletar arquivo se existir
                if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
                    Storage::disk('public')->delete($upload->file_path);
                }

                $upload->delete();

                session()->flash('success', 'Histórico de upload excluído com sucesso.');
                Log::info('Histórico de upload excluído', ['upload_id' => $this->confirmDeleteUploadId]);
            }

            $this->confirmDeleteUploadId = null;
            $this->loadUploadHistory();
            $this->dispatch('hide-delete-upload-modal');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir histórico de upload', [
                'error' => $e->getMessage(),
                'upload_id' => $this->confirmDeleteUploadId
            ]);
            session()->flash('error', 'Erro ao excluir histórico: ' . $e->getMessage());
        }
    }

    public function createInvoiceFromSkipped($skippedData)
    {
        try {
            // Verificar se os dados são válidos
            if (empty($skippedData['description']) || empty($skippedData['value']) || empty($skippedData['date'])) {
                session()->flash('error', 'Dados incompletos para criar a transação.');
                return;
            }

            // Criar a invoice
            $invoice = Invoice::create([
                'id_bank' => $this->bankId,
                'invoice_date' => $skippedData['date'],
                'value' => $skippedData['value'],
                'description' => $skippedData['description'],
                'installments' => null,
                'category_id' => 1, // Categoria padrão
                'client_id' => null,
                'user_id' => Auth::id(),
            ]);

            session()->flash('success', 'Transação criada com sucesso!');
            Log::info('Invoice criada a partir de transação ignorada', [
                'invoice_id' => $invoice->id,
                'description' => $skippedData['description']
            ]);

            // Recarregar histórico
            $this->loadUploadHistory();
            $this->selectedUpload = InvoiceUploadHistory::find($this->selectedUpload->id);

        } catch (\Exception $e) {
            Log::error('Erro ao criar invoice de transação ignorada', [
                'error' => $e->getMessage(),
                'data' => $skippedData
            ]);
            session()->flash('error', 'Erro ao criar transação: ' . $e->getMessage());
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
        Log::info('Upload cancelado pelo usuário', [
            'user_id' => Auth::id(),
            'bankId' => $this->bankId,
            'transactions_count' => count($this->transactions),
            'upload_id' => $this->currentUploadId
        ]);

        // Atualizar histórico como cancelado se existir
        if ($this->currentUploadId) {
            InvoiceUploadHistory::where('id', $this->currentUploadId)->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
        }

        // Resetar tudo
        $this->reset(['file', 'transactions', 'showConfirmation', 'currentUploadId', 'processing']);

        // Recarregar histórico
        $this->loadUploadHistory();

        session()->flash('info', 'Upload cancelado.');
        $this->dispatch('upload-cancelled');
    }

    protected function extractTransactionsFromCsv($csvPath)
    {
        $transactions = [];
        $categoryMapping = $this->getCategoryMapping();

        Log::info('Iniciando extração de CSV', ['path' => $csvPath]);

        if (!file_exists($csvPath)) {
            Log::error('Arquivo CSV não encontrado', ['path' => $csvPath]);
            return $transactions;
        }

        // Verificar se o arquivo é legível
        if (!is_readable($csvPath)) {
            Log::error('Arquivo CSV não é legível', ['path' => $csvPath]);
            return $transactions;
        }

        // Verificar o tamanho do arquivo
        $fileSize = filesize($csvPath);
        Log::info('Tamanho do arquivo CSV', ['size' => $fileSize]);

        if ($fileSize === 0) {
            Log::error('Arquivo CSV está vazio');
            return $transactions;
        }

        if (($handle = fopen($csvPath, 'r')) !== false) {
            // Primeiro, vamos ler o arquivo linha por linha para debug
            $allContent = file_get_contents($csvPath);
            Log::info('Conteúdo do arquivo (primeiros 500 chars)', [
                'content' => substr($allContent, 0, 500)
            ]);

            // Detectar o separador (vírgula ou ponto e vírgula)
            $separator = ',';
            if (substr_count($allContent, ';') > substr_count($allContent, ',')) {
                $separator = ';';
            }
            Log::info('Separador detectado', ['separator' => $separator]);

            // Voltar para o início do arquivo
            rewind($handle);

            // Detectar encoding - tentar UTF-8 primeiro, depois ISO-8859-1
            $firstLine = fgets($handle);
            rewind($handle);

            Log::info('Primeira linha do arquivo', ['line' => trim($firstLine)]);

            if (!mb_check_encoding($firstLine, 'UTF-8')) {
                Log::info('Arquivo não está em UTF-8, convertendo de ISO-8859-1');
                // Se não for UTF-8, converter de ISO-8859-1
                stream_filter_append($handle, 'convert.iconv.ISO-8859-1/UTF-8');
            }

            $headers = fgetcsv($handle, 1000, $separator);

            // Verificar se conseguiu ler os headers
            if ($headers === false || !is_array($headers)) {
                Log::error('Falha ao ler headers do CSV');
                fclose($handle);
                return [];
            }

            Log::info('Headers CSV detectados', ['headers' => $headers]);

            // Detectar formato do CSV baseado nos headers
            $csvFormat = 'default'; // formato padrão (5 colunas: date, description, category, type, value)
            if (count($headers) === 3) {
                // Verificar se é formato Nubank (date, title, amount)
                $header0 = strtolower(trim($headers[0]));
                $header1 = strtolower(trim($headers[1]));
                $header2 = strtolower(trim($headers[2]));

                if (($header0 === 'date' || $header0 === 'data') &&
                    ($header1 === 'title' || $header1 === 'description' || $header1 === 'titulo' || $header1 === 'descrição') &&
                    ($header2 === 'amount' || $header2 === 'value' || $header2 === 'valor')) {
                    $csvFormat = 'nubank';
                    Log::info('Formato Nubank detectado (3 colunas: date, title, amount)');
                }
            }
            Log::info('Formato CSV detectado', ['format' => $csvFormat, 'columns' => count($headers)]);

            // Array para acumular transações Nubank antes do processamento Gemini
            $nubankRawTransactions = [];

            $lineNumber = 1;
            while (($data = fgetcsv($handle, 1000, $separator)) !== false) {
                $lineNumber++;

                // Limpar aspas e espaços em branco
                $data = array_map(function ($item) {
                    return trim(str_replace('"', '', $item));
                }, $data);

                Log::debug("Processando linha {$lineNumber}", ['data' => $data]);

                $transaction = null;

                if ($csvFormat === 'nubank' && count($data) >= 3) {
                    // Formato Nubank: date, title, amount
                    $dateString = $data[0] ?? '';
                    $description = $data[1] ?? 'Descrição não disponível';
                    $valueString = $data[2] ?? '0';

                    $parsedDate = $this->parseDate($dateString);
                    $parsedValue = $this->processValue($valueString);

                    // Valores negativos são receitas (invertemos para tornar positivo se for despesa)
                    // Valores positivos são despesas
                    if ($parsedValue < 0) {
                        // Receita - ignorar ou processar conforme necessário
                        Log::debug("Transação ignorada (receita/crédito)", ['description' => $description, 'value' => $parsedValue]);
                        continue;
                    }

                    // Acumular transação bruta para processamento em lote com Gemini
                    $nubankRawTransactions[] = [
                        'date' => $parsedDate,
                        'description' => $description,
                        'value' => abs($parsedValue),
                    ];

                    Log::debug("Transação Nubank acumulada", ['description' => $description]);

                } elseif (count($data) >= 5) {
                    // Formato padrão (Inter/outros): date, description, category, type, value
                    $dateString = $data[0] ?? '';
                    $description = $data[1] ?? 'Descrição não disponível';
                    $category = $data[2] ?? '';
                    $type = $data[3] ?? 'Compra à vista';
                    $valueString = $data[4] ?? '0';

                    $parsedDate = $this->parseDate($dateString);
                    $parsedValue = $this->processValue($valueString);

                    $transaction = [
                        'date' => $parsedDate,
                        'description' => $description,
                        'installments' => $type,
                        'value' => $parsedValue,
                        'category_id' => $this->determineCategoryId($description, $categoryMapping),
                        'client_id' => null,
                    ];

                    Log::debug("Transação padrão processada", $transaction);
                } else {
                    Log::warning("Linha {$lineNumber} não corresponde a nenhum formato esperado", [
                        'data_count' => count($data),
                        'data' => $data,
                        'expected_format' => $csvFormat
                    ]);
                }

                // Adicionar transação se válida
                if ($transaction && $transaction['value'] > 0 && !empty($transaction['date']) && !empty($transaction['description'])) {
                    $transactions[] = $transaction;
                    Log::info("Transação adicionada", $transaction);
                } elseif ($transaction) {
                    Log::warning("Transação rejeitada - dados insuficientes", [
                        'transaction' => $transaction,
                        'value_empty' => empty($transaction['value']),
                        'date_empty' => empty($transaction['date']),
                        'description_empty' => empty($transaction['description'])
                    ]);
                }
            }

            fclose($handle);

            // Processar transações Nubank com Gemini se houver alguma
            if ($csvFormat === 'nubank' && !empty($nubankRawTransactions)) {
                Log::info('Processando transações Nubank com Gemini AI', [
                    'total_raw_transactions' => count($nubankRawTransactions)
                ]);

                try {
                    $geminiService = new GeminiTransactionProcessorService();

                    // Preparar categorias disponíveis para o Gemini
                    $availableCategories = $this->categories->map(function($cat) {
                        return [
                            'id' => $cat->id,
                            'name' => $cat->name,
                        ];
                    })->toArray();

                    $processedTransactions = $geminiService->processTransactions(
                        $nubankRawTransactions,
                        $availableCategories
                    );

                    // Preencher category_id para transações que não tiveram categorização pelo Gemini
                    foreach ($processedTransactions as &$transaction) {
                        if (empty($transaction['category_id'])) {
                            $transaction['category_id'] = $this->determineCategoryId(
                                $transaction['description'],
                                $categoryMapping
                            );
                        }
                    }

                    $transactions = array_merge($transactions, $processedTransactions);

                    Log::info('Processamento Gemini concluído', [
                        'total_processed' => count($processedTransactions)
                    ]);

                } catch (\Exception $e) {
                    Log::error('Erro ao processar com Gemini, usando fallback', [
                        'error' => $e->getMessage()
                    ]);

                    // Fallback: processar sem Gemini
                    foreach ($nubankRawTransactions as $rawTransaction) {
                        $transactions[] = [
                            'date' => $rawTransaction['date'],
                            'description' => $rawTransaction['description'],
                            'installments' => 'Compra à vista',
                            'value' => $rawTransaction['value'],
                            'category_id' => $this->determineCategoryId(
                                $rawTransaction['description'],
                                $categoryMapping
                            ),
                            'client_id' => null,
                        ];
                    }
                }
            }
        } else {
            Log::error('Não foi possível abrir o arquivo CSV', ['path' => $csvPath]);
        }

        Log::info('Extração de CSV finalizada', [
            'total_transactions' => count($transactions),
            'transactions' => $transactions
        ]);

        return $transactions;
    }    protected function extractTransactionsFromPdf($pdfPath)
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

                // Processar parcelas - Múltiplos formatos:
                // 1. (8 de 10) ou (8/10)
                // 2. Pcl8de10 ou Pcl 8 de 10
                // 3. Parc8/10
                // 4. 8de10
                if (preg_match('/\(?\s*(\d+)\s*(?:de|\/)\s*(\d+)\s*\)?/', $trimmedLine, $parcelMatches)) {
                    $currentTransaction['installments'] = "{$parcelMatches[1]} de {$parcelMatches[2]}";
                } elseif (preg_match('/(?:pcl|parc|parcela)\s*(\d+)\s*(?:de|\/)\s*(\d+)/i', $trimmedLine, $parcelMatches)) {
                    $currentTransaction['installments'] = "{$parcelMatches[1]} de {$parcelMatches[2]}";
                } elseif (preg_match('/(?:^|\*|_)(\d+)\s*de\s*(\d+)(?:$|\*|_)/i', $trimmedLine, $parcelMatches)) {
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

        Log::debug('Parseando data', ['original' => $dateString]);

        // Tentar diferentes formatos de data
        $formats = [
            'd/m/Y',    // 30/04/2025
            'Y-m-d',    // 2025-04-30
            'd-m-Y',    // 30-04-2025
            'm/d/Y',    // 04/30/2025
        ];

        foreach ($formats as $format) {
            try {
                $date = \DateTime::createFromFormat($format, trim($dateString));
                if ($date !== false) {
                    $result = $date->format('Y-m-d');
                    Log::debug('Data parseada com sucesso', [
                        'format' => $format,
                        'result' => $result
                    ]);
                    return $result;
                }
            } catch (\Exception $e) {
                // Continue tentando outros formatos
                continue;
            }
        }

        // Se não conseguiu parsear, tentar regex para formato dd/mm/yyyy
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            $result = $year . '-' . $month . '-' . $day;

            Log::debug('Data parseada via regex', ['result' => $result]);
            return $result;
        }

        Log::warning('Não foi possível parsear a data', ['dateString' => $dateString]);
        return '';
    }

    private function processValue($value)
    {
        if (empty($value)) return 0;

        // Log do valor original
        Log::debug('Processando valor', ['original' => $value]);

        // Remover prefixos como "R$" e espaços
        $value = str_replace(['R$', ' '], '', $value);

        // Se contém vírgula, assumir formato brasileiro (1.234,56)
        if (strpos($value, ',') !== false) {
            // Remover pontos (separadores de milhares) e trocar vírgula por ponto
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        // Remover qualquer caractere que não seja número, ponto ou sinal negativo
        $value = preg_replace('/[^\d.-]/', '', $value);
        $value = trim($value);

        $result = is_numeric($value) ? abs(floatval($value)) : 0;

        Log::debug('Valor processado', ['processed' => $result]);

        return $result;
    }

    private function determineCategoryId($description, $categoryMapping)
    {
        // 1. PRIORIDADE MÁXIMA: Tentar aprendizado de máquina primeiro
        $learnedCategoryId = InvoiceCategoryLearning::findCategoryForDescription($description, Auth::id());

        if ($learnedCategoryId) {
            Log::info('Categoria encontrada via aprendizado de máquina', [
                'learned_category_id' => $learnedCategoryId,
                'description' => $description
            ]);
            return $learnedCategoryId;
        }

        // 2. Tentar encontrar categoria baseada nas palavras-chave do mapeamento estático
        foreach ($categoryMapping as $keyword => $categoryId) {
            if (stripos($description, $keyword) !== false) {
                Log::info('Categoria encontrada via mapeamento estático', [
                    'keyword' => $keyword,
                    'category_id' => $categoryId,
                    'description' => $description
                ]);
                return $categoryId;
            }
        }

        // 3. Se não encontrar, buscar a categoria "Outros"
        $outrosCategory = Category::where('is_active', 1)
            ->where('user_id', Auth::id())
            ->where('type', 'transaction')
            ->where('name', 'Outros')
            ->first();

        // Se encontrou a categoria "Outros", usar ela
        if ($outrosCategory) {
            Log::info('Categoria não identificada - usando "Outros"', [
                'category_id' => $outrosCategory->id_category,
                'description' => $description
            ]);
            return $outrosCategory->id_category;
        }

        // 4. Se não existe categoria "Outros", usar a primeira categoria de transação
        $defaultCategory = Category::where('is_active', 1)
            ->where('user_id', Auth::id())
            ->where('type', 'transaction')
            ->orderBy('id_category')
            ->first();

        $defaultId = $defaultCategory ? $defaultCategory->id_category : null;

        Log::warning('Categoria "Outros" não encontrada - usando primeira categoria', [
            'default_category_id' => $defaultId,
            'description' => $description
        ]);

        return $defaultId;
    }    private function shouldExcludeLine($line)
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

    /**
     * Cria mapeamento dinâmico de palavras-chave para categorias do banco de dados
     * Retorna array com [palavra-chave => id_category]
     */
    private function getCategoryMapping()
    {
        $mapping = [];

        // Buscar todas as categorias do tipo 'transaction' do usuário
        $categories = Category::where('is_active', 1)
            ->where('user_id', Auth::id())
            ->where('type', 'transaction')
            ->get();

        Log::info('Categorias encontradas para mapeamento', [
            'total' => $categories->count(),
            'categories' => $categories->pluck('name', 'id_category')->toArray()
        ]);

        // Mapear categorias e suas palavras-chave
        foreach ($categories as $category) {
            $categoryId = $category->id_category;
            $categoryName = strtoupper($category->name);
            $categoryNameLower = strtolower($category->name);

            // Adicionar o nome da categoria como palavra-chave
            $mapping[$categoryName] = $categoryId;

            // Mapeamentos específicos baseados no nome da categoria (case-insensitive e com variações)

            // SUPERMERCADOS E ALIMENTAÇÃO
            if (stripos($categoryNameLower, 'supermercado') !== false ||
                stripos($categoryNameLower, 'alimenta') !== false ||
                stripos($categoryNameLower, 'aliment') !== false) {
                $mapping['SUPERMERCADO'] = $categoryId;
                $mapping['SUPERMERCA'] = $categoryId;
                $mapping['ANTONELLI'] = $categoryId;
                $mapping['ATACADAO'] = $categoryId;
                $mapping['ATACADÃO'] = $categoryId;
                $mapping['1A99'] = $categoryId;
                $mapping['POPULAR'] = $categoryId;
                $mapping['ROFATTO'] = $categoryId;
                $mapping['CUBATAO'] = $categoryId;
                $mapping['CUBATÃO'] = $categoryId;
            }

            // BARES E RESTAURANTES
            if (stripos($categoryNameLower, 'bar') !== false ||
                stripos($categoryNameLower, 'restaurante') !== false ||
                stripos($categoryNameLower, 'beer') !== false ||
                stripos($categoryNameLower, 'maco') !== false) {
                $mapping['RESTAURANTE'] = $categoryId;
                $mapping['LANCHONETE'] = $categoryId;
                $mapping['BEER'] = $categoryId;
                $mapping['BURGER'] = $categoryId;
                $mapping['TOURO'] = $categoryId;
                $mapping['TUTTIBOM'] = $categoryId;
                $mapping['ACAITERIA'] = $categoryId;
                $mapping['COMITIVALANCH'] = $categoryId;
                $mapping['SOSBEER'] = $categoryId;
                $mapping['AYLTONCERAGIOLI'] = $categoryId;
            }

            // COMBUSTÍVEIS E POSTOS
            if (stripos($categoryNameLower, 'combusti') !== false ||
                stripos($categoryNameLower, 'posto') !== false) {
                $mapping['POSTO'] = $categoryId;
                $mapping['ABAST'] = $categoryId;
                $mapping['SHELL'] = $categoryId;
                $mapping['FROGPAY'] = $categoryId;
                $mapping['AUTO POSTO'] = $categoryId;
                $mapping['AUTOPOSTO'] = $categoryId;
                $mapping['ARENA'] = $categoryId;
            }

            // MECÂNICO E PNEUS
            if (stripos($categoryNameLower, 'mecan') !== false ||
                stripos($categoryNameLower, 'pneu') !== false) {
                $mapping['PNEUS'] = $categoryId;
                $mapping['JSROSAPNEUS'] = $categoryId;
                $mapping['MECANICA'] = $categoryId;
                $mapping['BORRACHARIA'] = $categoryId;
            }

            // COMPRAS E BELEZA / COMPRAS ONLINE
            if (stripos($categoryNameLower, 'compra') !== false ||
                stripos($categoryNameLower, 'beleza') !== false ||
                stripos($categoryNameLower, 'online') !== false) {
                $mapping['TABACARIA'] = $categoryId;
                $mapping['SHOPEE'] = $categoryId;
                $mapping['MERCADOLIVRE'] = $categoryId;
                $mapping['MERCADO LIVRE'] = $categoryId;
                $mapping['NETSHOES'] = $categoryId;
                $mapping['HUB NETSHOES'] = $categoryId;
                $mapping['SHOPPING'] = $categoryId;
                $mapping['CP PARC'] = $categoryId;
                $mapping['MAGAZINE'] = $categoryId;
                $mapping['LOJAS'] = $categoryId;
            }

            // EUDORA & BOTICÁRIO
            if (stripos($categoryNameLower, 'eudora') !== false ||
                stripos($categoryNameLower, 'boticario') !== false ||
                stripos($categoryNameLower, 'boticário') !== false ||
                stripos($categoryNameLower, 'cosmetico') !== false ||
                stripos($categoryNameLower, 'cosmético') !== false) {
                $mapping['BOTICARIO'] = $categoryId;
                $mapping['BOTICÁRIO'] = $categoryId;
                $mapping['NATURA'] = $categoryId;
                $mapping['EUDORA'] = $categoryId;
                $mapping['AVON'] = $categoryId;
            }

            // FARMÁCIAS E SAÚDE
            if (stripos($categoryNameLower, 'farmacia') !== false ||
                stripos($categoryNameLower, 'farmácia') !== false ||
                stripos($categoryNameLower, 'saude') !== false ||
                stripos($categoryNameLower, 'saúde') !== false) {
                $mapping['PHARMA'] = $categoryId;
                $mapping['DROGARIA'] = $categoryId;
                $mapping['FARMACIA'] = $categoryId;
                $mapping['FARMÁCIA'] = $categoryId;
                $mapping['DROGASIL'] = $categoryId;
                $mapping['PACHECO'] = $categoryId;
                $mapping['ULTRAFARMA'] = $categoryId;
                $mapping['PAGUE MENOS'] = $categoryId;
                $mapping['SAOPAULO'] = $categoryId;
                $mapping['SÃO PAULO'] = $categoryId;
            }

            // SEGUROS E SERVIÇOS FINANCEIROS
            if (stripos($categoryNameLower, 'seguro') !== false ||
                stripos($categoryNameLower, 'protec') !== false) {
                $mapping['MAPFRE'] = $categoryId;
                $mapping['PORTO'] = $categoryId;
                $mapping['PORTO SEGURO'] = $categoryId;
                $mapping['SULAMERICA'] = $categoryId;
                $mapping['ALLIANZ'] = $categoryId;
                $mapping['BRADESCO SEGUROS'] = $categoryId;
            }

            // STREAMING
            if (stripos($categoryNameLower, 'streaming') !== false) {
                $mapping['NETFLIX'] = $categoryId;
                $mapping['SPOTIFY'] = $categoryId;
                $mapping['PRIME'] = $categoryId;
                $mapping['DISNEY'] = $categoryId;
                $mapping['HBO'] = $categoryId;
            }

            // HOSPEDAGEM E VIAGENS
            if (stripos($categoryNameLower, 'hospedagem') !== false ||
                stripos($categoryNameLower, 'viag') !== false ||
                stripos($categoryNameLower, 'turismo') !== false) {
                $mapping['AIRBNB'] = $categoryId;
                $mapping['HOTEL'] = $categoryId;
                $mapping['POUSADA'] = $categoryId;
                $mapping['BOOKING'] = $categoryId;
                $mapping['DECOLAR'] = $categoryId;
                $mapping['HOPI HARI'] = $categoryId;
                $mapping['HOPIHARI'] = $categoryId;
                $mapping['CVC'] = $categoryId;
                $mapping['LATAM'] = $categoryId;
                $mapping['GOL'] = $categoryId;
                $mapping['AZUL'] = $categoryId;
            }

            // ACADEMIA E FITNESS
            if (stripos($categoryNameLower, 'academia') !== false ||
                stripos($categoryNameLower, 'fitness') !== false) {
                $mapping['ACADEMIA'] = $categoryId;
                $mapping['GYM'] = $categoryId;
                $mapping['FITNESS'] = $categoryId;
                $mapping['SMARTFIT'] = $categoryId;
                $mapping['BLUEFIT'] = $categoryId;
            }

            // EDUCAÇÃO E CURSOS
            if (stripos($categoryNameLower, 'educacao') !== false ||
                stripos($categoryNameLower, 'educação') !== false ||
                stripos($categoryNameLower, 'curso') !== false ||
                stripos($categoryNameLower, 'escola') !== false) {
                $mapping['UDEMY'] = $categoryId;
                $mapping['COURSERA'] = $categoryId;
                $mapping['ALURA'] = $categoryId;
                $mapping['ESCOLA'] = $categoryId;
                $mapping['UNIVERSIDADE'] = $categoryId;
                $mapping['FACULDADE'] = $categoryId;
            }

            // TRANSPORTES E MOBILIDADE
            if (stripos($categoryNameLower, 'transport') !== false ||
                stripos($categoryNameLower, 'uber') !== false ||
                stripos($categoryNameLower, 'mobilidade') !== false ||
                stripos($categoryNameLower, 'taxi') !== false) {
                $mapping['UBER'] = $categoryId;
                $mapping['99'] = $categoryId;
                $mapping['99POP'] = $categoryId;
                $mapping['CABIFY'] = $categoryId;
                $mapping['TEM'] = $categoryId; // TEM (Transporte Escolar Municipal)
            }

            // CONTAS E UTILIDADES
            if (stripos($categoryNameLower, 'conta') !== false ||
                stripos($categoryNameLower, 'utilidade') !== false ||
                stripos($categoryNameLower, 'energia') !== false ||
                stripos($categoryNameLower, 'agua') !== false ||
                stripos($categoryNameLower, 'água') !== false ||
                stripos($categoryNameLower, 'internet') !== false ||
                stripos($categoryNameLower, 'telefone') !== false) {
                $mapping['CPFL'] = $categoryId;
                $mapping['SABESP'] = $categoryId;
                $mapping['VIVO'] = $categoryId;
                $mapping['TIM'] = $categoryId;
                $mapping['CLARO'] = $categoryId;
                $mapping['NET'] = $categoryId;
                $mapping['OI'] = $categoryId;
            }

            // Adicionar tags da categoria se existirem
            if (!empty($category->tags)) {
                $tags = explode(',', $category->tags);
                foreach ($tags as $tag) {
                    $tag = trim(strtoupper($tag));
                    if (!empty($tag)) {
                        $mapping[$tag] = $categoryId;
                    }
                }
            }

            // Adicionar regras de auto-categorização se existirem
            if (!empty($category->regras_auto_categorizacao)) {
                try {
                    $regras = json_decode($category->regras_auto_categorizacao, true);
                    if (is_array($regras)) {
                        foreach ($regras as $palavra) {
                            $mapping[strtoupper($palavra)] = $categoryId;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignorar erros de JSON
                }
            }
        }

        Log::info('Mapeamento de categorias criado', [
            'total_mappings' => count($mapping),
            'sample' => array_slice($mapping, 0, 10, true)
        ]);

        return $mapping;
    }    public function render()
    {
        return view('livewire.invoices.upload-invoice');
    }
}
