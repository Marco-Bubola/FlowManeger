<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ClientMetrics extends Component
{
    public Client $client;
    public $clienteId;
    
    // Métricas avançadas
    public $frequenciaCompras = [];
    public $sazonalidade = [];
    public $tendencias = [];
    public $previsaoProximaCompra = null;
    public $scoreFidelidade = 0;
    public $categoriaPreferida = null;
    public $horarioPreferido = null;
    public $diasSemanaPreferidos = [];
    
    public function mount($clienteId)
    {
        $this->clienteId = $clienteId;
        $this->client = Client::where('user_id', Auth::id())->findOrFail($clienteId);
        $this->calculateMetrics();
    }
    
    private function calculateMetrics()
    {
        $this->calculateFrequenciaCompras();
        $this->calculateSazonalidade();
        $this->calculateTendencias();
        $this->calculatePrevisaoProximaCompra();
        $this->calculateScoreFidelidade();
        $this->calculateCategoriaPreferida();
        $this->calculateHorarioPreferido();
        $this->calculateDiasSemanaPreferidos();
    }
    
    private function calculateFrequenciaCompras()
    {
        $vendas = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->orderBy('created_at')
            ->get();
            
        if ($vendas->count() < 2) {
            $this->frequenciaCompras = ['intervalo_medio' => 0, 'regularidade' => 'Insuficiente'];
            return;
        }
        
        $intervalos = [];
        for ($i = 1; $i < $vendas->count(); $i++) {
            $intervalo = Carbon::parse($vendas[$i]->created_at)
                ->diffInDays(Carbon::parse($vendas[$i-1]->created_at));
            $intervalos[] = $intervalo;
        }
        
        $intervaloMedio = array_sum($intervalos) / count($intervalos);
        $desvio = $this->calculateStandardDeviation($intervalos);
        
        $regularidade = 'Irregular';
        if ($desvio < $intervaloMedio * 0.3) {
            $regularidade = 'Muito Regular';
        } elseif ($desvio < $intervaloMedio * 0.5) {
            $regularidade = 'Regular';
        } elseif ($desvio < $intervaloMedio * 0.8) {
            $regularidade = 'Moderadamente Regular';
        }
        
        $this->frequenciaCompras = [
            'intervalo_medio' => round($intervaloMedio),
            'regularidade' => $regularidade,
            'desvio_padrao' => round($desvio, 2)
        ];
    }
    
    private function calculateSazonalidade()
    {
        $vendasPorMes = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->select(
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('COUNT(*) as total_vendas'),
                DB::raw('SUM(total_price) as total_valor')
            )
            ->groupBy('mes')
            ->get();
            
        $this->sazonalidade = $vendasPorMes->map(function ($item) {
            return [
                'mes' => Carbon::create()->month($item->mes)->translatedFormat('F'),
                'vendas' => $item->total_vendas,
                'valor' => $item->total_valor
            ];
        })->toArray();
    }
    
    private function calculateTendencias()
    {
        $vendasUltimos12Meses = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('YEAR(created_at) as ano'),
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('COUNT(*) as total_vendas'),
                DB::raw('SUM(total_price) as total_valor')
            )
            ->groupBy('ano', 'mes')
            ->orderBy('ano')
            ->orderBy('mes')
            ->get();
            
        if ($vendasUltimos12Meses->count() >= 3) {
            $valores = $vendasUltimos12Meses->pluck('total_valor')->toArray();
            $tendencia = $this->calculateTrend($valores);
            
            $this->tendencias = [
                'tendencia' => $tendencia > 0.1 ? 'Crescente' : ($tendencia < -0.1 ? 'Decrescente' : 'Estável'),
                'percentual' => round($tendencia * 100, 1),
                'dados' => $vendasUltimos12Meses->toArray()
            ];
        }
    }
    
    private function calculatePrevisaoProximaCompra()
    {
        if ($this->frequenciaCompras['intervalo_medio'] > 0) {
            $ultimaCompra = Sale::where('client_id', $this->client->id)
                ->where('user_id', Auth::id())
                ->latest()
                ->first();
                
            if ($ultimaCompra) {
                $proximaCompra = Carbon::parse($ultimaCompra->created_at)
                    ->addDays($this->frequenciaCompras['intervalo_medio']);
                    
                $this->previsaoProximaCompra = [
                    'data' => $proximaCompra->format('d/m/Y'),
                    'dias_restantes' => now()->diffInDays($proximaCompra, false),
                    'probabilidade' => $this->calculateProbabilidade()
                ];
            }
        }
    }
    
    private function calculateScoreFidelidade()
    {
        $totalVendas = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->count();
            
        $valorTotal = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->sum('total_price');
            
        $diasComoCliente = $this->client->created_at->diffInDays(now());
        $recencia = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first()
            ?->created_at
            ?->diffInDays(now()) ?? 365;
            
        // Score baseado em RFM (Recency, Frequency, Monetary)
        $scoreRecencia = max(0, 100 - ($recencia * 2)); // Penaliza recência alta
        $scoreFrequencia = min(100, ($totalVendas / max(1, $diasComoCliente)) * 3650); // Vendas por ano
        $scoreMonetario = min(100, $valorTotal / 100); // R$ 100 = 1 ponto
        
        $this->scoreFidelidade = round(($scoreRecencia + $scoreFrequencia + $scoreMonetario) / 3);
    }
    
    private function calculateCategoriaPreferida()
    {
        $categorias = SaleItem::whereHas('sale', function ($query) {
                $query->where('client_id', $this->client->id)
                    ->where('user_id', Auth::id());
            })
            ->whereHas('product.category')
            ->with('product.category')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantidade'))
            ->groupBy('product_id')
            ->get()
            ->groupBy('product.category.name')
            ->map(function ($items) {
                return $items->sum('total_quantidade');
            })
            ->sortByDesc(function ($quantidade) {
                return $quantidade;
            })
            ->first();
            
        $this->categoriaPreferida = $categorias ? array_key_first($categorias->toArray()) : null;
    }
    
    private function calculateHorarioPreferido()
    {
        $horarios = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->select(DB::raw('HOUR(created_at) as hora'), DB::raw('COUNT(*) as total'))
            ->groupBy('hora')
            ->orderBy('total', 'desc')
            ->first();
            
        if ($horarios) {
            $this->horarioPreferido = [
                'hora' => $horarios->hora,
                'periodo' => $this->getPeriodoDoDia($horarios->hora),
                'total_vendas' => $horarios->total
            ];
        }
    }
    
    private function calculateDiasSemanaPreferidos()
    {
        $this->diasSemanaPreferidos = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->select(
                DB::raw('DAYOFWEEK(created_at) as dia_semana'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('dia_semana')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'dia' => $this->getDiaSemana($item->dia_semana),
                    'total' => $item->total
                ];
            })
            ->toArray();
    }
    
    private function calculateStandardDeviation($array)
    {
        $mean = array_sum($array) / count($array);
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $array)) / count($array);
        
        return sqrt($variance);
    }
    
    private function calculateTrend($values)
    {
        $n = count($values);
        if ($n < 2) return 0;
        
        $x = range(1, $n);
        $xy = array_map(function($i) use ($values, $x) {
            return $x[$i] * $values[$i];
        }, range(0, $n-1));
        
        $slope = (($n * array_sum($xy)) - (array_sum($x) * array_sum($values))) /
                 (($n * array_sum(array_map(function($xi) { return $xi * $xi; }, $x))) - pow(array_sum($x), 2));
                 
        return $slope / array_sum($values) * $n; // Normalizado
    }
    
    private function calculateProbabilidade()
    {
        $regularidade = $this->frequenciaCompras['regularidade'];
        
        switch ($regularidade) {
            case 'Muito Regular':
                return 90;
            case 'Regular':
                return 75;
            case 'Moderadamente Regular':
                return 60;
            default:
                return 40;
        }
    }
    
    private function getPeriodoDoDia($hora)
    {
        if ($hora >= 6 && $hora < 12) return 'Manhã';
        if ($hora >= 12 && $hora < 18) return 'Tarde';
        if ($hora >= 18 && $hora < 24) return 'Noite';
        return 'Madrugada';
    }
    
    private function getDiaSemana($dia)
    {
        $dias = [
            1 => 'Domingo',
            2 => 'Segunda-feira',
            3 => 'Terça-feira',
            4 => 'Quarta-feira',
            5 => 'Quinta-feira',
            6 => 'Sexta-feira',
            7 => 'Sábado'
        ];
        
        return $dias[$dia] ?? 'N/A';
    }
    
    public function render()
    {
        return view('livewire.clients.client-metrics');
    }
}
