<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ClientFaturas extends Component
{
    use WithPagination;
    
    public Client $client;
    public $clienteId;
    
    public function mount($cliente)
    {
        $this->clienteId = $cliente;
        $this->client = Client::where('user_id', Auth::id())->findOrFail($cliente);
    }
    
    public function toggleDividida($invoiceId)
    {
        $invoice = Invoice::whereHas('category', function($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($invoiceId);
        
        $invoice->dividida = !$invoice->dividida;
        $invoice->save();
        
        session()->flash('success', 'Fatura atualizada com sucesso!');
    }
    
    public function render()
    {
        $faturas = Invoice::where('client_id', $this->clienteId)
            ->select('id_invoice as id', 'invoice_date', 'description', 'value', 'category_id', 'dividida')
            ->with(['category:id_category,name,icone,hexcolor_category'])
            ->whereHas('category', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('invoice_date', 'desc')
            ->paginate(12);
            
        // Totais de todas as faturas do cliente (não só da página aberta)
        $todas = Invoice::where('client_id', $this->clienteId)
            ->whereHas('category', fn ($q) => $q->where('user_id', Auth::id()))
            ->get(['value', 'dividida']);

        return view('livewire.clients.client-faturas', [
            'faturas' => $faturas,
            'totalOriginal' => (float) $todas->sum('value'),
            'totalCliente' => (float) $todas->sum(fn ($f) => $f->dividida ? $f->value / 2 : $f->value),
            'totalDivididas' => $todas->where('dividida', true)->count(),
        ]);
    }
}
