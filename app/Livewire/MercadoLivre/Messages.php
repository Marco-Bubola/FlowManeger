<?php

namespace App\Livewire\MercadoLivre;

use App\Services\MercadoLivre\MessageService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Messages extends Component
{
    use HasNotifications;

    // Conversa selecionada (identificada pelo pack_id)
    public string  $packId       = '';
    public string  $packIdInput  = '';
    public array   $messages     = [];
    public bool    $loading      = false;
    public ?int    $sellerId     = null; // ml_user_id do vendedor logado
    public ?int    $buyerId      = null; // comprador da conversa (detectado pelas mensagens)

    // Nova mensagem
    public string  $newMessage   = '';
    public bool    $sending      = false;

    // UI
    public bool    $tipsOpen     = false;

    public function mount(string $packId = ''): void
    {
        if ($packId) {
            $this->packId      = $packId;
            $this->packIdInput = $packId;
            $this->loadMessages();
        }
    }

    public function loadMessages(): void
    {
        if (empty($this->packId)) {
            return;
        }

        $this->loading = true;

        try {
            $service = app(MessageService::class);
            $result  = $service->getMessages($this->packId);

            if ($result['success']) {
                $this->messages = $result['messages'];
                $this->sellerId = $result['seller_id'] ?? $this->sellerId;
                $this->buyerId  = $this->detectBuyerId($this->messages);
            } else {
                $this->notifyError($result['message'] ?? 'Erro ao carregar mensagens.');
                $this->messages = [];
            }
        } catch (\Exception $e) {
            Log::error('Messages::loadMessages', ['error' => $e->getMessage()]);
            $this->notifyError('Erro ao carregar mensagens.');
        } finally {
            $this->loading = false;
        }
    }

    public function searchPack(): void
    {
        $packId = trim($this->packIdInput);
        if (empty($packId)) {
            $this->notifyError('Digite um Pack ID ou Order ID.');
            return;
        }

        $this->packId  = $packId;
        $this->buyerId = null;
        $this->loadMessages();
    }

    public function sendMessage(): void
    {
        $this->validate(['newMessage' => 'required|min:1|max:2000']);

        if ($this->sending || empty($this->packId)) {
            return;
        }

        $this->sending = true;

        try {
            $service = app(MessageService::class);
            $result  = $service->sendMessage($this->packId, $this->newMessage, $this->buyerId);

            if ($result['success']) {
                $this->notifySuccess($result['message']);
                $this->newMessage = '';
                $this->loadMessages();
            } else {
                $this->notifyError($result['message']);
            }
        } catch (\Exception $e) {
            $this->notifyError('Erro ao enviar mensagem.');
        } finally {
            $this->sending = false;
        }
    }

    /**
     * Detecta o comprador a partir das mensagens: o participante que não é o vendedor.
     */
    protected function detectBuyerId(array $messages): ?int
    {
        foreach ($messages as $msg) {
            $from = (int)($msg['from']['user_id'] ?? 0);
            $to   = (int)($msg['to']['user_id'] ?? ($msg['to'][0]['user_id'] ?? 0));
            if ($from && $from !== (int)$this->sellerId) {
                return $from;
            }
            if ($to && $to !== (int)$this->sellerId) {
                return $to;
            }
        }
        return null;
    }

    public function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse($date)
                ->setTimezone('America/Sao_Paulo')
                ->format('d/m H:i');
        } catch (\Exception) {
            return $date;
        }
    }

    public function render()
    {
        if ($this->sellerId === null) {
            $token = auth()->check()
                ? (new \App\Services\MercadoLivre\AuthService())->getActiveToken(auth()->id(), false)
                : null;
            $this->sellerId = $token?->ml_user_id ? (int)$token->ml_user_id : null;
        }

        return view('livewire.mercadolivre.messages', [
                'sellerMlUserId' => $this->sellerId,
            ])
            ->layout('components.layouts.app', [
                'title' => 'Mensagens – Mercado Livre',
            ]);
    }
}
