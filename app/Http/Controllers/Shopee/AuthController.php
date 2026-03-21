<?php

namespace App\Http\Controllers\Shopee;

use App\Http\Controllers\Controller;
use App\Services\Shopee\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller de autenticação OAuth 2.0 da Shopee.
 *
 * Fluxo:
 * GET  /shopee/auth/connect  → redireciona para URL de autorização da Shopee
 * GET  /shopee/auth/callback → recebe o code + shop_id e troca por tokens
 * POST /shopee/auth/disconnect → remove a conexão da loja
 */
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Inicia o fluxo OAuth — redireciona o usuário para a página de autorização da Shopee.
     */
    public function connect(): RedirectResponse
    {
        try {
            $url = $this->authService->getAuthorizationUrl(Auth::id());
            return redirect()->away($url);
        } catch (\Exception $e) {
            Log::error('ShopeeAuthController: erro ao gerar URL de autorização', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);
            return redirect()->route('shopee.settings')
                ->with('error', 'Erro ao conectar com a Shopee: ' . $e->getMessage());
        }
    }

    /**
     * Callback após autorização na Shopee — troca code por tokens.
     */
    public function callback(Request $request): RedirectResponse
    {
        $code    = $request->query('code');
        $shopId  = $request->query('shop_id');
        $state   = $request->query('state');

        // Validar parâmetros obrigatórios
        if (!$code || !$shopId) {
            return redirect()->route('shopee.settings')
                ->with('error', 'Parâmetros inválidos no callback da Shopee.');
        }

        try {
            $token = $this->authService->handleCallback($code, $shopId, $state ?? '');

            $shopName = $token->shop_name ?? $shopId;
            return redirect()->route('shopee.settings')
                ->with('success', "Shopee conectada com sucesso! Loja: {$shopName}");

        } catch (\Exception $e) {
            Log::error('ShopeeAuthController: erro no callback', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);
            return redirect()->route('shopee.settings')
                ->with('error', 'Erro ao conectar com a Shopee: ' . $e->getMessage());
        }
    }

    /**
     * Desconecta a loja Shopee do usuário.
     */
    public function disconnect(Request $request): RedirectResponse
    {
        try {
            $this->authService->disconnect(Auth::id());

            return redirect()->route('shopee.settings')
                ->with('success', 'Loja Shopee desconectada com sucesso.');

        } catch (\Exception $e) {
            return redirect()->route('shopee.settings')
                ->with('error', 'Erro ao desconectar: ' . $e->getMessage());
        }
    }
}
