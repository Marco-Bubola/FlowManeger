<?php

namespace App\Livewire\MercadoLivre;

use App\Services\MercadoLivre\OrderService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ShowOrder extends Component
{
    use HasNotifications;

    public string $orderId;
    public ?array $order = null;
    public bool $loading = true;
    public bool $importing = false;
    public ?string $errorMessage = null;

    // Seções expandidas
    public bool $showShipping = true;
    public bool $showPayments = true;
    public bool $showFeedback = false;

    public function mount(string $orderId): void
    {
        $this->orderId = $orderId;
        $this->loadOrder();
    }

    public function loadOrder(): void
    {
        $this->loading = true;
        $this->errorMessage = null;

        try {
            $service = app(OrderService::class);
            $this->order = $service->getOrderDetails($this->orderId);

            if (!$this->order) {
                $this->errorMessage = 'Pedido não encontrado ou sem permissão de acesso.';
            }
        } catch (\Exception $e) {
            Log::error('ShowOrder: erro ao carregar pedido', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);
            $this->errorMessage = 'Erro ao carregar dados do pedido. Tente novamente.';
        } finally {
            $this->loading = false;
        }
    }

    public function importOrder(): void
    {
        if ($this->importing) {
            return;
        }

        $this->importing = true;

        try {
            $service = app(OrderService::class);
            $result  = $service->importOrder($this->orderId);

            if ($result['success'] ?? false) {
                $this->notifySuccess($result['message'] ?? 'Pedido importado com sucesso!');
            } else {
                $this->notifyError($result['message'] ?? 'Erro ao importar o pedido.');
            }
        } catch (\Exception $e) {
            Log::error('ShowOrder: erro ao importar', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);
            $this->notifyError('Erro inesperado ao importar o pedido.');
        } finally {
            $this->importing = false;
        }
    }

    public function getStatusBadge(string $status): array
    {
        return match ($status) {
            'confirmed'           => ['text' => 'Confirmado',         'color' => 'emerald'],
            'payment_required'    => ['text' => 'Ag. Pagamento',      'color' => 'yellow'],
            'payment_in_process'  => ['text' => 'Pag. em Processo',   'color' => 'blue'],
            'paid'                => ['text' => 'Pago',               'color' => 'green'],
            'fulfilled'           => ['text' => 'Entregue',           'color' => 'emerald'],
            'partially_fulfilled' => ['text' => 'Parcialmente Entregue', 'color' => 'blue'],
            'cancelled'           => ['text' => 'Cancelado',          'color' => 'red'],
            default               => ['text' => ucfirst($status),     'color' => 'gray'],
        };
    }

    public function formatPrice(float $price): string
    {
        return 'R$ ' . number_format($price, 2, ',', '.');
    }

    public function formatDate(string $date): string
    {
        try {
            return \Carbon\Carbon::parse($date)
                ->setTimezone(config('app.timezone', 'America/Sao_Paulo'))
                ->format('d/m/Y H:i');
        } catch (\Exception) {
            return $date;
        }
    }

    public function render()
    {
        return view('livewire.mercadolivre.show-order')
            ->layout('components.layouts.app', [
                'title' => 'Pedido #' . $this->orderId . ' – Mercado Livre',
            ]);
    }
}
