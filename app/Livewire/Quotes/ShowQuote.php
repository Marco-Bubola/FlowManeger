<?php

namespace App\Livewire\Quotes;

use App\Models\Quote;
use App\Services\Quotes\QuoteService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Ver orçamento: o que o cliente vê, o custo (só seu), a linha do tempo e as ações. */
#[Layout('components.layouts.app')]
class ShowQuote extends Component
{
    public int $quoteId;
    public string $rejectReason = '';

    public function mount(int $quote): void
    {
        $this->quoteId = Quote::where('user_id', Auth::id())->findOrFail($quote)->id;
    }

    protected function quote(): Quote
    {
        return Quote::where('user_id', Auth::id())->with('client')->findOrFail($this->quoteId);
    }

    public function markSent(string $how = 'pelo WhatsApp'): void
    {
        QuoteService::markSent($this->quote(), in_array($how, ['pelo WhatsApp', 'por link', 'em PDF'], true) ? $how : 'pelo WhatsApp');
    }

    public function linkCopied(): void
    {
        $q = $this->quote();
        if (in_array($q->status, ['pedido', 'rascunho'], true)) {
            QuoteService::markSent($q, 'por link');
        }
        $this->dispatch('notify', type: 'success', message: 'Link copiado. Cole no WhatsApp, Instagram ou e-mail.');
    }

    public function approve(): void
    {
        QuoteService::approve($this->quote(), 'loja');
        $this->dispatch('notify', type: 'success', message: 'Marcado como aprovado.');
    }

    public function reject(): void
    {
        QuoteService::reject($this->quote(), 'loja', $this->rejectReason);
        $this->rejectReason = '';
        $this->dispatch('notify', type: 'success', message: 'Marcado como recusado.');
    }

    public function reopen(): void
    {
        $q = $this->quote();
        $q->status = $q->sent_at ? 'enviado' : 'rascunho';
        $q->rejected_at = null;
        $q->addEvent('reaberto', 'Reaberto');
        $q->save();
    }

    public function renew(): void
    {
        $q = $this->quote();
        $days = QuoteService::settings(Auth::user())['validity_days'];
        $q->valid_until = Carbon::today(QuoteService::TZ)->addDays($days);
        if ($q->status === 'vencido') {
            $q->status = $q->sent_at ? 'enviado' : 'rascunho';
        }
        $q->addEvent('renovado', 'Validade renovada até ' . $q->valid_until->format('d/m/Y'));
        $q->save();
        $this->dispatch('notify', type: 'success', message: 'Válido até ' . $q->valid_until->format('d/m/Y') . '.');
    }

    public function convert()
    {
        $q = $this->quote();
        if (!in_array($q->status, ['aprovado', 'enviado', 'rascunho'], true) && !$q->sale_id) {
            $this->dispatch('notify', type: 'warning', message: 'Este orçamento não pode virar venda agora.');

            return null;
        }
        if (!$q->items) {
            $this->dispatch('notify', type: 'warning', message: 'Coloque pelo menos um item antes.');

            return null;
        }
        $sale = QuoteService::convertToSale($q);
        session()->flash('success', 'Orçamento ' . $q->code() . ' virou a venda #' . $sale->id . '.');

        return $this->redirectRoute('sales.show', $sale->id, navigate: true);
    }

    public function delete()
    {
        $q = $this->quote();
        $q->delete();
        session()->flash('success', $q->code() . ' apagado.');

        return $this->redirectRoute('orcamentos.index', navigate: true);
    }

    public function render()
    {
        $q = $this->quote();

        return view('livewire.quotes.show-quote', [
            'quote' => $q,
            'wa' => QuoteService::whatsappUrl($q),
            'stale' => QuoteService::staleCosts($q),
            'settings' => QuoteService::settings(Auth::user()),
        ])->title($q->code() . ' · ' . $q->displayTitle());
    }
}
