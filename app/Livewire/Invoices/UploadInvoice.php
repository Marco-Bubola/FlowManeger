<?php

namespace App\Livewire\Invoices;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceCategoryLearning;
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
        // Carregar apenas categorias do tipo 'transaction' (transação)
        $this->categories = Category::where('is_active', 1)
            ->where('user_id', Auth::id())
            ->where('type', 'transaction')
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
                    // Criar a invoice
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
            }

            session()->flash('success', 'Transações confirmadas com sucesso!');
            $this->dispatch('invoice-created');

            return redirect()->route('invoices.index', ['bankId' => $this->bankId]);

        } catch (\Exception $e) {
            Log::error('Erro ao confirmar transações', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Erro ao confirmar as transações: ' . $e->getMessage());
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
                stripos($categoryNameLower, 'boticario') !== false) {
                $mapping['BOTICARIO'] = $categoryId;
                $mapping['BOTICÁRIO'] = $categoryId;
                $mapping['NATURA'] = $categoryId;
                $mapping['EUDORA'] = $categoryId;
            }

            // FARMÁCIAS E SAÚDE
            if (stripos($categoryNameLower, 'farmacia') !== false ||
                stripos($categoryNameLower, 'saude') !== false ||
                stripos($categoryNameLower, 'saúde') !== false) {
                $mapping['PHARMA'] = $categoryId;
                $mapping['DROGARIA'] = $categoryId;
                $mapping['FARMACIA'] = $categoryId;
                $mapping['FARMÁCIA'] = $categoryId;
                $mapping['DROGASIL'] = $categoryId;
                $mapping['PACHECO'] = $categoryId;
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
                stripos($categoryNameLower, 'viag') !== false) {
                $mapping['AIRBNB'] = $categoryId;
                $mapping['HOTEL'] = $categoryId;
                $mapping['POUSADA'] = $categoryId;
                $mapping['BOOKING'] = $categoryId;
                $mapping['DECOLAR'] = $categoryId;
                $mapping['HOPI HARI'] = $categoryId;
                $mapping['HOPIHARI'] = $categoryId;
            }

            // ACADEMIA
            if (stripos($categoryNameLower, 'academia') !== false) {
                $mapping['ACADEMIA'] = $categoryId;
                $mapping['GYM'] = $categoryId;
                $mapping['FITNESS'] = $categoryId;
                $mapping['SMARTFIT'] = $categoryId;
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
