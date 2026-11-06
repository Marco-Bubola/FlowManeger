<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\ClientNote;
use App\Models\ClientProductAlert;
use App\Models\ClientQuoteRequest;
use App\Models\ClientTask;
use App\Models\PaymentReminder;
use App\Models\PortalCart;
use App\Models\PortalReview;
use App\Models\PromotionSend;
use App\Models\Sale;
use App\Models\SalePayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Linha do tempo do cliente: junta, em ordem de data, tudo o que aconteceu com
 * ele (vendas, pagamentos, pedidos do site, promoções e cobranças enviadas,
 * avaliações, avise-me, carrinho esquecido) e as anotações de contato.
 *
 * Cada item: at, group, icon, tone, title, detail, value, url, note_id, task_id.
 */
class ClientTimeline
{
    /** Filtros da tela (chave => rótulo). */
    public const GROUPS = [
        'todos' => 'Tudo',
        'contatos' => 'Contatos',
        'vendas' => 'Vendas',
        'site' => 'Site',
        'mensagens' => 'Mensagens',
    ];

    public function for(Client $client, int $userId, string $group = 'todos', int $limit = 60): Collection
    {
        $sources = [
            'contatos' => ['notes', 'tasks', 'deals'],
            'vendas' => ['sales', 'payments'],
            'site' => ['quotes', 'reviews', 'alerts', 'carts'],
            'mensagens' => ['promotions', 'reminders'],
        ];
        $wanted = $group === 'todos' || ! isset($sources[$group])
            ? array_merge(...array_values($sources))
            : $sources[$group];

        $items = collect();
        foreach ($wanted as $source) {
            try {
                $items = $items->merge($this->{$source}($client, $userId, $limit));
            } catch (\Throwable $e) {
                // Base antiga sem a tabela/coluna: o histórico segue sem essa parte.
                Log::debug('ClientTimeline: fonte ignorada', ['source' => $source, 'error' => $e->getMessage()]);
            }
        }

        if ($group === 'todos' && $client->created_at) {
            $items->push($this->item($client->created_at, 'contatos', 'bi-person-plus', 'sky', 'Virou cliente', 'Cadastro criado'));
        }

        return $items->sortByDesc(fn ($i) => $i['at']->getTimestamp())->take($limit)->values();
    }

    /** Números para o topo da aba. */
    public function stats(Client $client, int $userId): array
    {
        $lastContact = ClientNote::where('user_id', $userId)->where('client_id', $client->id)->max('happened_at');
        $lastSale = Sale::where('client_id', $client->id)->max('created_at');

        return [
            'notes' => ClientNote::where('user_id', $userId)->where('client_id', $client->id)->count(),
            'open_tasks' => ClientTask::ownedBy($userId)->open()->where('client_id', $client->id)->count(),
            'last_contact' => $lastContact ? Carbon::parse($lastContact) : null,
            'last_sale' => $lastSale ? Carbon::parse($lastSale) : null,
        ];
    }

    protected function item(Carbon $at, string $group, string $icon, string $tone, string $title, ?string $detail = null, array $extra = []): array
    {
        return array_merge([
            'at' => $at,
            'group' => $group,
            'icon' => $icon,
            'tone' => $tone,
            'title' => $title,
            'detail' => $detail,
            'value' => null,
            'url' => null,
            'note_id' => null,
            'task_id' => null,
            'kind' => null,
            'no_time' => false,
        ], $extra);
    }

    protected function url(string $name, $param): ?string
    {
        return Route::has($name) ? route($name, $param) : null;
    }

    protected function notes(Client $client, int $userId, int $limit): Collection
    {
        return ClientNote::where('user_id', $userId)->where('client_id', $client->id)
            ->latest('happened_at')->limit($limit)->get()
            ->map(function (ClientNote $n) {
                $k = $n->kindInfo();

                return $this->item($n->happened_at, 'contatos', $k['icon'], 'indigo', $k['label'], $n->body,
                    ['note_id' => $n->id, 'kind' => $n->kind]);
            });
    }

    protected function tasks(Client $client, int $userId, int $limit): Collection
    {
        return ClientTask::ownedBy($userId)->where('client_id', $client->id)
            ->latest('due_date')->limit($limit)->get()
            ->map(function (ClientTask $t) {
                $done = (bool) $t->done_at;
                $at = $done ? $t->done_at : $t->dueAt();

                return $this->item($at, 'contatos', $done ? 'bi-check2-circle' : 'bi-alarm', $done ? 'emerald' : ($t->isLate() ? 'rose' : 'amber'),
                    $done ? 'Tarefa feita' : 'Tarefa: ' . $t->dueLabel(), $t->title, ['task_id' => $t->id, 'kind' => $done ? 'feita' : 'aberta', 'no_time' => ! $done && ! $t->due_time]);
            });
    }

    /** Negócios do funil: quando entrou e quando fechou. */
    protected function deals(Client $client, int $userId, int $limit): Collection
    {
        $out = collect();
        $money = fn ($v) => $v ? ' · R$ ' . number_format((float) $v, 2, ',', '.') : '';
        \App\Models\CrmDeal::ownedBy($userId)->where('client_id', $client->id)->latest()->limit($limit)->get()
            ->each(function (\App\Models\CrmDeal $d) use ($out, $money) {
                $url = $this->url('crm.funnel', ['q' => $d->title]);
                $out->push($this->item($d->created_at, 'contatos', 'bi-funnel', 'violet', 'Negócio no funil', $d->title . $money($d->value), ['url' => $url]));
                if ($d->closed_at && $d->stage === 'ganho') {
                    $out->push($this->item($d->closed_at, 'contatos', 'bi-trophy', 'emerald', 'Negócio ganho', $d->title . $money($d->value), ['url' => $url]));
                } elseif ($d->closed_at && $d->stage === 'perdido') {
                    $out->push($this->item($d->closed_at, 'contatos', 'bi-x-circle', 'rose', 'Negócio perdido',
                        $d->title . ' · ' . (\App\Models\CrmDeal::LOST_REASONS[$d->lost_reason] ?? 'sem motivo'), ['url' => $url]));
                }
            });

        return $out;
    }

    protected function sales(Client $client, int $userId, int $limit): Collection
    {
        $labels = ['pendente' => 'pendente', 'orcamento' => 'orçamento', 'confirmada' => 'confirmada', 'concluida' => 'concluída', 'cancelada' => 'cancelada', 'pago' => 'paga'];

        return Sale::where('client_id', $client->id)->latest('created_at')->limit($limit)
            ->withCount('saleItems')->get()
            ->map(fn (Sale $s) => $this->item(Carbon::parse($s->created_at), 'vendas', 'bi-cart-check', $s->status === 'cancelada' ? 'slate' : 'emerald',
                'Venda #' . $s->id,
                trim(($s->sale_items_count ? $s->sale_items_count . ($s->sale_items_count == 1 ? ' item' : ' itens') : '') . ' · ' . ($labels[$s->status] ?? $s->status) . ($s->source === 'portal' ? ' · pelo site' : ''), ' ·'),
                ['value' => (float) $s->total_price, 'url' => $this->url('sales.show', $s->id)]));
    }

    protected function payments(Client $client, int $userId, int $limit): Collection
    {
        return SalePayment::whereHas('sale', fn ($q) => $q->where('client_id', $client->id))
            ->latest('payment_date')->limit($limit)->get()
            ->map(fn (SalePayment $p) => $this->item($p->payment_date ? Carbon::parse(substr((string) $p->payment_date, 0, 10) . ' 12:00', \App\Models\ConsortiumNotification::DISPLAY_TZ) : Carbon::parse($p->created_at), 'vendas', 'bi-cash-coin', 'emerald',
                'Pagamento recebido', 'Venda #' . $p->sale_id . ($p->payment_method ? ' · ' . $p->payment_method : ''),
                ['value' => (float) $p->amount_paid, 'url' => $this->url('sales.show', $p->sale_id), 'no_time' => (bool) $p->payment_date]));
    }

    protected function quotes(Client $client, int $userId, int $limit): Collection
    {
        return ClientQuoteRequest::where('client_id', $client->id)->where('user_id', $userId)
            ->latest()->limit($limit)->get()
            ->map(fn (ClientQuoteRequest $q) => $this->item($q->created_at, 'site', 'bi-bag-heart', 'pink',
                'Pedido pelo site #' . $q->id, $q->status_label,
                ['value' => $q->quoted_total ? (float) $q->quoted_total : null, 'url' => $this->url('sales.portal-orders', ['pedido' => $q->id])]));
    }

    protected function reviews(Client $client, int $userId, int $limit): Collection
    {
        return PortalReview::with('product:id,name')->where('user_id', $userId)->where('client_id', $client->id)
            ->latest()->limit($limit)->get()
            ->map(fn (PortalReview $r) => $this->item($r->created_at, 'site', 'bi-star-fill', 'amber',
                'Avaliou com ' . $r->rating . ' ' . ($r->rating == 1 ? 'estrela' : 'estrelas'),
                trim(($r->product?->name ?? '') . ($r->comment ? ': "' . \Illuminate\Support\Str::limit($r->comment, 120) . '"' : '')),
                ['url' => $this->url('promotions.reviews', [])]));
    }

    protected function alerts(Client $client, int $userId, int $limit): Collection
    {
        return ClientProductAlert::with('product:id,name')->where('user_id', $userId)->where('client_id', $client->id)
            ->latest()->limit($limit)->get()
            ->map(fn (ClientProductAlert $a) => $this->item($a->created_at, 'site', 'bi-bell', 'violet',
                'Pediu aviso do produto', $a->product?->name));
    }

    protected function carts(Client $client, int $userId, int $limit): Collection
    {
        if (! Schema::hasTable('portal_carts')) {
            return collect();
        }

        return PortalCart::where('user_id', $userId)->where('client_id', $client->id)
            ->whereNotNull('reminded_at')->latest('reminded_at')->limit($limit)->get()
            ->map(fn (PortalCart $c) => $this->item(Carbon::parse($c->reminded_at), 'site', 'bi-cart-x', 'orange',
                $c->recovered_at ? 'Carrinho recuperado' : 'Deixou produtos no carrinho', null,
                ['value' => $c->total ? (float) $c->total : null, 'url' => $this->url('promotions.carts', [])]));
    }

    protected function promotions(Client $client, int $userId, int $limit): Collection
    {
        return PromotionSend::where('user_id', $userId)->where('client_id', $client->id)
            ->latest()->limit($limit)->get()
            ->map(function (PromotionSend $s) {
                $n = count($s->promotion_ids ?? []) ?: 1;

                return $this->item($s->created_at, 'mensagens', 'bi-megaphone', 'fuchsia', 'Promoção enviada',
                    ($n > 1 ? $n . ' promoções' : '1 promoção') . ($s->channel ? ' · ' . ucfirst($s->channel) : ''));
            });
    }

    protected function reminders(Client $client, int $userId, int $limit): Collection
    {
        return PaymentReminder::where('user_id', $userId)->where('client_id', $client->id)
            ->whereNotNull('sent_at')->latest('sent_at')->limit($limit)->get()
            ->map(fn (PaymentReminder $r) => $this->item($r->sent_at, 'mensagens', 'bi-chat-left-dots', 'sky', 'Cobrança enviada',
                $r->channel ? ucfirst($r->channel) : null));
    }
}
