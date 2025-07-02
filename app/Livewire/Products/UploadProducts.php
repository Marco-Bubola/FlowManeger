<?php

namespace App\Livewire\Products;

use App\Models\Product;
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

    public function rules()
    {
        return [
            'pdf_file' => 'required|file|mimes:pdf,csv|max:2048',
        ];
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

        // Usa o caminho real do arquivo temporário do Livewire
        $filePath = $this->pdf_file->getRealPath();
        $extension = $this->pdf_file->extension();
        
        Log::info('Arquivo real em: ' . $filePath . ' com extensão: ' . $extension);

        $this->productsUpload = [];

        try {
            if ($extension === 'pdf') {
                Log::info('Processando PDF...');
                // Processa o PDF usando o caminho real
                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
                
                Log::info('Texto extraído do PDF (primeiros 500 chars): ' . substr($text, 0, 500));

                // Filtra o texto para pegar apenas os dados entre "OPERAÇÃO" e "PEDIDO Nº"
                $filteredText = $this->filterText($text);
                
                Log::info('Texto filtrado: ' . substr($filteredText, 0, 500));

                // Limpa o texto de tabulações e múltiplos espaços
                $cleanText = preg_replace('/\s+/', ' ', $filteredText);

                // Organize o texto limpo em produtos individuais
                $productsText = $this->separateProducts($cleanText);
                
                Log::info('Produtos separados: ' . json_encode($productsText));

                // Extraí os produtos do texto separado
                $this->productsUpload = $this->extractProductsFromText($productsText);
                
                Log::info('Produtos extraídos: ' . count($this->productsUpload));
            } elseif ($extension === 'csv') {
                // Processa o CSV usando o caminho real
                $this->productsUpload = $this->extractProductsFromCsv($filePath);
            }

            // Verifica se algum produto foi extraído
            if (empty($this->productsUpload)) {
                Log::info('Nenhum produto encontrado');
                session()->flash('error', 'Nenhum produto encontrado no arquivo.');
                return;
            }

            Log::info('Produtos encontrados: ' . count($this->productsUpload));

            // Agora, vamos dividir o price e price_sale pela quantidade de cada produto
            foreach ($this->productsUpload as $key => $product) {
                $this->productsUpload[$key]['price'] = $product['price'] / $product['stock_quantity'];
                $this->productsUpload[$key]['price_sale'] = $product['price_sale'] / $product['stock_quantity'];
            }

            $this->showProductsTable = true;
            Log::info('Definindo showProductsTable como true');
            Log::info('Valor atual de showProductsTable: ' . ($this->showProductsTable ? 'true' : 'false'));
            session()->flash('success', 'Produtos extraídos com sucesso! Revise os dados abaixo antes de salvar.');

        } catch (\Exception $e) {
            Log::error('Erro ao processar arquivo: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            session()->flash('error', 'Erro ao processar o arquivo: ' . $e->getMessage());
        }
    }

    public function store()
    {
        if (empty($this->productsUpload)) {
            session()->flash('error', 'Nenhum produto para salvar.');
            return;
        }

        // Processar cada produto
        foreach ($this->productsUpload as $index => $product) {
            $imageName = 'product-placeholder.png'; // Imagem padrão

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
                $existingProduct = Product::where('product_code', $product['product_code'])
                    ->where('price', $product['price'])
                    ->where('price_sale', $product['price_sale'])
                    ->where('user_id', Auth::id())
                    ->first();

                if ($existingProduct) {
                    // Se o preço de venda for o mesmo, apenas atualiza a quantidade
                    if ($existingProduct->price_sale == $product['price_sale']) {
                        $existingProduct->stock_quantity += $product['stock_quantity']; // Aumenta a quantidade
                        $existingProduct->save(); // Salva as alterações
                    } else {
                        // Caso o preço de venda seja diferente, cria um novo produto com o mesmo código
                        Product::create([
                            'name' => $product['name'],
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
                    }
                } else {
                    // Se o produto não existe, cria um novo produto
                    Product::create([
                        'name' => $product['name'],
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
                }
            } catch (\Exception $e) {
                // Log: Erro durante o processamento
                Log::error('Erro ao salvar o produto', [
                    'error' => $e->getMessage(),
                    'product' => $product
                ]);

                session()->flash('error', 'Erro ao salvar os produtos: ' . $e->getMessage());
                return;
            }
        }

        session()->flash('success', 'Produtos salvos com sucesso!');
        
        // Reset do formulário
        $this->reset();

        // Emite evento para atualizar a lista
        $this->dispatch('products-uploaded');

        // Redireciona para a lista
        return redirect()->route('products.index');
    }

    public function updateProduct($index, $field, $value)
    {
        $this->productsUpload[$index][$field] = $value;
    }

    public function removeProduct($index)
    {
        unset($this->productsUpload[$index]);
        $this->productsUpload = array_values($this->productsUpload); // Reindexar array
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

    private function filterText($text)
    {
        $startPos = strpos($text, 'OPERAÇÃO');
        $endPos = strpos($text, 'PEDIDO Nº');

        if ($startPos !== false && $endPos !== false) {
            $filteredText = substr($text, $startPos + strlen('OPERAÇÃO'), $endPos - ($startPos + strlen('OPERAÇÃO')));
            $filteredText = preg_replace('/\s+/', ' ', $filteredText);
            $filteredText = preg_replace('/\bOPERAÇÃO\b/', '', $filteredText);
            return $filteredText;
        }

        return '';
    }

    private function separateProducts($text)
    {
        if (!is_string($text)) {
            // Se for um array, transforma-o em uma string
            $text = implode(' ', $text); // converte array para string com separação por espaço
        }
        // A regex para separação dos produtos, com modificação para aceitar parênteses nos nomes dos produtos
        $pattern = '/(\d{1,5}\.\d{3})\s+           # Código do produto (CÓD.)
                (\d+)\s+                       # Quantidade (QTD.)
                ([A-Za-z0-9À-ÿ\s&\-\/,()+=]+(?:\s*\+\s*\d+\s*[A-Za-z0-9À-ÿ\s&\-\/,()]*)*)\s+   # Nome do produto (incluindo + números)
                ([\d,\.]+)\s+                  # Preço Tabela (R$ TABELA)
                ([\d,\.]+)\s+                  # Preço Praticado (R$ PRATICADO)
                ([\d,\.]+)\s+                  # Preço Revenda (R$ REVENDA)
                ([\d,\.]+)\s+                  # Preço a Pagar (R$ A PAGAR)
                ([\d,\.]+)\s+                  # Lucro (R$ LUCRO)
                (Venda)?                       # Operação (opcional)
            /x';

        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        $productsText = [];

        if ($matches) {
            foreach ($matches[0] as $key => $match) {
                $productsText[] = [
                    'product_code' => (string) $matches[1][$key][0],
                    'stock_quantity' => (string) $matches[2][$key][0],
                    'name' => trim((string) $matches[3][$key][0]),
                    'price_resell' => $this->formatPrice((string) $matches[4][$key][0]),
                    'price_to_pay' => $this->formatPrice((string) $matches[5][$key][0]),
                    'price_sale' => $this->formatPrice((string) $matches[6][$key][0]),
                    'price' => $this->formatPrice((string) $matches[7][$key][0]),
                    'profit' => $this->formatPrice((string) $matches[8][$key][0]),
                    'category_id' => 1,
                    'user_id' => Auth::id(),
                    'image' => 'product-placeholder.png',
                    'status' => 'ativo',
                ];
            }
        }

        return ['products' => $productsText];
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

    public function render()
    {
        return view('livewire.products.upload-products');
    }
}
