<?php

namespace App\Livewire\Quotes;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\Quote;
use App\Services\Quotes\QuoteService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Lista de orçamentos (estilo Vendas): abas por situação, busca, tipo e números do topo. */
#[Layout('components.layouts.app')]
class QuotesIndex extends Component
{
    use WithPagination, WithInfiniteScroll;

    #[Url(as: 'ver', except: 'abertos')]
    public string $tab = 'abertos';

    #[Url(as: 'tipo', except: '')]
    public string $kind = '';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function setTab(string $tab): void
    {
        $this->tab = $tab === 'todos' || $tab === 'abertos' || isset(Quote::STATUSES[$tab]) ? $tab : 'abertos';
        $this->resetPage();
    }

    public function setKind(string $kind): void
    {
        $this->kind = $this->kind === $kind ? '' : $kind;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /** Tocou no WhatsApp do card: conta como enviado. */
    public function markSent(int $id): void
    {
        if ($q = Quote::where('user_id', Auth::id())->find($id)) {
            QuoteService::markSent($q, 'pelo WhatsApp');
        }
    }

    public function duplicate(int $id)
    {
        $src = Quote::where('user_id', Auth::id())->findOrFail($id);
        $copy = QuoteService::make(Auth::id(), collect($src->only([
            'client_id', 'contact_name', 'contact_phone', 'kind', 'title', 'items', 'services', 'pickup', 'discount', 'delivery_fee',
            'production_days', 'payment_terms', 'client_notes', 'admin_notes', 'product_id',
        ]))->all() + ['status' => 'rascunho', 'source' => 'app']);
        $copy->addEvent('criado', 'Cópia do ' . $src->code());
        QuoteService::recalc($copy)->save();

        return $this->redirectRoute('orcamentos.edit', $copy->id, navigate: true);
    }

    public function delete(int $id): void
    {
        if ($q = Quote::where('user_id', Auth::id())->find($id)) {
            $q->delete();
            $this->dispatch('notify', type: 'success', message: $q->code() . ' apagado.');
        }
    }

    public function render()
    {
        $uid = Auth::id();
        $base = Quote::where('user_id', $uid);
        if ($this->kind !== '') {
            $base->where('kind', $this->kind);
        }
        if (($s = trim($this->search)) !== '') {
            $num = (int) preg_replace('/\D+/', '', $s);
            $base->where(function ($w) use ($s, $num) {
                $w->where('title', 'like', "%$s%")->orWhere('contact_name', 'like', "%$s%")->orWhere('items', 'like', "%$s%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));
                if ($num > 0) {
                    $w->orWhere('number', $num);
                }
            });
        }

        $counts = (clone $base)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status')->all();
        $counts['abertos'] = collect(Quote::OPEN)->sum(fn ($st) => $counts[$st] ?? 0);
        $counts['todos'] = array_sum(array_intersect_key($counts, Quote::STATUSES));

        $list = clone $base;
        if ($this->tab === 'abertos') {
            $list->whereIn('status', Quote::OPEN);
        } elseif ($this->tab !== 'todos') {
            $list->where('status', $this->tab);
        }
        // Pedido novo e aprovado (ação sua) primeiro; depois os mais recentes.
        $list->orderByRaw("CASE status WHEN 'pedido' THEN 0 WHEN 'aprovado' THEN 1 ELSE 2 END")->orderByDesc('updated_at');
        $quotes = $this->infinitePaginate($list->with('client:id,name,phone'), 18);

        $monthStart = Carbon::now(QuoteService::TZ)->startOfMonth();
        $all = Quote::where('user_id', $uid);
        $openValue = (clone $all)->whereIn('status', ['rascunho', 'enviado', 'aprovado'])->sum('total');
        $sentMonth = (clone $all)->where('sent_at', '>=', $monthStart)->count();
        $wonMonth = (clone $all)->where('status', 'convertido')->where('updated_at', '>=', $monthStart)->count();
        $wonValue = (clone $all)->where('status', 'convertido')->where('updated_at', '>=', $monthStart)->sum('total');

        return view('livewire.quotes.quotes-index', [
            'quotes' => $quotes,
            'counts' => $counts,
            'openValue' => (float) $openValue,
            'conversion' => $sentMonth > 0 ? round($wonMonth / $sentMonth * 100) : null,
            'wonMonth' => $wonMonth,
            'wonValue' => (float) $wonValue,
            'waiting' => ($counts['pedido'] ?? 0) + ($counts['aprovado'] ?? 0),
        ])->title('Orçamentos');
    }
}
