<?php

namespace App\Services\MercadoLivre;

use App\Models\MercadoLivreToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço de Autenticação OAuth 2.0 do Mercado Livre
 * 
 * Responsável por:
 * - Gerar URL de autorização
 * - Processar callback e obter tokens
 * - Renovar tokens expirados
 * - Revogar acesso
 * - Verificar status de autenticação
 */
class AuthService extends MercadoLivreService
{
    /**
     * URL de autorização do Mercado Livre
     */
    protected string $authUrl = 'https://auth.mercadolivre.com.br/authorization';

    /**
     * URL para obter/renovar tokens
     */
    protected string $tokenUrl = 'https://api.mercadolibre.com/oauth/token';

    /**
     * Redirect URI configurada
     */
    protected string $redirectUri;

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->redirectUri = config(
            'services.mercadolivre.redirect_uri', 
            env('MERCADOLIVRE_REDIRECT_URI', url('/mercadolivre/auth/callback'))
        );
    }

    /**
     * Gera a URL de autorização para o usuário
     * 
     * @param int $userId ID do usuário que está conectando
     * @return string URL de autorização
     * @throws Exception
     */
    public function getAuthorizationUrl(int $userId): string
    {
        if (!$this->hasCredentials()) {
            throw new Exception('Mercado Livre credentials not configured');
        }

        // Gerar state para segurança (prevenir CSRF)
        $state = base64_encode(json_encode([
            'user_id' => $userId,
            'timestamp' => time(),
            'random' => bin2hex(random_bytes(16)),
        ]));

        $params = [
            'response_type' => 'code',
            'client_id' => $this->appId,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
        ];

        $url = $this->authUrl . '?' . http_build_query($params);

        Log::info('ML Authorization URL generated', [
            'user_id' => $userId,
            'redirect_uri' => $this->redirectUri,
        ]);

        return $url;
    }

    /**
     * Processa o callback do OAuth e obtém os tokens
     * 
     * @param string $code Código de autorização recebido
     * @param string $state State recebido (para validação)
     * @return MercadoLivreToken Token salvo
     * @throws Exception
     */
    public function handleCallback(string $code, string $state): MercadoLivreToken
    {
        // Validar state
        $stateData = json_decode(base64_decode($state), true);
        
        if (!$stateData || !isset($stateData['user_id'])) {
            throw new Exception('Invalid state parameter');
        }

        $userId = $stateData['user_id'];

        // Verificar se o timestamp não é muito antigo (5 minutos)
        if (isset($stateData['timestamp']) && (time() - $stateData['timestamp']) > 300) {
            throw new Exception('Authorization expired, please try again');
        }

        // Trocar o código por tokens
        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->appId,
            'client_secret' => $this->secretKey,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ];

        try {
            $response = $this->makeRequest('POST', '/oauth/token', $data, null, $userId);

            if (!isset($response['access_token'])) {
                throw new Exception('Access token not received from Mercado Livre');
            }

            // Buscar informações do usuário ML
            $userInfo = $this->getUserInfo($response['access_token']);

            // Desativar tokens antigos deste usuário
            MercadoLivreToken::where('user_id', $userId)
                ->update(['is_active' => false]);

            // Salvar novo token
            $token = MercadoLivreToken::create([
                'user_id' => $userId,
                'ml_user_id' => $userInfo['id'] ?? null,
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'token_type' => $response['token_type'] ?? 'Bearer',
                'expires_at' => now()->addSeconds($response['expires_in'] ?? 21600), // 6 horas padrão
                'scope' => $response['scope'] ?? null,
                'is_active' => true,
                'ml_nickname' => $userInfo['nickname'] ?? null,
                'user_info' => $userInfo,
            ]);

            Log::info('ML Token obtained successfully', [
                'user_id' => $userId,
                'ml_user_id' => $token->ml_user_id,
                'nickname' => $token->ml_nickname,
                'expires_at' => $token->expires_at,
            ]);

            return $token;

        } catch (Exception $e) {
            Log::error('ML OAuth callback failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            
            throw new Exception('Failed to obtain access token: ' . $e->getMessage());
        }
    }

    /**
     * Renova um token expirado usando o refresh token
     * 
     * @param MercadoLivreToken $token Token a ser renovado
     * @return MercadoLivreToken Token renovado
     * @throws Exception
     */
    public function refreshToken(MercadoLivreToken $token): MercadoLivreToken
    {
        if (!$token->refresh_token) {
            throw new Exception('No refresh token available');
        }

        $data = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->appId,
            'client_secret' => $this->secretKey,
            'refresh_token' => $token->refresh_token,
        ];

        try {
            $response = $this->makeRequest('POST', '/oauth/token', $data, null, $token->user_id);

            if (!isset($response['access_token'])) {
                throw new Exception('New access token not received');
            }

            // Atualizar token
            $token->update([
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expires_at' => now()->addSeconds($response['expires_in'] ?? 21600),
                'is_active' => true,
            ]);

            Log::info('ML Token refreshed successfully', [
                'user_id' => $token->user_id,
                'ml_user_id' => $token->ml_user_id,
                'new_expires_at' => $token->expires_at,
            ]);

            return $token->fresh();

        } catch (Exception $e) {
            Log::error('ML Token refresh failed', [
                'token_id' => $token->id,
                'user_id' => $token->user_id,
                'error' => $e->getMessage(),
            ]);

            // Desativar token se refresh falhar
            $token->update(['is_active' => false]);
            
            throw new Exception('Failed to refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Revoga o acesso do usuário
     * 
     * @param int $userId ID do usuário
     * @return bool
     */
    public function revokeToken(int $userId): bool
    {
        try {
            $token = MercadoLivreToken::where('user_id', $userId)
                ->where('is_active', true)
                ->first();

            if (!$token) {
                return true; // Já estava desconectado
            }

            // Tentar revogar na API do ML (não é obrigatório, mas é boa prática)
            try {
                $this->makeRequest(
                    'DELETE', 
                    '/oauth/token/' . $token->access_token,
                    [],
                    $token->access_token,
                    $userId
                );
            } catch (Exception $e) {
                // Ignorar erros de revogação na API
                Log::warning('Failed to revoke token on ML API', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Desativar token localmente
            $token->update(['is_active' => false]);

            Log::info('ML Token revoked', [
                'user_id' => $userId,
                'ml_user_id' => $token->ml_user_id,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to revoke ML token', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Obtém informações do usuário ML autenticado
     * 
     * @param string $accessToken Token de acesso
     * @return array Informações do usuário
     * @throws Exception
     */
    protected function getUserInfo(string $accessToken): array
    {
        try {
            return $this->makeRequest('GET', '/users/me', [], $accessToken);
        } catch (Exception $e) {
            Log::warning('Failed to get ML user info', [
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    /**
     * Obtém o token ativo de um usuário
     * 
     * @param int $userId ID do usuário
     * @param bool $autoRefresh Renovar automaticamente se estiver expirado
     * @return MercadoLivreToken|null
     */
    public function getActiveToken(int $userId, bool $autoRefresh = true): ?MercadoLivreToken
    {
        $token = MercadoLivreToken::where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        if (!$token) {
            return null;
        }

        // Verificar se está expirado
        if ($token->isExpired()) {
            if ($autoRefresh) {
                try {
                    return $this->refreshToken($token);
                } catch (Exception $e) {
                    Log::error('Auto-refresh failed', [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ]);
                    return null;
                }
            }
            
            return null;
        }

        // Renovar se estiver próximo de expirar (< 24h)
        if ($autoRefresh && $token->needsRefresh()) {
            try {
                return $this->refreshToken($token);
            } catch (Exception $e) {
                // Se falhar ao renovar, ainda retorna o token atual
                Log::warning('Preventive refresh failed', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $token;
    }

    /**
     * Verifica se o usuário está conectado ao Mercado Livre
     * 
     * @param int $userId ID do usuário
     * @return bool
     */
    public function isConnected(int $userId): bool
    {
        return $this->getActiveToken($userId, false) !== null;
    }

    /**
     * Retorna a Redirect URI configurada
     * 
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * Testa a conexão com a API usando um token
     * 
     * @param MercadoLivreToken $token
     * @return bool
     */
    public function testConnection(MercadoLivreToken $token): bool
    {
        try {
            $this->makeRequest('GET', '/users/me', [], $token->access_token, $token->user_id);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
