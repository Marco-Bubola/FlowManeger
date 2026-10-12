<?php

namespace App\Livewire\Gestao;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\VendaParcela;
use App\Services\Sales\SaleInstallmentService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Contas a receber: o que cada cliente ainda deve, por cliente ou por compra.
 *
 * O saldo de cada venda é sempre total − pago (pagamentos registrados), não a
 * soma das parcelas: o cliente quase sempre paga mais ou menos que a parcela.
 * As parcelas só dão a data do próximo vencimento.
 */
class Receivables extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'clientes')]
    public string $view = 'clientes'; // clientes | compras

    #[Url(as: 'venc', except: 'todas')]
    public string $filter = 'todas'; // todas | vencidas | semana | mes | sem

    #[Url(as: 'ordem', except: 'divida')]
    public string $sort = 'divida'; // divida | atraso | nome | recente

    #[Url(as: 'periodo', except: '')]
    public string $period = ''; // '' | 30 | 90 | 180 | 365 (idade da venda em dias)

    #[Url(as: 'min', except: '')]
    public string $minValue = '';

    // Janela de receber
    public bool $showReceive = false;
    public string $receiveType = 'sale'; // sale | client
    public ?int $receiveId = null;
    public string $receiveAmount = '';
    public string $receiveMethod = 'pix';
    public string $receiveDate = '';

    // Janela de perdoar saldo
    public ?int $forgiveSaleId = null;

    public const METHODS = [
        'pix' => ['PIX', 'bi-lightning-charge'],
        'dinheiro' => ['Dinheiro', 'bi-cash'],
        'cartao_debito' => ['Débito', 'bi-credit-card-2-front'],
        'cartao_credito' => ['Crédito', 'bi-credit-card'],
    ];

    public function clearFilters(): void
    {
        $this->reset('search', 'filter', 'period', 'minValue');
    }

    /** Vendas em aberto do usuário, já com saldo e próximo vencimento. */
    private function openSales(): Collection
    {
        $today = now()->startOfDay();

        $sales = Sale::query()
            ->where('user_id', Auth::id())
            ->whereNotIn('status', ['cancelada', 'orcamento'])
            ->with(['client:id,name,phone,caminho_foto'])
            ->withSum(['payments as paid_sum' => fn ($q) => $q->where('payment_method', '<>', 'desconto')], 'amount_paid')
            ->withCount('saleItems')
            ->get();

        $nextDue = VendaParcela::query()
            ->whereIn('sale_id', $sales->where('tipo_pagamento', 'parcelado')->pluck('id'))
            ->where('status', '!=', 'paga')
            ->orderBy('data_vencimento')
            ->get(['sale_id', 'data_vencimento', 'valor', 'numero_parcela'])
            ->groupBy('sale_id');

        return $sales->map(function (Sale $s) use ($nextDue, $today) {
            $total = (float) $s->total_price;
            $paid = max((float) $s->paid_sum, (float) $s->amount_paid);
            $remaining = round($total - $paid, 2);
            if ($remaining <= 0.009) {
                return null;
            }

            $pending = $nextDue->get($s->id);
            $first = $pending?->first();
            // Só venda parcelada tem vencimento; venda sem parcelas fica "sem vencimento".
            $due = $first?->data_vencimento ? Carbon::parse($first->data_vencimento)->startOfDay() : null;

            return (object) [
                'sale' => $s,
                'client' => $s->client,
                'total' => $total,
                'paid' => $paid,
                'remaining' => $remaining,
                'pct' => $total > 0 ? (int) min(100, round($paid / $total * 100)) : 0,
                'due' => $due,
                'overdue' => $due && $due->lt($today),
                'late' => $due && $due->lt($today) ? (int) $due->diffInDays($today) : 0,
                'parcelado' => $s->tipo_pagamento === 'parcelado',
                'pendingParcels' => $pending?->count() ?? 0,
                'parcelsTotal' => (int) $s->parcelas,
                'nextParcelValue' => $first ? (float) $first->valor : null,
                'date' => $s->created_at,
            ];
        })->filter()->values();
    }

    private function applyFilters(Collection $rows): Collection
    {
        $today = now()->startOfDay();
        $term = mb_strtolower(trim($this->search));
        $digits = preg_replace('/\D/', '', $this->search);
        $min = (float) str_replace(',', '.', str_replace('.', '', $this->minValue));

        return $rows->filter(function ($r) use ($today, $term, $digits, $min) {
            if ($term !== '') {
                $name = mb_strtolower($r->client->name ?? 'cliente não informado');
                $phone = preg_replace('/\D/', '', $r->client->phone ?? '');
                $hit = str_contains($name, $term)
                    || ($digits !== '' && (str_contains($phone, $digits) || (string) $r->sale->id === $digits));
                if (! $hit) {
                    return false;
                }
            }
            if ($min > 0 && $r->remaining < $min) {
                return false;
            }
            if ($this->period !== '' && $r->date && $r->date->lt(now()->subDays((int) $this->period))) {
                return false;
            }

            return match ($this->filter) {
                'vencidas' => $r->overdue,
                'semana' => $r->due && $r->due->between($today, $today->copy()->addDays(7)),
                'mes' => $r->due && $r->due->between($today, $today->copy()->endOfMonth()),
                'sem' => ! $r->parcelado,
                default => true,
            };
        })->values();
    }

    private function groupByClient(Collection $rows): Collection
    {
        return $rows->groupBy(fn ($r) => $r->client->id ?? 0)->map(function ($sales, $key) {
            $sales = $sales->sortBy(fn ($r) => $r->due?->timestamp ?? PHP_INT_MAX)->values();

            return (object) [
                'key' => $key,
                'client' => $sales->first()->client,
                'sales' => $sales,
                'total' => $sales->sum('total'),
                'paid' => $sales->sum('paid'),
                'remaining' => round($sales->sum('remaining'), 2),
                'overdueValue' => round($sales->where('overdue', true)->sum('remaining'), 2),
                'late' => $sales->max('late'),
                'oldestDue' => $sales->first()->due,
            'oldestDate' => $sales->min(fn ($r) => $r->date?->timestamp),
                'lastDate' => $sales->max(fn ($r) => $r->date?->timestamp ?? 0),
            ];
        })->values();
    }

    private function sortRows(Collection $rows, bool $groups): Collection
    {
        $name = fn ($r) => mb_strtolower($r->client->name ?? 'zzz');

        return (match ($this->sort) {
            // Mais atrasado: vencimento mais antigo; sem vencimento vai pela data da compra.
            'atraso' => $rows->sortBy(fn ($r) => $groups
                ? $r->sales->min(fn ($x) => ($x->due ?? $x->date)?->timestamp ?? PHP_INT_MAX)
                : (($r->due ?? $r->date)?->timestamp ?? PHP_INT_MAX)),
            'nome' => $rows->sortBy($name),
            'recente' => $rows->sortByDesc(fn ($r) => $groups ? $r->lastDate : ($r->date?->timestamp ?? 0)),
            default => $rows->sortByDesc('remaining'),
        })->values();
    }

    // ── Receber ─────────────────────────────────────────────────────────

    public function openReceive(string $type, int $id, bool $full = false): void
    {
        $this->resetErrorBag();
        $this->receiveType = $type === 'client' ? 'client' : 'sale';
        $this->receiveId = $id;
        $this->receiveMethod = 'pix';
        $this->receiveDate = now()->format('Y-m-d');
        $owed = $this->owedFor($this->receiveType, $id);
        $this->receiveAmount = $full ? number_format($owed, 2, ',', '.') : '';
        $this->showReceive = true;
    }

    public function closeReceive(): void
    {
        $this->showReceive = false;
        $this->receiveId = null;
    }

    public function fillAll(): void
    {
        $this->receiveAmount = number_format($this->owedFor($this->receiveType, (int) $this->receiveId), 2, ',', '.');
    }

    /** Vendas em aberto do alvo (uma venda ou todas do cliente), mais antiga primeiro. */
    private function targetRows(string $type, int $id): Collection
    {
        return $this->openSales()
            ->filter(fn ($r) => $type === 'client' ? (int) ($r->client->id ?? 0) === $id : $r->sale->id === $id)
            ->sortBy(fn ($r) => $r->due?->timestamp ?? PHP_INT_MAX)
            ->values();
    }

    private function owedFor(string $type, int $id): float
    {
        return round($this->targetRows($type, $id)->sum('remaining'), 2);
    }

    private function parseMoney(string $value): float
    {
        $v = preg_replace('/[^0-9,\.]/', '', $value);
        if (str_contains($v, ',')) {
            $v = str_replace(',', '.', str_replace('.', '', $v));
        }

        return round((float) $v, 2);
    }

    public function confirmReceive(): void
    {
        $amount = $this->parseMoney($this->receiveAmount);
        $this->validate([
            'receiveMethod' => 'required|in:' . implode(',', array_keys(self::METHODS)),
            'receiveDate' => 'required|date',
        ], [
            'receiveDate.required' => 'Informe a data do pagamento.',
        ]);

        $rows = $this->targetRows($this->receiveType, (int) $this->receiveId);
        $owed = round($rows->sum('remaining'), 2);

        if ($amount <= 0) {
            $this->addError('receiveAmount', 'Informe quanto o cliente pagou.');
            return;
        }
        if ($amount - $owed > 0.009) {
            $this->addError('receiveAmount', 'O valor passa do que falta receber (R$ ' . number_format($owed, 2, ',', '.') . ').');
            return;
        }

        $left = $amount;
        $touched = 0;
        DB::transaction(function () use ($rows, &$left, &$touched) {
            foreach ($rows as $r) {
                if ($left <= 0.009) {
                    break;
                }
                $part = round(min($left, $r->remaining), 2);
                $this->registerPayment($r->sale, $part, $this->receiveMethod, $this->receiveDate);
                $left = round($left - $part, 2);
                $touched++;
            }
        });

        $this->closeReceive();
        $msg = 'Recebido R$ ' . number_format($amount, 2, ',', '.');
        if ($touched > 1) {
            $msg .= ', abatido em ' . $touched . ' compras (da mais antiga para a mais nova)';
        }
        session()->flash('success', $msg . '.');
    }

    private function registerPayment(Sale $sale, float $amount, string $method, string $date): void
    {
        abort_unless((int) $sale->user_id === (int) Auth::id(), 403);

        SalePayment::create([
            'sale_id' => $sale->id,
            'amount_paid' => $amount,
            'payment_method' => $method,
            'payment_date' => $date,
        ]);

        app(SaleInstallmentService::class)->reconcile($sale, $date);
        $this->refreshSaleTotals($sale);
    }

    private function refreshSaleTotals(Sale $sale): void
    {
        $sale->refresh();
        $paid = (float) $sale->payments()->where('payment_method', '<>', 'desconto')->sum('amount_paid');
        $sale->amount_paid = $paid;
        $sale->status = $paid + 0.009 >= (float) $sale->total_price ? 'pago' : 'pendente';
        $sale->save();
        $sale->applyStockIfPaid();
    }

    // ── Perdoar saldo (desconto do que falta) ───────────────────────────

    public function askForgive(int $saleId): void
    {
        $this->forgiveSaleId = $saleId;
    }

    public function cancelForgive(): void
    {
        $this->forgiveSaleId = null;
    }

    public function forgive(): void
    {
        $row = $this->targetRows('sale', (int) $this->forgiveSaleId)->first();
        $this->forgiveSaleId = null;
        if (! $row) {
            return;
        }
        $sale = $row->sale;
        abort_unless((int) $sale->user_id === (int) Auth::id(), 403);

        DB::transaction(function () use ($sale, $row) {
            SalePayment::create([
                'sale_id' => $sale->id,
                'amount_paid' => $row->remaining,
                'payment_method' => 'desconto',
                'payment_date' => now()->format('Y-m-d'),
            ]);
            $sale->total_price = max(0, (float) $sale->total_price - $row->remaining);
            $sale->save();
            if ($sale->tipo_pagamento === 'parcelado') {
                VendaParcela::where('sale_id', $sale->id)->where('status', '!=', 'paga')
                    ->update(['status' => 'paga', 'pago_em' => now()->format('Y-m-d')]);
            }
            $this->refreshSaleTotals($sale);
        });

        session()->flash('success', 'Saldo de R$ ' . number_format($row->remaining, 2, ',', '.') . ' da venda #' . $sale->id . ' perdoado (lançado como desconto).');
    }

    // ── Exportar ────────────────────────────────────────────────────────

    public function export()
    {
        $rows = $this->sortRows($this->applyFilters($this->openSales()), false);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Cliente', 'Telefone', 'Venda', 'Data da venda', 'Total', 'Pago', 'Falta', 'Vencimento', 'Dias em atraso'], ';');
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->client->name ?? 'Cliente não informado',
                    $r->client->phone ?? '',
                    $r->sale->id,
                    $r->date?->format('d/m/Y'),
                    number_format($r->total, 2, ',', ''),
                    number_format($r->paid, 2, ',', ''),
                    number_format($r->remaining, 2, ',', ''),
                    $r->due?->format('d/m/Y'),
                    $r->late,
                ], ';');
            }
            fclose($out);
        }, 'a-receber-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        $today = now()->startOfDay();
        $all = $this->openSales();
        $rows = $this->applyFilters($all);

        $list = $this->view === 'compras'
            ? $this->sortRows($rows, false)
            : $this->sortRows($this->groupByClient($rows), true);

        $receiveTarget = null;
        if ($this->showReceive && $this->receiveId !== null) {
            $t = $this->targetRows($this->receiveType, $this->receiveId);
            $receiveTarget = (object) [
                'name' => $t->first()->client->name ?? 'Cliente não informado',
                'owed' => round($t->sum('remaining'), 2),
                'count' => $t->count(),
                'saleId' => $this->receiveType === 'sale' ? $this->receiveId : null,
            ];
        }

        return view('livewire.gestao.receivables', [
            'list' => $list,
            'today' => $today,
            'shownTotal' => round($rows->sum('remaining'), 2),
            'shownCount' => $rows->count(),
            'receiveTarget' => $receiveTarget,
            'forgiveRow' => $this->forgiveSaleId ? $all->first(fn ($r) => $r->sale->id === $this->forgiveSaleId) : null,
            'filtersOn' => $this->search !== '' || $this->filter !== 'todas' || $this->period !== '' || $this->minValue !== '',
            'totals' => [
                'all' => round($all->sum('remaining'), 2),
                'overdue' => round($all->where('overdue', true)->sum('remaining'), 2),
                'week' => round($all->filter(fn ($r) => $r->due && $r->due->between($today, $today->copy()->addDays(7)))->sum('remaining'), 2),
                'clients' => $all->groupBy(fn ($r) => $r->client->id ?? 0)->count(),
                'sales' => $all->count(),
                'overdueClients' => $all->where('overdue', true)->groupBy(fn ($r) => $r->client->id ?? 0)->count(),
            ],
        ]);
    }
}
