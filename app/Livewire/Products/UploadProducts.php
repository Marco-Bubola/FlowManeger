<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductCategoryLearning;
use App\Models\ProductUploadHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Smalot\PdfParser\Parser;

class UploadProducts extends Component
{
    use WithFileUploads;

    public $pdf_file;
    public $productsUpload = [];
    public $showProductsTable = false;
    public $uploadProgress = 0;
    public $isProcessing = false;
    public $errorMessage = '';
    public $successMessage = '';
    public $duplicates = []; // Produtos duplicados encontrados
    public $removedProducts = []; // Produtos removidos (para undo)
    public $bulkCategoryId = null; // Categoria para edição em massa
    public $bulkStatus = null; // Status para edição em massa
    public $showHistory = false; // Controle para exibir histórico
    public $uploadHistory = []; // Histórico de uploads
    public $currentUploadId = null; // ID do upload atual
    public $showTipsModal = false; // Controle para exibir modal de dicas

    public function mount()
    {
        $this->loadUploadHistory();
    }

    public function loadUploadHistory()
    {
        $this->uploadHistory = ProductUploadHistory::forUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function toggleHistory()
    {
        $this->showHistory = !$this->showHistory;
        if ($this->showHistory) {
            $this->loadUploadHistory();
        }
    }

    public function toggleTips()
    {
        $this->showTipsModal = !$this->showTipsModal;
    }

    public function viewPdf($uploadId)
    {
        $upload = ProductUploadHistory::find($uploadId);

        if ($upload && $upload->file_path && Storage::exists($upload->file_path)) {
            $pdfUrl = Storage::url($upload->file_path);
            $this->dispatch('open-pdf', ['url' => $pdfUrl]);
        } else {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Arquivo PDF não encontrado'
            ]);
        }
    }

    public function getCategoryIcon($icone)
    {
        // Mapear ícones icons8 para Bootstrap Icons
        $iconMap = [
            'icons8-perfume' => 'bi-emoji-heart-eyes',
            'icons8-nubank' => 'bi-credit-card-2-front',
            'icons8-pagamento' => 'bi-currency-dollar',
            'icons8-pix' => 'bi-lightning-charge',
            'icons8-xp' => 'bi-graph-up-arrow',
            'icons8-inter' => 'bi-bank',
            'icons8-rendimento' => 'bi-graph-up',
            'icons8-restaurante' => 'bi-cup-straw',
            'icons8-beleza' => 'bi-heart',
            'icons8-supermercado' => 'bi-cart',
            'icons8-transporte' => 'bi-bus-front',
            'icons8-casa' => 'bi-house',
            'icons8-saude' => 'bi-heart-pulse',
            'icons8-educacao' => 'bi-book',
            'icons8-entretenimento' => 'bi-controller',
            'icons8-vestuario' => 'bi-bag',
            'icons8-tecnologia' => 'bi-laptop',
            'icons8-combustivel' => 'bi-fuel-pump',
            'icons8-farmacia' => 'bi-capsule',
            'icons8-pet' => 'bi-heart',
        ];

        return $iconMap[$icone] ?? 'bi-tag';
    }

    public function rules()
    {
        return [
            'pdf_file' => 'required|file|mimes:pdf,csv|max:2048',
        ];
    }

    public function updatedPdfFile()
    {
        // Reset states when new file is selected
        $this->reset(['productsUpload', 'showProductsTable', 'uploadProgress', 'errorMessage', 'successMessage']);

        if ($this->pdf_file) {
            $this->validateFileType();
        }
    }

    public function validateFileType()
    {
        $extension = $this->pdf_file->extension();
        $maxSize = 2048; // 2MB em KB
        $fileSize = $this->pdf_file->getSize() / 1024; // Converter para KB

        if (!in_array($extension, ['pdf', 'csv'])) {
            $this->errorMessage = 'Tipo de arquivo não suportado. Use apenas PDF ou CSV.';
            $this->reset('pdf_file');
            return false;
        }

        if ($fileSize > $maxSize) {
            $this->errorMessage = 'Arquivo muito grande. Máximo permitido: 2MB.';
            $this->reset('pdf_file');
            return false;
        }

        $this->errorMessage = '';
        return true;
    }

    // Método intermediário para compatibilidade Livewire
    public function processUpload()
    {
        Log::info('ProcessUpload chamado');
        return $this->upload();
    }

    public function upload()
    {
        Log::info('Upload método chamado');
        $this->validate();

        $this->isProcessing = true;
        $this->uploadProgress = 10;
        $this->errorMessage = '';
        $this->successMessage = '';

        $filePath = $this->pdf_file->getRealPath();
        $extension = $this->pdf_file->extension();

        Log::info('Arquivo real em: ' . $filePath . ' com extensão: ' . $extension);

        $this->productsUpload = [];
        $this->uploadProgress = 30;

        try {
            if ($extension === 'pdf') {
                Log::info('Processando PDF...');
                $this->uploadProgress = 40;

                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
                Log::info('Texto extraído do PDF (primeiros 500 chars): ' . substr($text, 0, 500));

                $filteredText = $this->filterText($text);
                Log::info('Texto filtrado para IA e Regex: ' . substr($filteredText, 0, 500));

                if (empty(trim($filteredText))) {
                    Log::warning('Texto filtrado está vazio. Não há produtos para extrair.');
                    $this->errorMessage = 'Não foi possível encontrar a lista de produtos no PDF.';
                    $this->isProcessing = false;
                    $this->uploadProgress = 0;
                    return;
                }

                $geminiService = new \App\Services\GeminiPdfExtractorService();

                if ($geminiService->isConfigured()) {
                    Log::info('🤖 Tentando extração com IA (Gemini)...');

                    try {
                        $geminiProducts = $geminiService->extractProductsFromPdf($filteredText);

                        if (!empty($geminiProducts)) {
                            Log::info("✓ IA extraiu " . count($geminiProducts) . " produtos com sucesso!");
                            $this->productsUpload = $geminiProducts;
                            $this->uploadProgress = 70;

                            foreach ($this->productsUpload as $key => $product) {
                                if (($product['stock_quantity'] ?? 0) > 0) {
                                    $this->productsUpload[$key]['price'] = ($product['price'] ?? 0) / $product['stock_quantity'];
                                    $this->productsUpload[$key]['price_sale'] = ($product['price_sale'] ?? 0) / $product['stock_quantity'];
                                }
                                $this->autoFillProductData($key);
                            }

                            $this->uploadProgress = 100;
                            $this->showProductsTable = true;
                            $this->successMessage = "🤖 IA extraiu " . count($geminiProducts) . " produtos automaticamente!";
                            Log::info('Definindo showProductsTable como true (IA)');
                            return;
                        }
                        Log::warning('IA não retornou produtos, usando método tradicional...');
                    } catch (\Exception $e) {
                        Log::warning('Erro ao usar IA, fallback para regex: ' . $e->getMessage());
                    }
                } else {
                    Log::info('Gemini não configurado, usando extração tradicional');
                }

                Log::info('📄 Usando extração tradicional (regex)...');
                $this->uploadProgress = 60;

                $productsText = $this->separateProducts($filteredText);

                $this->uploadProgress = 80;
                Log::info('Produtos separados: ' . json_encode($productsText));

                $this->productsUpload = $this->extractProductsFromText($productsText);

                Log::info('Produtos extraídos: ' . count($this->productsUpload));
            } elseif ($extension === 'csv') {
                $this->uploadProgress = 50;
                $this->productsUpload = $this->extractProductsFromCsv($filePath);
                $this->uploadProgress = 80;
            }

            if (empty($this->productsUpload)) {
                Log::info('Nenhum produto encontrado');
                $this->errorMessage = 'Nenhum produto encontrado no arquivo. Verifique o formato e tente novamente.';
                $this->isProcessing = false;
                $this->uploadProgress = 0;
                return;
            }

            Log::info('Produtos encontrados: ' . count($this->productsUpload));

            foreach ($this->productsUpload as $key => $product) {
                if (($product['stock_quantity'] ?? 0) > 0) {
                    $this->productsUpload[$key]['price'] = ($product['price'] ?? 0) / $product['stock_quantity'];
                    $this->productsUpload[$key]['price_sale'] = ($product['price_sale'] ?? 0) / $product['stock_quantity'];
                }
                $this->autoFillProductData($key);
            }

            $this->uploadProgress = 100;
            $this->showProductsTable = true;
            $this->successMessage = 'Produtos extraídos com sucesso! Revise os dados abaixo antes de salvar.';

            Log::info('Definindo showProductsTable como true');
        } catch (\Exception $e) {
            Log::error('Erro ao processar arquivo: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            $this->errorMessage = 'Erro ao processar o arquivo: ' . $e->getMessage();
            $this->isProcessing = false;
            $this->uploadProgress = 0;

            $this->reset(['productsUpload', 'showProductsTable']);
        } finally {
            $this->isProcessing = false;
        }
    }

    public function store()
    {
        if (empty($this->productsUpload)) {
            session()->flash('error', 'Nenhum produto para salvar.');
            return;
        }

        // Salvar arquivo PDF para visualização futura
        $filePath = null;
        if ($this->pdf_file) {
            $filePath = $this->pdf_file->store('uploads/pdfs', 'public');
        }

        // Criar registro de histórico
        $uploadHistory = ProductUploadHistory::create([
            'user_id' => Auth::id(),
            'filename' => $this->pdf_file ? $this->pdf_file->getClientOriginalName() : 'upload_manual',
            'file_path' => $filePath,
            'file_type' => $this->pdf_file ? $this->pdf_file->extension() : 'manual',
            'total_products' => count($this->productsUpload),
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $this->currentUploadId = $uploadHistory->id;

        $productsCreated = 0;
        $productsUpdated = 0;
        $productsSkipped = 0;

        // Processar cada produto
        foreach ($this->productsUpload as $index => $product) {
            $imageName = 'product-placeholder.png'; // Imagem padrão
            $shouldUpdateImage = false;

            // Verificar se há imagem base64 (prioritário) ou temp_image
            $imageData = $product['image_base64'] ?? $product['temp_image'] ?? null;

            if (!empty($imageData)) {
                Log::info("Processando imagem para produto {$index}");

                $type = 'png'; // padrão caso não consiga detectar
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $type = strtolower($matches[1]);
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                } else {
                    // Se não tem o header data:image, assume que já é base64 puro
                    Log::info("Imagem sem header data:image, assumindo base64 puro");
                }

                $decodedImageData = base64_decode($imageData);
                if ($decodedImageData !== false) {
                    $imageName = 'product_' . uniqid() . '.' . $type;
                    $saved = Storage::disk('public')->put('products/' . $imageName, $decodedImageData);

                    if ($saved) {
                        Log::info("Imagem salva com sucesso: {$imageName}");
                        $shouldUpdateImage = true;
                    } else {
                        Log::error("Falha ao salvar imagem para produto {$index}");
                        $imageName = 'product-placeholder.png'; // Fallback
                    }
                } else {
                    Log::error("Falha ao decodificar base64 para produto {$index}");
                    $imageName = 'product-placeholder.png'; // Fallback
                }
            } else {
                Log::info("Nenhuma imagem personalizada para produto {$index}, usando placeholder");
            }

            try {
                // Buscar produto existente apenas por código e usuário
                $existingProduct = Product::where('product_code', $product['product_code'])
                    ->where('user_id', Auth::id())
                    ->where('price', $product['price'])
                    ->where('price_sale', $product['price_sale'])
                    ->first();

                if ($existingProduct) {
                    // ✅ CENÁRIO A: Produto existe com EXATAMENTE mesmo preço custo E venda
                    // Ação: SOMA ESTOQUE + Mantém imagem se não enviar nova
                    Log::info("Produto {$product['product_code']} já existe, somando estoque");
                    $existingProduct->stock_quantity += $product['stock_quantity'];

                    // Atualizar imagem apenas se foi enviada uma nova
                    if ($shouldUpdateImage) {
                        $existingProduct->image = $imageName;
                        Log::info("Imagem atualizada para produto existente: {$imageName}");
                    } else {
                        Log::info("Mantendo imagem existente: {$existingProduct->image}");
                    }

                    $existingProduct->save();
                    $productsUpdated++;
                } else {
                    // Verificar se existe produto com mesmo código mas preço diferente
                    $similarProduct = Product::where('product_code', $product['product_code'])
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($similarProduct) {
                        // ✅ CENÁRIO B: Produto existe mas com preço DIFERENTE
                        // Ação: CRIA NOVA VARIAÇÃO
                        Log::info("Criando nova variação para {$product['product_code']} com preço diferente");
                        $newProduct = Product::create([
                            'name' => $this->sanitizeName($product['name'] ?? ''),
                            'description' => $product['description'] ?? null,
                            'price' => $product['price'],
                            'price_sale' => $product['price_sale'],
                            'stock_quantity' => $product['stock_quantity'],
                            'product_code' => $product['product_code'],
                            'status' => $product['status'] ?? 'ativo',
                            'tipo' => $product['tipo'] ?? 'simples',
                            'custos_adicionais' => $product['custos_adicionais'] ?? 0,
                            'image' => $imageName,
                            'user_id' => Auth::id(),
                            'category_id' => $similarProduct->category_id, // Usa categoria do produto similar
                        ]);

                        // Aprender categorização
                        $this->learnCategorization($newProduct);
                        $productsCreated++;
                    } else {
                        // ✅ CENÁRIO C: Produto NÃO EXISTE
                        // Ação: CRIA NOVO PRODUTO
                        Log::info("Criando produto novo: {$product['product_code']}");
                        $newProduct = Product::create([
                            'name' => $this->sanitizeName($product['name'] ?? ''),
                            'description' => $product['description'] ?? null,
                            'price' => $product['price'],
                            'price_sale' => $product['price_sale'],
                            'stock_quantity' => $product['stock_quantity'],
                            'product_code' => $product['product_code'],
                            'status' => $product['status'] ?? 'ativo',
                            'tipo' => $product['tipo'] ?? 'simples',
                            'custos_adicionais' => $product['custos_adicionais'] ?? 0,
                            'image' => $imageName,
                            'user_id' => Auth::id(),
                            'category_id' => $product['category_id'] ?? 1,
                        ]);

                        // Aprender categorização para futuros produtos similares
                        $this->learnCategorization($newProduct);
                        $productsCreated++;
                    }
                }
            } catch (\Exception $e) {
                // Log: Erro durante o processamento
                Log::error('Erro ao salvar o produto', [
                    'error' => $e->getMessage(),
                    'product' => $product
                ]);

                session()->flash('error', 'Erro ao salvar os produtos: ' . $e->getMessage());

                // Atualizar histórico como falho
                if ($uploadHistory) {
                    $uploadHistory->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'completed_at' => now(),
                    ]);
                }

                return;
            }
        }

        // Atualizar histórico como completo
        $uploadHistory->update([
            'status' => 'completed',
            'products_created' => $productsCreated,
            'products_updated' => $productsUpdated,
            'products_skipped' => $productsSkipped,
            'completed_at' => now(),
            'summary' => [
                'created' => $productsCreated,
                'updated' => $productsUpdated,
                'skipped' => $productsSkipped,
                'total' => count($this->productsUpload),
            ],
        ]);

        session()->flash('success', "Produtos salvos! Criados: {$productsCreated}, Atualizados: {$productsUpdated}, Pulados: {$productsSkipped}");

        // Recarregar histórico
        $this->loadUploadHistory();

        // Reset do formulário
        $this->reset();

        // Emite evento para atualizar a lista
        $this->dispatch('products-uploaded');

        // Redireciona para a lista
        return redirect()->route('products.index');
    }

    public function updateProduct($index, $field, $value)
    {
        // Se for campo nome, sanitiza imediatamente para manter consistência
        if ($field === 'name') {
            $this->productsUpload[$index][$field] = $this->sanitizeName($value);
        } else {
            $this->productsUpload[$index][$field] = $value;
        }
    }

    public function removeProduct($index)
    {
        // Salvar produto removido para possível undo
        if (isset($this->productsUpload[$index])) {
            $productName = $this->productsUpload[$index]['name'] ?? 'Produto';

            $this->removedProducts[] = [
                'index' => $index,
                'product' => $this->productsUpload[$index],
                'timestamp' => now()
            ];

            unset($this->productsUpload[$index]);
            $this->productsUpload = array_values($this->productsUpload); // Reindexar array

            // Disparar evento para toast notification
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => "'{$productName}' removido! Use 'Desfazer' para recuperar.",
                'duration' => 5000
            ]);
        }
    }    public function undoRemove()
    {
        if (empty($this->removedProducts)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Nenhum produto para desfazer.',
                'duration' => 3000
            ]);
            return;
        }

        $lastRemoved = array_pop($this->removedProducts);
        $this->productsUpload[] = $lastRemoved['product'];

        $productName = $lastRemoved['product']['name'] ?? 'Produto';
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => "'{$productName}' recuperado com sucesso!",
            'duration' => 3000
        ]);
    }

    public function checkDuplicates()
    {
        $this->duplicates = [];

        foreach ($this->productsUpload as $index => $product) {
            $productCode = $product['product_code'] ?? null;

            if (!$productCode) {
                continue;
            }

            // Buscar produto existente no sistema
            $existing = Product::where('product_code', $productCode)
                ->where('user_id', Auth::id())
                ->where('price', $product['price'])
                ->where('price_sale', $product['price_sale'])
                ->first();

            if ($existing) {
                $this->duplicates[$index] = [
                    'product' => $product,
                    'existing' => $existing,
                    'action' => 'sum_stock', // Padrão: somar estoque
                ];
            }
        }

        return count($this->duplicates) > 0;
    }

    public function setDuplicateAction($index, $action)
    {
        if (isset($this->duplicates[$index])) {
            $this->duplicates[$index]['action'] = $action;
        }
    }

    public function updateProductImage($index, $base64Image)
    {
        if (isset($this->productsUpload[$index])) {
            // Salvar para exibição no frontend
            $this->productsUpload[$index]['temp_image'] = $base64Image;
            // Salvar para processamento no backend (store)
            $this->productsUpload[$index]['image_base64'] = $base64Image;

            Log::info("Imagem atualizada para produto {$index}");
        }
    }

    public function bulkUpdateCategory($categoryId)
    {
        foreach ($this->productsUpload as $index => $product) {
            $this->productsUpload[$index]['category_id'] = $categoryId;
        }
        $this->successMessage = "Categoria atualizada para todos os produtos!";
    }

    public function bulkUpdateStatus($status)
    {
        foreach ($this->productsUpload as $index => $product) {
            $this->productsUpload[$index]['status'] = $status;
        }
        $this->successMessage = "Status atualizado para todos os produtos!";
    }

    private function filterText($text)
    {
        // A primeira ocorrência de 'OPERAÇÃO' marca o início do cabeçalho da tabela de produtos.
        $startPos = strpos($text, 'OPERAÇÃO');
        if ($startPos === false) {
            Log::warning('Marcador inicial "OPERAÇÃO" não encontrado no texto do PDF.');
            return ''; // Retorna vazio se não encontrar o início
        }

        // O marcador final é a linha que começa com "TOTAL"
        // Usamos regex com modo multiline (m) para encontrar a linha que começa com TOTAL
        $endPos = false;
        if (preg_match('/^\s*TOTAL/m', $text, $matches, PREG_OFFSET_CAPTURE, $startPos)) {
            $endPos = $matches[0][1];
            Log::info('Marcador final "TOTAL" encontrado na posição: ' . $endPos);
        }

        // Fallbacks, caso "TOTAL" não seja encontrado
        if ($endPos === false) {
            $endPos = strpos($text, 'PRODUTOS NÃO DISPONÍVEIS', $startPos);
            if($endPos) Log::info('Usando fallback "PRODUTOS NÃO DISPONÍVEIS"');
        }
        if ($endPos === false) {
            $endPos = strpos($text, 'AJUSTES', $startPos);
            if($endPos) Log::info('Usando fallback "AJUSTES"');
        }
        if ($endPos === false) {
            $endPos = strpos($text, 'PLANO DE PAGAMENTO', $startPos);
            if($endPos) Log::info('Usando fallback "PLANO DE PAGAMENTO"');
        }

        // Pega o texto a partir do fim do marcador 'OPERAÇÃO'
        $textStart = $startPos + strlen('OPERAÇÃO');

        $filteredText = '';
        if ($endPos !== false) {
            $filteredText = substr($text, $textStart, $endPos - $textStart);
        } else {
            // Se NENHUM marcador final for encontrado, pega tudo do início até o fim do texto.
            Log::warning('Nenhum marcador final foi encontrado. Usando o resto do texto a partir de "OPERAÇÃO".');
            $filteredText = substr($text, $textStart);
        }

        Log::info('Texto filtrado (com quebras de linha) - caracteres: ' . strlen($filteredText));
        return trim($filteredText);
    }

    private function separateProducts($text)
    {
        $lines = explode("\n", $text);
        $allProducts = [];
        $currentProduct = null;
        $productRegex = '/^(\d{2,5}\.\d{3})\s+(\d+)\s+(.*)/';
        $valuesRegex = '/([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+(Venda|Brinde)$/';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Tenta casar o início de um produto
            $isNewProductLine = preg_match($productRegex, $line, $matches);

            if ($isNewProductLine) {
                // Se um produto estava sendo montado, salva ele (caso de linha órfã)
                if ($currentProduct) {
                    $allProducts[] = $currentProduct;
                }

                $potentialName = trim($matches[3]);
                $currentProduct = [
                    'product_code' => $matches[1],
                    'stock_quantity' => (int)$matches[2],
                    'name' => $potentialName,
                    'values' => [],
                    'operation' => ''
                ];

                // VERIFICA SE A MESMA LINHA JÁ CONTÉM OS VALORES (PRODUTO DE LINHA ÚNICA)
                if (preg_match($valuesRegex, $potentialName, $valueMatches)) {
                    // Remove a parte dos valores do nome do produto
                    $currentProduct['name'] = trim(preg_replace($valuesRegex, '', $potentialName));
                    $currentProduct['values'] = array_slice($valueMatches, 1, 5);
                    $currentProduct['operation'] = end($valueMatches);

                    $allProducts[] = $currentProduct;
                    $currentProduct = null; // Reseta pois o produto está completo
                }

            } elseif ($currentProduct) {
                // SE É CONTINUAÇÃO, VERIFICA SE É A LINHA DE VALORES
                if (preg_match($valuesRegex, $line, $valueMatches)) {
                    $currentProduct['values'] = array_slice($valueMatches, 1, 5);
                    $currentProduct['operation'] = end($valueMatches);

                    $allProducts[] = $currentProduct;
                    $currentProduct = null; // Reseta
                } else {
                    // SE NÃO, É CONTINUAÇÃO DO NOME
                    $currentProduct['name'] .= ' ' . $line;
                }
            }
        }

        // Adiciona o último produto se ele existir (caso o arquivo termine)
        if ($currentProduct) {
            $allProducts[] = $currentProduct;
        }

        Log::info('Regex finalizou ' . count($allProducts) . ' produtos completos.');

        $formattedProducts = [];
        foreach ($allProducts as $p) {
            if (!empty($p['operation']) && (count($p['values']) === 5 || $p['operation'] === 'Brinde')) {
                 // Para brindes, os valores podem não estar presentes, mas ainda assim são produtos válidos.
                $isBrindeSemValor = ($p['operation'] === 'Brinde' && empty($p['values']));

                $formattedProducts[] = [
                    'product_code' => $p['product_code'],
                    'name' => preg_replace('/\s+/', ' ', trim($p['name'])),
                    'stock_quantity' => $p['stock_quantity'],
                    'price_resell' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][0]),
                    'price_to_pay' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][1]),
                    'price_sale' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][2]),
                    'price' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][3]),
                    'profit' => $this->formatPrice($isBrindeSemValor ? '0' : $p['values'][4]),
                    'operation' => $p['operation'],
                    'category_id' => 1,
                    'user_id' => Auth::id(),
                    'image' => 'product-placeholder.png',
                    'status' => 'ativo',
                ];
            }
        }

        return ['products' => $formattedProducts];
    }

    private function extractProductsFromText($productsText)
    {
        $products = [];
        if (isset($productsText['products'])) {
            foreach ($productsText['products'] as $item) {
                $products[] = [
                    'product_code' => $item['product_code'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'price_sale' => $item['price_sale'],
                    'stock_quantity' => $item['stock_quantity'],
                    'description' => '',
                    'status' => 'ativo',
                    'category_id' => 1,
                ];
            }
        }
        return $products;
    }

    private function formatPrice($price)
    {
        $price = str_replace([' ', ','], ['', '.'], trim($price));
        return (float) $price;
    }

    private function extractProductsFromCsv($path)
    {
        $productsUpload = [];
        $csv = array_map('str_getcsv', file($path));

        foreach ($csv as $row) {
            $productsUpload[] = [
                'name' => $row[0],
                'price' => $this->formatPrice($row[1]),
                'price_sale' => $this->formatPrice($row[2]),
                'stock_quantity' => (int) $row[3],
                'product_code' => $row[4] ?? 'PROD-' . strtoupper(uniqid()),
                'description' => $row[5] ?? '',
                'status' => 'ativo',
                'category_id' => 1,
            ];
        }

        return $productsUpload;
    }

    /**
     * Busca produto existente por código e preenche dados automaticamente
     */
    private function autoFillProductData($index)
    {
        $productCode = $this->productsUpload[$index]['product_code'] ?? null;

        if (!$productCode) {
            return;
        }

        // Buscar produto existente no sistema
        $existingProduct = Product::where('product_code', $productCode)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        if ($existingProduct) {
            Log::info("Produto encontrado por código {$productCode}, preenchendo dados automaticamente");

            // Preencher imagem se houver
            if ($existingProduct->image && $existingProduct->image !== 'product-placeholder.png') {
                $imagePath = storage_path('app/public/products/' . $existingProduct->image);
                if (file_exists($imagePath)) {
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $mimeType = mime_content_type($imagePath);
                    $this->productsUpload[$index]['temp_image'] = "data:{$mimeType};base64,{$imageData}";
                    $this->productsUpload[$index]['image_base64'] = "data:{$mimeType};base64,{$imageData}";
                }
            }

            // Preencher categoria
            if ($existingProduct->category_id) {
                $this->productsUpload[$index]['category_id'] = $existingProduct->category_id;
            }

            Log::info("Dados preenchidos: imagem e categoria do produto {$productCode}");
        } else {
            // Se não encontrou por código, tentar sugerir categoria por nome
            $this->suggestCategoryByName($index);
        }
    }

    /**
     * Sugere categoria baseada em aprendizado anterior e análise inteligente
     */
    private function suggestCategoryByName($index)
    {
        $productName = $this->productsUpload[$index]['name'] ?? '';
        $productCode = $this->productsUpload[$index]['product_code'] ?? '';

        if (!$productName) {
            return;
        }

        Log::info("Sugerindo categoria para: {$productName}");

        // 🧠 ESTRATÉGIA 1: Buscar em ProductCategoryLearning (histórico de aprendizado)
        $suggestedCategoryId = ProductCategoryLearning::suggestCategory(Auth::id(), $productName);

        if ($suggestedCategoryId) {
            $this->productsUpload[$index]['category_id'] = $suggestedCategoryId;
            Log::info("✓ Categoria sugerida por aprendizado para '{$productName}': {$suggestedCategoryId}");
            return;
        }

        // 🔍 ESTRATÉGIA 2: Buscar produtos similares por código (prioridade)
        if ($productCode) {
            $similarByCode = Product::where('user_id', Auth::id())
                ->where('product_code', $productCode)
                ->whereNotNull('category_id')
                ->first();

            if ($similarByCode) {
                $this->productsUpload[$index]['category_id'] = $similarByCode->category_id;
                Log::info("✓ Categoria sugerida por código '{$productCode}': {$similarByCode->category_id}");
                return;
            }
        }

        // 📦 ESTRATÉGIA 3: Buscar produtos similares por nome
        $words = explode(' ', $productName);
        $firstWords = implode(' ', array_slice($words, 0, min(3, count($words))));

        $similarProducts = Product::where('user_id', Auth::id())
            ->where('name', 'LIKE', '%' . $firstWords . '%')
            ->whereNotNull('category_id')
            ->get();

        if ($similarProducts->isNotEmpty()) {
            // Pegar a categoria mais comum
            $categoryCount = [];
            foreach ($similarProducts as $product) {
                $categoryId = $product->category_id;
                $categoryCount[$categoryId] = ($categoryCount[$categoryId] ?? 0) + 1;
            }
            arsort($categoryCount);
            $suggestedCategoryId = array_key_first($categoryCount);

            if ($suggestedCategoryId) {
                $this->productsUpload[$index]['category_id'] = $suggestedCategoryId;
                Log::info("✓ Categoria sugerida por nome similar '{$firstWords}': {$suggestedCategoryId}");
                return;
            }
        }

        // 🏷️ ESTRATÉGIA 4: Buscar em categorias tipo 'produto' por palavras-chave no nome
        $categories = Category::where('user_id', Auth::id())
            ->orWhereNull('user_id')
            ->where('tipo', 'produto')
            ->get();

        if ($categories->isNotEmpty()) {
            $normalizedProductName = strtolower($productName);
            $bestMatch = null;
            $bestScore = 0;

            foreach ($categories as $category) {
                $categoryName = strtolower($category->name);
                $categoryWords = explode(' ', $categoryName);

                // Calcular score de similaridade
                $score = 0;
                foreach ($categoryWords as $word) {
                    if (strlen($word) > 3 && strpos($normalizedProductName, $word) !== false) {
                        $score += strlen($word); // Palavras maiores têm mais peso
                    }
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $category->id_category;
                }
            }

            if ($bestMatch && $bestScore >= 4) { // Score mínimo de 4
                $this->productsUpload[$index]['category_id'] = $bestMatch;
                Log::info("✓ Categoria sugerida por análise de palavras-chave: {$bestMatch} (score: {$bestScore})");
                return;
            }
        }

        Log::info("⚠ Nenhuma categoria sugerida para '{$productName}', usando padrão");
    }

    /**
     * Aprende a categorização para futuros produtos
     */
    private function learnCategorization($product)
    {
        try {
            $normalizedName = ProductCategoryLearning::normalizeProductName($product->name);

            // Buscar se já existe aprendizado
            $learning = ProductCategoryLearning::where('user_id', Auth::id())
                ->where('product_name_pattern', $normalizedName)
                ->where('category_id', $product->category_id)
                ->first();

            if ($learning) {
                // Se existe, incrementa confiança
                $learning->incrementConfidence();
                Log::info("Aprendizado atualizado para '{$product->name}' (confidence: {$learning->confidence})");
            } else {
                // Se não existe, cria novo
                ProductCategoryLearning::create([
                    'user_id' => Auth::id(),
                    'product_name_pattern' => $normalizedName,
                    'product_code' => $product->product_code,
                    'category_id' => $product->category_id,
                    'confidence' => 1,
                    'last_used_at' => now(),
                ]);
                Log::info("Aprendizado criado para '{$product->name}' -> categoria {$product->category_id}");
            }
        } catch (\Exception $e) {
            Log::warning("Erro ao salvar aprendizado: " . $e->getMessage());
        }
    }

    /**
     * Sanitiza nomes de produtos para armazenamento: remove / ( ) - e normaliza espaços.
     */
    private function sanitizeName($name)
    {
        $clean = trim((string)$name);
        // Remove caracteres indesejados
        $clean = preg_replace('/[\/(\)\-]/', '', $clean);
        // Colapsar múltiplos espaços
        $clean = preg_replace('/\s+/', ' ', $clean);
        return $clean;
    }

    public function render()
    {
        $categories = \App\Models\Category::where('user_id', Auth::id())
            ->orWhereNull('user_id')
            ->orderBy('name')
            ->get();

        return view('livewire.products.upload-products', [
            'categories' => $categories
        ]);
    }
}
