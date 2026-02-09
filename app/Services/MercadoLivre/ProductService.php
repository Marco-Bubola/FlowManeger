<?php

namespace App\Services\MercadoLivre;

use App\Models\Product;
use App\Models\MercadoLivreProduct;
use App\Services\MercadoLivre\AuthService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * ProductService
 * 
 * Serviço responsável por gerenciar produtos no Mercado Livre
 * - Publicar produtos
 * - Atualizar informações (preço, estoque, título, descrição)
 * - Pausar/ativar anúncios
 * - Sincronizar produtos
 * - Gerenciar categorias e atributos
 */
class ProductService extends MercadoLivreService
{
    /**
     * Listar todas as categorias do site
     * 
     * @param string $siteId (MLB = Brasil)
     * @param int|null $userId ID do usuário para autenticação
     * @return array
     */
    public function getCategories(string $siteId = 'MLB', ?int $userId = null): array
    {
        try {
            // Buscar token do usuário se fornecido
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            $response = $this->makeRequest('GET', "/sites/{$siteId}/categories", [], $accessToken, $userId);
            
            return [
                'success' => true,
                'categories' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar categorias ML', [
                'error' => $e->getMessage(),
                'site_id' => $siteId,
            ]);
            
            // Fallback: retornar categorias comuns do Brasil
            return $this->getCommonCategories();
        }
    }
    
    /**
     * Retorna categorias mais comuns do Mercado Livre Brasil
     */
    private function getCommonCategories(): array
    {
        return [
            'success' => true,
            'categories' => [
                ['id' => 'MLB1246', 'name' => 'Esportes e Fitness'],
                ['id' => 'MLB1051', 'name' => 'Celulares e Telefones'],
                ['id' => 'MLB1648', 'name' => 'Informática'],
                ['id' => 'MLB1000', 'name' => 'Eletrônicos, Áudio e Vídeo'],
                ['id' => 'MLB1574', 'name' => 'Casa, Móveis e Decoração'],
                ['id' => 'MLB1430', 'name' => 'Moda'],
                ['id' => 'MLB1276', 'name' => 'Beleza e Cuidado Pessoal'],
                ['id' => 'MLB1132', 'name' => 'Brinquedos e Hobbies'],
                ['id' => 'MLB1196', 'name' => 'Música, Filmes e Seriados'],
                ['id' => 'MLB1499', 'name' => 'Indústria e Comércio'],
                ['id' => 'MLB1367', 'name' => 'Livros, Revistas e Comics'],
                ['id' => 'MLB1168', 'name' => 'Veículos'],
                ['id' => 'MLB1403', 'name' => 'Alimentos e Bebidas'],
                ['id' => 'MLB1071', 'name' => 'Animais'],
                ['id' => 'MLB1953', 'name' => 'Ferramentas'],
                ['id' => 'MLB1039', 'name' => 'Câmeras e Acessórios'],
                ['id' => 'MLB1384', 'name' => 'Bebês'],
                ['id' => 'MLB263532', 'name' => 'Saúde'],
                ['id' => 'MLB1144', 'name' => 'Consoles e Videogames'],
                ['id' => 'MLB12404', 'name' => 'Agro'],
            ]
        ];
    }

    /**
     * Obter detalhes de uma categoria
     * 
     * @param string $categoryId
     * @return array
     */
    public function getCategoryDetails(string $categoryId): array
    {
        try {
            $response = $this->makeRequest('GET', "/categories/{$categoryId}");
            
            return [
                'success' => true,
                'category' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes da categoria ML', [
                'error' => $e->getMessage(),
                'category_id' => $categoryId,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Buscar categorias recomendadas baseado no título usando Domain Discovery
     * 
     * @param string $title
     * @param string $siteId
     * @return array
     */
    public function predictCategory(string $title, string $siteId = 'MLB', ?int $userId = null): array
    {
        try {
            // Buscar token do usuário
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }

            // 1. Tentar Domain Discovery API (retorna leaf categories diretamente)
            try {
                $url = "/sites/{$siteId}/domain_discovery/search?q=" . urlencode($title);
                $response = $this->makeRequest('GET', $url, [], $accessToken, $userId);
                
                if (is_array($response) && !empty($response)) {
                    $uniqueResults = [];
                    $seen = [];
                    foreach ($response as $domain) {
                        $catId = $domain['category_id'] ?? null;
                        $catName = $domain['category_name'] ?? null;
                        if ($catId && $catName && !isset($seen[$catId])) {
                            $seen[$catId] = true;
                            $uniqueResults[] = [
                                'id' => $catId,
                                'name' => $catName,
                            ];
                            if (count($uniqueResults) >= 10) break;
                        }
                    }
                    
                    if (!empty($uniqueResults)) {
                        return [
                            'success' => true,
                            'predictions' => $uniqueResults,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Domain Discovery falhou, usando fallback', ['error' => $e->getMessage()]);
            }

            // 2. Fallback: buscar por keywords na API de search
            $keywords = $this->extractKeywords($title);
            $allResults = [];
            
            foreach ($keywords as $keyword) {
                $result = $this->searchCategories($keyword, $siteId, $userId);
                if ($result['success'] && !empty($result['categories'])) {
                    $allResults = array_merge($allResults, $result['categories']);
                }
            }
            
            // Remove duplicatas e limita a 10
            $uniqueResults = [];
            $seen = [];
            foreach ($allResults as $cat) {
                if (!isset($seen[$cat['id']])) {
                    $seen[$cat['id']] = true;
                    $uniqueResults[] = [
                        'id' => $cat['id'],
                        'name' => $cat['name']
                    ];
                    if (count($uniqueResults) >= 10) break;
                }
            }
            
            if (empty($uniqueResults)) {
                return $this->getCommonCategories();
            }
            
            return [
                'success' => true,
                'predictions' => $uniqueResults,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao prever categoria ML', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);
            
            return $this->getCommonCategories();
        }
    }
    
    /**
     * Encontra automaticamente uma categoria leaf (folha) para publicação
     * Usa domain_discovery e/ou navegação na árvore de categorias
     */
    private function findLeafCategory(string $productName, string $parentCategoryId, ?string $accessToken = null, ?int $userId = null): ?string
    {
        // 1. Tentar domain_discovery com nome completo e também keywords isoladas
        $searchQueries = [$productName];
        
        // Extrair keywords relevantes para tentar individualmente
        $keywords = $this->extractKeywords($productName);
        foreach ($keywords as $kw) {
            $searchQueries[] = $kw;
        }
        
        foreach ($searchQueries as $query) {
            try {
                $url = '/sites/MLB/domain_discovery/search?q=' . urlencode($query);
                $response = $this->makeRequest('GET', $url, [], $accessToken, $userId);
                
                if (is_array($response) && !empty($response)) {
                    // Verificar se alguma está dentro da árvore da categoria pai
                    foreach ($response as $domain) {
                        $catId = $domain['category_id'] ?? null;
                        if ($catId) {
                            $catPath = $this->getCategoryPath($catId, $accessToken, $userId);
                            foreach ($catPath as $pathItem) {
                                if ($pathItem['id'] === $parentCategoryId) {
                                    Log::info('Leaf category encontrada via domain_discovery', [
                                        'leaf' => $catId,
                                        'name' => $domain['category_name'] ?? '',
                                        'parent' => $parentCategoryId,
                                        'query' => $query,
                                    ]);
                                    return $catId;
                                }
                            }
                        }
                    }
                    
                    // Se nenhum é descendente mas temos resultado, usar o primeiro (mais relevante)
                    if ($query === $productName) {
                        // Só usar fallback com query completa, não com keywords isoladas
                        $firstResult = $response[0]['category_id'] ?? null;
                        if ($firstResult) {
                            Log::info('Usando primeira leaf category do domain_discovery (sem match de parent)', [
                                'leaf' => $firstResult,
                                'name' => $response[0]['category_name'] ?? '',
                            ]);
                            return $firstResult;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('domain_discovery falhou', ['query' => $query, 'error' => $e->getMessage()]);
            }
        }
        
        // 2. Fallback: Navegar na árvore de categorias filhas recursivamente
        try {
            return $this->findLeafInTree($parentCategoryId, $productName, $accessToken, $userId, 0);
        } catch (\Exception $e) {
            Log::warning('Navegação na árvore de categorias falhou', ['error' => $e->getMessage()]);
        }
        
        return null;
    }
    
    /**
     * Obtém o path_from_root de uma categoria
     */
    private function getCategoryPath(string $categoryId, ?string $accessToken = null, ?int $userId = null): array
    {
        try {
            $catInfo = $this->makeRequest('GET', '/categories/' . $categoryId, [], $accessToken, $userId);
            return $catInfo['path_from_root'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Navega recursivamente a árvore de categorias buscando uma leaf
     */
    private function findLeafInTree(string $categoryId, string $productName, ?string $accessToken, ?int $userId, int $depth): ?string
    {
        if ($depth > 5) return null; // Evitar recursão infinita
        
        try {
            $catInfo = $this->makeRequest('GET', '/categories/' . $categoryId, [], $accessToken, $userId);
            
            // Se permite publicação, é leaf
            if (isset($catInfo['settings']['listing_allowed']) && $catInfo['settings']['listing_allowed'] === true) {
                return $categoryId;
            }
            
            // Buscar nos filhos pelo nome mais relevante
            $children = $catInfo['children_categories'] ?? [];
            if (empty($children)) return null;
            
            $keywords = array_map('strtolower', explode(' ', $productName));
            $bestMatch = null;
            $bestScore = 0; // Começar em 0 - só selecionar se pelo menos 1 keyword matchear
            
            foreach ($children as $child) {
                $childName = strtolower($child['name']);
                $score = 0;
                foreach ($keywords as $kw) {
                    if (strlen($kw) > 3 && str_contains($childName, $kw)) {
                        $score++;
                    }
                }
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $child['id'];
                }
            }
            
            if ($bestMatch) {
                return $this->findLeafInTree($bestMatch, $productName, $accessToken, $userId, $depth + 1);
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao navegar árvore de categorias', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }

    /**
     * Auto-preenche atributos obrigatórios de uma categoria que não foram fornecidos
     * Para atributos do tipo list, seleciona o primeiro valor disponível
     */
    private function autoFillRequiredAttributes(string $categoryId, array &$attributes, Product $product, ?string $accessToken = null, ?int $userId = null): void
    {
        try {
            $catAttrs = $this->makeRequest('GET', '/categories/' . $categoryId . '/attributes', [], $accessToken, $userId);
            
            if (!is_array($catAttrs)) return;
            
            foreach ($catAttrs as $attr) {
                $attrId = $attr['id'] ?? null;
                $tags = $attr['tags'] ?? [];
                
                // Verificar se é obrigatório ou catalog_required ou conditional_required
                $isRequired = false;
                if (is_array($tags)) {
                    if (isset($tags[0])) {
                        $isRequired = in_array('required', $tags) || in_array('catalog_required', $tags) || in_array('conditional_required', $tags);
                    } else {
                        $isRequired = !empty($tags['required']) || !empty($tags['catalog_required']) || !empty($tags['conditional_required']);
                    }
                }
                
                // Pular se não é obrigatório ou já foi preenchido
                if (!$isRequired || isset($attributes[$attrId])) {
                    continue;
                }
                
                // Pular atributos read_only
                if (is_array($tags) && !empty($tags['read_only'])) continue;
                
                $valueType = $attr['value_type'] ?? 'string';
                
                if ($valueType === 'list' && !empty($attr['values'])) {
                    // Para listas, selecionar o primeiro valor
                    $firstValue = $attr['values'][0];
                    $attributes[$attrId] = [
                        'id' => $attrId,
                        'value_id' => $firstValue['id'],
                    ];
                    Log::info('Atributo obrigatório auto-preenchido (list)', [
                        'attr_id' => $attrId,
                        'value' => $firstValue['name'] ?? $firstValue['id'],
                    ]);
                } elseif ($valueType === 'boolean' && !empty($attr['values'])) {
                    // Para boolean, selecionar "Não" por padrão
                    $noValue = null;
                    foreach ($attr['values'] as $v) {
                        if (isset($v['metadata']['value']) && $v['metadata']['value'] === false) {
                            $noValue = $v;
                            break;
                        }
                    }
                    $selectedValue = $noValue ?? $attr['values'][0];
                    $attributes[$attrId] = [
                        'id' => $attrId,
                        'value_id' => $selectedValue['id'],
                    ];
                } elseif ($valueType === 'string') {
                    // Para strings, usar nome do produto como fallback
                    $attributes[$attrId] = [
                        'id' => $attrId,
                        'value_name' => mb_substr($product->name, 0, 255),
                    ];
                    Log::info('Atributo obrigatório auto-preenchido (string)', [
                        'attr_id' => $attrId,
                        'value' => mb_substr($product->name, 0, 50),
                    ]);
                } elseif ($valueType === 'number') {
                    $attributes[$attrId] = [
                        'id' => $attrId,
                        'value_name' => '1',
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Falha ao auto-preencher atributos obrigatórios', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Extrai palavras-chave relevantes do título
     */
    private function extractKeywords(string $title): array
    {
        // Remove números, pontuação e palavras pequenas
        $title = strtolower($title);
        $title = preg_replace('/[0-9]+/', '', $title);
        $title = preg_replace('/[^a-záàâãéèêíïóôõöúçñ\s]/', ' ', $title);
        
        $words = explode(' ', $title);
        $keywords = [];
        
        $stopWords = ['ml', 'un', 'de', 'da', 'do', 'para', 'com', 'em', 'o', 'a', 'os', 'as'];
        
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }
        
        // Retorna no máximo 3 palavras-chave
        return array_slice($keywords, 0, 3);
    }

    /**
     * Buscar categorias por termo de busca
     * 
     * @param string $search
     * @param string $siteId
     * @param int|null $userId
     * @return array
     */
    public function searchCategories(string $search, string $siteId = 'MLB', ?int $userId = null): array
    {
        try {
            // Buscar token do usuário
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            // Usar API de busca de produtos e extrair categorias
            $url = "/sites/{$siteId}/search?q=" . urlencode($search) . "&limit=50";
            $response = $this->makeRequest('GET', $url, [], $accessToken, $userId);
            
            // Extrair categorias únicas dos resultados
            $categories = [];
            $seen = [];
            
            if (isset($response['results'])) {
                foreach ($response['results'] as $item) {
                    $catId = $item['category_id'] ?? null;
                    if ($catId && !isset($seen[$catId])) {
                        $seen[$catId] = true;
                        $categories[] = [
                            'id' => $catId,
                            'name' => $this->getCategoryName($catId, $accessToken, $userId)
                        ];
                    }
                    
                    if (count($categories) >= 10) break;
                }
            }
            
            if (empty($categories)) {
                // Fallback: filtrar categorias comuns por termo
                return $this->filterCommonCategories($search);
            }
            
            return [
                'success' => true,
                'categories' => $categories,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar categorias ML', [
                'error' => $e->getMessage(),
                'search' => $search,
            ]);
            
            // Fallback: filtrar categorias comuns
            return $this->filterCommonCategories($search);
        }
    }
    
    /**
     * Filtra categorias comuns por termo de busca
     */
    private function filterCommonCategories(string $search): array
    {
        $common = $this->getCommonCategories();
        $search = strtolower($search);
        
        $filtered = array_filter($common['categories'], function($cat) use ($search) {
            return stripos($cat['name'], $search) !== false;
        });
        
        return [
            'success' => true,
            'categories' => array_values($filtered)
        ];
    }
    
    /**
     * Busca nome da categoria por ID
     */
    private function getCategoryName(string $categoryId, ?string $accessToken = null, ?int $userId = null): string
    {
        try {
            $response = $this->makeRequest('GET', "/categories/{$categoryId}", [], $accessToken, $userId);
            return $response['name'] ?? 'Categoria';
        } catch (\Exception $e) {
            return 'Categoria';
        }
    }

    /**
     * Obter atributos obrigatórios e opcionais de uma categoria
     * 
     * @param string $categoryId
     * @return array
     */
    /**
     * Buscar produto no catálogo do ML por código de barras (GTIN/EAN)
     * 
     * @param string $barcode Código de barras
     * @param int|null $userId ID do usuário para autenticação
     * @return array
     */
    public function searchCatalogByBarcode(string $barcode, ?int $userId = null): array
    {
        try {
            // Buscar token se userId fornecido
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            // Busca no catálogo usando o GTIN
            $response = $this->makeRequest('GET', "/products/search", [
                'site_id' => 'MLB',
                'q' => $barcode,
                'limit' => 5
            ], $accessToken, $userId);
            
            $results = $response['results'] ?? [];
            
            Log::info('Busca no catálogo ML por código de barras', [
                'barcode' => $barcode,
                'found' => count($results),
                'sample' => array_slice($results, 0, 2)
            ]);
            
            return [
                'success' => true,
                'results' => $results,
                'total' => count($results)
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar no catálogo ML', [
                'error' => $e->getMessage(),
                'barcode' => $barcode,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => []
            ];
        }
    }

    /**
     * Obter category_id a partir de um domain_id
     * 
     * @param string $domainId Domain ID (ex: MLB-HAIR_SHAMPOOS_AND_CONDITIONERS)
     * @param int|null $userId ID do usuário para autenticação
     * @return array
     */
    public function getCategoryFromDomain(string $domainId, ?int $userId = null): array
    {
        try {
            // Buscar token se userId fornecido
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            // Buscar informações do domínio
            $response = $this->makeRequest('GET', "/domains/{$domainId}", [], $accessToken, $userId);
            
            $categoryId = $response['category_id'] ?? null;
            
            return [
                'success' => true,
                'category_id' => $categoryId,
                'domain_info' => $response
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar categoria do domínio', [
                'error' => $e->getMessage(),
                'domain_id' => $domainId,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter category_id a partir de um product_id do catálogo ML
     * Usa o endpoint /products/{id} que retorna detalhes completos incluindo category_id
     * 
     * @param string $productId Product ID do catálogo (ex: MLB47777941)
     * @param int|null $userId ID do usuário para autenticação
     * @return array
     */
    public function getCategoryFromCatalogProduct(string $productId, ?int $userId = null): array
    {
        try {
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            // Buscar detalhes do produto do catálogo
            $response = $this->makeRequest('GET', "/products/{$productId}", [], $accessToken, $userId);
            
            // Extrair category_id do produto - pode estar em 'main_features' ou diretamente
            $categoryId = $response['category_id'] ?? null;
            
            // Se não veio direto, tentar buscar das settings ou buy_box
            if (empty($categoryId) && !empty($response['settings']['category_id'])) {
                $categoryId = $response['settings']['category_id'];
            }
            
            Log::info('Detalhes do produto do catálogo obtidos', [
                'product_id' => $productId,
                'category_id' => $categoryId,
                'response_keys' => array_keys($response),
            ]);
            
            return [
                'success' => true,
                'category_id' => $categoryId,
                'product_info' => $response
            ];
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar categoria do produto do catálogo', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getCategoryAttributes(string $categoryId, ?int $userId = null): array
    {
        try {
            // Buscar token se userId fornecido
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            $response = $this->makeRequest('GET', "/categories/{$categoryId}/attributes", [], $accessToken, $userId);
            
            // A resposta pode ser array direto ou ter chave 'attributes'
            $attributes = $response['attributes'] ?? $response;
            
            // Log temporário para debug
            Log::info('Resposta da API de atributos', [
                'category_id' => $categoryId,
                'total_attributes' => count($attributes),
                'sample' => array_slice($attributes, 0, 2)
            ]);
            
            return [
                'success' => true,
                'attributes' => $attributes,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar atributos da categoria ML', [
                'error' => $e->getMessage(),
                'category_id' => $categoryId,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'attributes' => [],
            ];
        }
    }

    /**
     * Publicar produto no Mercado Livre (método público para uso em Livewire)
     * 
     * @param Product $product Produto do sistema
     * @param array $publishData Dados de publicação (category_id, listing_type, attributes, shipping)
     * @param int|null $userId ID do usuário para autenticação
     * @return array
     */
    public function publishProduct(Product $product, array $publishData, ?int $userId = null): array
    {
        try {
            // Usar preço customizado do formulário se fornecido, senão do produto
            $price = $publishData['price'] ?? ($product->price_sale ?? $product->price);
            $price = is_numeric($price) ? (float) $price : (float) str_replace(',', '.', $price);
            
            // Usar quantidade customizada do formulário se fornecida, senão do produto
            $quantity = (int) ($publishData['quantity'] ?? $product->stock_quantity ?? 0);
            
            // Preparar dados no formato esperado por createProduct()
            $mlData = [
                'title' => mb_substr($product->name, 0, 60),
                'price' => $price, // IMPORTANTE: deve ser float, não string
                'currency_id' => 'BRL',
                'available_quantity' => $quantity,
                'buying_mode' => 'buy_it_now',
                'condition' => 'new',
                'listing_type' => $publishData['listing_type'] ?? 'gold_special',
            ];
            
            // category_id é SEMPRE obrigatório pela API do ML
            $mlData['category_id'] = $publishData['category_id'];
            
            // family_name é obrigatório no modelo User Products do ML
            $mlData['family_name'] = $publishData['family_name'] ?? mb_substr($product->name, 0, 60);
            
            // Se usar catalog_product_id, passar como product_id
            if (!empty($publishData['catalog_product_id'])) {
                $mlData['catalog_product_id'] = $publishData['catalog_product_id'];
            }
            // Fallback: se usar product_id (formato legado)
            elseif (!empty($publishData['product_id'])) {
                $mlData['product_id'] = $publishData['product_id'];
            }
            
            // Adicionar descrição se existir
            if (!empty($product->description)) {
                $mlData['description'] = $product->description;
            }
            
            // Adicionar imagens - priorizar fotos customizadas (catálogo) se fornecidas
            // publishData['pictures'] já vem no formato [['source' => url], ...] do Livewire component
            if (!empty($publishData['pictures'])) {
                $mlData['pictures_formatted'] = $publishData['pictures'];
            } elseif (!empty($product->image_url)) {
                $mlData['pictures'] = [$product->image_url];
            }
            
            // Montar atributos - BRAND e MODEL são SEMPRE obrigatórios no modelo User Products
            $attributes = [];
            
            // Coletar atributos preenchidos pelo usuário
            if (!empty($publishData['attributes'])) {
                foreach ($publishData['attributes'] as $attrId => $attrValue) {
                    if (!empty($attrValue)) {
                        $attributes[$attrId] = [
                            'id' => $attrId,
                            'value_name' => (string) $attrValue,
                        ];
                    }
                }
            }
            
            // Auto-preencher BRAND se não foi fornecido
            if (!isset($attributes['BRAND'])) {
                $brandName = $product->brand ?? 'Genérica';
                $attributes['BRAND'] = [
                    'id' => 'BRAND',
                    'value_name' => $brandName,
                ];
            }
            
            // Auto-preencher MODEL se não foi fornecido
            if (!isset($attributes['MODEL'])) {
                $attributes['MODEL'] = [
                    'id' => 'MODEL',
                    'value_name' => mb_substr($product->name, 0, 255),
                ];
            }
            
            // Auto-preencher GTIN (código de barras) se disponível
            if (!isset($attributes['GTIN'])) {
                if (!empty($product->barcode)) {
                    $attributes['GTIN'] = [
                        'id' => 'GTIN',
                        'value_name' => $product->barcode,
                    ];
                } else {
                    // Sem código de barras - informar motivo
                    $attributes['EMPTY_GTIN_REASON'] = [
                        'id' => 'EMPTY_GTIN_REASON',
                        'value_id' => '17055160', // "O produto não tem código cadastrado"
                    ];
                }
            }
            
            // Auto-preencher SALE_FORMAT se não foi fornecido
            if (!isset($attributes['SALE_FORMAT'])) {
                $attributes['SALE_FORMAT'] = [
                    'id' => 'SALE_FORMAT',
                    'value_id' => '1359391', // "Unidade"
                ];
            }
            
            // UNITS_PER_PACK é obrigatório quando SALE_FORMAT = "Unidade"
            if (!isset($attributes['UNITS_PER_PACK'])) {
                $attributes['UNITS_PER_PACK'] = [
                    'id' => 'UNITS_PER_PACK',
                    'value_name' => '1',
                ];
            }
            
            // NAME é catalog_required - nome do produto no catálogo ML
            if (!isset($attributes['NAME'])) {
                $attributes['NAME'] = [
                    'id' => 'NAME',
                    'value_name' => mb_substr($product->name, 0, 255),
                ];
            }
            
            // Sempre enviar atributos (API exige pelo menos BRAND e MODEL)
            $mlData['attributes'] = array_values($attributes);
            
            // Configurar shipping - sem especificar mode (ML auto-detecta)
            $mlData['shipping'] = [
                'free_shipping' => $publishData['free_shipping'] ?? false,
                'local_pick_up' => $publishData['local_pickup'] ?? false,
            ];
            
            // Validar se a categoria é leaf (permite publicação) - auto-resolver se necessário
            try {
                $accessToken = null;
                if ($userId) {
                    $authService = new AuthService();
                    $token = $authService->getActiveToken($userId);
                    $accessToken = $token?->access_token;
                }
                $catInfo = $this->makeRequest('GET', '/categories/' . $mlData['category_id'], [], $accessToken, $userId);
                
                if (isset($catInfo['settings']['listing_allowed']) && $catInfo['settings']['listing_allowed'] === false) {
                    Log::info('Categoria não é leaf, tentando auto-resolver', [
                        'original_category' => $mlData['category_id'],
                        'product_name' => $product->name,
                    ]);
                    
                    // Tentar domain_discovery para encontrar leaf category automaticamente
                    $leafCategoryId = $this->findLeafCategory($product->name, $mlData['category_id'], $accessToken, $userId);
                    
                    if ($leafCategoryId) {
                        Log::info('Categoria leaf encontrada automaticamente', [
                            'original' => $mlData['category_id'],
                            'resolved' => $leafCategoryId,
                        ]);
                        $mlData['category_id'] = $leafCategoryId;
                        
                        // Buscar atributos obrigatórios da nova categoria e auto-preencher
                        $this->autoFillRequiredAttributes($leafCategoryId, $attributes, $product, $accessToken, $userId);
                        $mlData['attributes'] = array_values($attributes);
                    } else {
                        throw new \Exception("Não foi possível encontrar uma categoria específica para publicar. A categoria {$mlData['category_id']} é muito genérica.");
                    }
                }
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Não foi possível encontrar') || str_contains($e->getMessage(), 'não permite publicação')) {
                    throw $e;
                }
                Log::warning('Não foi possível validar categoria leaf', ['error' => $e->getMessage()]);
            }
            
            // Chamar método de criação existente (que buscará o token internamente)
            return $this->createProduct($product, $mlData, $userId);
            
        } catch (\Exception $e) {
            Log::error('Erro ao publicar produto no ML', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'publish_data' => $publishData,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Criar um novo produto no Mercado Livre
     * 
     * @param Product $product
     * @param array $mlData (dados específicos do ML)
     * @param int|null $userId ID do usuário para logs
     * @return array
     */
    public function createProduct(Product $product, array $mlData, ?int $userId = null): array
    {
        DB::beginTransaction();
        
        try {
            // Buscar token do usuário
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            // Validar dados obrigatórios
            $this->validateProductData($mlData);
            
            // Preparar payload
            $payload = $this->prepareProductPayload($product, $mlData);
            
            // Log temporário para debug
            Log::info('Payload sendo enviado para ML API', [
                'payload' => $payload,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'product_stock' => $product->stock_quantity,
            ]);
            
            // Criar produto no ML com autenticação
            $response = $this->makeRequest('POST', '/items', $payload, $accessToken, $userId);
            
            // Salvar relacionamento no banco
            $mlProduct = MercadoLivreProduct::create([
                'product_id' => $product->id,
                'ml_item_id' => $response['id'],
                'ml_permalink' => $response['permalink'] ?? null,
                'ml_category_id' => $mlData['category_id'] ?? $response['category_id'] ?? null,
                'listing_type' => $mlData['listing_type'] ?? 'gold_special',
                'status' => $response['status'] ?? 'active',
                'ml_price' => $response['price'] ?? $product->price,
                'ml_quantity' => $response['available_quantity'] ?? $product->stock_quantity,
                'ml_attributes' => $mlData['attributes'] ?? [],
                'sync_status' => 'synced',
                'last_sync_at' => now(),
            ]);
            
            // Registrar sincronização
            $this->logSync(
                $userId,
                'product',
                'create',
                'success',
                "Produto {$product->name} publicado com sucesso",
                [],
                ['ml_item_id' => $response['id'], 'product_id' => $product->id]
            );
            
            DB::commit();
            
            Log::info('Produto criado no ML com sucesso', [
                'product_id' => $product->id,
                'ml_item_id' => $response['id'],
            ]);
            
            return [
                'success' => true,
                'ml_product' => $mlProduct->fresh(),
                'ml_response' => $response,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao criar produto no ML', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Registrar falha
            $this->logSync(
                $userId,
                'product',
                'create',
                'error',
                $e->getMessage()
            );
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Atualizar produto existente no ML
     * 
     * @param MercadoLivreProduct $mlProduct
     * @param array $data
     * @return array
     */
    public function updateProduct(MercadoLivreProduct $mlProduct, array $data): array
    {
        try {
            // Preparar dados de atualização
            $payload = $this->prepareUpdatePayload($data);
            
            // Obter token de acesso
            $userId = $mlProduct->product->user_id ?? null;
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            // Atualizar no ML
            $response = $this->makeRequest('PUT', "/items/{$mlProduct->ml_item_id}", $payload, $accessToken, $userId);
            
            // Atualizar no banco com campos corretos do model
            $mlProduct->update([
                'status' => $response['status'] ?? $mlProduct->status,
                'ml_price' => $response['price'] ?? $mlProduct->ml_price,
                'ml_quantity' => $response['available_quantity'] ?? $mlProduct->ml_quantity,
                'sync_status' => 'synced',
                'last_sync_at' => now(),
            ]);
            
            // Registrar sincronização
            $this->logSync(
                $mlProduct->product->user_id ?? null,
                'product',
                'update',
                'success',
                "Produto atualizado: {$mlProduct->ml_item_id}",
                $payload,
                $response
            );
            
            Log::info('Produto atualizado no ML', [
                'ml_item_id' => $mlProduct->ml_item_id,
                'updated_fields' => array_keys($data),
            ]);
            
            return [
                'success' => true,
                'ml_product' => $mlProduct->fresh(),
                'ml_response' => $response,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto no ML', [
                'error' => $e->getMessage(),
                'ml_item_id' => $mlProduct->ml_item_id,
            ]);
            
            $this->logSync(
                $mlProduct->product->user_id ?? null,
                'product',
                'update',
                'error',
                $e->getMessage()
            );
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Atualizar apenas o preço
     * 
     * @param MercadoLivreProduct $mlProduct
     * @param float $newPrice
     * @return array
     */
    public function updatePrice(MercadoLivreProduct $mlProduct, float $newPrice): array
    {
        return $this->updateProduct($mlProduct, ['price' => $newPrice]);
    }

    /**
     * Atualizar apenas o estoque
     * 
     * @param MercadoLivreProduct $mlProduct
     * @param int $newQuantity
     * @return array
     */
    public function updateStock(MercadoLivreProduct $mlProduct, int $newQuantity): array
    {
        return $this->updateProduct($mlProduct, ['available_quantity' => $newQuantity]);
    }

    /**
     * Pausar anúncio
     * 
     * @param MercadoLivreProduct $mlProduct
     * @return array
     */
    public function pauseProduct(MercadoLivreProduct $mlProduct): array
    {
        return $this->updateProduct($mlProduct, ['status' => 'paused']);
    }

    /**
     * Ativar anúncio
     * 
     * @param MercadoLivreProduct $mlProduct
     * @return array
     */
    public function activateProduct(MercadoLivreProduct $mlProduct): array
    {
        return $this->updateProduct($mlProduct, ['status' => 'active']);
    }

    /**
     * Finalizar anúncio (encerrar)
     * 
     * @param MercadoLivreProduct $mlProduct
     * @return array
     */
    public function closeProduct(MercadoLivreProduct $mlProduct): array
    {
        return $this->updateProduct($mlProduct, ['status' => 'closed']);
    }

    /**
     * Obter informações de um produto do ML
     * 
     * @param string $mlItemId
     * @param int|null $userId
     * @return array
     */
    public function getProduct(string $mlItemId, ?int $userId = null): array
    {
        try {
            $accessToken = null;
            if ($userId) {
                $authService = new AuthService();
                $token = $authService->getActiveToken($userId);
                $accessToken = $token?->access_token;
            }
            
            $response = $this->makeRequest('GET', "/items/{$mlItemId}", [], $accessToken, $userId);
            
            return [
                'success' => true,
                'product' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto do ML', [
                'error' => $e->getMessage(),
                'ml_item_id' => $mlItemId,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sincronizar produto (buscar dados do ML e atualizar banco)
     * 
     * @param MercadoLivreProduct $mlProduct
     * @return array
     */
    public function syncProduct(MercadoLivreProduct $mlProduct): array
    {
        try {
            $userId = $mlProduct->product->user_id ?? null;
            $result = $this->getProduct($mlProduct->ml_item_id, $userId);
            
            if (!$result['success']) {
                // Se o item não existe mais no ML (404), marcar como closed
                if (str_contains($result['error'] ?? '', '404') || str_contains($result['error'] ?? '', 'not found') || str_contains($result['error'] ?? '', 'Resource not found')) {
                    $mlProduct->update([
                        'status' => 'closed',
                        'sync_status' => 'synced',
                        'last_sync_at' => now(),
                        'error_message' => 'Item não encontrado no Mercado Livre (possivelmente excluído)',
                    ]);
                    
                    return [
                        'success' => true,
                        'ml_product' => $mlProduct->fresh(),
                        'status_changed' => true,
                        'new_status' => 'closed',
                        'message' => 'O anúncio não existe mais no Mercado Livre. Status atualizado para encerrado.',
                    ];
                }
                
                throw new \Exception($result['error']);
            }
            
            $mlData = $result['product'];
            
            // Atualizar dados locais com campos corretos do model
            $updateData = [
                'status' => $mlData['status'] ?? $mlProduct->status,
                'ml_price' => $mlData['price'] ?? $mlProduct->ml_price,
                'ml_quantity' => $mlData['available_quantity'] ?? $mlProduct->ml_quantity,
                'ml_permalink' => $mlData['permalink'] ?? $mlProduct->ml_permalink,
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'error_message' => null,
            ];
            
            $oldStatus = $mlProduct->status;
            $mlProduct->update($updateData);
            
            $statusChanged = $oldStatus !== $updateData['status'];
            
            $this->logSync(
                $mlProduct->product->user_id ?? null,
                'product',
                'sync',
                'success',
                "Produto sincronizado: {$mlProduct->ml_item_id}" . ($statusChanged ? " (status: {$oldStatus} → {$updateData['status']})" : '')
            );
            
            return [
                'success' => true,
                'ml_product' => $mlProduct->fresh(),
                'status_changed' => $statusChanged,
                'old_status' => $oldStatus,
                'new_status' => $updateData['status'],
                'message' => $statusChanged 
                    ? "Status atualizado: {$oldStatus} → {$updateData['status']}" 
                    : 'Produto sincronizado com sucesso',
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar produto', [
                'error' => $e->getMessage(),
                'ml_item_id' => $mlProduct->ml_item_id,
            ]);
            
            $mlProduct->update([
                'sync_status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
            
            $this->logSync(
                $mlProduct->product->user_id ?? null,
                'product',
                'sync',
                'error',
                $e->getMessage()
            );
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sincronizar múltiplos produtos
     * 
     * @param array $mlProductIds
     * @return array
     */
    public function syncMultipleProducts(array $mlProductIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'details' => [],
        ];
        
        foreach ($mlProductIds as $mlProductId) {
            $mlProduct = MercadoLivreProduct::find($mlProductId);
            
            if (!$mlProduct) {
                $results['failed']++;
                $results['details'][] = [
                    'id' => $mlProductId,
                    'success' => false,
                    'error' => 'Produto não encontrado',
                ];
                continue;
            }
            
            $result = $this->syncProduct($mlProduct);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'id' => $mlProductId,
                'ml_item_id' => $mlProduct->ml_item_id,
                'success' => $result['success'],
                'error' => $result['error'] ?? null,
            ];
        }
        
        return $results;
    }

    /**
     * Validar dados obrigatórios para criação
     * 
     * @param array $data
     * @throws \Exception
     */
    private function validateProductData(array $data): void
    {
        // Validar campos obrigatórios (exceto numéricos que podem ser 0)
        // No modelo User Products: family_name substitui title
        // category_id é SEMPRE obrigatório pela API do ML
        $requiredStrings = ['currency_id', 'category_id'];
        foreach ($requiredStrings as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Campo obrigatório ausente: {$field}");
            }
        }
        
        // Validar que tem title OU family_name
        $productName = $data['family_name'] ?? $data['title'] ?? null;
        if (empty($productName)) {
            throw new \Exception('Campo obrigatório ausente: family_name (ou title)');
        }
        
        // Validar campos numéricos (que podem ser 0)
        if (!isset($data['price']) || $data['price'] === null || $data['price'] === '') {
            throw new \Exception('Campo obrigatório ausente: price');
        }
        
        if (!isset($data['available_quantity']) || $data['available_quantity'] === null || $data['available_quantity'] === '') {
            throw new \Exception('Campo obrigatório ausente: available_quantity');
        }
        
        // Validações específicas
        if (mb_strlen($productName) > 60) {
            throw new \Exception('Nome do produto deve ter no máximo 60 caracteres');
        }
        
        if ($data['price'] <= 0) {
            throw new \Exception('Preço deve ser maior que zero');
        }
        
        if ($data['available_quantity'] < 0) {
            throw new \Exception('Quantidade deve ser maior ou igual a zero');
        }
    }

    /**
     * Preparar payload para criação de produto
     * 
     * @param Product $product
     * @param array $mlData
     * @return array
     */
    private function prepareProductPayload(Product $product, array $mlData): array
    {
        $title = $mlData['title'] ?? mb_substr($product->name, 0, 60);
        
        // Garantir que price seja numérico
        $price = $mlData['price'] ?? $product->price_sale ?? $product->price;
        $price = is_numeric($price) ? (float) $price : (float) str_replace(',', '.', $price);
        
        $payload = [
            // No modelo User Products do ML, 'title' foi substituído por 'family_name'
            // Enviar 'title' causa erro "The fields [title] are invalid for requested call."
            'family_name' => $mlData['family_name'] ?? $title,
            'price' => $price, // IMPORTANTE: price deve ser numérico, não string
            'currency_id' => $mlData['currency_id'] ?? 'BRL',
            'available_quantity' => (int) ($mlData['available_quantity'] ?? $product->stock_quantity),
            'buying_mode' => $mlData['buying_mode'] ?? 'buy_it_now',
            'condition' => $mlData['condition'] ?? 'new',
            'listing_type_id' => $mlData['listing_type'] ?? 'gold_special',
        ];
        
        // category_id é SEMPRE obrigatório pela API do ML
        $payload['category_id'] = $mlData['category_id'];
        
        // Descrição
        if (!empty($mlData['description'])) {
            $payload['description'] = [
                'plain_text' => $mlData['description'],
            ];
        } elseif (!empty($product->description)) {
            $payload['description'] = [
                'plain_text' => $product->description,
            ];
        }
        
        // Imagens
        if (!empty($mlData['pictures_formatted'])) {
            // Já vem no formato correto [['source' => url], ...]
            $payload['pictures'] = $mlData['pictures_formatted'];
        } elseif (!empty($mlData['pictures'])) {
            $payload['pictures'] = array_map(function ($url) {
                return ['source' => $url];
            }, $mlData['pictures']);
        }
        
        // Atributos - SEMPRE enviar (API exige pelo menos BRAND e MODEL)
        if (!empty($mlData['attributes'])) {
            $payload['attributes'] = $mlData['attributes'];
        }
        
        // Envio - não especificar mode para deixar o ML auto-detectar
        if (isset($mlData['shipping'])) {
            // Remover mode se for me1/me2 (pode não estar disponível para o usuário)
            $shipping = $mlData['shipping'];
            if (isset($shipping['mode']) && in_array($shipping['mode'], ['me1', 'me2'])) {
                unset($shipping['mode']);
            }
            $payload['shipping'] = $shipping;
        } else {
            $payload['shipping'] = [
                'free_shipping' => false,
            ];
        }
        
        return $payload;
    }

    /**
     * Preparar payload para atualização
     * 
     * @param array $data
     * @return array
     */
    private function prepareUpdatePayload(array $data): array
    {
        $allowed = [
            'title',
            'price',
            'available_quantity',
            'status',
            'description',
            'pictures',
            'attributes',
            'shipping',
        ];
        
        $payload = [];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $payload[$field] = $data[$field];
            }
        }
        
        return $payload;
    }
}
