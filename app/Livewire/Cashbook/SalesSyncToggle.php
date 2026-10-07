<?php

namespace App\Livewire\Cashbook;

use App\Services\Cashbook\SalePaymentCashbookSync;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SalesSyncToggle extends Component
{
    public bool $enabled = false;

    public function mount(): void
    {
        $this->enabled = SalePaymentCashbookSync::enabledFor(Auth::user());
    }

    public function updatedEnabled(bool $value): void
    {
        SalePaymentCashbookSync::setEnabled(Auth::user(), $value);

        $this->dispatch('notify', type: 'success', message: $value
            ? 'Pagamentos de vendas novos vão entrar aqui como receita.'
            : 'Pagamentos de vendas não entram mais no livro-caixa.');
    }

    public function render()
    {
        return <<<'BLADE'
            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 px-4 py-2.5 cursor-pointer select-none">
                <input type="checkbox" wire:model.live="enabled" class="w-5 h-5 rounded border-slate-400 text-emerald-600 focus:ring-emerald-500">
                <span class="text-sm text-slate-700 dark:text-slate-200">
                    <i class="bi bi-bag-check text-emerald-500 mr-1"></i>
                    Lançar pagamentos de vendas aqui automaticamente
                    <span class="block text-xs text-slate-500 dark:text-slate-400">Cada pagamento recebido vira uma receita na categoria "Vendas". Vale só para pagamentos novos.</span>
                </span>
            </label>
        BLADE;
    }
}
