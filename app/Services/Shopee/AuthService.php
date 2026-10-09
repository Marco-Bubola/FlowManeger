<?php

namespace App\Services\Shopee;

use App\Models\ShopeeToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço de Autenticação OAuth 2.0 da Shopee Open Platform (API v2)
 *
 * Fluxo:
 * 1. Gerar URL de autorização → usuário clica e autoriza
 * 2. Shopee redireciona com ?code=... e ?shop_id=...
 * 3. Trocar code por access_token + refresh_token
 * 4. Renovar access_token antes de expirar usando refresh_token
 *
 * Ref: https://open.shopee.com/documents/v2/OpenAPI_Guide
 */
class AuthService extends ShopeeService
{
    /** Endpoint de autorização */
    protected string $authPath = '/api/v2/shop/auth_partner';

    /** Endpoint para obter token via authorization code */
    protected string $tokenPath = '/api/v2/auth/token/get';

    /** Endpoint para renovar token */
    protected string $refreshPath = '/api/v2/auth/access_token/get';

    /** Redirect URI configurada */
    protected string $redirectUri;

    public function __construct()
    {
        parent::__construct();
        $this->redirectUri = config(
            'services.shopee.redirect_uri',
            env('APP_URL') . '/shopee/auth/callback'
        );
    }

    // =========================================================================
    // Geração da URL de Autorização
    // =========================================================================

    /**
     * Gera a URL de autorização para o usuário conectar a loja.
     *
     * @param int $userId ID do usuário interno
     * @return string URL de autorização da Shopee
     * @throws Exception
     */
    public function getAuthorizationUrl(int $userId): string
    {
        if (!$this->hasCredentials()) {
            throw new Exception('Credenciais da Shopee não configuradas (SHOPEE_PARTNER_ID / SHOPEE_PARTNER_KEY).');
        }

        $timestamp = time();
        $sign      = $this->signPublic($this->authPath, $timestamp);

        // State cifrado com a APP_KEY (anti-CSRF): não dá para forjar o user_id.
        $state = \Illuminate\Support\Facades\Crypt::encryptString(json_encode([
            'user_id'   => $userId,
            'timestamp' => $timestamp,
            'random'    => bin2hex(random_bytes(16)),
        ]));

        $params = [
            'partner_id'    => (int) $this->partnerId,
            'timestamp'     => $timestamp,
            'sign'          => $sign,
            'redirect'      => $this->redirectUri . '?state=' . urlencode($state),
        ];

        return $this->getBaseUrl() . $this->authPath . '?' . http_build_query($params);
    }

    // =========================================================================
    // Callback — trocar code por token
    // =========================================================================

    /**
     * Processa o callback da Shopee e obtém os tokens.
     *
     * @param string $code    Código de autorização recebido da Shopee
     * @param string $shopId  Shop ID recebido da Shopee
     * @param string $state   State para validação anti-CSRF
     * @return ShopeeToken
     * @throws Exception
     */
    public function handleCallback(string $code, string $shopId, string $state, ?int $loggedUserId = null): ShopeeToken
    {
        // Validar state (cifrado; um state forjado/alterado não decifra)
        try {
            $stateData = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($state), true);
        } catch (\Throwable $e) {
            $stateData = null;
        }

        if (!$stateData || !isset($stateData['user_id'])) {
            // Sem state válido, só aceita se houver alguém logado (a loja vai para ele).
            if (!$loggedUserId) {
                throw new Exception('Parâmetro state inválido na autorização Shopee.');
            }
            $stateData = ['user_id' => $loggedUserId, 'timestamp' => time()];
        }

        if ($loggedUserId && (int) $stateData['user_id'] !== $loggedUserId) {
            throw new Exception('A autorização Shopee foi iniciada por outro usuário.');
        }

        // Verificar validade (30 minutos — dá tempo de fazer login na Shopee)
        if (isset($stateData['timestamp']) && (time() - $stateData['timestamp']) > 1800) {
            throw new Exception('Autorização Shopee expirada. Tente novamente.');
        }

        $userId    = (int) $stateData['user_id'];
        $timestamp = time();
        $sign      = $this->signPublic($this->tokenPath, $timestamp);

        $payload = [
            'code'       => $code,
            'shop_id'    => (int) $shopId,
            'partner_id' => (int) $this->partnerId,
        ];

        $query = [
            'partner_id' => (int) $this->partnerId,
            'timestamp'  => $timestamp,
            'sign'       => $sign,
        ];

        $response = Http::timeout($this->timeout)
            ->withQueryParameters($query)
            ->post($this->getBaseUrl() . $this->tokenPath, $payload);

        if (!$response->successful()) {
            throw new Exception('Erro ao obter token Shopee: ' . $response->body());
        }

        $data = $response->json();

        if (!empty($data['error'])) {
            throw new Exception('Shopee retornou erro: ' . ($data['message'] ?? $data['error']));
        }

        return $this->saveToken($userId, $shopId, $data);
    }

    // =========================================================================
    // Renovação de token
    // =========================================================================

    /**
     * Renova o access_token usando o refresh_token.
     *
     * @param ShopeeToken $token Token a renovar
     * @return ShopeeToken Token atualizado
     * @throws Exception
     */
    public function refreshToken(ShopeeToken $token): ShopeeToken
    {
        // Um refresh_token só vale uma vez: duas renovações ao mesmo tempo
        // (fila + tela) fariam a segunda falhar. Trava a linha e confere de novo.
        return \Illuminate\Support\Facades\DB::transaction(function () use ($token) {
            $locked = ShopeeToken::whereKey($token->id)->lockForUpdate()->first() ?? $token;

            if ($locked->isAccessTokenValid() && $locked->access_token !== $token->access_token) {
                return $locked; // outro processo já renovou
            }

            $timestamp = time();
            $sign      = $this->signPublic($this->refreshPath, $timestamp);

            $payload = [
                'refresh_token' => $locked->refresh_token,
                'shop_id'       => (int) $locked->shop_id,
                'partner_id'    => (int) $this->partnerId,
            ];

            $query = [
                'partner_id' => (int) $this->partnerId,
                'timestamp'  => $timestamp,
                'sign'       => $sign,
            ];

            $response = Http::timeout($this->timeout)
                ->withQueryParameters($query)
                ->post($this->getBaseUrl() . $this->refreshPath, $payload);

            $data = $response->json() ?? [];

            if (!$response->successful() || !empty($data['error']) || empty($data['access_token'])) {
                $msg = $data['message'] ?? $data['error'] ?? $response->body();
                // refresh_token recusado: a loja precisa ser reconectada
                if (!empty($data['error'])
                    && (in_array($data['error'], ['error_auth', 'invalid_refresh_token'], true)
                        || str_contains(strtolower((string) $msg), 'refresh'))) {
                    $locked->update(['refresh_expires_at' => now()]);
                }
                throw new Exception('Erro ao renovar token Shopee: ' . $msg);
            }

            $locked->update([
                'access_token'       => $data['access_token'],
                'refresh_token'      => $data['refresh_token'] ?? $locked->refresh_token,
                'expires_at'         => now()->addSeconds((int) ($data['expire_in'] ?? 14400)),
                // Cada renovação gera um refresh_token novo, válido por 30 dias
                'refresh_expires_at' => !empty($data['refresh_token'])
                    ? now()->addDays(30)
                    : $locked->refresh_expires_at,
                'last_refreshed_at'  => now(),
            ]);

            Log::info('ShopeeAuthService: token renovado', [
                'user_id' => $locked->user_id,
                'shop_id' => $locked->shop_id,
            ]);

            return $locked->fresh();
        });
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Persiste ou atualiza o token no banco de dados.
     */
    private function saveToken(int $userId, string $shopId, array $data): ShopeeToken
    {
        if (empty($data['access_token'])) {
            throw new Exception('Shopee não devolveu o access_token.');
        }

        $expiresAt        = now()->addSeconds((int) ($data['expire_in'] ?? 14400));
        $refreshExpiresAt = isset($data['refresh_token_expire_in'])
            ? now()->addSeconds($data['refresh_token_expire_in'])
            : now()->addDays(30);

        $token = ShopeeToken::updateOrCreate(
            ['user_id' => $userId, 'shop_id' => $shopId],
            [
                'partner_id'          => $this->partnerId,
                'access_token'        => $data['access_token'],
                'refresh_token'       => $data['refresh_token'] ?? null,
                'expires_at'          => $expiresAt,
                'refresh_expires_at'  => $refreshExpiresAt,
                'is_active'           => true,
                'last_refreshed_at'   => now(),
            ]
        );

        // Uma loja pertence a uma conta só: os webhooks dela vão para este usuário.
        ShopeeToken::where('shop_id', $shopId)
            ->where('user_id', '!=', $userId)
            ->update(['is_active' => false]);

        Log::info('ShopeeAuthService: token salvo', [
            'user_id' => $userId,
            'shop_id' => $shopId,
        ]);

        return $token;
    }

    /**
     * Verifica se um usuário tem Shopee conectada e token válido.
     */
    public function isConnected(int $userId): bool
    {
        $token = ShopeeToken::getActiveForUser($userId);
        return $token !== null && ($token->isAccessTokenValid() || $token->isRefreshTokenValid());
    }

    /**
     * Desconecta a loja Shopee de um usuário.
     */
    public function disconnect(int $userId): void
    {
        ShopeeToken::where('user_id', $userId)->update(['is_active' => false]);

        Log::info('ShopeeAuthService: loja desconectada', ['user_id' => $userId]);
    }
}
