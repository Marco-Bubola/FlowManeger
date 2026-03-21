<?php

namespace App\Services\Shopee;

use App\Models\ShopeeToken;
use App\Models\ShopeeSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço Base da Shopee Open Platform API v2
 *
 * Responsável por:
 * - Geração de assinatura HMAC-SHA256 (requisito obrigatório de todas as chamadas)
 * - Requisições HTTP autenticadas
 * - Rate limiting e retries
 * - Logging centralizado
 *
 * Documentação oficial: https://open.shopee.com/documents
 */
class ShopeeService
{
    /** URL base da API Shopee (produção) */
    protected string $baseUrl = 'https://partner.shopeemobile.com';

    /** URL base da API Shopee (sandbox) */
    protected string $sandboxUrl = 'https://partner.test-stable.shopeemobile.com';

    /** Partner ID configurado */
    protected string $partnerId;

    /** Partner Key (secret) para assinar requisições */
    protected string $partnerKey;

    /** Ambiente atual */
    protected string $environment;

    /** Timeout HTTP em segundos */
    protected int $timeout = 30;

    /** Máximo de tentativas */
    protected int $maxRetries = 3;

    public function __construct()
    {
        $this->partnerId   = (string) config('services.shopee.partner_id', '');
        $this->partnerKey  = (string) config('services.shopee.partner_key', '');
        $this->environment = config('services.shopee.environment', 'sandbox');
    }

    // =========================================================================
    // Assinatura HMAC-SHA256 (obrigatório em todas as chamadas)
    // =========================================================================

    /**
     * Gera a assinatura de um endpoint sem access_token (ex.: obter token).
     *
     * @param string $path   Caminho do endpoint (ex.: /api/v2/auth/token/get)
     * @param int    $timestamp Unix timestamp
     */
    protected function signPublic(string $path, int $timestamp): string
    {
        $baseString = $this->partnerId . $path . $timestamp;
        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Gera a assinatura de um endpoint com access_token e shop_id.
     *
     * @param string $path        Caminho do endpoint
     * @param int    $timestamp   Unix timestamp
     * @param string $accessToken Token de acesso da loja
     * @param string $shopId      Shop ID da loja
     */
    protected function signShop(
        string $path,
        int    $timestamp,
        string $accessToken,
        string $shopId
    ): string {
        $baseString = $this->partnerId . $path . $timestamp . $accessToken . $shopId;
        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    // =========================================================================
    // Helpers de requisição
    // =========================================================================

    /**
     * Retorna a URL base de acordo com o ambiente.
     */
    protected function getBaseUrl(): string
    {
        return $this->environment === 'production' ? $this->baseUrl : $this->sandboxUrl;
    }

    /**
     * Retorna os query params comuns a todos os endpoints autenticados (com shop).
     */
    protected function getShopParams(string $path, string $accessToken, string $shopId): array
    {
        $timestamp = time();
        $sign      = $this->signShop($path, $timestamp, $accessToken, $shopId);

        return [
            'partner_id'   => (int) $this->partnerId,
            'timestamp'    => $timestamp,
            'access_token' => $accessToken,
            'shop_id'      => (int) $shopId,
            'sign'         => $sign,
        ];
    }

    /**
     * Realiza um GET autenticado na API Shopee.
     *
     * @param string       $path   Caminho do endpoint (ex.: /api/v2/product/get_item_list)
     * @param array        $params Parâmetros adicionais de query string
     * @param ShopeeToken  $token  Token ativo do usuário/loja
     */
    protected function get(string $path, array $params, ShopeeToken $token): array
    {
        $authParams = $this->getShopParams($path, $token->access_token, $token->shop_id);
        $allParams  = array_merge($authParams, $params);

        $url = $this->getBaseUrl() . $path;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = Http::timeout($this->timeout)->get($url, $allParams);

                if ($response->successful()) {
                    return $response->json() ?? [];
                }

                // Erro de rate limit — aguarda e tenta novamente
                if ($response->status() === 429 && $attempt < $this->maxRetries) {
                    sleep(2 ** $attempt);
                    continue;
                }

                throw new Exception(
                    "Shopee API GET error [{$response->status()}]: " . $response->body()
                );
            } catch (Exception $e) {
                if ($attempt === $this->maxRetries) {
                    throw $e;
                }
                sleep($attempt);
            }
        }

        return [];
    }

    /**
     * Realiza um POST autenticado na API Shopee.
     *
     * @param string      $path   Caminho do endpoint
     * @param array       $body   Payload JSON
     * @param ShopeeToken $token  Token ativo
     * @param array       $query  Query params adicionais
     */
    protected function post(string $path, array $body, ShopeeToken $token, array $query = []): array
    {
        $authParams = $this->getShopParams($path, $token->access_token, $token->shop_id);
        $allQuery   = array_merge($authParams, $query);

        $url = $this->getBaseUrl() . $path;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withQueryParameters($allQuery)
                    ->post($url, $body);

                if ($response->successful()) {
                    return $response->json() ?? [];
                }

                if ($response->status() === 429 && $attempt < $this->maxRetries) {
                    sleep(2 ** $attempt);
                    continue;
                }

                throw new Exception(
                    "Shopee API POST error [{$response->status()}]: " . $response->body()
                );
            } catch (Exception $e) {
                if ($attempt === $this->maxRetries) {
                    throw $e;
                }
                sleep($attempt);
            }
        }

        return [];
    }

    // =========================================================================
    // Helpers de configuração
    // =========================================================================

    /**
     * Verifica se as credenciais mínimas estão configuradas.
     */
    public function hasCredentials(): bool
    {
        return !empty($this->partnerId) && !empty($this->partnerKey);
    }

    /**
     * Obtém o token ativo para um usuário, renovando-o se necessário.
     *
     * @param int $userId
     * @return ShopeeToken
     * @throws Exception
     */
    protected function getActiveToken(int $userId): ShopeeToken
    {
        $token = ShopeeToken::getActiveForUser($userId);

        if (!$token) {
            throw new Exception('Shopee não está conectada. Configure o Partner ID e Shop ID nas configurações.');
        }

        if (!$token->isAccessTokenValid()) {
            if (!$token->isRefreshTokenValid()) {
                throw new Exception('Sessão Shopee expirada. Reconecte sua conta nas configurações.');
            }

            // Renovar token automaticamente
            $authService = app(AuthService::class);
            $token = $authService->refreshToken($token);
        }

        return $token;
    }

    /**
     * Formata um log de sincronização no padrão do sistema.
     */
    protected function logSync(
        int    $userId,
        string $syncType,
        string $status,
        string $message,
        array  $extra = []
    ): void {
        ShopeeSyncLog::create(array_merge([
            'user_id'    => $userId,
            'platform'   => 'shopee',
            'sync_type'  => $syncType,
            'status'     => $status,
            'message'    => $message,
            'created_at' => now(),
        ], $extra));
    }
}
