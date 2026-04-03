<?php

namespace App\Livewire\Dashboard;

use App\Models\Client;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class DashboardClientes extends Component
{
    public int $totalClientes = 0;
    public int $clientesNovosMes = 0;
    public int $clientesInadimplentes = 0;
    public int $clientesAniversariantes = 0;
    public int $clientesComCompraMes = 0;
    public int $clientesRecorrentesCount = 0;
    public int $clientesInativosCount = 0;
    public float $receitaClientesMes = 0;
    public float $ticketMedioClientes = 0;
    public float $taxaRetencao = 0;
    public array $topClientes = [];
    public array $clientesPendentes = [];
    public array $clientesRecentes = [];
    public array $clientesRecorrentes = [];
    public array $clientesInativos = [];
    public array $aniversariantesLista = [];
    public array $atividadeRecente = [];
    public string $periodLabel = '';

    public function mount()
    {
        $userId = Auth::id();
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        $inactiveLimit = now()->copy()->subMonths(6);

        $this->periodLabel = ucfirst(Carbon::now()->locale('pt_BR')->translatedFormat('F/Y'));
        $this->totalClientes = Client::where('user_id', $userId)->count();

        $this->clientesNovosMes = Client::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        $this->receitaClientesMes = (float) Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('total_price');

        $salesMonthCount = (int) Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        $this->ticketMedioClientes = $salesMonthCount > 0
            ? $this->receitaClientesMes / $salesMonthCount
            : 0;

        $this->clientesComCompraMes = (int) Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->distinct('client_id')
            ->count('client_id');

        $pendingClients = Client::where('user_id', $userId)
            ->whereHas('sales', function ($query) use ($userId) {
                $query->where('status', 'pendente')->where('user_id', $userId);
            })
            ->with(['sales' => function ($query) use ($userId) {
                $query->where('status', 'pendente')->where('user_id', $userId)->with('payments');
            }])
            ->orderByDesc('created_at')
            ->get();

        $this->clientesInadimplentes = $pendingClients->count();
        $this->clientesPendentes = $pendingClients
            ->take(6)
            ->map(function (Client $client) {
                $pendingValue = $client->sales->sum(fn ($sale) => (float) $sale->remaining_amount);

                return [
                    'name' => $client->name,
                    'sales_count' => $client->sales->count(),
                    'pending_value' => $pendingValue,
                    'email' => $client->email,
                    'phone' => $client->phone,
                ];
            })
            ->values()
            ->all();

        $topClients = Sale::select('client_id', DB::raw('SUM(total_price) as total_vendas'), DB::raw('COUNT(*) as qtd_vendas'))
            ->where('user_id', $userId)
            ->with('client')
            ->groupBy('client_id')
            ->orderByDesc('total_vendas')
            ->limit(6)
            ->get();

        $this->topClientes = $topClients
            ->map(fn ($item) => [
                'name' => data_get($item, 'client.name', 'Cliente'),
                'total_vendas' => (float) ($item->total_vendas ?? 0),
                'qtd_vendas' => (int) ($item->qtd_vendas ?? 0),
            ])
            ->values()
            ->all();

        $recurringClients = Sale::select('client_id', DB::raw('SUM(total_price) as total_vendas'), DB::raw('COUNT(*) as qtd_vendas'))
            ->where('user_id', $userId)
            ->with('client')
            ->groupBy('client_id')
            ->having('qtd_vendas', '>', 2)
            ->orderByDesc('qtd_vendas')
            ->limit(6)
            ->get();

        $this->clientesRecorrentesCount = $recurringClients->count();
        $this->clientesRecorrentes = $recurringClients
            ->map(fn ($item) => [
                'name' => data_get($item, 'client.name', 'Cliente'),
                'total_vendas' => (float) ($item->total_vendas ?? 0),
                'qtd_vendas' => (int) ($item->qtd_vendas ?? 0),
            ])
            ->values()
            ->all();

        $this->taxaRetencao = $this->totalClientes > 0
            ? ($this->clientesRecorrentesCount / $this->totalClientes) * 100
            : 0;

        $inactiveClients = Client::where('user_id', $userId)
            ->whereDoesntHave('sales', function ($query) use ($userId, $inactiveLimit) {
                $query->where('user_id', $userId)->where('created_at', '>=', $inactiveLimit);
            })
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $this->clientesInativosCount = $inactiveClients->count();
        $this->clientesInativos = $inactiveClients
            ->map(fn (Client $client) => [
                'name' => $client->name,
                'created_at' => optional($client->created_at)?->format('d/m/Y'),
                'email' => $client->email,
            ])
            ->values()
            ->all();

        $this->clientesRecentes = Client::where('user_id', $userId)
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(fn (Client $client) => [
                'name' => $client->name,
                'created_at' => optional($client->created_at)?->format('d/m/Y'),
                'email' => $client->email,
                'phone' => $client->phone,
            ])
            ->values()
            ->all();

        $birthdayColumn = collect(['birth_date', 'date_of_birth', 'data_nascimento', 'nascimento'])
            ->first(fn ($column) => Schema::hasColumn('clients', $column));

        if ($birthdayColumn) {
            $birthdays = Client::where('user_id', $userId)
                ->whereMonth($birthdayColumn, now()->month)
                ->orderByRaw('DAY(' . $birthdayColumn . ') asc')
                ->limit(6)
                ->get();

            $this->clientesAniversariantes = $birthdays->count();
            $this->aniversariantesLista = $birthdays
                ->map(fn (Client $client) => [
                    'name' => $client->name,
                    'date' => Carbon::parse($client->{$birthdayColumn})->format('d/m'),
                ])
                ->values()
                ->all();
        }

        $this->atividadeRecente = Sale::where('user_id', $userId)
            ->with('client')
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(fn ($sale) => [
                'client' => data_get($sale, 'client.name', 'Cliente'),
                'value' => (float) ($sale->total_price ?? 0),
                'status' => (string) ($sale->status ?? 'pendente'),
                'date' => optional($sale->created_at)?->format('d/m/Y H:i'),
            ])
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-clientes', [
            'totalClientes' => $this->totalClientes,
            'clientesNovosMes' => $this->clientesNovosMes,
            'clientesInadimplentes' => $this->clientesInadimplentes,
            'clientesAniversariantes' => $this->clientesAniversariantes,
            'clientesComCompraMes' => $this->clientesComCompraMes,
            'clientesRecorrentesCount' => $this->clientesRecorrentesCount,
            'clientesInativosCount' => $this->clientesInativosCount,
            'receitaClientesMes' => $this->receitaClientesMes,
            'ticketMedioClientes' => $this->ticketMedioClientes,
            'taxaRetencao' => $this->taxaRetencao,
            'topClientes' => $this->topClientes,
            'clientesPendentes' => $this->clientesPendentes,
            'clientesRecentes' => $this->clientesRecentes,
            'clientesRecorrentes' => $this->clientesRecorrentes,
            'clientesInativos' => $this->clientesInativos,
            'aniversariantesLista' => $this->aniversariantesLista,
            'atividadeRecente' => $this->atividadeRecente,
            'periodLabel' => $this->periodLabel,
        ]);
    }
}
