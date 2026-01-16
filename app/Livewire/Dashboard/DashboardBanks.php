<?php

namespace App\Livewire\Dashboard;

use App\Models\Bank;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardBanks extends Component
{
    public $bancos = [];
    public $bancosInfo = [];
    public int $totalBancos = 0;
    public float $totalInvoicesBancos = 0;
    public float $saldoTotalBancos = 0;
    public float $totalSaidasBancos = 0;

    public function mount()
    {
        $this->loadBanksData();
    }

    private function loadBanksData()
    {
        $userId = Auth::id();
        $this->bancos = Bank::where('user_id', $userId)->get();
        $bankIds = $this->bancos->pluck('id_bank');

        // Busca todos os invoices de uma vez
        $allInvoices = Invoice::where('user_id', $userId)
            ->whereIn('id_bank', $bankIds)
            ->get()
            ->groupBy('id_bank');

        // Informações detalhadas de bancos e invoices
        $this->bancosInfo = $this->bancos->map(function ($bank) use ($allInvoices) {
            $invoices = $allInvoices->get($bank->id_bank, collect());
            $totalInvoices = $invoices->sum('value');
            $qtdInvoices = $invoices->count();
            $mediaInvoices = $qtdInvoices > 0 ? $totalInvoices / $qtdInvoices : 0;
            $maiorInvoice = $qtdInvoices > 0 ? $invoices->sortByDesc('value')->first() : null;
            $menorInvoice = $qtdInvoices > 0 ? $invoices->sortBy('value')->first() : null;

            return [
                'id_bank' => $bank->id_bank,
                'nome' => $bank->name,
                'descricao' => $bank->description,
                'total_invoices' => $totalInvoices,
                'qtd_invoices' => $qtdInvoices,
                'media_invoices' => $mediaInvoices,
                'maior_invoice' => $maiorInvoice,
                'menor_invoice' => $menorInvoice,
                'saldo' => -$totalInvoices,
                'saidas' => $totalInvoices,
            ];
        })->toArray();


        // Totais gerais de bancos
        $this->totalBancos = $this->bancos->count();
        $this->totalInvoicesBancos = collect($this->bancosInfo)->sum('total_invoices');
        $this->saldoTotalBancos = collect($this->bancosInfo)->sum('saldo');
        $this->totalSaidasBancos = collect($this->bancosInfo)->sum('saidas');
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-banks');
    }
}
