<?php

namespace App\Console\Commands;

use App\Models\PortalPayment;
use App\Services\Payments\PortalCheckoutService;
use Illuminate\Console\Command;

/**
 * Rede de segurança do Pix do portal: se o aviso (webhook) do Mercado Pago se perder,
 * consulta os pagamentos em aberto e aplica o resultado (venda paga, estoque, aviso).
 */
class SyncPortalPayments extends Command
{
    protected $signature = 'payments:sync';

    protected $description = 'Confere no Mercado Pago os Pix do portal que ainda estão em aberto';

    public function handle(PortalCheckoutService $checkout): int
    {
        $done = 0;
        PortalPayment::whereIn('status', ['pending', 'in_process', 'authorized'])
            ->whereNotNull('provider_id')
            ->where('created_at', '>=', now()->subDays(3))
            ->orderBy('id')
            ->each(function (PortalPayment $payment) use ($checkout, &$done) {
                try {
                    $before = $payment->status;
                    $after = $checkout->refresh($payment)->status;
                    if ($after !== $before) {
                        $done++;
                    }
                } catch (\Throwable $e) {
                    $this->warn("Pix {$payment->id}: {$e->getMessage()}");
                }
            });

        // Pix que nem chegou a ser criado no Mercado Pago (erro de rede no meio).
        PortalPayment::where('status', 'creating')->where('created_at', '<', now()->subMinutes(15))
            ->update(['status' => 'error', 'status_detail' => 'não criado']);

        $this->info("Pix atualizados: {$done}");

        return self::SUCCESS;
    }
}
