<?php

namespace App\Services\MercadoLivre;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\MercadoLivreSyncLog;
use Exception;

/**
 * Serviço Base para Integração com Mercado Livre
 * 
 * Responsável por:
 * - Comunicação HTTP com API do Mercado Livre
 * - Rate limiting (10 requisições/segundo)
 * - Retry automático em caso de falhas
 * - Logging de todas as requisições
 * - Tratamento de erros
 */
class MercadoLivreService
{
    /**
     * URL base da API do Mercado Livre
     */
    protected string $baseUrl;

    /**
     * App ID do Mercado Livre
     */
    protected ?string $appId;

    /**
     * Secret Key do Mercado Livre
     */
    protected ?string $secretKey;

    /**
     * Ambiente (sandbox ou production)
     */
    protected string $environment;

    /**
     * Timeout padrão em segundos
     */
    protected int $timeout = 30;

    /**
     * Número de tentativas em caso de erro
     */
    protected int $maxRetries = 3;

    /**
     * Delay entre tentativas (ms)
     */
    protected int $retryDelay = 1000;

    /**
     * Limite de requisições por segundo
     */
    protected int $rateLimit = 10;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->appId = config('services.mercadolivre.app_id');
        $this->secretKey = config('services.mercadolivre.secret_key');
        $this->environment = config('services.mercadolivre.environment', 'sandbox');
        
        Log::info('ML Service initialized', [
            'app_id' => $this->appId ? substr($this->appId, 0, 15) . '...' : 'NULL',
            'secret_key' => $this->secretKey ? 'SET (' . strlen($this->secretKey) . ' chars)' : 'NULL',
            'environment' => $this->environment,
        ]);
        
        // Define a URL base de acordo com o ambiente
        $this->baseUrl = $this->environment === 'sandbox' 
            ? 'https://api.mercadolibre.com' 
            : 'https://api.mercadolibre.com';
    }

    /**
     * Realiza uma requisição HTTP para a API do Mercado Livre
     * 
     * @param string $method Método HTTP (GET, POST, PUT, DELETE)
     * @param string $endpoint Endpoint da API (ex: /items/MLB123)
     * @param array $data Dados a serem enviados
     * @param string|null $accessToken Token de acesso (opcional)
     * @param int|null $userId ID do usuário para logging
     * @return array Resposta da API
     * @throws Exception
     */
    public function makeRequest(
        string $method, 
        string $endpoint, 
        array $data = [], 
        ?string $accessToken = null,
        ?int $userId = null
    ): array {
        $startTime = microtime(true);
        $url = $this->baseUrl . $endpoint;
        
        // Rate limiting
        $this->checkRateLimit();

        // Preparar headers
        $headers = $this->getHeaders($accessToken);

        // Tentar a requisição com retry
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $attempt++;
                
                // Log da tentativa
                Log::info("ML API Request Attempt {$attempt}/{$this->maxRetries}", [
                    'method' => $method,
                    'url' => $url,
                    'has_token' => !empty($accessToken),
                ]);

                // Fazer a requisição
                // Para GET, não passar $data se estiver vazio (pode sobrescrever query params da URL)
                if (strtoupper($method) === 'GET' && empty($data)) {
                    $response = Http::timeout($this->timeout)
                        ->withHeaders($headers)
                        ->get($url);
                } else {
                    $response = Http::timeout($this->timeout)
                        ->withHeaders($headers)
                        ->$method($url, $data);
                }

                // Verificar rate limit da API
                $apiCallsRemaining = $response->header('X-RateLimit-Remaining');

                // Tempo de execução
                $executionTime = (int)((microtime(true) - $startTime) * 1000);

                // Verificar se foi sucesso
                if ($response->successful()) {
                    // Log de sucesso
                    $this->logSync(
                        userId: $userId,
                        syncType: $this->determineSyncType($endpoint),
                        action: "{$method} {$endpoint}",
                        status: 'success',
                        requestData: $data,
                        responseData: $response->json(),
                        httpStatus: $response->status(),
                        executionTime: $executionTime,
                        apiCallsRemaining: (int)$apiCallsRemaining
                    );

                    return $response->json() ?? [];
                }

                // Se não for sucesso, capturar detalhes do erro
                $responseBody = $response->json() ?? [];
                $errorMessage = $responseBody['message'] ?? $response->body();
                
                // Log COMPLETO da resposta de erro para debugging
                Log::error('ML API Error Response COMPLETO', [
                    'status' => $response->status(),
                    'body_raw' => $response->body(),
                    'body_json' => $responseBody,
                    'url' => $url,
                    'method' => $method,
                    'sent_data_keys' => array_keys($data),
                ]);
                
                // Se houver detalhes sobre campos obrigatórios, incluir
                $errorDetails = '';
                if (isset($responseBody['cause'])) {
                    $causes = is_array($responseBody['cause']) ? $responseBody['cause'] : [$responseBody['cause']];
                    $errorDetails = ' - Detalhes: ' . json_encode($causes);
                }
                
                // Incluir campos inválidos no erro se houver
                if (isset($responseBody['error']) && $responseBody['error'] === 'validation_error') {
                    $errorDetails .= ' - Validation: ' . json_encode($responseBody);
                }
                
                throw new Exception(
                    "ML API Error: {$response->status()} - {$errorMessage}{$errorDetails}"
                );

            } catch (Exception $e) {
                $lastException = $e;
                
                Log::warning("ML API Request Failed", [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'url' => $url,
                ]);

                // Se não for a última tentativa, aguardar antes de tentar novamente
                if ($attempt < $this->maxRetries) {
                    usleep($this->retryDelay * 1000 * $attempt); // Exponential backoff
                    continue;
                }

                // Última tentativa falhou, logar erro
                $executionTime = (int)((microtime(true) - $startTime) * 1000);
                
                $this->logSync(
                    userId: $userId,
                    syncType: $this->determineSyncType($endpoint),
                    action: "{$method} {$endpoint}",
                    status: 'error',
                    message: $e->getMessage(),
                    requestData: $data,
                    responseData: ['error' => $e->getMessage()],
                    httpStatus: $e->getCode(),
                    executionTime: $executionTime
                );

                throw $e;
            }
        }

        throw $lastException ?? new Exception('Unknown error occurred');
    }

    /**
     * Retorna os headers para requisições
     * 
     * @param string|null $accessToken Token de acesso
     * @return array
     */
    protected function getHeaders(?string $accessToken = null): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken}";
        }

        return $headers;
    }

    /**
     * Verifica e aplica rate limiting
     * 
     * ML permite 10 requisições por segundo
     */
    protected function checkRateLimit(): void
    {
        $key = 'ml_rate_limit_' . now()->format('Y-m-d_H:i:s');
        
        $requestCount = Cache::get($key, 0);
        
        if ($requestCount >= $this->rateLimit) {
            // Aguardar 1 segundo se o limite foi atingido
            sleep(1);
            $key = 'ml_rate_limit_' . now()->format('Y-m-d_H:i:s');
            $requestCount = 0;
        }
        
        Cache::put($key, $requestCount + 1, 2); // 2 segundos de TTL
    }

    /**
     * Determina o tipo de sincronização baseado no endpoint
     * 
     * @param string $endpoint
     * @return string
     */
    protected function determineSyncType(string $endpoint): string
    {
        if (str_contains($endpoint, '/items')) {
            return 'product';
        }
        
        if (str_contains($endpoint, '/orders')) {
            return 'order';
        }
        
        if (str_contains($endpoint, '/oauth')) {
            return 'status';
        }
        
        if (str_contains($endpoint, '/users')) {
            return 'status';
        }
        
        return 'full';
    }

    /**
     * Registra log de sincronização
     * 
     * @param int|null $userId
     * @param string $syncType
     * @param string $action
     * @param string $status
     * @param string|null $message
     * @param array $requestData
     * @param array $responseData
     * @param int|null $httpStatus
     * @param int $executionTime
     * @param int|null $apiCallsRemaining
     */
    protected function logSync(
        ?int $userId,
        string $syncType,
        string $action,
        string $status,
        ?string $message = null,
        array $requestData = [],
        array $responseData = [],
        ?int $httpStatus = null,
        int $executionTime = 0,
        ?int $apiCallsRemaining = null
    ): void {
        try {
            // Não salvar log se user_id for null (chamadas públicas/sem contexto de usuário)
            if ($userId === null) {
                Log::info('Skipping ML sync log (no user context)', [
                    'action' => $action,
                    'status' => $status,
                ]);
                return;
            }
            
            MercadoLivreSyncLog::create([
                'user_id' => $userId,
                'sync_type' => $syncType,
                'entity_type' => 'api_request',
                'action' => $action,
                'status' => $status,
                'message' => $message,
                'request_data' => $requestData,
                'response_data' => $responseData,
                'http_status' => $httpStatus,
                'execution_time' => $executionTime,
                'api_calls_remaining' => $apiCallsRemaining,
            ]);
        } catch (Exception $e) {
            // Se falhar ao salvar log, apenas registrar no Laravel log
            Log::error('Failed to save ML sync log', [
                'error' => $e->getMessage(),
                'action' => $action,
            ]);
        }
    }

    /**
     * Verifica se as credenciais estão configuradas
     * 
     * @return bool
     */
    public function hasCredentials(): bool
    {
        $hasAppId = !empty($this->appId);
        $hasSecretKey = !empty($this->secretKey);
        
        Log::info('ML: Checking credentials', [
            'app_id_set' => $hasAppId,
            'app_id_value' => $hasAppId ? substr($this->appId, 0, 10) . '...' : 'NULL',
            'secret_key_set' => $hasSecretKey,
            'secret_key_value' => $hasSecretKey ? substr($this->secretKey, 0, 10) . '...' : 'NULL',
        ]);
        
        return $hasAppId && $hasSecretKey;
    }

    /**
     * Retorna o App ID
     * 
     * @return string|null
     */
    public function getAppId(): ?string
    {
        return $this->appId;
    }

    /**
     * Retorna a Secret Key
     * 
     * @return string|null
     */
    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * Retorna o ambiente atual
     * 
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Retorna a URL base
     * 
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
