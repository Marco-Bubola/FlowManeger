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

        $this->packId = $packId;
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
            $result  = $service->sendMessage($this->packId, $this->newMessage);

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

    public function formatDate(string $date): string
    {
        try {
            return \Carbon\Carbon::parse($date)
                ->setTimezone(config('app.timezone', 'America/Sao_Paulo'))
                ->format('d/m H:i');
        } catch (\Exception) {
            return $date;
        }
    }

    public function render()
    {
        return view('livewire.mercadolivre.messages')
            ->layout('components.layouts.app', [
                'title' => 'Mensagens – Mercado Livre',
            ]);
    }
}
