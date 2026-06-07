<?php

namespace App\Http\Controllers\MercadoLivre;

use App\Http\Controllers\Controller;
use App\Services\MercadoLivre\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Controller para gerenciar autenticação OAuth 2.0 com Mercado Livre
 */
class AuthController extends Controller
{
    /**
     * Service de autenticação
     */
    protected AuthService $authService;

    /**
     * Construtor
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redireciona o usuário para autorização no Mercado Livre
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        Log::info('=== ML AUTH REDIRECT START ===', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email ?? 'N/A',
        ]);

        try {
            // Verificar se credenciais estão configuradas
            Log::info('ML: Checking credentials...');
            $hasCredentials = $this->authService->hasCredentials();
            Log::info('ML: Has credentials?', ['result' => $hasCredentials]);
            
            if (!$hasCredentials) {
                Log::warning('ML: Credentials NOT configured');
                return redirect()
                    ->route('mercadolivre.settings')
                    ->with('error', 'Credenciais do Mercado Livre não configuradas. Entre em contato com o administrador.');
            }

            // Verificar se usuário já está conectado
            Log::info('ML: Checking if user is already connected...');
            $isConnected = $this->authService->isConnected(Auth::id());
            Log::info('ML: Is connected?', ['result' => $isConnected]);
            
            if ($isConnected) {
                Log::info('ML: User already connected');
                return redirect()
                    ->route('mercadolivre.settings')
                    ->with('info', 'Você já está conectado ao Mercado Livre.');
            }

            // Gerar URL de autorização
            Log::info('ML: Generating authorization URL...');
            $authorizationUrl = $this->authService->getAuthorizationUrl(Auth::id());
            Log::info('ML: Authorization URL generated', [
                'url' => $authorizationUrl,
            ]);

            Log::info('ML: Redirecting to Mercado Livre...');
            
            // Redirecionar para Mercado Livre
            return redirect()->away($authorizationUrl);

        } catch (Exception $e) {
            Log::error('=== ML AUTH REDIRECT ERROR ===', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('mercadolivre.settings')
                ->with('error', 'Erro ao conectar com Mercado Livre: ' . $e->getMessage());
        }
    }

    /**
     * Processa o callback de autorização do Mercado Livre
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        try {
            // Verificar se há erro retornado pelo ML
            if ($request->has('error')) {
                $error = $request->input('error');
                $errorDescription = $request->input('error_description', 'Autorização negada');

                Log::warning('ML authorization error', [
                    'error' => $error,
                    'description' => $errorDescription,
                ]);

                return redirect()
                    ->route('mercadolivre.settings')
                    ->with('warning', 'Autorização cancelada: ' . $errorDescription);
            }

            // Validar parâmetros
            $code = $request->input('code');
            $state = $request->input('state');

            if (!$code || !$state) {
                throw new Exception('Parâmetros de autorização inválidos');
            }

            // Processar callback e obter token
            $token = $this->authService->handleCallback($code, $state);

            Log::info('ML token obtained successfully', [
                'user_id' => $token->user_id,
                'ml_user_id' => $token->ml_user_id,
                'nickname' => $token->ml_nickname,
            ]);

            // Redirecionar com sucesso de volta para a página de configurações do ML
            return redirect()
                ->route('mercadolivre.settings')
                ->with('success', "Conectado com sucesso ao Mercado Livre! 🎉 Conta: {$token->ml_nickname}");

        } catch (Exception $e) {
            Log::error('ML callback processing failed', [
                'user_id' => Auth::id() ?? 'guest',
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('mercadolivre.settings')
                ->with('error', 'Erro ao processar autorização: ' . $e->getMessage());
        }
    }

    /**
     * Desconecta o usuário do Mercado Livre
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect()
    {
        try {
            $userId = Auth::id();
            
            // Verificar se está conectado
            if (!$this->authService->isConnected($userId)) {
                return redirect()
                    ->back()
                    ->with('info', 'Você não está conectado ao Mercado Livre.');
            }

            // Revogar token
            $success = $this->authService->revokeToken($userId);

            if ($success) {
                Log::info('User disconnected from ML', [
                    'user_id' => $userId,
                ]);

                return redirect()
                    ->back()
                    ->with('success', 'Desconectado do Mercado Livre com sucesso!');
            } else {
                throw new Exception('Falha ao revogar token');
            }

        } catch (Exception $e) {
            Log::error('Failed to disconnect from ML', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Erro ao desconectar: ' . $e->getMessage());
        }
    }

    /**
     * Retorna o status de conexão do usuário (AJAX)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        try {
            $userId = Auth::id();
            $isConnected = $this->authService->isConnected($userId);
            
            $data = [
                'connected' => $isConnected,
                'user_id' => $userId,
            ];

            if ($isConnected) {
                $token = $this->authService->getActiveToken($userId, false);
                
                if ($token) {
                    $data['ml_user_id'] = $token->ml_user_id;
                    $data['nickname'] = $token->ml_nickname;
                    $data['expires_at'] = $token->expires_at?->format('Y-m-d H:i:s');
                    $data['expires_in_hours'] = $token->expires_at?->diffInHours(now());
                    $data['needs_refresh'] = $token->needsRefresh();
                }
            }

            return response()->json($data);

        } catch (Exception $e) {
            Log::error('Failed to get ML status', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'connected' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Testa a conexão com o Mercado Livre (AJAX)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        try {
            $userId = Auth::id();
            $token = $this->authService->getActiveToken($userId);

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não conectado ao Mercado Livre',
                ], 401);
            }

            // Testar conexão
            $isValid = $this->authService->testConnection($token);

            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão válida!',
                    'nickname' => $token->ml_nickname,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido ou expirado',
                ], 401);
            }

        } catch (Exception $e) {
            Log::error('ML connection test failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage(),
            ], 500);
        }
    }
}
