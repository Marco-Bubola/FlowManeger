<?php

namespace App\Livewire\Shopee;

use App\Models\ShopeeToken;
use App\Services\Shopee\AuthService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Página de configurações da integração com a Shopee.
 * Permite conectar/desconectar a loja e visualizar status da autenticação.
 */
class Settings extends Component
{
    use HasNotifications;

    public bool $isConnected = false;
    public ?ShopeeToken $token = null;
    public array $shopInfo = [];
    public ?string $expiresAt = null;
    public bool $isLoading = false;

    public function mount(): void
    {
        // Capturar mensagens de flash de redirects
        if (session()->has('success')) $this->notifySuccess(session('success'));
        if (session()->has('error'))   $this->notifyError(session('error'));
        if (session()->has('info'))    $this->notifyInfo(session('info'));

        $this->checkConnection();
    }

    public function checkConnection(): void
    {
        $this->token = ShopeeToken::getActiveForUser(Auth::id());

        if ($this->token) {
            $this->isConnected = true;
            $this->shopInfo    = $this->token->shop_info ?? [];
            $this->expiresAt   = $this->token->expires_at?->format('d/m/Y H:i');
        } else {
            $this->isConnected = false;
            $this->shopInfo    = [];
            $this->expiresAt   = null;
        }
    }

    /**
     * Retorna a URL de autorização e redireciona o usuário para a Shopee.
     */
    public function connect(): mixed
    {
        try {
            $authService = app(AuthService::class);
            $url = $authService->getAuthorizationUrl(Auth::id());
            return redirect()->away($url);
        } catch (\Exception $e) {
            $this->notifyError('Erro ao conectar: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Remove a conexão da loja Shopee.
     */
    public function disconnect(): void
    {
        try {
            $authService = app(AuthService::class);
            $authService->disconnect(Auth::id());
            $this->checkConnection();
            $this->notifySuccess('Loja Shopee desconectada com sucesso.');
        } catch (\Exception $e) {
            $this->notifyError('Erro ao desconectar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.shopee.settings');
    }
}
