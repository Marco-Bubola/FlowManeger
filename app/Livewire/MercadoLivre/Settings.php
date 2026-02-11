<?php

namespace App\Livewire\MercadoLivre;

use App\Services\MercadoLivre\AuthService;
use App\Models\MercadoLivreToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Componente de configurações do Mercado Livre
 * 
 * Permite ao usuário:
 * - Conectar conta do Mercado Livre
 * - Visualizar status da conexão
 * - Desconectar
 * - Ver informações do vendedor
 */
class Settings extends Component
{
    /**
     * Token ativo do usuário
     */
    public ?MercadoLivreToken $token = null;

    /**
     * Status de conexão
     */
    public bool $isConnected = false;

    /**
     * Informações do usuário ML
     */
    public array $userInfo = [];

    /**
     * Dados de expiração
     */
    public ?string $expiresAt = null;
    public ?int $expiresInHours = null;
    public bool $needsRefresh = false;

    /**
     * Loading states
     */
    public bool $isLoading = false;
    public bool $isTesting = false;

    /**
     * Listeners
     */
    protected $listeners = ['refreshStatus' => 'checkConnection'];

    /**
     * Método invocável necessário para uso direto em rotas
     */
    public function __invoke()
    {
        return $this->render();
    }

    /**
     * Mount
     */
    public function mount(): void
    {
        // Capturar mensagens flash da sessão (de redirects de controllers)
        if (session()->has('error')) {
            $errorMessage = session('error');
            Log::error('ML Settings: Error message from session', ['message' => $errorMessage]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $errorMessage,
            ]);
        }
        
        if (session()->has('info')) {
            $infoMessage = session('info');
            Log::info('ML Settings: Info message from session', ['message' => $infoMessage]);
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => $infoMessage,
            ]);
        }
        
        if (session()->has('success')) {
            $successMessage = session('success');
            Log::info('ML Settings: Success message from session', ['message' => $successMessage]);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $successMessage,
            ]);
        }
        
        $this->checkConnection();
    }

    /**
     * Verifica o status de conexão.
     * Usa autoRefresh=true para que, ao abrir a página, um token expirado seja renovado automaticamente quando possível.
     */
    public function checkConnection(): void
    {
        try {
            $authService = app(AuthService::class);
            $userId = Auth::id();
            // Tenta obter token ativo e, se expirado ou próximo de expirar, já tenta renovar
            $this->token = $authService->getActiveToken($userId, true);
            $this->isConnected = $this->token !== null;

            if ($this->token) {
                $this->userInfo = $this->token->user_info ?? [];
                $this->expiresAt = $this->token->expires_at?->format('d/m/Y H:i');
                $this->expiresInHours = $this->token->expires_at->isPast() ? 0 : (int) $this->token->expires_at->diffInHours(now());
                $this->needsRefresh = $this->token->needsRefresh();
            } else {
                $this->reset(['token', 'userInfo', 'expiresAt', 'expiresInHours', 'needsRefresh']);
            }
        } catch (\Exception $e) {
            $this->isConnected = false;
            $this->token = null;
            $this->reset(['userInfo', 'expiresAt', 'expiresInHours', 'needsRefresh']);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao verificar status: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Redireciona para autorização do Mercado Livre
     */
    public function connect(): void
    {
        $this->isLoading = true;
        $this->redirect(route('mercadolivre.auth.redirect'), navigate: false);
    }

    /**
     * Desconecta do Mercado Livre
     */
    public function disconnect(): void
    {
        try {
            $authService = app(AuthService::class);
            $success = $authService->revokeToken(Auth::id());

            if ($success) {
                $this->checkConnection();
                
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Desconectado do Mercado Livre com sucesso!',
                ]);
            } else {
                throw new \Exception('Falha ao revogar token');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao desconectar: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Testa a conexão atual
     */
    public function testConnection(): void
    {
        $this->isTesting = true;

        try {
            if (!$this->token) {
                throw new \Exception('Token não encontrado');
            }

            $authService = app(AuthService::class);
            $isValid = $authService->testConnection($this->token);

            if ($isValid) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => '✅ Conexão válida! Tudo funcionando corretamente.',
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => '❌ Token inválido ou expirado. Reconecte sua conta.',
                ]);
            }

            $this->checkConnection();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao testar conexão: ' . $e->getMessage(),
            ]);
        } finally {
            $this->isTesting = false;
        }
    }

    /**
     * Renovar token manualmente
     */
    public function refreshToken(): void
    {
        try {
            if (!$this->token) {
                throw new \Exception('Token não encontrado');
            }

            $authService = app(AuthService::class);
            $newToken = $authService->refreshToken($this->token);

            $this->token = $newToken;
            $this->checkConnection();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => '✅ Token renovado com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao renovar token: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Render
     */
    public function render()
    {
        return view('livewire.mercadolivre.settings');
    }
}
