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
            ->where('user_id', Auth::id())
            ->get();
        $this->clients = Client::all();
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
        try {
            foreach ($this->transactions as $transaction) {
                if (!empty($transaction['date']) && !empty($transaction['description']) && !empty($transaction['value'])) {
                    Invoice::create([
                        'id_bank' => $this->bankId,
                        'invoice_date' => $transaction['date'],
                        'value' => $transaction['value'],
                        'description' => $transaction['description'],
                        'installments' => $transaction['installments'] ?? null,
                        'category_id' => $transaction['category_id'] ?? '1',
                        'client_id' => $transaction['client_id'] ?? null,
                        'user_id' => Auth::id(),
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
            Log::info('Headers CSV detectados', ['headers' => $headers]);

            $lineNumber = 1;
            while (($data = fgetcsv($handle, 1000, $separator)) !== false) {
                $lineNumber++;

                // Limpar aspas e espaços em branco
                $data = array_map(function ($item) {
                    return trim(str_replace('"', '', $item));
                }, $data);

                Log::debug("Processando linha {$lineNumber}", ['data' => $data]);

                if (count($data) >= 5) {
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

                    Log::debug("Transação processada", $transaction);

                    if ($transaction['value'] > 0 && !empty($transaction['date']) && !empty($transaction['description'])) {
                        $transactions[] = $transaction;
                        Log::info("Transação adicionada", $transaction);
                    } else {
                        Log::warning("Transação rejeitada - dados insuficientes", [
                            'transaction' => $transaction,
                            'value_empty' => empty($transaction['value']),
                            'date_empty' => empty($transaction['date']),
                            'description_empty' => empty($transaction['description'])
                        ]);
                    }
                } else {
                    Log::warning("Linha {$lineNumber} tem menos de 5 colunas", [
                        'data_count' => count($data),
                        'data' => $data
                    ]);
                }
            }

            fclose($handle);
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
        foreach ($categoryMapping as $keyword => $categoryId) {
            if (stripos($description, $keyword) !== false) {
                return $categoryId;
            }
        }
        return '1'; // Categoria padrão
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
            // Supermercados - usar categoria ID 1 (padrão)
            'SUPERMERCADO' => '1',
            'SUPERMERCA' => '1',
            'ANTONELLI' => '1',
            'ATACADAO' => '1',
            '1A99' => '1',
            'POPULAR' => '1',
            'ROFATTO' => '1',
            'CUBATAO' => '1',

            // Restaurantes e Alimentação
            'BEER' => '1',
            'BURGER' => '1',
            'SosBeer' => '1',
            'TOURO' => '1',
            'TUTTIBOM' => '1',
            'ACAITERIA' => '1',
            'AyltonCeragioli' => '1',
            'ComitivaLanch' => '1',
            'RESTAURANTE' => '1',
            'LANCHONETE' => '1',

            // Transporte e Combustível
            'POSTO' => '1',
            'ABAST' => '1',
            'SHELL' => '1',
            'FROGPAY' => '1',
            'AUTO POSTO' => '1',
            'ARENA' => '1',
            'JSRosaPneus' => '1',
            'PNEUS' => '1',

            // Compras e Varejo
            'TABACARIA' => '1',
            'SHOPEE' => '1',
            'MERCADOLIVRE' => '1',
            'NETSHOES' => '1',
            'HUB NETSHOES' => '1',

            // Beleza e Cosméticos
            'BOTICARIO' => '1',
            'NATURA' => '1',

            // Farmácia
            'PHARMA' => '1',
            'DROGARIA' => '1',
            'FARMACIA' => '1',

            // Telecomunicações
            'CLARO' => '1',
            'VIVO' => '1',
            'TIM' => '1',
            'OI' => '1',

            // Entretenimento
            'HOPI HARI' => '1',
            'CINEMA' => '1',
            'TEATRO' => '1',

            // Viagem
            'AIRBNB' => '1',
            'HOTEL' => '1',
            'POUSADA' => '1',

            // Shopping
            'SHOPPING' => '1',
            'CP PARC' => '1',
        ];
    }

    public function render()
    {
        return view('livewire.invoices.upload-invoice');
    }
}
